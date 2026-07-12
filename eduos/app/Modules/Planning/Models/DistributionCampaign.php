<?php

namespace App\Modules\Planning\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionCampaign extends Model
{
    protected $fillable = ['name', 'academic_year', 'status', 'created_by', 'approved_by', 'approved_at'];
    protected $casts = ['approved_at' => 'datetime'];

    public function allocations() { return $this->hasMany(Allocation::class); }
}
