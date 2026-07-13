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

    public const LOW_STOCK_THRESHOLD = 2000;

    public static function lowStockThreshold(): int
    {
        return (int) \App\Modules\Platform\Models\Setting::get('low_stock_threshold', (string) self::LOW_STOCK_THRESHOLD);
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

        // Journal (INV-07): the ledger is reconstructible from transactions
        StockTransaction::create([
            'warehouse_id' => $warehouseId, 'textbook_title_id' => $titleId,
            'stock_class' => $class, 'delta' => $delta, 'balance_after' => $rec->quantity,
            'actor' => auth()->user()->name ?? 'System',
            'context' => app()->runningInConsole() ? 'console' : request()->path(),
        ]);

        // Low-stock alert (Problem 26): fire once per warehouse/title while unread
        if ($class === 'AVAILABLE' && $delta < 0 && $rec->quantity < self::lowStockThreshold()) {
            $title = \App\Modules\Catalogue\Models\TextbookTitle::find($titleId);
            $wh = Warehouse::find($warehouseId);
            $marker = "/warehouses/{$warehouseId}?low={$titleId}";
            $exists = \App\Modules\Platform\Models\Alert::where('link', $marker)->whereNull('read_at')->exists();
            if (! $exists) {
                \App\Modules\Platform\Models\Alert::create([
                    'severity' => 'WARNING',
                    'title' => "Low stock: {$title?->ntid} at {$wh?->name}",
                    'message' => "AVAILABLE stock fell to {$rec->quantity} (threshold ".self::lowStockThreshold()."). Consider replenishment or redistribution.",
                    'link' => $marker,
                ]);
            }
        }

        return $rec;
    }
}
