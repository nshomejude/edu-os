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
        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('flash', 'Password changed.');
    }

    /** Admin reset: back to the initial password, forcing a change at next login is future work. */
    public function resetPassword(\App\Models\User $user)
    {
        $user->update(['password' => Hash::make('password')]);

        return back()->with('flash', "Password for {$user->email} reset to the initial value.");
    }
}
