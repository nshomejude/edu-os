<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = ['warehouse_id', 'zone', 'capacity'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
