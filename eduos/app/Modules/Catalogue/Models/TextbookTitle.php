<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class TextbookTitle extends Model
{
    protected $fillable = [
        'ntid', 'title_en', 'title_fr', 'ministry',
        'subject_code', 'grade_code', 'language', 'status', 'tracking_granularity', 'isbn',
    ];
}
