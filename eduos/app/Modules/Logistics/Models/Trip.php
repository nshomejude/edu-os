<?php

namespace App\Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = ['shipment_id', 'vehicle_id', 'driver_id', 'status', 'departed_at', 'arrived_at', 'incident_note', 'route_note', 'route_stops'];
    protected $casts = ['departed_at' => 'datetime', 'arrived_at' => 'datetime'];

    public function shipment() { return $this->belongsTo(\App\Modules\Custody\Models\Shipment::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
}
