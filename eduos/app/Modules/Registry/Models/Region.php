<?php

namespace App\Modules\Registry\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['code', 'name_en', 'name_fr', 'books_distributed'];
}
