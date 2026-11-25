<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionRate;
use App\Models\RealEstateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCommissionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return view('admin.commissions', [
            'agents' => RealEstateUser::with('agentProfile')
                ->where('role', 'agent')->orderBy('email')->get(),
            'rates' => CommissionRate::with('agent.agentProfile')->orderByDesc('created_at')->orderByDesc('id')->paginate(config('app.pagination_per_page'), ['*'], 'rates_page')->withQueryString(),
            'commissions' => Commission::with(['agent', 'payment'])
                ->orderByDesc('created_at')->orderByDesc('id')->paginate(config('app.pagination_per_page'), ['*'], 'commissions_page')->withQueryString(),
        ]);
    }

    public function storeRate(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate([
            'agent_id' => ['required', 'exists:real_estate_users,id'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        abort_unless(RealEstateUser::whereKey($data['agent_id'])->where('role', 'agent')->exists(), 422);

        DB::transaction(function () use ($data, $request) {
            if ($request->boolean('active')) {
                CommissionRate::where('agent_id', $data['agent_id'])
                    ->where('active', true)->update(['active' => false]);
            }
            CommissionRate::create([
                'agent_id' => $data['agent_id'],
                'percentage' => $data['percentage'],
                'active' => $request->boolean('active'),
            ]);
        });

        return back()->with('success', __('Commission rate saved.'));
    }

    public function updateRate(Request $request, CommissionRate $rate)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate([
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($rate, $data, $request) {
            if ($request->boolean('active')) {
                CommissionRate::where('agent_id', $rate->agent_id)
                    ->where('active', true)->update(['active' => false]);
            }

            $rate->update([
                'percentage' => $data['percentage'],
                'active' => $request->boolean('active'),
            ]);
        });

        return back()->with('success', __('Commission rate updated.'));
    }

    public function destroyRate(Request $request, CommissionRate $rate)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $rate->delete();

        return back()->with('success', __('Commission rate deleted.'));
    }
}
