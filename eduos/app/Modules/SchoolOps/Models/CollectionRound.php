<?php

namespace App\Modules\SchoolOps\Models;

use Illuminate\Database\Eloquent\Model;

/** End-of-year collection round: opens, gathers returns with condition, closes as LOST. */
class CollectionRound extends Model
{
    protected $fillable = [
        'academic_year', 'status', 'opened_by', 'opened_at', 'closed_at',
        'returned_count', 'lost_count',
    ];

    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];
}
