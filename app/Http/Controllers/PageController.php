<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\DemoHome;
use App\Models\Message;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\RealEstateUser;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;

use App\Models\RealEstateUser;
final class PageController extends Controller
{
    public function show(Request $request, string $page): View
    {
        if ($page === 'owner.dashboard') {
            return $this->ownerDashboard();
        }

        $homes = in_array($page, ['home.index', 'seeker.search', 'seeker.property'], true)
            ? DemoHome::all()
            : [];

        $data = ['homes' => $homes];

        if ($page === 'seeker.search') {
            $ordered = collect($homes)->groupBy(fn(array $home) => (int) $home['featured']);
            $data['featuredHomes'] = $ordered->get(1, collect())->concat($ordered->get(0, collect()));
            $data['defaultFavorites'] = $data['featuredHomes']->whereIn('id', [1, 4]);

            // Dana's code: get available, verified properties
            $data['properties'] = Property::where('status', 'available')
                ->where('verification_status', 'verified')
                ->with('images')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->get();

            // Dana's code: get this user's favorites and favorite IDs
            $data['favorites'] = $user?->favoriteProperties()
                ->with('images')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->get() ?? collect();

            $data['favoriteIds'] = $data['favorites']->pluck('id')->all();
            $data['favoritesCount'] = $data['favorites']->count();
        }

//dana added code 
        if ($page === 'seeker.property' && $request->filled('property_id')) {
    $property = Property::where('verification_status', 'verified')
        ->where('status', '!=', 'hidden')
        ->findOrFail($request->query('property_id'));

    $data['property'] = $property;
    //
}

        return view($page, $data);
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

        if (!$agent instanceof RealEstateUser || $agent->role !== 'agent') {
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
            'unreadMessageCount' => Message::where('is_read', false)
                ->whereHas('conversation', fn ($query) => $query
                    ->where('agent_id', $agent->id)
                    ->whereColumn('conversations.tenant_id', 'messages.sender_id'))
                ->count(),
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
