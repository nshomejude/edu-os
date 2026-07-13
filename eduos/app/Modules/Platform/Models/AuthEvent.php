<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

/** AUTH-01 §M: authentication audit stream (login, MFA, lockout, reset, logout). */
class AuthEvent extends Model
{
    protected $fillable = ['user_id', 'email', 'event', 'ip', 'user_agent'];

    public static function log(string $event, string $email, ?int $userId = null): void
    {
        static::create([
            'event' => $event, 'email' => $email, 'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => \Illuminate\Support\Str::limit((string) request()->userAgent(), 190),
        ]);
    }
}
