<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Property;
use App\Models\PropertyDailyView;
use App\Models\RealEstateUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class PageController extends Controller
{
    public function show(Request $request, string $page): View
    {
        if ($page === 'owner.dashboard') {
            return $this->ownerDashboard();
        }

        if ($page === 'home.index') {
            $filters = $request->validate(['q' => 'nullable|string|max:255']);
            $category = $request->query('category');
            $excludedPropertyId = config('app.home_excluded_property_id');
            $properties = Property::discoverable()->with('images')->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->when($excludedPropertyId > 0, fn ($query) => $query->whereKeyNot($excludedPropertyId))
                ->when(is_string($category) && isset(Property::CATEGORIES[$category]),
                    fn ($query) => $query->where('category', $category))
                ->matchingTitleOrArea($filters['q'] ?? '')
                ->latest()->orderByDesc('id')->paginate(config('app.properties_per_page'))->withQueryString()->fragment('homes');
            $favoriteIds = $request->user()?->role === 'tenant'
                ? $request->user()->favoriteProperties()->whereKey($properties->getCollection()->modelKeys())->pluck('properties.id')->all()
                : [];

            return view($page, compact('properties', 'favoriteIds'));
        }

        return view($page);
    }

    public function showProperty(Request $request): View
    {
        $propertyId = filter_var(
            $request->query('home'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        abort_if($propertyId === false, 404);

        $property = Property::query()
            ->with(['images', 'services', 'agent'])
            ->whereIn('status', ['available', 'partially_rented'])
            ->where('verification_status', 'verified')
            ->findOrFail($propertyId);

        return view('seeker.property', [
            'property' => $property,
            'homes' => [],
        ]);
    }

    private function ownerDashboard(): View
    {
        $agent = request()->user();
        $data = [
            'agentName' => null,
            'verificationStatus' => null,
            'totalProperties' => 0,
            'activeProperties' => 0,
            'recentBookingCount' => 0,
            'pendingBookingCount' => 0,
            'pendingBookings' => collect(),
            'unreadMessageCount' => 0,
            'propertyPreview' => collect(),
            'latestVerifiedProperty' => null,
            'dailyPropertyViews' => [],
        ];

        if (! $agent instanceof RealEstateUser || $agent->role !== 'agent') {
            return view('owner.dashboard', $data);
        }

        $profile = $agent->agentProfile;
        $name = trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? ''));
        $properties = $agent->properties();
        $bookings = Booking::whereHas('property', fn ($query) => $query->where('agent_id', $agent->id));
        $pendingBookings = (clone $bookings)->where('status', 'pending');
        $today = today('Asia/Amman');
        $firstDay = $today->copy()->subDays(13)->toDateString();
        $lastDay = $today->toDateString();
        $dailyCounts = PropertyDailyView::whereIn('property_id', (clone $properties)->select('properties.id'))
            ->whereBetween('viewed_on', [$firstDay, $lastDay])
            ->selectRaw('viewed_on, SUM(views) as total')
            ->groupBy('viewed_on')->pluck('total', 'viewed_on');
        $dailyPropertyViews = [];
        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            $date = $today->copy()->subDays($daysAgo);
            $dailyPropertyViews[] = [
                'label' => $date->translatedFormat('j M'),
                'amount' => (int) ($dailyCounts[$date->toDateString()] ?? 0),
            ];
        }

        return view('owner.dashboard', [
            'agentName' => $name ?: $agent->email,
            'verificationStatus' => $profile?->verification_status,
            'totalProperties' => (clone $properties)->count(),
            'activeProperties' => (clone $properties)->whereIn('status', ['available', 'partially_rented'])->count(),
            'recentBookingCount' => $bookings->where('created_at', '>=', now()->subDays(30))->count(),
            'pendingBookingCount' => (clone $pendingBookings)->count(),
            'pendingBookings' => $pendingBookings->with(['property', 'tenant.tenantProfile'])
                ->latest()->orderByDesc('id')->paginate(config('app.pagination_per_page'), ['*'], 'requests_page')->withQueryString(),
            'unreadMessageCount' => $agent->unreadMessageCount(),
            'propertyPreview' => (clone $properties)
                ->withSum(['dailyViews as period_views' => fn ($query) => $query->whereBetween('viewed_on', [$firstDay, $lastDay])], 'views')
                ->latest()->orderByDesc('id')->paginate(config('app.properties_per_page'), ['*'], 'properties_page')->withQueryString(),
            'dailyPropertyViews' => $dailyPropertyViews,
            'latestVerifiedProperty' => $properties->where('verification_status', 'verified')
                ->whereNotNull('verified_at')->latest('verified_at')->first(['title', 'title_ar', 'title_en', 'verified_at']),
        ]);
    }

    public function legacy(Request $request, string $target): RedirectResponse
    {
        return redirect()->route($target, $request->query());
    }

    public function demoNotice(): RedirectResponse
    {
        return back();
    }

    public function adminDashboard(): View
    {
        $totalUsers = RealEstateUser::count();
        $totalProperties = Property::count();
        $monthStart = now()->startOfMonth();
        $bookingsThisMonth = Booking::where('created_at', '>=', $monthStart)
            ->where('created_at', '<', $monthStart->copy()->addMonth())
            ->count();

        $documents = Storage::disk('local');
        $pendingProperties = Property::where('verification_status', 'pending')
            ->where('ownership_deed_url', 'like', 'verifications/%')
            ->latest()->orderByDesc('id')->get(['id', 'title', 'title_ar', 'title_en', 'ownership_deed_url'])
            ->filter(fn (Property $property) => $documents->exists($property->ownership_deed_url));
        $pendingVerificationCount = $pendingProperties->count();
        $perPage = config('app.properties_per_page');
        $verificationPage = Paginator::resolveCurrentPage('verifications_page');
        $pendingVerificationPreview = new LengthAwarePaginator(
            $pendingProperties->forPage($verificationPage, $perPage),
            $pendingVerificationCount, $perPage, $verificationPage,
            ['path' => request()->url(), 'pageName' => 'verifications_page', 'query' => request()->query()],
        );

        $earnedCommissions = Commission::query()
            ->leftJoin('payments', 'payments.id', '=', 'commissions.payment_id')
            ->where(function ($query): void {
                $query->where('payments.status', 'successful')
                    ->orWhere(fn ($legacy) => $legacy->whereNull('commissions.payment_id')->where('commissions.status', 'paid'));
            });
        $totalCommissionRevenue = (clone $earnedCommissions)->sum('commissions.commission_amount');
        $monthlyCommissionRevenue = [];

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $month = $monthStart->copy()->subMonths($monthsAgo);
            $monthlyCommissionRevenue[] = [
                'label' => $month->translatedFormat('M'),
                'amount' => (clone $earnedCommissions)
                    ->whereRaw('COALESCE(payments.paid_at, commissions.paid_at) >= ?', [$month])
                    ->whereRaw('COALESCE(payments.paid_at, commissions.paid_at) < ?', [$month->copy()->addMonth()])
                    ->sum('commissions.commission_amount'),
            ];
        }

        $recentPaidCommissions = Commission::where('status', 'paid')->with('agent.agentProfile')
            ->latest('paid_at')->latest('id')->paginate(config('app.pagination_per_page'), ['*'], 'commissions_page')->withQueryString();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalProperties',
            'bookingsThisMonth',
            'pendingVerificationCount',
            'pendingVerificationPreview',
            'totalCommissionRevenue',
            'monthlyCommissionRevenue',
            'recentPaidCommissions',
        ));
    }
}
