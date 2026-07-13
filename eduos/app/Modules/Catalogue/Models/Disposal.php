<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

/** Governed end-of-life: a disposal record backing a printable certificate. */
class Disposal extends Model
{
    protected $fillable = ['ncid', 'textbook_title_id', 'reason', 'location', 'actor'];

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
