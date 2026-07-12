<?php

namespace App\Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['plate', 'model', 'capacity_books', 'status'];
}
