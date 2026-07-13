<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockAdjustment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Platform\Models\ExceptionCase;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\Inspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Wave 3: two-step adjustments + count thresholds, inspector-independence SOD, partial receipt. */
class Wave3Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $keeper;
    private User $manager;
    private School $school;
    private Warehouse $warehouse;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Alpha', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
        $this->keeper = User::create(['name' => 'Keeper', 'email' => 'k@t.cm', 'password' => 'x', 'role' => 'STOREKEEPER']);
        $this->manager = User::create(['name' => 'Manager', 'email' => 'm@t.cm', 'password' => 'x', 'role' => 'WAREHOUSE_MANAGER']);
    }

    public function test_storekeeper_adjustments_wait_for_manager_approval(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 300);

        $this->actingAs($this->keeper)->post(route('warehouses.adjust', $this->warehouse), [
            'textbook_title_id' => $this->title->id, 'delta' => -20, 'reason' => 'DAMAGE',
        ]);
        $adj = StockAdjustment::first();
        $this->assertSame('REQUESTED', $adj->status);
        $this->assertSame(300, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));   // untouched

        $this->actingAs($this->manager)->post(route('adjustments.approve', $adj));
        $this->assertSame('APPROVED', $adj->fresh()->status);
        $this->assertSame('Manager', $adj->fresh()->decided_by);
        $this->assertSame(280, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));

        // rejection posts nothing
        $this->actingAs($this->keeper)->post(route('warehouses.adjust', $this->warehouse), [
            'textbook_title_id' => $this->title->id, 'delta' => -50, 'reason' => 'LOSS',
        ]);
        $this->actingAs($this->manager)->post(route('adjustments.reject', StockAdjustment::latest('id')->first()));
        $this->assertSame(280, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
    }

    public function test_large_count_variance_requires_approval_before_posting(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 10000);

        // variance 100 > max(50, 0.5%) → held as a pending adjustment
        $this->actingAs($this->keeper)->post(route('warehouses.count', $this->warehouse), [
            'textbook_title_id' => $this->title->id, 'counted_qty' => 9900,
        ]);
        $this->assertSame(10000, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
        $this->assertSame('REQUESTED', StockAdjustment::first()->status);
        $this->assertSame(-100, (int) StockAdjustment::first()->delta);

        // small variance (−5) posts straight through
        $this->actingAs($this->keeper)->post(route('warehouses.count', $this->warehouse), [
            'textbook_title_id' => $this->title->id, 'counted_qty' => 9995,
        ]);
        $this->assertSame(9995, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
    }

    public function test_inspector_cannot_verify_deliveries_they_received(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 100);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 50]);

        // Admin dispatched AND received — cannot inspect this school (VER SOD)
        $this->actingAs($this->admin)->post(route('inspections.store'), [
            'school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'counted_qty' => 50,
        ]);
        $this->assertSame(0, Inspection::count());

        // an independent inspector can, and gets a VER number
        $inspector = User::create(['name' => 'Inspector I', 'email' => 'i@t.cm', 'password' => 'x', 'role' => 'INSPECTOR']);
        $this->actingAs($inspector)->post(route('inspections.store'), [
            'school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'counted_qty' => 50,
        ]);
        $this->assertSame(1, Inspection::count());
        $this->assertMatchesRegularExpression('/^VER-\d{4}-\d{4}$/', Inspection::first()->ver_no);
    }

    public function test_partial_receipts_accumulate_without_discrepancy(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 200);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 100,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);

        // first tranche: 60, declared partial → no discrepancy, balance stays in transit
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 60, 'partial' => 1]);
        $shipment->refresh();
        $this->assertSame('PARTIALLY_RECEIVED', $shipment->status);
        $this->assertSame(60, (int) $shipment->received_books);
        $this->assertSame(40, (int) StockRecord::where('stock_class', 'IN_TRANSIT_OUT')->sum('quantity'));
        $this->assertSame(60, (int) \App\Modules\SchoolOps\Models\SchoolStock::sum('quantity'));
        $this->assertSame(0, ExceptionCase::count());

        // balance arrives → RECEIVED_FULL, clean chain, no case
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 40]);
        $shipment->refresh();
        $this->assertSame('RECEIVED_FULL', $shipment->status);
        $this->assertSame(100, (int) $shipment->received_books);
        $this->assertSame(0, (int) StockRecord::where('stock_class', 'IN_TRANSIT_OUT')->sum('quantity'));
        $this->assertSame(0, ExceptionCase::count());
    }
}
