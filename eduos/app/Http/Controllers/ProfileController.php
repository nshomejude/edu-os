<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.index');
    }

    /** Self-service password change: current password required. */
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $request->user()->forceFill([
            'password' => Hash::make($data['password']), 'must_change_password' => false,
        ])->save();

        return back()->with('flash', 'Password changed.');
    }

    /** Admin reset: random temporary password, rotation forced at next login. */
    public function resetPassword(\App\Models\User $user)
    {
        $temp = \Illuminate\Support\Str::random(12);
        $user->forceFill(['password' => Hash::make($temp), 'must_change_password' => true])->save();
        \App\Modules\Platform\Models\AuthEvent::log('PASSWORD_RESET', $user->email, $user->id);

        return back()->with('flash', "Temporary password for {$user->email} (shown once): {$temp} — rotation forced at next login.");
    }
}
