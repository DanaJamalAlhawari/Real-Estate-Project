<?php

namespace App\Http\Controllers;

use App\Models\RealEstateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'in:tenant,agent'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:real_estate_users,email'],
            'phone' => [
                'required',
                'regex:/^\+[1-9][0-9]{7,14}$/',
                'unique:tenant_profiles,phone',
                'unique:agent_profiles,phone',
            ],
            'owner_type' => ['exclude_unless:role,agent', 'nullable', 'in:individual,agency'],
            'agency_name' => ['exclude_unless:role,agent', 'exclude_unless:owner_type,agency', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = RealEstateUser::create([
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
            ]);

            $profileData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
            ];

            if ($data['role'] === 'agent') {
                $profileData['agency_name'] = $data['agency_name'] ?? null;
                $user->agentProfile()->create($profileData);
            } else {
                $user->tenantProfile()->create($profileData);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Email or password are wrong'])
                ->onlyInput('email');
        }

        $user = $request->user();
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    private function redirectByRole(RealEstateUser $user)
    {
        if ($user->role === 'tenant') {
            return redirect()->route('seeker.search');
        }

        if ($user->role === 'agent') {
            return redirect()->route('owner.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        abort(403);
    }
}
