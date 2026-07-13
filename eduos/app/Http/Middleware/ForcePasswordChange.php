<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Users holding a temporary password must set their own before doing anything else. */
class ForcePasswordChange
{
    private const ALLOWED = ['profile', 'profile.password', 'logout', 'locale'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->must_change_password && ! in_array($request->route()?->getName(), self::ALLOWED)) {
            return redirect()->route('profile')
                ->with('flash_error', __('Your password is temporary — set a new one before continuing.'));
        }

        return $next($request);
    }
}
