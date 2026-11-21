<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $profile = $user->tenantProfile;

        return view('seeker.profile', [
            'user' => $user,
            'profile' => $profile,
            'homes' => [],
        ]);
    }

public function update(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'emergency_contact' => 'nullable|string|max:30',
        'student_id' => 'nullable|string|max:255',
    ]);

    $profile = $request->user()->tenantProfile;

    if ($profile === null) {
        abort(404);
    }

    $profile->first_name = $request->input('first_name');
    $profile->last_name = $request->input('last_name');
    $profile->emergency_contact = $request->input('emergency_contact');
    $profile->student_id = $request->input('student_id');

    $profile->save();

    return redirect()->route('seeker.profile')
        ->with('success', 'Profile updated successfully.');
}

}