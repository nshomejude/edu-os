<?php

namespace App\Modules\Registry\Models;

use Illuminate\Database\Eloquent\Model;

class Subdivision extends Model
{
    protected $fillable = ['division_id', 'code', 'name'];

    public function division() { return $this->belongsTo(Division::class); }
    public function schools() { return $this->hasMany(School::class); }
}
