<?php

namespace App\Modules\Registry\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['region_id', 'code', 'name'];

    public function region() { return $this->belongsTo(Region::class); }
    public function subdivisions() { return $this->hasMany(Subdivision::class); }
}
