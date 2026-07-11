<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class PassportEvent extends Model
{
    protected $fillable = ['print_batch_id', 'event_type', 'location', 'actor', 'occurred_at'];
    protected $casts = ['occurred_at' => 'datetime'];

    public function batch()
    {
        return $this->belongsTo(PrintBatch::class, 'print_batch_id');
    }
}
