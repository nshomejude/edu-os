<?php

namespace App\Modules\SchoolOps\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

/** Replacement fee raised when assigned books are declared lost at collection close. */
class ReplacementCharge extends Model
{
    protected $fillable = [
        'school_id', 'textbook_title_id', 'quantity', 'amount_fcfa',
        'academic_year', 'status', 'settled_by', 'settled_at',
    ];

    protected $casts = ['settled_at' => 'datetime'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
