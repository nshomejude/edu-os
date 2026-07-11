<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Edition extends Model
{
    protected $fillable = ['textbook_title_id', 'edition_no', 'effective_academic_year', 'changes_summary', 'superseded'];

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
