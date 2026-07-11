<?php

namespace App\Modules\Custody\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

class RedistributionProposal extends Model
{
    protected $fillable = [
        'from_warehouse_id', 'to_school_id', 'textbook_title_id',
        'quantity', 'reason', 'status', 'shipment_id',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toSchool()
    {
        return $this->belongsTo(School::class, 'to_school_id');
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
