<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\Shipment;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Wave 4: return-to-warehouse chain and the season-readiness view. */
class Wave4Test extends TestCase
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
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Alpha', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'REGIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
    }

    public function test_school_returns_books_to_warehouse_through_the_custody_chain(): void
    {
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 50, 'condition' => 'GOOD']);

        // over-return refused (only unassigned stock)
        $this->actingAs($this->admin)->post(route('schoolops.return_wh', $this->school), [
            'textbook_title_id' => $this->title->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 60,
        ]);
        $this->assertSame(0, Shipment::count());

        $this->actingAs($this->admin)->post(route('schoolops.return_wh', $this->school), [
            'textbook_title_id' => $this->title->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 20,
        ]);
        $shipment = Shipment::first();
        $this->assertSame('CONFIRMED', $shipment->status);
        $this->assertSame($this->school->id, (int) $shipment->origin_school_id);
        $this->assertNull($shipment->origin_warehouse_id);
        $this->assertSame(30, (int) SchoolStock::sum('quantity'));   // written out at request
        $this->assertTrue($shipment->custodyEvents->contains('event_type', 'RETURN_REQUESTED'));

        // normal chain: approve → dispatch (no warehouse ledger on the school side) → counted receipt restocks
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'RTN-1']);
        $this->assertSame('IN_TRANSIT', $shipment->fresh()->status);
        $this->assertSame(0, (int) StockRecord::sum('quantity'));   // nothing invented on any ledger yet

        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 20]);
        $this->assertSame('RECEIVED_FULL', $shipment->fresh()->status);
        $this->assertSame(20, (int) StockRecord::where('warehouse_id', $this->warehouse->id)->where('stock_class', 'AVAILABLE')->sum('quantity'));
    }

    public function test_return_shortfall_quarantines_at_the_receiving_warehouse(): void
    {
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 50, 'condition' => 'GOOD']);
        $this->actingAs($this->admin)->post(route('schoolops.return_wh', $this->school), [
            'textbook_title_id' => $this->title->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 20,
        ]);
        $shipment = Shipment::first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'RTN-2']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 15]);

        $this->assertSame('RECEIVED_WITH_DISCREPANCY', $shipment->fresh()->status);
        $this->assertSame(15, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
        $this->assertSame(5, (int) StockRecord::where('warehouse_id', $this->warehouse->id)->where('stock_class', 'QUARANTINE')->sum('quantity'));
    }

    public function test_season_readiness_report_renders_regional_fulfilment(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 500);
        \App\Modules\Registry\Models\Enrolment::create(['school_id' => $this->school->id, 'academic_year' => '2025/2026', 'class_level' => 'P1', 'boys' => 60, 'girls' => 60, 'validation_status' => 'VALIDATED']);
        $programme = User::create(['name' => 'P', 'email' => 'p@t.cm', 'password' => 'x', 'role' => 'PROGRAMME_ADMIN']);
        $this->actingAs($programme)->post(route('plan.store'), ['name' => 'Season']);
        $campaign = \App\Modules\Planning\Models\DistributionCampaign::first();
        $this->actingAs($programme)->post(route('plan.transition', $campaign), ['to' => 'REVIEW']);
        $this->actingAs($this->admin)->post(route('plan.transition', $campaign), ['to' => 'APPROVED']);
        $this->actingAs($this->admin)->post(route('plan.execute', $campaign));

        $this->actingAs($this->admin)->get('/reports/season-readiness')
            ->assertOk()->assertSee('Centre')->assertSee('Season Readiness');
    }
}
