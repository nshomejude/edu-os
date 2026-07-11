<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class CustodyEvent extends Model
{
    protected $fillable = ['shipment_id', 'event_type', 'actor', 'notes', 'occurred_at'];
    protected $casts = ['occurred_at' => 'datetime'];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
