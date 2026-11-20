<?php

namespace App\Http\Controllers;

use App\Models\RealEstateUser;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $users = RealEstateUser::with(['agentProfile', 'tenantProfile'])
            ->whereIn('role', ['tenant', 'agent'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->q . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.users', ['users' => $users]);
    }

    public function show(Request $request, RealEstateUser $user)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        abort_unless(in_array($user->role, ['tenant', 'agent'], true), 404);

        $user->load(['agentProfile', 'tenantProfile']);

        return view('admin.user-show', ['user' => $user]);
    }
}
