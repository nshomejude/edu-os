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

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:'.implode(',', \App\Providers\AppServiceProvider::ROLES),
            'ministry' => 'nullable|in:MINEDUB,MINESEC',
            'school_id' => 'nullable|exists:schools,id',
        ]);
        $data['password'] = \Illuminate\Support\Facades\Hash::make('password');
        $user = User::create($data);

        return back()->with('flash', "User {$user->email} created (initial password: password).");
    }

    /** Edit role/ministry/scoping after creation (RBAC scoping was previously write-once). */
    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:'.implode(',', \App\Providers\AppServiceProvider::ROLES),
            'ministry' => 'nullable|in:MINEDUB,MINESEC',
            'school_id' => 'nullable|exists:schools,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);
        $user->update($data);

        return back()->with('flash', "{$user->email} updated: {$data['role']}.");
    }

    public function toggleUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('flash_error', 'You cannot deactivate your own account.');
        }
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('flash', "{$user->email} ".($user->is_active ? 'activated' : 'deactivated').'.');
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
        $candidate = \App\Models\User::where('email', $credentials['email'])->where('is_active', 1)->first();
        if ($candidate && $candidate->mfa_enabled && \Illuminate\Support\Facades\Hash::check($credentials['password'], $candidate->password)) {
            session(['mfa:pending' => $candidate->id]);

            return redirect()->route('mfa.challenge');
        }
        if (auth()->attempt($credentials + ['is_active' => 1], true)) {
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
