<?php

namespace App\Modules\Planning\Models;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    protected $fillable = ['distribution_campaign_id', 'school_id', 'textbook_title_id', 'quantity', 'shipment_id'];

    public function campaign() { return $this->belongsTo(DistributionCampaign::class, 'distribution_campaign_id'); }
    public function school() { return $this->belongsTo(\App\Modules\Registry\Models\School::class); }
    public function title() { return $this->belongsTo(\App\Modules\Catalogue\Models\TextbookTitle::class, 'textbook_title_id'); }
    public function shipment() { return $this->belongsTo(\App\Modules\Custody\Models\Shipment::class); }
}
