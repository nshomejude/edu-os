<?php

namespace App\Http\Controllers;

use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Http\Request;

class TextbookController extends Controller
{
    public function index(Request $request)
    {
        $titles = TextbookTitle::query()
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('title_en', 'like', "%{$v}%")->orWhere('title_fr', 'like', "%{$v}%")->orWhere('ntid', 'like', "%{$v}%")))
            ->when($request->ministry, fn ($q, $v) => $q->where('ministry', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderBy('ntid')
            ->paginate(12)->withQueryString();

        return view('textbooks.index', [
            'titles' => $titles,
            'counts' => [
                'total' => TextbookTitle::count(),
                'approved' => TextbookTitle::where('status', 'APPROVED')->count(),
                'batches' => PrintBatch::count(),
                'copies' => PrintBatch::sum('quantity'),
            ],
        ]);
    }

    /** Lifecycle start: register a title in DRAFT with a generated NTID (FR-NTR-01). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title_en' => 'nullable|string|max:300|required_without:title_fr',
            'title_fr' => 'nullable|string|max:300|required_without:title_en',
            'ministry' => 'required|in:MINEDUB,MINESEC',
            'subject_code' => 'required|string|size:3|alpha',
            'grade_code' => 'required|string|max:2',
            'language' => 'required|in:EN,FR,BI',
            'tracking_granularity' => 'required|in:COPY,BATCH',
            'isbn' => 'nullable|string|max:17',
            'publisher' => 'nullable|string|max:160',
            'pages' => 'nullable|integer|min:1|max:2000',
            'weight_grams' => 'nullable|integer|min:1|max:5000',
            'curriculum_version_id' => 'nullable|exists:curriculum_versions,id',
        ]);
        if (! empty($data['isbn']) && ! self::isbnValid($data['isbn'])) {
            return back()->withInput()->with('flash_error', 'ISBN-13 check digit does not verify — check the number (BOOK-05).');
        }
        $data['subject_code'] = strtoupper($data['subject_code']);
        $data['grade_code'] = strtoupper($data['grade_code']);
        $min = $data['ministry'] === 'MINEDUB' ? 'B' : 'S';
        $seq = TextbookTitle::where([
            'ministry' => $data['ministry'],
            'subject_code' => $data['subject_code'],
            'grade_code' => $data['grade_code'],
            'language' => $data['language'],
        ])->count() + 1;
        $data['ntid'] = sprintf('CM-TB-%s-%s-%s-%s-%04d-01', $min, $data['subject_code'], $data['grade_code'], $data['language'], $seq);
        $extras = array_intersect_key($data, array_flip(['publisher', 'pages', 'weight_grams', 'curriculum_version_id']));
        $title = TextbookTitle::create(array_diff_key($data, $extras));   // status defaults to DRAFT
        $title->forceFill($extras)->save();

        return redirect()->route('textbooks.show', $title)
            ->with('flash', "Title registered as {$title->ntid} in DRAFT — approve it to enter the catalogue (FR-NTR-02).");
    }

    public function show(TextbookTitle $textbook)
    {
        $batches = PrintBatch::with('passportEvents')->where('textbook_title_id', $textbook->id)->get();
        $stock = \App\Modules\Custody\Models\StockRecord::with('warehouse')
            ->where('textbook_title_id', $textbook->id)->get();
        $editions = \App\Modules\Catalogue\Models\Edition::where('textbook_title_id', $textbook->id)
            ->orderBy('edition_no')->get();
        $copies = \App\Modules\Catalogue\Models\Copy::whereIn('print_batch_id', $batches->pluck('id'))
            ->selectRaw('lifecycle_state, count(*) as n')->groupBy('lifecycle_state')->pluck('n', 'lifecycle_state');

        return view('textbooks.show', compact('textbook', 'batches', 'stock', 'editions', 'copies'));
    }

    /** New edition supersedes prior ones for future academic years (FR-NTR-03). */
    public function storeEdition(Request $request, TextbookTitle $textbook)
    {
        $data = $request->validate([
            'effective_academic_year' => 'required|string|max:9',
            'changes_summary' => 'nullable|string|max:300',
        ]);
        \App\Modules\Catalogue\Models\Edition::where('textbook_title_id', $textbook->id)->update(['superseded' => true]);
        $ed = \App\Modules\Catalogue\Models\Edition::create($data + [
            'textbook_title_id' => $textbook->id,
            'edition_no' => \App\Modules\Catalogue\Models\Edition::where('textbook_title_id', $textbook->id)->max('edition_no') + 1,
        ]);

        return back()->with('flash', "Edition {$ed->edition_no} registered, effective {$ed->effective_academic_year}; prior editions superseded.");
    }

    /** Browse minted per-copy passports (FR-NTR-ID). */
    public function copies(TextbookTitle $textbook)
    {
        $copies = \App\Modules\Catalogue\Models\Copy::with('batch')
            ->whereIn('print_batch_id', PrintBatch::where('textbook_title_id', $textbook->id)->pluck('id'))
            ->orderBy('ncid')->paginate(25);

        return view('textbooks.copies', compact('textbook', 'copies'));
    }

    /** Explicit QA outcome on a batch (FR-NTR-07): FAILED batches are blocked from warehouses. */
    public function batchQa(Request $request, PrintBatch $batch)
    {
        $status = $request->validate(['qa_status' => 'required|in:PASSED,FAILED'])['qa_status'];
        $batch->update(['qa_status' => $status]);
        PassportEvent::create([
            'print_batch_id' => $batch->id, 'event_type' => 'QA_'.$status,
            'location' => $batch->printer, 'actor' => auth()->user()->name,
            'occurred_at' => now(),
        ]);

        return back()->with($status === 'FAILED' ? 'flash_error' : 'flash',
            "Batch {$batch->batch_no} QA {$status}".($status === 'FAILED' ? ' - warehouse receipt is now blocked.' : '.'));
    }

    /** Edit a title while DRAFT only (FR-NTR-01 PATCH semantics). */
    public function update(Request $request, TextbookTitle $textbook)
    {
        if ($textbook->status !== 'DRAFT') {
            return back()->with('flash_error', 'Only DRAFT titles are editable; approved titles change via editions.');
        }
        $data = $request->validate([
            'title_en' => 'nullable|string|max:300|required_without:title_fr',
            'title_fr' => 'nullable|string|max:300|required_without:title_en',
            'isbn' => 'nullable|string|max:17',
        ]);
        $textbook->update($data);

        return back()->with('flash', 'Draft title updated.');
    }

    /** Copy passport with QR code (FR-NTR-ID-04). */
    public function copy(\App\Modules\Catalogue\Models\Copy $copy)
    {
        $copy->load('batch.title', 'batch.passportEvents');
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(220),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $qrSvg = (new \BaconQrCode\Writer($renderer))->writeString(route('verify', ['ncid' => $copy->ncid]));

        return view('textbooks.copy', compact('copy', 'qrSvg'));
    }

    /** Field actions on a single copy: repair-complete, lost, found, retire, dispose. */
    public function copyTransition(Request $request, \App\Modules\Catalogue\Models\Copy $copy)
    {
        $data = $request->validate([
            'to' => 'required|in:AT_SCHOOL,LOST,RETIRED,DISPOSED,UNDER_REPAIR,IN_WAREHOUSE',
            'reason' => 'required_if:to,DISPOSED|nullable|string|max:200',
        ]);
        $to = $data['to'];
        if (! $copy->canTransition($to)) {
            return back()->with('flash_error', "ILLEGAL_TRANSITION: {$copy->lifecycle_state} → {$to} (FRS §5.2).");
        }
        // Disposal is governed: ministry tier only, and it always issues a certificate
        if ($to === 'DISPOSED') {
            if (! auth()->user()->can('ministry')) {
                return back()->with('flash_error', 'Disposal requires the ministry approval tier.');
            }
            $copy->update(['lifecycle_state' => 'DISPOSED']);
            $disposal = \App\Modules\Catalogue\Models\Disposal::create([
                'ncid' => $copy->ncid,
                'textbook_title_id' => $copy->batch->textbook_title_id,
                'reason' => $data['reason'],
                'location' => $copy->current_school_id
                    ? \App\Modules\Registry\Models\School::find($copy->current_school_id)?->name_official
                    : 'National warehouse network',
                'actor' => auth()->user()->name,
            ]);

            return redirect()->route('disposals.cert', $disposal)
                ->with('flash', "Copy {$copy->ncid} disposed — certificate DSP-".str_pad($disposal->id, 5, '0', STR_PAD_LEFT).' issued.');
        }
        $update = ['lifecycle_state' => $to];
        if ($to === 'AT_SCHOOL' && $copy->lifecycle_state === 'UNDER_REPAIR') {
            $update['condition'] = 'FAIR';   // repaired copies return serviceable
        }
        $copy->update($update);

        return back()->with('flash', "Copy {$copy->ncid} → ".str_replace('_', ' ', $to).'.');
    }

    /** Scan simulation: NCID lookup → copy passport (field scanning stand-in). */
    public function scan(Request $request)
    {
        $ncid = trim($request->validate(['ncid' => 'required|string|max:64'])['ncid']);
        $copy = \App\Modules\Catalogue\Models\Copy::where('ncid', $ncid)->first();
        if (! $copy) {
            return back()->with('flash_error', "No copy found for NCID {$ncid}.");
        }

        return redirect()->route('copies.show', $copy);
    }

    /** Toggle per-copy tracking policy (FR-NTR-ID-05). */
    public function setGranularity(Request $request, TextbookTitle $textbook)
    {
        $g = $request->validate(['granularity' => 'required|in:COPY,BATCH'])['granularity'];
        $textbook->update(['tracking_granularity' => $g]);

        return back()->with('flash', "Tracking granularity set to {$g}.");
    }

    public function transition(Request $request, TextbookTitle $textbook)
    {
        $to = $request->validate(['to' => 'required|in:APPROVED,SUSPENDED,RETIRED'])['to'];
        $legal = match ($textbook->status) {
            'DRAFT' => ['APPROVED'],
            'APPROVED' => ['SUSPENDED', 'RETIRED'],
            'SUSPENDED' => ['APPROVED', 'RETIRED'],
            default => [],
        };
        if (! in_array($to, $legal)) {
            return back()->with('flash_error', "Illegal transition {$textbook->status} → {$to} (FR-NTR-SM-01).");
        }
        $textbook->update(['status' => $to]);

        return back()->with('flash', "Title {$textbook->ntid} moved to {$to}.");
    }

    public function storeBatch(Request $request, TextbookTitle $textbook)
    {
        if ($textbook->status !== 'APPROVED') {
            return back()->with('flash_error', 'Only APPROVED titles may register print batches (FR-NTR-04).');
        }
        $data = $request->validate([
            'printer' => 'required|string|max:120',
            'quantity' => 'required|integer|min:1|max:1000000',
        ]);
        $batch = PrintBatch::create([
            'batch_no' => sprintf('BAT-%s-%05d', now()->format('Y'), PrintBatch::count() + 1),
            'textbook_title_id' => $textbook->id,
            'printer' => $data['printer'],
            'quantity' => $data['quantity'],
        ]);
        PassportEvent::create([
            'print_batch_id' => $batch->id, 'event_type' => 'PRINTED',
            'location' => $data['printer'], 'actor' => auth()->user()->name ?? 'System',
            'occurred_at' => now(),
        ]);

        // Per-copy passports for COPY-tracked titles (FR-NTR-ID-05); demo caps NCID minting at 500/batch
        $minted = 0;
        if ($textbook->tracking_granularity === 'COPY') {
            $minted = min($batch->quantity, 500);
            $rows = [];
            for ($i = 1; $i <= $minted; $i++) {
                $rows[] = [
                    'ncid' => sprintf('%s-%05d-%06d', $textbook->ntid, $batch->id, $i),
                    'print_batch_id' => $batch->id, 'lifecycle_state' => 'PRINTED',
                    'condition' => 'NEW', 'created_at' => now(), 'updated_at' => now(),
                ];
            }
            \App\Modules\Catalogue\Models\Copy::insert($rows);
        }

        return back()->with('flash', "Batch {$batch->batch_no} registered ({$batch->quantity} copies, QA pending".($minted ? ", {$minted} NCIDs minted" : '').').');
    }
    /** BOOK-05: ISBN-13 checksum validation. */
    public static function isbnValid(string $isbn): bool
    {
        $d = preg_replace('/[^0-9]/', '', $isbn);
        if (strlen($d) !== 13) {
            return false;
        }
        $sum = 0;
        foreach (str_split(substr($d, 0, 12)) as $i => $c) {
            $sum += (int) $c * ($i % 2 ? 3 : 1);
        }

        return (10 - $sum % 10) % 10 === (int) $d[12];
    }

    /** BOOK: batch recall — pull every traceable copy of a defective batch from circulation. */
    public function recallBatch(Request $request, PrintBatch $batch)
    {
        if ($batch->recalled_at) {
            return back()->with('flash_error', 'Batch already recalled.');
        }
        $reason = $request->validate(['reason' => 'required|string|max:200'])['reason'];
        $batch->forceFill(['recalled_at' => now(), 'recall_reason' => $reason])->save();

        // write recalled copies out of the schools that hold them
        $atSchools = \App\Modules\Catalogue\Models\Copy::where('print_batch_id', $batch->id)
            ->whereIn('lifecycle_state', ['AT_SCHOOL', 'ASSIGNED'])->whereNotNull('current_school_id')
            ->selectRaw('current_school_id sid, count(*) n')->groupBy('current_school_id')->get();
        foreach ($atSchools as $row) {
            $stock = \App\Modules\SchoolOps\Models\SchoolStock::where('school_id', $row->sid)
                ->where('textbook_title_id', $batch->textbook_title_id)->first();
            $stock?->update(['quantity' => max(0, $stock->quantity - $row->n)]);
        }
        $pulled = \App\Modules\Catalogue\Models\Copy::where('print_batch_id', $batch->id)
            ->whereIn('lifecycle_state', ['IN_WAREHOUSE', 'IN_TRANSIT', 'AT_SCHOOL', 'ASSIGNED'])
            ->update(['lifecycle_state' => 'RECALLED']);

        PassportEvent::create([
            'print_batch_id' => $batch->id, 'event_type' => 'RECALL_ISSUED',
            'location' => 'National', 'actor' => auth()->user()->name, 'occurred_at' => now(),
        ]);
        \App\Modules\Platform\Models\Alert::create([
            'severity' => 'CRITICAL',
            'title' => "Batch recall — {$batch->batch_no}",
            'message' => "{$pulled} copies pulled from circulation: {$reason}. Affected school stock written down; recalled copies await disposition.",
            'link' => "/batches/{$batch->id}/recall",
        ]);

        return back()->with('flash_error', "Recall issued — {$pulled} copies pulled from circulation.");
    }

    /** Recall trace: where every copy of the batch is right now. */
    public function recallTrace(PrintBatch $batch)
    {
        $byState = \App\Modules\Catalogue\Models\Copy::where('print_batch_id', $batch->id)
            ->selectRaw('lifecycle_state, count(*) n')->groupBy('lifecycle_state')->pluck('n', 'lifecycle_state');
        $schools = \App\Modules\Catalogue\Models\Copy::where('print_batch_id', $batch->id)
            ->whereIn('lifecycle_state', ['AT_SCHOOL', 'ASSIGNED'])->whereNotNull('current_school_id')
            ->selectRaw('current_school_id school_id, count(*) n')->groupBy('current_school_id')->get()
            ->each(fn ($r) => $r->name = \App\Modules\Registry\Models\School::find($r->school_id)?->name_official ?? '—');

        return view('batches.recall', compact('batch', 'byState', 'schools'));
    }

    /** Disposals register. */
    public function disposals()
    {
        return view('disposals.index', [
            'disposals' => \App\Modules\Catalogue\Models\Disposal::with('title')->orderByDesc('id')->limit(50)->get(),
        ]);
    }

    public function disposalCertificate(\App\Modules\Catalogue\Models\Disposal $disposal)
    {
        $disposal->load('title');

        return view('disposals.certificate', compact('disposal'));
    }

    /** BOOK-04: retiring a curriculum flags every approved title mapped to it. */
    public function retireCurriculum(\App\Modules\Catalogue\Models\CurriculumVersion $curriculum)
    {
        if ($curriculum->status === 'RETIRED') {
            return back()->with('flash_error', 'Curriculum already retired.');
        }
        $curriculum->update(['status' => 'RETIRED']);
        $affected = TextbookTitle::where('curriculum_version_id', $curriculum->id)->where('status', 'APPROVED')->count();
        \App\Modules\Platform\Models\Alert::create([
            'severity' => 'WARNING',
            'title' => "Curriculum retired — {$curriculum->name}",
            'message' => "{$affected} approved title(s) map to the retired curriculum and need review for supersession or retirement (BOOK-04).",
            'link' => '/textbooks',
        ]);

        return back()->with('flash', "Curriculum retired; {$affected} approved title(s) flagged for review.");
    }

    /** BOOK: link EN/FR language counterparts (both directions). */
    public function linkCounterpart(Request $request, TextbookTitle $textbook)
    {
        $other = TextbookTitle::find((int) $request->validate(['counterpart_id' => 'required|exists:textbook_titles,id'])['counterpart_id']);
        if ($other->id === $textbook->id || $other->language === $textbook->language) {
            return back()->with('flash_error', 'Counterpart must be a different title in the other language.');
        }
        $textbook->forceFill(['counterpart_id' => $other->id])->save();
        $other->forceFill(['counterpart_id' => $textbook->id])->save();

        return back()->with('flash', "Linked {$textbook->ntid} ↔ {$other->ntid} as language counterparts.");
    }

}
