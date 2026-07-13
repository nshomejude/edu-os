<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

/** EXC: persistent exception case — ownership, severity SLA, governed status machine. */
class ExceptionCase extends Model
{
    protected $fillable = [
        'case_no', 'type', 'source_id', 'title', 'severity', 'status',
        'assigned_to', 'opened_by', 'reason', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public const TRANSITIONS = [
        'OPEN' => ['ASSIGNED', 'INVESTIGATING', 'REJECTED'],
        'ASSIGNED' => ['INVESTIGATING', 'AWAITING_INFO', 'REJECTED'],
        'INVESTIGATING' => ['AWAITING_INFO', 'CORRECTIVE_ACTION', 'RESOLVED', 'REJECTED'],
        'AWAITING_INFO' => ['INVESTIGATING', 'RESOLVED', 'REJECTED'],
        'CORRECTIVE_ACTION' => ['PENDING_APPROVAL', 'RESOLVED'],
        'PENDING_APPROVAL' => ['RESOLVED', 'CORRECTIVE_ACTION'],
        'RESOLVED' => ['CLOSED', 'REOPENED'],
        'REJECTED' => ['CLOSED', 'REOPENED'],
        'REOPENED' => ['INVESTIGATING'],
        'CLOSED' => ['REOPENED'],
    ];

    public static function open(string $type, string $severity, string $title, ?int $sourceId = null): self
    {
        return static::create([
            'case_no' => sprintf('EXC-%s-%04d', now()->format('Y'), static::count() + 1),
            'type' => $type, 'severity' => $severity, 'title' => $title,
            'source_id' => $sourceId, 'opened_by' => auth()->user()->name ?? 'System',
        ]);
    }

    /** Hours allowed for this severity (ADM-02 settings, spec §56 SLA keys). */
    public function slaHours(): int
    {
        $defaults = ['LOW' => 168, 'MEDIUM' => 72, 'HIGH' => 24, 'CRITICAL' => 8];

        return (int) Setting::get('exception_sla_'.strtolower($this->severity).'_hours',
            (string) ($defaults[$this->severity] ?? 72));
    }

    public function breached(): bool
    {
        return ! $this->resolved_at && $this->created_at->diffInHours(now()) > $this->slaHours();
    }
}
