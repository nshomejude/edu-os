<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementOrder extends Model
{
    protected $fillable = ['order_no', 'supplier_id', 'textbook_title_id', 'quantity', 'unit_price_fcfa', 'contract_ref', 'status', 'print_batch_id'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function title() { return $this->belongsTo(TextbookTitle::class, 'textbook_title_id'); }
    public function batch() { return $this->belongsTo(PrintBatch::class, 'print_batch_id'); }
}
