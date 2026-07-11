<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_no', 'origin_name', 'destination_name',
        'status', 'books', 'shipped_on',
        'origin_warehouse_id', 'destination_school_id', 'textbook_title_id', 'received_books',
    ];

    protected $casts = ['shipped_on' => 'date'];

    public function originWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }

    public function destinationSchool()
    {
        return $this->belongsTo(\App\Modules\Registry\Models\School::class, 'destination_school_id');
    }

    public function title()
    {
        return $this->belongsTo(\App\Modules\Catalogue\Models\TextbookTitle::class, 'textbook_title_id');
    }

    public function custodyEvents()
    {
        return $this->hasMany(CustodyEvent::class)->orderBy('occurred_at');
    }

    public function variance(): ?int
    {
        return $this->received_books === null ? null : $this->received_books - $this->books;
    }

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
