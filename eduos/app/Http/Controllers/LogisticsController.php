<?php

namespace App\Http\Controllers;

use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Logistics\Models\Driver;
use App\Modules\Logistics\Models\Trip;
use App\Modules\Logistics\Models\Vehicle;
use App\Modules\Platform\Models\Alert;
use Illuminate\Http\Request;

/** LOG module (screens 58–64): fleet, drivers, trips and incidents. */
class LogisticsController extends Controller
{
    public function index()
    {
        return view('logistics.index', [
            'vehicles' => Vehicle::orderBy('plate')->get(),
            'drivers' => Driver::orderBy('name')->get(),
            'trips' => Trip::with(['shipment', 'vehicle', 'driver'])->orderByDesc('id')->limit(20)->get(),
            'active' => Trip::where('status', 'EN_ROUTE')->count(),
            'incidents' => Trip::where('status', 'INCIDENT')->count(),
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $data = $request->validate([
            'plate' => 'required|string|max:20|unique:vehicles,plate',
            'model' => 'required|string|max:80',
            'capacity_books' => 'required|integer|min:100',
        ]);
        Vehicle::create($data);

        return back()->with('flash', "Vehicle {$data['plate']} registered.");
    }

    public function storeDriver(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'licence_no' => 'required|string|max:40|unique:drivers,licence_no',
            'phone' => 'nullable|string|max:30',
        ]);
        Driver::create($data);

        return back()->with('flash', "Driver {$data['name']} registered.");
    }

    /** LOG-07: incident on an active trip → custody event + critical alert. */
    public function incident(Request $request, Trip $trip)
    {
        $note = $request->validate(['incident_note' => 'required|string|max:500'])['incident_note'];
        $trip->update(['status' => 'INCIDENT', 'incident_note' => $note]);
        CustodyEvent::create([
            'shipment_id' => $trip->shipment_id, 'event_type' => 'TRANSPORT_INCIDENT',
            'actor' => auth()->user()->name, 'notes' => $note, 'occurred_at' => now(),
        ]);
        Alert::create([
            'severity' => 'CRITICAL',
            'title' => "Transport incident — {$trip->shipment->shipment_no}",
            'message' => $note.' (vehicle '.($trip->vehicle->plate ?? 'n/a').', driver '.($trip->driver->name ?? 'n/a').')',
            'link' => "/shipments/{$trip->shipment_id}",
        ]);

        return back()->with('flash_error', 'Incident recorded on the custody chain; national alert raised.');
    }

    /** Trip arrival is posted automatically on shipment receipt; manual close for edge cases. */
    public function arrive(Trip $trip)
    {
        $trip->update(['status' => 'ARRIVED', 'arrived_at' => now()]);
        $trip->vehicle?->update(['status' => 'AVAILABLE']);
        $trip->driver?->update(['status' => 'AVAILABLE']);

        return back()->with('flash', 'Trip closed; vehicle and driver released.');
    }
    /** LOG-06: trip details — consignment, crew, route and the custody timeline. */
    public function showTrip(Trip $trip)
    {
        $trip->load(['shipment.custodyEvents', 'shipment.title', 'vehicle', 'driver']);

        return view('logistics.trip', compact('trip'));
    }

}
