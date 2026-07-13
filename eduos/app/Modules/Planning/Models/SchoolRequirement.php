<?php

namespace App\Modules\Planning\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

/** PLAN-03: a school's own submitted textbook requirement for a planning cycle. */
class SchoolRequirement extends Model
{
    protected $fillable = [
        'school_id', 'textbook_title_id', 'academic_year',
        'quantity', 'note', 'submitted_by', 'status',
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
