<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Planning\Models\DistributionCampaign;
use App\Modules\Platform\Models\AuthEvent;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\Assignment;
use App\Modules\SchoolOps\Models\CollectionRound;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Coverage drill-down, collection cycle, campaign fulfilment, loss/supplier analytics, uncapped audit export. */
class ReportsLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $school;
    private Warehouse $warehouse;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Nkolbisson', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
        Enrolment::create(['school_id' => $this->school->id, 'academic_year' => '2025/2026', 'class_level' => 'P1', 'boys' => 60, 'girls' => 60, 'validation_status' => 'VALIDATED']);
    }

    public function test_coverage_report_computes_ratio_and_shortfall_with_drilldown(): void
    {
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 40, 'condition' => 'GOOD']);

        // national level: 40 books / 120 learners = 33.3%, shortfall 80
        $this->actingAs($this->admin)->get('/reports/coverage')
            ->assertOk()->assertSee('33.3')->assertSee('80')->assertSee('Centre');

        // region drill-down shows the school row
        $this->actingAs($this->admin)->get('/reports/coverage?region=CE')
            ->assertOk()->assertSee('GPS Nkolbisson');
    }

    public function test_collection_round_mass_returns_and_declares_losses(): void
    {
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 50, 'condition' => 'GOOD']);
        $this->actingAs($this->admin)->post(route('schoolops.assign', $this->school), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1', 'quantity' => 10,
        ]);

        $this->actingAs($this->admin)->post(route('collections.open'));
        $round = CollectionRound::first();
        $this->assertSame('OPEN', $round->status);

        // mass return at FAIR condition
        $this->actingAs($this->admin)->post(route('collections.bulk'), [
            'school_id' => $this->school->id, 'condition_on_return' => 'FAIR',
        ]);
        $returned = Assignment::first();
        $this->assertSame('RETURNED', $returned->status);
        $this->assertSame('FAIR', $returned->condition_on_return);

        // a second assignment stays out — closing the round declares it LOST and writes down stock
        $this->actingAs($this->admin)->post(route('schoolops.assign', $this->school), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1', 'quantity' => 5,
        ]);
        $this->actingAs($this->admin)->post(route('collections.close', $round));

        $round->refresh();
        $this->assertSame('CLOSED', $round->status);
        $this->assertSame(1, (int) $round->returned_count);
        $this->assertSame(1, (int) $round->lost_count);
        $this->assertSame('LOST', Assignment::latest('id')->first()->status);
        $this->assertSame(45, (int) SchoolStock::where('school_id', $this->school->id)->sum('quantity'));
        $this->assertTrue(\App\Modules\Platform\Models\Alert::where('title', 'like', '%closed with losses')->exists());
    }

    public function test_campaign_fulfilment_report_measures_delivery(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 500);
        $programme = User::create(['name' => 'P', 'email' => 'p@t.cm', 'password' => 'x', 'role' => 'PROGRAMME_ADMIN']);
        $this->actingAs($programme)->post(route('plan.store'), ['name' => 'Wave F']);
        $campaign = DistributionCampaign::first();
        $this->actingAs($programme)->post(route('plan.transition', $campaign), ['to' => 'REVIEW']);
        $this->actingAs($this->admin)->post(route('plan.transition', $campaign), ['to' => 'APPROVED']);
        $this->actingAs($this->admin)->post(route('plan.execute', $campaign));

        $shipment = Shipment::latest('id')->first();   // 120 books allocated from enrolment
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 100]);

        // fulfilment: 100 received of 120 allocated = 83.3%
        $this->actingAs($this->admin)->get('/reports/campaign-performance')
            ->assertOk()->assertSee('Wave F')->assertSee('83.3');
    }

    public function test_loss_and_supplier_analytics(): void
    {
        // transit loss: ship 50, receive 45 → 10% loss on the Centre lane
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 100);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 45]);

        // supplier: 100 ordered, 10 rejected damaged → 10% damage rate
        $supplier = \App\Modules\Catalogue\Models\Supplier::create(['name' => 'Print Co', 'type' => 'PRINTER']);
        $this->actingAs($this->admin)->post(route('procurement.store'), [
            'supplier_id' => $supplier->id, 'textbook_title_id' => $this->title->id,
            'quantity' => 100, 'unit_price_fcfa' => 1500, 'contract_ref' => 'CT-01',
        ]);
        $this->actingAs($this->admin)->post(route('procurement.approve', \App\Modules\Catalogue\Models\ProcurementOrder::first()));
        $this->actingAs($this->admin)->post(route('procurement.delivered', \App\Modules\Catalogue\Models\ProcurementOrder::first()), ['damaged_qty' => 10]);

        $page = $this->actingAs($this->admin)->get('/reports/performance');
        $page->assertOk()->assertSee('Centre')->assertSee('10%')->assertSee('Print Co');
    }

    public function test_audit_export_is_uncapped_beyond_screen_limit(): void
    {
        for ($i = 0; $i < 150; $i++) {
            AuthEvent::create(['event' => 'LOGIN_OK', 'email' => "bulk{$i}@t.cm", 'ip' => '127.0.0.1']);
        }
        // screen merges max 120 events; the export must carry all 150+
        $csv = $this->actingAs($this->admin)->get('/reports/audit.csv')->assertOk()->getContent();
        $this->assertGreaterThan(140, substr_count($csv, "\nAUTH") + substr_count($csv, ",AUTH,"));
    }
}
