<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

/** BOOK-04: national curriculum versions that approved titles map to. */
class CurriculumVersion extends Model
{
    protected $fillable = ['name', 'cycle', 'year', 'status'];
}
