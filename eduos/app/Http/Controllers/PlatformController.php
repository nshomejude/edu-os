<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Platform\Models\Alert;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function alerts()
    {
        return view('alerts.index', [
            'alerts' => Alert::orderByRaw('read_at is not null')->orderByDesc('created_at')->paginate(15),
        ]);
    }

    public function markRead(Alert $alert)
    {
        $alert->update(['read_at' => now()]);

        return back()->with('flash', 'Alert marked as read.');
    }

    public function markAllRead()
    {
        Alert::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('flash', 'All alerts marked as read.');
    }

    public function users()
    {
        return view('users.index', ['users' => User::orderBy('name')->get()]);
    }

    public function settings()
    {
        return view('settings.index');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (auth()->attempt($credentials, true)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withInput()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
