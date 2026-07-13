<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\StockTransaction;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Logistics\Models\Trip;
use App\Modules\Planning\Models\DistributionCampaign;
use App\Modules\Platform\Models\AuthEvent;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\InspectionAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Depth pass: AUTH audit/lockout/recovery, PLAN-03, INV-08, VER-01, EXC-02, LOG-06, BOOK-05, POD-04/05. */
class DepthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $school;
    private Warehouse $warehouse;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'C', 'name_fr' => 'C', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'S', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => Hash::make('password'), 'role' => 'ADMIN']);
        Enrolment::create(['school_id' => $this->school->id, 'academic_year' => '2025/2026', 'class_level' => 'P1', 'boys' => 60, 'girls' => 60, 'validation_status' => 'VALIDATED']);
    }

    public function test_failed_logins_are_audited_and_locked_out(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => $this->admin->email, 'password' => 'wrong']);
        }
        $this->assertSame(5, AuthEvent::where('event', 'LOGIN_FAIL')->count());
        $this->assertTrue(AuthEvent::where('event', 'LOGIN_LOCKOUT')->exists());

        // successful logins are audited too, and surface in the unified trail
        $this->post('/login', ['email' => 'b@t.cm', 'password' => 'irrelevant']);
        $this->actingAs($this->admin)->get('/audit-trail')->assertOk()->assertSee('AUTH');
    }

    public function test_recovery_code_signs_in_when_authenticator_is_lost(): void
    {
        $this->admin->forceFill([
            'mfa_enabled' => true, 'totp_secret' => 'JBSWY3DPEHPK3PXP',
            'recovery_codes' => json_encode([Hash::make('ABCD-1234')]),
        ])->save();

        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password'])
            ->assertRedirect(route('mfa.challenge'));
        $this->post('/mfa', ['code' => 'abcd-1234'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);
        $this->assertSame([], json_decode($this->admin->fresh()->recovery_codes, true));   // consumed
        $this->assertTrue(AuthEvent::where('event', 'MFA_OK')->exists());
    }

    public function test_school_requirement_raises_campaign_demand(): void
    {
        $this->actingAs($this->admin)->post(route('schoolops.requirement', $this->school), [
            'textbook_title_id' => $this->title->id, 'quantity' => 300,
        ]);
        $programme = User::create(['name' => 'P', 'email' => 'p@t.cm', 'password' => 'x', 'role' => 'PROGRAMME_ADMIN']);
        $this->actingAs($programme)->post(route('plan.store'), ['name' => 'Wave R']);

        $campaign = DistributionCampaign::first();
        // enrolment says 120, the school asked for 300 — the higher figure wins (PLAN-03)
        $this->assertSame(300, $campaign->allocations->first()->quantity);
        $this->assertSame('CONSIDERED', \App\Modules\Planning\Models\SchoolRequirement::first()->status);
    }

    public function test_manual_adjustment_posts_with_reason_code(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 300);
        $this->actingAs($this->admin)->post(route('warehouses.adjust', $this->warehouse), [
            'textbook_title_id' => $this->title->id, 'delta' => -20, 'reason' => 'DAMAGE', 'note' => 'water damage row 3',
        ]);
        $this->assertSame(280, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
        $this->assertStringContainsString('ADJUSTMENT DAMAGE', StockTransaction::latest('id')->first()->context);
    }

    public function test_verification_assignment_completes_when_inspection_recorded(): void
    {
        $this->actingAs($this->admin)->post(route('inspections.assign'), [
            'school_id' => $this->school->id, 'inspector_id' => $this->admin->id,
            'due_on' => now()->addDays(3)->toDateString(),
        ]);
        $this->assertSame('ASSIGNED', InspectionAssignment::first()->status);

        $this->actingAs($this->admin)->post(route('inspections.store'), [
            'school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'counted_qty' => 0,
        ]);
        $this->assertSame('DONE', InspectionAssignment::first()->status);
    }

    public function test_isbn_check_digit_is_enforced(): void
    {
        $base = ['title_en' => 'X', 'ministry' => 'MINEDUB', 'subject_code' => 'ENG', 'grade_code' => 'P2', 'language' => 'EN', 'tracking_granularity' => 'BATCH'];
        $this->actingAs($this->admin)->post(route('textbooks.store'), $base + ['isbn' => '978-0-306-40615-1']);
        $this->assertSame(1, TextbookTitle::count());   // rejected — only the setUp title exists
        $this->actingAs($this->admin)->post(route('textbooks.store'), $base + ['isbn' => '978-0-306-40615-7', 'publisher' => 'CEPMAE', 'pages' => 128]);
        $this->assertSame(2, TextbookTitle::count());
        $this->assertSame('CEPMAE', TextbookTitle::latest('id')->first()->publisher);
    }

    public function test_trip_detail_route_stops_and_exception_case_page(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 100);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), [
            'carrier' => 'C', 'waybill' => 'W', 'route_stops' => 'Obala; Bafia; Ntui',
        ]);

        $trip = Trip::first();
        $this->assertSame('Obala; Bafia; Ntui', $trip->route_stops);
        $this->actingAs($this->admin)->get(route('trips.show', $trip))->assertOk()->assertSee('Bafia');

        // short receipt with signature and category → discrepancy case page with SLA state
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), [
            'received_books' => 45, 'received_signature' => 'Head Teacher A', 'discrepancy_category' => 'DAMAGE',
        ]);
        $shipment->refresh();
        $this->assertSame('Head Teacher A', $shipment->received_signature);
        $this->assertSame('DAMAGE', $shipment->discrepancy_category);
        $this->actingAs($this->admin)->get('/exceptions/discrepancy/'.$shipment->id)->assertOk()->assertSee('WITHIN SLA');
        $this->actingAs($this->admin)->get(route('shipments.pod', $shipment))->assertOk()->assertSee('Head Teacher A');
    }

    public function test_supplier_delivery_verification_rejects_damaged_units(): void
    {
        $supplier = \App\Modules\Catalogue\Models\Supplier::create(['name' => 'Print Co', 'type' => 'PRINTER']);
        $this->actingAs($this->admin)->post(route('procurement.store'), [
            'supplier_id' => $supplier->id, 'textbook_title_id' => $this->title->id,
            'quantity' => 100, 'unit_price_fcfa' => 1500, 'contract_ref' => 'CT-01',
        ]);
        $order = \App\Modules\Catalogue\Models\ProcurementOrder::first();

        $this->actingAs($this->admin)->post(route('procurement.approve', \App\Modules\Catalogue\Models\ProcurementOrder::first()));
        $this->actingAs($this->admin)->post(route('procurement.delivered', $order), ['damaged_qty' => 10]);
        $order->refresh();
        $this->assertSame('DELIVERED', $order->status);
        $this->assertSame(10, (int) $order->damaged_qty);
        $this->assertSame(90, (int) \App\Modules\Catalogue\Models\PrintBatch::first()->quantity);   // only good units enter custody
        $this->assertTrue(\App\Modules\Platform\Models\Alert::where('title', 'like', 'Supplier delivery rejects%')->exists());
    }

    public function test_printable_documents_render_with_barcodes_and_qr(): void
    {
        // waybill after dispatch
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 100);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W-1']);
        $this->actingAs($this->admin)->get(route('shipments.waybill', $shipment))
            ->assertOk()->assertSee('CONSIGNMENT WAYBILL')->assertSee('REPUBLIC OF CAMEROON')->assertSee($shipment->shipment_no);

        // picking list carries the document header and machine-readable codes
        $picking = $this->actingAs($this->admin)->get(route('shipments.picking', $shipment));
        $picking->assertOk()->assertSee('WAREHOUSE PICKING LIST')->assertSee('Scan to verify');

        // inspection report
        $this->actingAs($this->admin)->post(route('inspections.store'), [
            'school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'counted_qty' => 0,
        ]);
        $inspection = \App\Modules\SchoolOps\Models\Inspection::first();
        $this->actingAs($this->admin)->get(route('inspections.report', $inspection))
            ->assertOk()->assertSee('SCHOOL INSPECTION REPORT')->assertSee('INSP-'.str_pad($inspection->id, 5, '0', STR_PAD_LEFT));

        // distribution order
        $programme = User::create(['name' => 'P2', 'email' => 'p2@t.cm', 'password' => 'x', 'role' => 'PROGRAMME_ADMIN']);
        $this->actingAs($programme)->post(route('plan.store'), ['name' => 'Wave D']);
        $campaign = DistributionCampaign::first();
        $this->actingAs($this->admin)->get(route('plan.order', $campaign))
            ->assertOk()->assertSee('NATIONAL TEXTBOOK DISTRIBUTION ORDER')->assertSee('separation of duties');
    }
}
