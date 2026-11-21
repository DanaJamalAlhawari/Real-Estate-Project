<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TenantProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        [$view, $updateRoute, $profile] = match ($user->role) {
            'agent' => ['owner.account', 'owner.account.update', $user->agentProfile],
            'tenant' => ['seeker.profile', 'seeker.profile.update', $user->tenantProfile],
            'admin' => ['admin.account', 'admin.account.update', null],
        };

        return view($view, compact('user', 'profile', 'updateRoute'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $rules = [
            'email' => ['required', 'email', 'max:255', Rule::unique('real_estate_users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
        ];

        if ($user->role !== 'admin') {
            $isAgent = $user->role === 'agent';
            $profile = $isAgent ? $user->agentProfile : $user->tenantProfile;
            $phoneRule = Rule::unique($isAgent ? 'agent_profiles' : 'tenant_profiles', 'phone');
            if ($profile) {
                $phoneRule->ignore($profile->id);
            }

            $rules += [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'regex:/^\+[1-9][0-9]{7,14}$/', $phoneRule,
                    Rule::unique($isAgent ? 'tenant_profiles' : 'agent_profiles', 'phone')],
            ];
            $rules += $isAgent
                ? ['agency_name' => ['nullable', 'string', 'max:255'], 'license_number' => ['nullable', 'string', 'max:255']]
                : ['emergency_contact' => ['nullable', 'string', 'max:30'], 'student_id' => ['nullable', 'string', 'max:255']];
        }

        $data = $request->validate($rules);
        DB::transaction(function () use ($data, $user) {
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->save();

            if ($user->role === 'agent') {
                $user->agentProfile()->updateOrCreate([], [
                    'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'phone' => $data['phone'],
                    'agency_name' => $data['agency_name'] ?? null, 'license_number' => $data['license_number'] ?? null,
                ]);
            } elseif ($user->role === 'tenant') {
                $user->tenantProfile()->updateOrCreate([], [
                    'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'phone' => $data['phone'],
                    'emergency_contact' => $data['emergency_contact'] ?? null, 'student_id' => $data['student_id'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Profile updated successfully.');
    }
}
