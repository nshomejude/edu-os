<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Atomic mutations (production integrity): every state-changing request runs in a
 * database transaction, so a mid-flight failure can never leave the custody ledger,
 * stock journal or passport chains half-written.
 */
class TransactionalRequests
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        return DB::transaction(fn () => $next($request));
    }
}
