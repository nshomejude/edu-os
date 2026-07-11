<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_no', 'origin_name', 'destination_name',
        'status', 'books', 'shipped_on',
    ];

    protected $casts = ['shipped_on' => 'date'];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'RECEIVED_FULL', 'CLOSED' => 'Delivered',
            'IN_TRANSIT', 'DISPATCHED', 'LOADED', 'ARRIVED' => 'In Transit',
            'RECEIVED_WITH_DISCREPANCY' => 'Discrepancy',
            'LOST_IN_TRANSIT' => 'Lost',
            default => 'Pending',
        };
    }

    public function statusClass(): string
    {
        return match ($this->statusLabel()) {
            'Delivered' => 'pill-success',
            'In Transit' => 'pill-transit',
            'Discrepancy', 'Lost' => 'pill-error',
            default => 'pill-pending',
        };
    }
}
