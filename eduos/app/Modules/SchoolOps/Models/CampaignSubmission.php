<?php

namespace App\Modules\SchoolOps\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

class CampaignSubmission extends Model
{
    protected $fillable = ['campaign_id', 'school_id', 'textbook_title_id', 'expected', 'counted', 'submitted_by'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }

    public function variance(): int
    {
        return $this->counted - $this->expected;
    }
}
