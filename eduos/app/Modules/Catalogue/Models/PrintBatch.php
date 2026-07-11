<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class PrintBatch extends Model
{
    protected $fillable = ['batch_no', 'textbook_title_id', 'printer', 'quantity', 'qa_status', 'received_qty'];

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }

    public function passportEvents()
    {
        return $this->hasMany(PassportEvent::class)->orderBy('occurred_at');
    }
}
