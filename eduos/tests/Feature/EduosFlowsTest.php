<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\Copy;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Platform\Models\Alert;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\Registry\Models\Student;
use App\Modules\SchoolOps\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EduosFlowsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warehouseOfficer;
    private User $schoolHead;
    private School $school;
    private Warehouse $warehouse;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->school = School::create([
            'nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'Test School',
            'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id,
        ]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'Test WH', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create([
            'ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'Test Maths', 'ministry' => 'MINEDUB',
            'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN',
            'status' => 'APPROVED', 'tracking_granularity' => 'COPY',
        ]);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
        $this->warehouseOfficer = User::create(['name' => 'WO', 'email' => 'w@t.cm', 'password' => 'x', 'role' => 'WAREHOUSE_OFFICER']);
        $this->schoolHead = User::create(['name' => 'SH', 'email' => 's@t.cm', 'password' => 'x', 'role' => 'SCHOOL_HEAD', 'school_id' => $this->school->id]);
    }

    private function seedBatchInWarehouse(int $qty = 100): PrintBatch
    {
        $this->actingAs($this->admin)->post(route('textbooks.batches.store', $this->title), [
            'printer' => 'Test Printer', 'quantity' => $qty,
        ]);
        $batch = PrintBatch::latest('id')->first();
        $this->actingAs($this->warehouseOfficer)->post(route('warehouses.receive', $this->warehouse), [
            'print_batch_id' => $batch->id, 'quantity' => $qty,
        ]);

        return $batch;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_role_gates_block_unauthorized_mutations(): void
    {
        $this->actingAs($this->schoolHead)->get('/procurement')->assertForbidden();
        $this->actingAs($this->schoolHead)->post(route('warehouses.receive', $this->warehouse), [])->assertForbidden();
        $this->actingAs($this->schoolHead)->post(route('textbooks.transition', $this->title), ['to' => 'RETIRED'])->assertForbidden();
        $this->actingAs($this->warehouseOfficer)->post(route('textbooks.store'), [])->assertForbidden();
    }

    public function test_title_registration_generates_ntid_and_starts_draft(): void
    {
        $this->actingAs($this->admin)->post(route('textbooks.store'), [
            'title_en' => 'Physics F3', 'ministry' => 'MINESEC', 'subject_code' => 'PHY',
            'grade_code' => 'F3', 'language' => 'EN', 'tracking_granularity' => 'BATCH',
        ]);
        $t = TextbookTitle::where('subject_code', 'PHY')->first();
        $this->assertSame('CM-TB-S-PHY-F3-EN-0001-01', $t->ntid);
        $this->assertSame('DRAFT', $t->status);
    }

    public function test_illegal_title_transition_is_rejected(): void
    {
        $draft = TextbookTitle::create([
            'ntid' => 'CM-TB-B-X-P1-EN-0009-01', 'title_en' => 'X', 'ministry' => 'MINEDUB',
            'subject_code' => 'XXX', 'grade_code' => 'P1', 'language' => 'EN',
        ]);
        $this->actingAs($this->admin)->post(route('textbooks.transition', $draft), ['to' => 'SUSPENDED']);
        $this->assertSame('DRAFT', $draft->fresh()->status);   // DRAFT → SUSPENDED is illegal
    }

    public function test_batch_receipt_mints_copies_and_posts_stock(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->assertSame(100, Copy::where('lifecycle_state', 'IN_WAREHOUSE')->count());
        $this->assertSame(100, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
    }

    public function test_full_shipment_lifecycle_with_copy_binding_and_clean_receipt(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 40,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->assertSame('CONFIRMED', $shipment->status);
        $this->assertSame(40, (int) StockRecord::where('stock_class', 'RESERVED')->sum('quantity'));

        $this->actingAs($this->warehouseOfficer)->post(route('shipments.dispatch', $shipment), [
            'carrier' => 'Camrail', 'waybill' => 'WB-1',
        ]);
        $this->assertSame(40, Copy::where('shipment_id', $shipment->id)->where('lifecycle_state', 'IN_TRANSIT')->count());

        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 40]);
        $shipment->refresh();
        $this->assertSame('RECEIVED_FULL', $shipment->status);
        $this->assertSame(40, Copy::where('current_school_id', $this->school->id)->where('lifecycle_state', 'AT_SCHOOL')->count());
        $this->assertSame(0, (int) StockRecord::where('stock_class', 'IN_TRANSIT_OUT')->sum('quantity'));
    }

    public function test_variance_opens_discrepancy_quarantines_and_alerts_then_resolution_clears(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 45]);

        $shipment->refresh();
        $this->assertSame('RECEIVED_WITH_DISCREPANCY', $shipment->status);
        $this->assertSame(-5, $shipment->variance());
        $this->assertSame(5, (int) StockRecord::where('stock_class', 'QUARANTINE')->sum('quantity'));
        $this->assertTrue(Alert::where('severity', 'CRITICAL')->where('title', 'like', 'Discrepancy%')->exists());

        $this->actingAs($this->admin)->post(route('shipments.resolve', $shipment), ['resolution' => 'FOUND']);
        $this->assertSame(0, (int) StockRecord::where('stock_class', 'QUARANTINE')->sum('quantity'));
        $this->assertSame('CLOSED', $shipment->fresh()->status);
    }

    public function test_cancel_before_dispatch_reverses_reservation(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 30,
        ]);
        $shipment = Shipment::latest('id')->first();
        // storekeepers cannot cancel (warehouse-approve tier); the manager/admin can
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.cancel', $shipment))->assertForbidden();
        $this->actingAs($this->admin)->post(route('shipments.cancel', $shipment));
        $this->assertSame('CANCELLED', $shipment->fresh()->status);
        $this->assertSame(100, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
        $this->assertSame(0, (int) StockRecord::where('stock_class', 'RESERVED')->sum('quantity'));
    }

    public function test_student_assignment_forces_quantity_one_and_scopes_to_school(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->deliverToSchool(20);
        $student = Student::create([
            'lsid' => 'CM-STU-0000001', 'name' => 'Test Learner', 'sex' => 'F',
            'class_level' => 'P1', 'school_id' => $this->school->id,
        ]);
        $this->actingAs($this->schoolHead)->post(route('schoolops.assign', $this->school), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1',
            'quantity' => 99, 'student_id' => $student->id,
        ]);
        $a = \App\Modules\SchoolOps\Models\Assignment::latest('id')->first();
        $this->assertSame(1, $a->quantity);
        $this->assertSame($student->id, $a->student_id);
        $this->assertSame(1, Copy::where('lifecycle_state', 'ASSIGNED')->count());
    }

    public function test_poor_return_routes_copy_to_repair(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->deliverToSchool(20);
        $this->actingAs($this->schoolHead)->post(route('schoolops.assign', $this->school), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1', 'quantity' => 1,
        ]);
        $a = \App\Modules\SchoolOps\Models\Assignment::latest('id')->first();
        $this->actingAs($this->schoolHead)->post(route('schoolops.return', $a), ['condition_on_return' => 'POOR']);
        $this->assertSame(1, Copy::where('lifecycle_state', 'UNDER_REPAIR')->count());
    }

    public function test_campaign_close_marks_unaccounted_copies_lost(): void
    {
        $this->seedBatchInWarehouse(100);
        $this->deliverToSchool(20);
        $this->actingAs($this->admin)->post(route('campaigns.open'), ['name' => 'Test Campaign']);
        $campaign = Campaign::first();
        $this->actingAs($this->admin)->post(route('campaigns.submit', $campaign), [
            'school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'counted' => 17,
        ]);
        $this->actingAs($this->admin)->post(route('campaigns.close', $campaign));
        $this->assertSame(3, Copy::where('lifecycle_state', 'LOST')->count());
        $this->assertSame('CLOSED', $campaign->fresh()->status);
    }

    public function test_hash_chains_verify_intact_and_detect_tampering(): void
    {
        $this->seedBatchInWarehouse(50);
        $this->artisan('eduos:verify-chains')->assertExitCode(0);

        \App\Modules\Catalogue\Models\PassportEvent::first()->forceFill(['actor' => 'TAMPERED'])->saveQuietly();
        $this->artisan('eduos:verify-chains')->assertExitCode(1);
        $this->assertTrue(Alert::where('title', 'like', 'AUDIT%')->exists());
    }

    public function test_public_apis_are_open_and_report_counts(): void
    {
        $this->getJson('/api/catalogue')->assertOk()->assertJsonPath('count', 1);
        $this->getJson('/api/schools')->assertOk()->assertJsonPath('count', 1);
    }

    public function test_scan_lookup_resolves_ncid_to_copy(): void
    {
        $this->seedBatchInWarehouse(10);
        $copy = Copy::first();
        $this->actingAs($this->admin)->post(route('scan'), ['ncid' => $copy->ncid])
            ->assertRedirect(route('copies.show', $copy));
    }

    private function deliverToSchool(int $qty): void
    {
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => $qty,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->warehouseOfficer)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => $qty]);
    }
}
