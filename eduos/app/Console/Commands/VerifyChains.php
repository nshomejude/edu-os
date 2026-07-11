<?php

namespace App\Console\Commands;

use App\Modules\Catalogue\Models\PassportEvent;
use App\Modules\Custody\Models\CustodyEvent;
use App\Modules\Platform\Models\Alert;
use Illuminate\Console\Command;

/** Nightly chain verification (FR-NTR-DM-02): raises an audit alert on breakage. */
class VerifyChains extends Command
{
    protected $signature = 'eduos:verify-chains';
    protected $description = 'Verify passport and custody event hash chains; alert on tampering';

    public function handle(): int
    {
        $broken = 0;
        foreach (PassportEvent::whereNotNull('hash')->cursor() as $e) {
            if (! $e->verifyChainLink()) {
                $broken++;
                $this->error("Passport chain broken at event #{$e->id} (batch {$e->print_batch_id})");
            }
        }
        foreach (CustodyEvent::whereNotNull('hash')->cursor() as $e) {
            if (! $e->verifyChainLink()) {
                $broken++;
                $this->error("Custody chain broken at event #{$e->id} (shipment {$e->shipment_id})");
            }
        }
        if ($broken > 0) {
            Alert::create([
                'severity' => 'CRITICAL',
                'title' => 'AUDIT: event chain verification failed',
                'message' => "{$broken} event(s) failed hash-chain verification — possible tampering. Investigate immediately (FR-NTR-DM-02).",
                'link' => '/alerts',
            ]);
        }
        $this->info($broken === 0 ? 'All chains verified intact.' : "{$broken} broken links found.");

        return $broken === 0 ? self::SUCCESS : self::FAILURE;
    }
}
