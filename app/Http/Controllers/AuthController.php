<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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

            'role' => ['required', 'in:tenat,agent'],
            'full_name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:real_estate_users, email',
            ],

            'phone' => ['required', 'string', 'max:30'],
            'agency_name' => ['nullable', 'string', 'max:255'],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],

            'terms' => ['accepted'],

        ]);

    }

}
