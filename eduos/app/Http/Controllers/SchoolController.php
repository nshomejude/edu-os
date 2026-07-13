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

    /** School status lifecycle (FRS-NSR §4, simplified): guard closure against open shipments. */
    public function transition(Request $request, School $school)
    {
        $to = $request->validate(['to' => 'required|in:OPERATIONAL,TEMPORARILY_CLOSED,CLOSED'])['to'];
        $legal = match ($school->status) {
            'OPERATIONAL' => ['TEMPORARILY_CLOSED', 'CLOSED'],
            'TEMPORARILY_CLOSED' => ['OPERATIONAL', 'CLOSED'],
            default => [],
        };
        if (! in_array($to, $legal)) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$school->status} → {$to}.");
        }
        if ($to === 'CLOSED') {
            $open = \App\Modules\Custody\Models\Shipment::where('destination_school_id', $school->id)
                ->whereNotIn('status', ['CLOSED', 'RECEIVED_FULL', 'CANCELLED'])->count();
            if ($open > 0) {
                return back()->with('flash_error', "Cannot close: {$open} open shipment(s) reference this school (blocking references, FR-NSR-SM-01).");
            }
        }
        $school->update(['status' => $to]);

        return back()->with('flash', "School status → {$to}.");
    }

    /** Field-level controlled updates (FR-NSR-02). */
    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name_official' => 'required|string|max:300',
            'accessibility_class' => 'required|in:URBAN,RURAL_ROAD,RURAL_SEASONAL,REMOTE',
            'grid_power' => 'required|in:GRID,SOLAR,NONE',
            'connectivity' => 'required|in:NONE,2G,3G,4G',
            'classrooms_total' => 'nullable|integer|min:0|max:200',
            'storage_secure' => 'nullable|boolean',
        ]);
        $data['storage_secure'] = $request->boolean('storage_secure');
        $school->update($data);

        return back()->with('flash', 'School profile updated (change attributed to '.auth()->user()->name.').');
    }

    /** Learner detail with assignment history. */
    public function student(\App\Modules\Registry\Models\Student $student)
    {
        \Illuminate\Support\Facades\Gate::authorize('view-learners', \App\Modules\Registry\Models\School::findOrFail($student->school_id));
        $assignments = \App\Modules\SchoolOps\Models\Assignment::with('title')
            ->where('student_id', $student->id)->orderByDesc('id')->get();

        return view('schools.student', compact('student', 'assignments'));
    }

    /** Learner registration (light Student Registry write path). */
    public function storeStudent(Request $request, School $school)
    {
        \Illuminate\Support\Facades\Gate::authorize('operate-school', $school);
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'sex' => 'required|in:M,F',
            'class_level' => 'required|string|max:4',
        ]);
        $student = \App\Modules\Registry\Models\Student::create($data + [
            'lsid' => sprintf('CM-STU-%07d', \App\Modules\Registry\Models\Student::count() + 1),
            'school_id' => $school->id, 'academic_year' => \App\Modules\Platform\Models\Setting::get('academic_year', '2025/2026'),
        ]);

        return back()->with('flash', "Learner registered as {$student->lsid}.");
    }

    public function students(School $school, Request $request)
    {
        $students = \App\Modules\Registry\Models\Student::where('school_id', $school->id)
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%{$v}%")->orWhere('lsid', 'like', "%{$v}%")))
            ->when($request->class_level, fn ($q, $v) => $q->where('class_level', $v))
            ->orderBy('class_level')->orderBy('name')->paginate(20)->withQueryString();

        return view('schools.students', compact('school', 'students'));
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
