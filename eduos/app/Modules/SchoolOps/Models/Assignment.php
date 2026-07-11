<?php

namespace App\Modules\SchoolOps\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'school_id', 'textbook_title_id', 'class_level', 'academic_year',
        'quantity', 'status', 'condition_on_return', 'actor',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
