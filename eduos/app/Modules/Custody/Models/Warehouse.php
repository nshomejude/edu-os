<?php

namespace App\Modules\Custody\Models;

use App\Modules\Registry\Models\Region;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['wh_id', 'name', 'tier', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function stockRecords()
    {
        return $this->hasMany(StockRecord::class);
    }
}
