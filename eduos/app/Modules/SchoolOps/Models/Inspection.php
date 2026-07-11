<?php

namespace App\Modules\SchoolOps\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = ['school_id', 'inspector', 'inspected_on', 'textbook_title_id', 'recorded_qty', 'counted_qty', 'outcome', 'findings'];
    protected $casts = ['inspected_on' => 'date'];

    public function school() { return $this->belongsTo(School::class); }
    public function title() { return $this->belongsTo(TextbookTitle::class, 'textbook_title_id'); }
    public function variance(): int { return $this->counted_qty - $this->recorded_qty; }
}
