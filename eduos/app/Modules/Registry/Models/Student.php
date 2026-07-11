<?php

namespace App\Modules\Registry\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['lsid', 'name', 'sex', 'class_level', 'school_id', 'academic_year'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
