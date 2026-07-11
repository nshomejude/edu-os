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

        return view('textbooks.show', compact('textbook', 'batches', 'stock'));
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

        return back()->with('flash', "Batch {$batch->batch_no} registered ({$batch->quantity} copies, QA pending).");
    }
}
