<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Property;
use App\Models\RealEstateUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $category = $request->query('category');
            $excludedPropertyId = config('app.home_excluded_property_id');
            $properties = Property::discoverable()->with('images')->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->when($excludedPropertyId > 0, fn ($query) => $query->whereKeyNot($excludedPropertyId))
                ->when(is_string($category) && isset(Property::CATEGORIES[$category]),
                    fn ($query) => $query->where('category', $category))
                ->latest()->limit(6)->get();
            $favoriteIds = $request->user()?->role === 'tenant'
                ? $request->user()->favoriteProperties()->pluck('properties.id')->all()
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
        ];

        if (! $agent instanceof RealEstateUser || $agent->role !== 'agent') {
            return view('owner.dashboard', $data);
        }

        $profile = $agent->agentProfile;
        $name = trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? ''));
        $properties = $agent->properties();
        $bookings = Booking::whereHas('property', fn ($query) => $query->where('agent_id', $agent->id));
        $pendingBookings = (clone $bookings)->where('status', 'pending');

        return view('owner.dashboard', [
            'agentName' => $name ?: $agent->email,
            'verificationStatus' => $profile?->verification_status,
            'totalProperties' => (clone $properties)->count(),
            'activeProperties' => (clone $properties)->whereIn('status', ['available', 'partially_rented'])->count(),
            'recentBookingCount' => $bookings->where('created_at', '>=', now()->subDays(30))->count(),
            'pendingBookingCount' => (clone $pendingBookings)->count(),
            'pendingBookings' => $pendingBookings->with(['property', 'tenant.tenantProfile'])
                ->latest()->limit(3)->get(),
            'unreadMessageCount' => $agent->unreadMessageCount(),
            'propertyPreview' => (clone $properties)->latest()->limit(4)->get(['id', 'title']),
            'latestVerifiedProperty' => $properties->where('verification_status', 'verified')
                ->whereNotNull('verified_at')->latest('verified_at')->first(['title', 'verified_at']),
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
            ->get(['title', 'ownership_deed_url'])
            ->filter(fn (Property $property) => $documents->exists($property->ownership_deed_url));
        $pendingVerificationCount = $pendingProperties->count();
        $pendingVerificationPreview = $pendingProperties->take(4);

        $paidCommissions = Commission::where('status', 'paid');
        $totalCommissionRevenue = (clone $paidCommissions)->sum('commission_amount');
        $monthlyCommissionRevenue = [];

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $month = $monthStart->copy()->subMonths($monthsAgo);
            $monthlyCommissionRevenue[] = [
                'label' => $month->format('M'),
                'amount' => (clone $paidCommissions)
                    ->where('paid_at', '>=', $month)
                    ->where('paid_at', '<', $month->copy()->addMonth())
                    ->sum('commission_amount'),
            ];
        }

        $recentPaidCommissions = $paidCommissions->with('agent.agentProfile')
            ->latest('paid_at')->latest('id')->limit(3)->get();

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
