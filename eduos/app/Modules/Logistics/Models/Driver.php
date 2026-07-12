<?php

namespace App\Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['name', 'licence_no', 'phone', 'status'];
}
