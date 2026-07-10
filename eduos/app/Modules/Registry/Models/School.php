<?php

namespace App\Modules\Registry\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'nsid', 'name_official', 'ministry', 'school_type',
        'region_id', 'status', 'accessibility_class',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
