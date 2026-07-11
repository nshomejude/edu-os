<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['severity', 'title', 'message', 'link', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];

    public function severityClass(): string
    {
        return match ($this->severity) {
            'CRITICAL' => 'pill-error',
            'WARNING' => 'pill-transit',
            default => 'pill-info',
        };
    }
}
