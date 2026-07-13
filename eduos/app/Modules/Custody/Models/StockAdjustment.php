<?php

namespace App\Modules\Custody\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Database\Eloquent\Model;

/** Governed stock adjustment: requested by operations, posted only on approval (FR-NWD-04). */
class StockAdjustment extends Model
{
    protected $fillable = [
        'warehouse_id', 'textbook_title_id', 'delta', 'reason', 'note',
        'requested_by', 'status', 'decided_by', 'decided_at',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
