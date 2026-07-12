<?php

namespace App\Modules\Registry\Models;

use Illuminate\Database\Eloquent\Model;

class Enrolment extends Model
{
    protected $fillable = ['school_id', 'academic_year', 'class_level', 'boys', 'girls', 'validation_status', 'rejection_reason'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
