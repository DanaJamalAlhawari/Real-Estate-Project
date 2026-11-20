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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:real_estate_users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'agency_name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $nameParts = explode(' ', trim($data['full_name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $user = DB::transaction(function () use ($data, $firstName, $lastName) {
            $user = RealEstateUser::create([
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
            ]);

            if ($data['role'] === 'agent') {
                $user->agentProfile()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $data['phone'],
                    'agency_name' => $data['agency_name'] ?? null,
                ]);
            } else {
                $user->tenantProfile()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $data['phone'],
                ]);
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectByRole($request->user());
        }

        return back()
            ->withErrors(['email' => 'Email or password are wrong'])
            ->onlyInput('email');
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
