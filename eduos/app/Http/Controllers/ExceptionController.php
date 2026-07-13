<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\Shipment;
use App\Modules\Logistics\Models\Trip;
use App\Modules\Platform\Models\Alert;
use App\Modules\Platform\Models\Setting;
use App\Modules\SchoolOps\Models\Inspection;

/** EXC module (76–79): one queue for everything abnormal, with SLA ageing and escalation. */
class ExceptionController extends Controller
{
    public static function slaHours(): int
    {
        return (int) Setting::get('exception_sla_hours', '72');
    }

    public function index()
    {
        return view('exceptions.index', [
            'discrepancies' => Shipment::where('status', 'RECEIVED_WITH_DISCREPANCY')->whereNull('resolved_at')
                ->with('destinationSchool')->orderByDesc('shipped_on')->get(),
            'inspections' => Inspection::whereNull('resolved_at')->where('outcome', '!=', 'CONFORM')
                ->with('school')->orderByDesc('inspected_on')->get(),
            'incidents' => Trip::where('status', 'INCIDENT')->with(['shipment', 'vehicle', 'driver'])->get(),
            'critical' => Alert::where('severity', 'CRITICAL')->whereNull('read_at')->orderByDesc('id')->get(),
            'slaHours' => self::slaHours(),
            'cases' => \App\Modules\Platform\Models\ExceptionCase::orderByRaw("status in ('RESOLVED','CLOSED','REJECTED')")->orderByDesc('id')->limit(30)->get(),
        ]);
    }

    /** EXC-02: individual case page — source record, age and SLA state. */
    public function show(string $type, int $id)
    {
        $case = match ($type) {
            'discrepancy' => Shipment::with(['destinationSchool', 'title', 'custodyEvents'])->findOrFail($id),
            'inspection' => Inspection::with(['school', 'title'])->findOrFail($id),
            'incident' => Trip::with(['shipment', 'vehicle', 'driver'])->findOrFail($id),
            'alert' => Alert::findOrFail($id),
            default => abort(404),
        };
        $openedAt = match ($type) {
            'discrepancy', 'incident' => $case->updated_at,
            default => $case->created_at,
        };
        $ageHours = (int) $openedAt->diffInHours(now());

        return view('exceptions.show', [
            'type' => $type, 'case' => $case,
            'openedAt' => $openedAt, 'ageHours' => $ageHours,
            'slaHours' => self::slaHours(), 'breached' => $ageHours > self::slaHours(),
        ]);
    }

    /** EXC-04: escalate any open exception to national level. */
    public function escalate(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:160',
            'detail' => 'required|string|max:500',
            'link' => 'nullable|string|max:160',
        ]);
        \App\Modules\Platform\Models\ExceptionCase::open('ESCALATION', 'CRITICAL', $data['subject']);
        Alert::create([
            'severity' => 'CRITICAL',
            'title' => 'ESCALATION: '.$data['subject'],
            'message' => $data['detail'].' — escalated by '.auth()->user()->name.' ('.auth()->user()->role.')',
            'link' => $data['link'] ?? '/exceptions',
        ]);

        return back()->with('flash', 'Escalated to national level; the ministry alert is live.');
    }
    /** EXC-02: persistent case page with ownership and the governed status machine. */
    public function caseShow(\App\Modules\Platform\Models\ExceptionCase $case)
    {
        $staff = \App\Models\User::where('is_active', 1)
            ->whereNotIn('role', ['READONLY'])->orderBy('name')->get(['name', 'role']);

        return view('cases.show', compact('case', 'staff'));
    }

    public function caseAssign(\Illuminate\Http\Request $request, \App\Modules\Platform\Models\ExceptionCase $case)
    {
        $who = $request->validate(['assigned_to' => 'required|string|max:120'])['assigned_to'];
        $case->update(['assigned_to' => $who, 'status' => $case->status === 'OPEN' ? 'ASSIGNED' : $case->status]);

        return back()->with('flash', "Case assigned to {$who}.");
    }

    /** EXC-03/04: transitions with mandatory reason on resolution; high-severity closure is ministry-tier. */
    public function caseTransition(\Illuminate\Http\Request $request, \App\Modules\Platform\Models\ExceptionCase $case)
    {
        $data = $request->validate(['to' => 'required|string|max:20', 'reason' => 'nullable|string|max:300']);
        $to = strtoupper($data['to']);
        if (! in_array($to, \App\Modules\Platform\Models\ExceptionCase::TRANSITIONS[$case->status] ?? [])) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$case->status} → {$to}.");
        }
        if (in_array($to, ['RESOLVED', 'REJECTED', 'CLOSED']) && empty($data['reason'])) {
            return back()->with('flash_error', 'Resolution requires a reason and a responsible actor (EXC rule).');
        }
        if ($to === 'CLOSED' && in_array($case->severity, ['HIGH', 'CRITICAL']) && ! auth()->user()->can('ministry')) {
            return back()->with('flash_error', 'HIGH and CRITICAL cases can only be closed by the ministry tier.');
        }
        $case->update([
            'status' => $to,
            'reason' => $data['reason'] ?? $case->reason,
            'resolved_at' => in_array($to, ['RESOLVED', 'REJECTED']) ? now() : ($to === 'REOPENED' ? null : $case->resolved_at),
        ]);

        return back()->with('flash', "Case {$case->case_no} → ".str_replace('_', ' ', $to).' by '.auth()->user()->name.'.');
    }

}
