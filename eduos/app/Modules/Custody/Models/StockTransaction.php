<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = ['warehouse_id', 'textbook_title_id', 'stock_class', 'delta', 'balance_after', 'actor', 'context'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function title() { return $this->belongsTo(\App\Modules\Catalogue\Models\TextbookTitle::class, 'textbook_title_id'); }
}
