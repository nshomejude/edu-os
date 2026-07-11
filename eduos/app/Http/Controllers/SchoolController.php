<?php

namespace App\Http\Controllers;

use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::with('region')
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('name_official', 'like', "%{$v}%")->orWhere('nsid', 'like', "%{$v}%")))
            ->when($request->region, fn ($q, $v) => $q->whereHas('region', fn ($w) => $w->where('code', $v)))
            ->when($request->ministry, fn ($q, $v) => $q->where('ministry', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderBy('name_official')
            ->paginate(12)->withQueryString();

        return view('schools.index', [
            'schools' => $schools,
            'regions' => Region::orderBy('name_en')->get(),
            'counts' => [
                'total' => School::count(),
                'operational' => School::where('status', 'OPERATIONAL')->count(),
                'minedub' => School::where('ministry', 'MINEDUB')->count(),
                'minesec' => School::where('ministry', 'MINESEC')->count(),
            ],
        ]);
    }

    public function show(School $school)
    {
        $school->load('region');
        $enrolments = $school->hasMany(\App\Modules\Registry\Models\Enrolment::class)->orderBy('class_level')->get();
        $stock = \App\Modules\SchoolOps\Models\SchoolStock::with('title')->where('school_id', $school->id)->get();
        $shipments = \App\Modules\Custody\Models\Shipment::where('destination_school_id', $school->id)
            ->orderByDesc('shipped_on')->limit(10)->get();

        return view('schools.show', compact('school', 'enrolments', 'stock', 'shipments'));
    }

    public function create()
    {
        return view('schools.create', ['regions' => Region::orderBy('name_en')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_official' => 'required|string|max:300',
            'ministry' => 'required|in:MINEDUB,MINESEC',
            'school_type' => 'required|in:NURSERY,PRIMARY,GEN_SEC,TECH_SEC,COMBINED',
            'region_id' => 'required|exists:regions,id',
            'accessibility_class' => 'required|in:URBAN,RURAL_ROAD,RURAL_SEASONAL,REMOTE',
        ]);

        // Duplicate-candidate check (FR-NSR-01, simplified): same region + similar name
        $dup = School::where('region_id', $data['region_id'])
            ->where('name_official', 'like', '%'.trim($data['name_official']).'%')->first();
        if ($dup && ! $request->boolean('confirm_not_duplicate')) {
            return back()->withInput()->with('duplicate', $dup);
        }

        // NSID generation per FRS-NSR §2
        $region = Region::find($data['region_id']);
        $typeCode = match ($data['school_type']) {
            'NURSERY' => 'N', 'PRIMARY' => 'P', 'GEN_SEC' => 'G', 'TECH_SEC' => 'T', default => 'C',
        };
        $minCode = $data['ministry'] === 'MINEDUB' ? 'B' : 'S';
        $seq = School::count() + 1;
        $data['nsid'] = sprintf('CM-SCH-%s-0101-%s%s-%05d', $region->code, $minCode, $typeCode, $seq);

        $school = School::create($data);

        return redirect()->route('schools.show', $school)->with('flash', "School registered with NSID {$school->nsid}");
    }
}
