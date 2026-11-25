<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;

class AgentEarningsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === 'agent', 403);

        $commissions = Commission::with('payment')
            ->where('agent_id', $request->user()->id)
            ->whereHas('payment', function ($query) {
                $query->where('status', 'successful');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')->paginate(config('app.pagination_per_page'))->withQueryString();

        $totalEarnings = Commission::where('agent_id', $request->user()->id)
            ->whereHas('payment', function ($query) {
                $query->where('status', 'successful');
            })
            ->sum('agent_net_amount');

        return view('owner.earnings', compact('commissions', 'totalEarnings'));
    }
}
