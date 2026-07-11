<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class CustodyEvent extends Model
{
    protected $fillable = ['shipment_id', 'event_type', 'actor', 'notes', 'occurred_at', 'prev_hash', 'hash'];
    protected $casts = ['occurred_at' => 'datetime'];

    /** Tamper-evident chain per shipment (FR-NTR-DM-02 applied to custody). */
    protected static function booted(): void
    {
        static::creating(function (self $event) {
            $prev = static::where('shipment_id', $event->shipment_id)->latest('id')->value('hash');
            $event->prev_hash = $prev;
            $event->hash = hash('sha256', ($prev ?? 'GENESIS').'|'.$event->shipment_id.'|'.$event->event_type.'|'.$event->actor.'|'.$event->notes.'|'.$event->occurred_at);
        });
    }

    public function verifyChainLink(): bool
    {
        return $this->hash === hash('sha256', ($this->prev_hash ?? 'GENESIS').'|'.$this->shipment_id.'|'.$this->event_type.'|'.$this->actor.'|'.$this->notes.'|'.$this->occurred_at);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
