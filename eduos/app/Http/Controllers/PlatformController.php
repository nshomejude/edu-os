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

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'academic_year' => 'required|string|max:9',
            'low_stock_threshold' => 'required|integer|min:0',
            'exception_sla_hours' => 'required|integer|min:1|max:720',
            'carton_size' => 'required|integer|min:10|max:200',
        ]);
        \App\Modules\Platform\Models\Setting::put('academic_year', $data['academic_year']);
        \App\Modules\Platform\Models\Setting::put('low_stock_threshold', (string) $data['low_stock_threshold']);
        \App\Modules\Platform\Models\Setting::put('exception_sla_hours', (string) $data['exception_sla_hours']);
        \App\Modules\Platform\Models\Setting::put('carton_size', (string) $data['carton_size']);

        return back()->with('flash', 'System configuration saved.');
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
        // AUTH-01 §H: lockout after 5 failed attempts (per email+IP, 5-minute decay)
        $key = 'login:'.strtolower($credentials['email']).'|'.$request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            \App\Modules\Platform\Models\AuthEvent::log('LOGIN_LOCKOUT', $credentials['email']);
            $wait = \Illuminate\Support\Facades\RateLimiter::availableIn($key);

            return back()->withInput()->withErrors(['email' => "Too many failed attempts — account locked for {$wait}s (AUTH-01)."]);
        }
        $candidate = \App\Models\User::where('email', $credentials['email'])->where('is_active', 1)->first();
        if ($candidate && $candidate->mfa_enabled && \Illuminate\Support\Facades\Hash::check($credentials['password'], $candidate->password)) {
            session(['mfa:pending' => $candidate->id]);

            return redirect()->route('mfa.challenge');
        }
        if (auth()->attempt($credentials + ['is_active' => 1], true)) {
            \Illuminate\Support\Facades\RateLimiter::clear($key);
            \App\Modules\Platform\Models\AuthEvent::log('LOGIN_OK', $credentials['email'], auth()->id());
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 300);
        \App\Modules\Platform\Models\AuthEvent::log('LOGIN_FAIL', $credentials['email']);

        return back()->withInput()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        if (auth()->check()) {
            \App\Modules\Platform\Models\AuthEvent::log('LOGOUT', auth()->user()->email, auth()->id());
        }
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
