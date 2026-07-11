<?php

namespace App\Modules\Custody\Models;

use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Database\Eloquent\Model;

class StockRecord extends Model
{
    protected $fillable = ['warehouse_id', 'textbook_title_id', 'stock_class', 'quantity'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function title()
    {
        return $this->belongsTo(TextbookTitle::class, 'textbook_title_id');
    }

    /** Post a quantity change; ledger rule: quantities never edited directly elsewhere. */
    public static function post(int $warehouseId, int $titleId, string $class, int $delta): self
    {
        $rec = static::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'textbook_title_id' => $titleId, 'stock_class' => $class],
            ['quantity' => 0]
        );
        $rec->quantity = max(0, $rec->quantity + $delta);
        $rec->save();

        return $rec;
    }
}
