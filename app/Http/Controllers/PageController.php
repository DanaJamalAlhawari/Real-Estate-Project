<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\DemoHome;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\RealEstateUser;
final class PageController extends Controller
{
    public function show(string $page): View
    {
        $homes = in_array($page, ['home.index', 'seeker.search', 'seeker.property'], true)
            ? DemoHome::all()
            : [];

        $data = ['homes' => $homes];
        if ($page === 'seeker.search') {
            $ordered = collect($homes)->groupBy(fn (array $home) => (int) $home['featured']);
            $data['featuredHomes'] = $ordered->get(1, collect())->concat($ordered->get(0, collect()));
            $data['defaultFavorites'] = $data['featuredHomes']->whereIn('id', [1, 4]);
        }

        return view($page, $data);
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
