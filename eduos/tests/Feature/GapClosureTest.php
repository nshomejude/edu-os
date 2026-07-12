<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GapClosureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Warehouse $warehouse;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'C', 'name_fr' => 'C', 'books_distributed' => 0]);
        School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'S', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
    }

    public function test_failed_qa_blocks_warehouse_receipt(): void
    {
        $batch = PrintBatch::create(['batch_no' => 'BAT-1', 'textbook_title_id' => $this->title->id, 'printer' => 'P', 'quantity' => 100]);
        $this->actingAs($this->admin)->post(route('batches.qa', $batch), ['qa_status' => 'FAILED']);
        $this->assertSame('FAILED', $batch->fresh()->qa_status);

        $this->actingAs($this->admin)->post(route('warehouses.receive', $this->warehouse), [
            'print_batch_id' => $batch->id, 'quantity' => 100,
        ]);
        $this->assertSame(0, (int) StockRecord::sum('quantity'));   // nothing entered the ledger
    }

    public function test_enrolment_can_be_rejected_with_reason(): void
    {
        $e = Enrolment::create(['school_id' => School::first()->id, 'academic_year' => '2026/2027', 'class_level' => 'P1', 'boys' => 900, 'girls' => 900, 'validation_status' => 'SUBMITTED']);
        $this->actingAs($this->admin)->post(route('schoolops.enrolment.reject', $e), ['rejection_reason' => 'Implausible']);
        $e->refresh();
        $this->assertSame('REJECTED', $e->validation_status);
        $this->assertSame('Implausible', $e->rejection_reason);
    }

    public function test_inter_warehouse_transfer_moves_stock_between_ledgers(): void
    {
        $dest = Warehouse::create(['wh_id' => 'CM-WH-CE-002', 'name' => 'W2', 'tier' => 'REGIONAL', 'region_id' => Region::first()->id]);
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 200);

        $this->actingAs($this->admin)->post(route('warehouses.transfer', $this->warehouse), [
            'destination_warehouse_id' => $dest->id, 'textbook_title_id' => $this->title->id, 'books' => 80,
        ]);
        $shipment = \App\Modules\Custody\Models\Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 80]);

        $this->assertSame(80, (int) StockRecord::where(['warehouse_id' => $dest->id, 'stock_class' => 'AVAILABLE'])->sum('quantity'));
        $this->assertSame(120, (int) StockRecord::where(['warehouse_id' => $this->warehouse->id, 'stock_class' => 'AVAILABLE'])->sum('quantity'));
    }

    public function test_password_change_requires_current_password(): void
    {
        $this->admin->forceFill(['password' => \Illuminate\Support\Facades\Hash::make('old-secret')])->save();
        $this->actingAs($this->admin)->post(route('profile.password'), [
            'current_password' => 'WRONG', 'password' => 'new-secret-123', 'password_confirmation' => 'new-secret-123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($this->admin)->post(route('profile.password'), [
            'current_password' => 'old-secret', 'password' => 'new-secret-123', 'password_confirmation' => 'new-secret-123',
        ]);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-secret-123', $this->admin->fresh()->password));
    }
}
