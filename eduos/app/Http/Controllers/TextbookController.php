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

    /** Copy passport with QR code (FR-NTR-ID-04). */
    public function copy(\App\Modules\Catalogue\Models\Copy $copy)
    {
        $copy->load('batch.title', 'batch.passportEvents');
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(220),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $qrSvg = (new \BaconQrCode\Writer($renderer))->writeString($copy->ncid);

        return view('textbooks.copy', compact('copy', 'qrSvg'));
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
}
