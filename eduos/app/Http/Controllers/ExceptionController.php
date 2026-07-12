<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\Shipment;
use App\Modules\Logistics\Models\Trip;
use App\Modules\Platform\Models\Alert;
use App\Modules\SchoolOps\Models\Inspection;

/** EXC module (76–79): one queue for everything abnormal, with escalation. */
class ExceptionController extends Controller
{
    public function index()
    {
        return view('exceptions.index', [
            'discrepancies' => Shipment::where('status', 'RECEIVED_WITH_DISCREPANCY')->whereNull('resolved_at')
                ->with('destinationSchool')->orderByDesc('shipped_on')->get(),
            'inspections' => Inspection::whereNull('resolved_at')->where('outcome', '!=', 'CONFORM')
                ->with('school')->orderByDesc('inspected_on')->get(),
            'incidents' => Trip::where('status', 'INCIDENT')->with(['shipment', 'vehicle', 'driver'])->get(),
            'critical' => Alert::where('severity', 'CRITICAL')->whereNull('read_at')->orderByDesc('id')->get(),
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
        Alert::create([
            'severity' => 'CRITICAL',
            'title' => 'ESCALATION: '.$data['subject'],
            'message' => $data['detail'].' — escalated by '.auth()->user()->name.' ('.auth()->user()->role.')',
            'link' => $data['link'] ?? '/exceptions',
        ]);

        return back()->with('flash', 'Escalated to national level; the ministry alert is live.');
    }
}
