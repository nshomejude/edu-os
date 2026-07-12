<?php

namespace App\Modules\Custody\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Database\Eloquent\Model;

class WarehouseCount extends Model
{
    protected $fillable = ['warehouse_id', 'textbook_title_id', 'ledger_qty', 'counted_qty', 'actor', 'note'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }
}
