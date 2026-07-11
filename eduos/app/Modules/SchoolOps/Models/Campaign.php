<?php

namespace App\Modules\SchoolOps\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = ['name', 'academic_year', 'status', 'opened_at', 'closed_at'];
    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];

    public function submissions()
    {
        return $this->hasMany(CampaignSubmission::class);
    }
}
