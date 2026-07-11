<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class PassportEvent extends Model
{
    protected $fillable = ['print_batch_id', 'event_type', 'location', 'actor', 'occurred_at', 'prev_hash', 'hash'];
    protected $casts = ['occurred_at' => 'datetime'];

    /** Tamper-evident chain per batch (FR-NTR-DM-02). */
    protected static function booted(): void
    {
        static::creating(function (self $event) {
            $prev = static::where('print_batch_id', $event->print_batch_id)->latest('id')->value('hash');
            $event->prev_hash = $prev;
            $event->hash = hash('sha256', ($prev ?? 'GENESIS').'|'.$event->print_batch_id.'|'.$event->event_type.'|'.$event->location.'|'.$event->actor.'|'.$event->occurred_at);
        });
    }

    public function verifyChainLink(): bool
    {
        return $this->hash === hash('sha256', ($this->prev_hash ?? 'GENESIS').'|'.$this->print_batch_id.'|'.$this->event_type.'|'.$this->location.'|'.$this->actor.'|'.$this->occurred_at);
    }

    public function batch()
    {
        return $this->belongsTo(PrintBatch::class, 'print_batch_id');
    }
}
