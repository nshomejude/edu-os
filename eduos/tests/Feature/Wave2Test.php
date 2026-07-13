<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\ProcurementOrder;
use App\Modules\Catalogue\Models\Supplier;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Planning\Models\DistributionCampaign;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Wave 2: demand freeze + campaign amendment versioning; procurement approval + partial deliveries. */
class Wave2Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $programme;
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
        $this->programme = User::create(['name' => 'Prog', 'email' => 'p@t.cm', 'password' => 'x', 'role' => 'PROGRAMME_ADMIN']);
        Enrolment::create(['school_id' => $this->school->id, 'academic_year' => '2025/2026', 'class_level' => 'P1', 'boys' => 60, 'girls' => 60, 'validation_status' => 'VALIDATED']);
    }

    public function test_demand_is_frozen_with_formula_version_and_amendment_creates_new_version(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 500);
        $this->actingAs($this->programme)->post(route('plan.store'), ['name' => 'Wave V']);
        $campaign = DistributionCampaign::first();
        $this->assertNotNull($campaign->demand_frozen_at);
        $this->assertSame('enrolment-v1', $campaign->formula_version);

        // amendment is refused while still a draft
        $this->actingAs($this->programme)->post(route('plan.amend', $campaign));
        $this->assertSame(1, DistributionCampaign::count());

        // approve, then amend → immutable v1 + fresh v2 draft carrying unexecuted lines
        $this->actingAs($this->programme)->post(route('plan.transition', $campaign), ['to' => 'REVIEW']);
        $this->actingAs($this->admin)->post(route('plan.transition', $campaign), ['to' => 'APPROVED']);
        $this->actingAs($this->programme)->post(route('plan.amend', $campaign));

        $v2 = DistributionCampaign::latest('id')->first();
        $this->assertSame(2, (int) $v2->version);
        $this->assertSame($campaign->id, (int) $v2->parent_id);
        $this->assertSame('DRAFT', $v2->status);
        $this->assertSame('APPROVED', $campaign->fresh()->status);   // v1 untouched
        $this->assertSame(1, $v2->allocations()->count());           // line carried over
    }

    public function test_procurement_orders_require_approval_and_support_partial_deliveries(): void
    {
        $supplier = Supplier::create(['name' => 'Print Co', 'type' => 'PRINTER']);
        $this->actingAs($this->admin)->post(route('procurement.store'), [
            'supplier_id' => $supplier->id, 'textbook_title_id' => $this->title->id,
            'quantity' => 100, 'unit_price_fcfa' => 1500, 'contract_ref' => 'CT-01',
        ]);
        $order = ProcurementOrder::first();
        $this->assertSame('SUBMITTED', $order->status);

        // delivery before approval refused
        $this->actingAs($this->admin)->post(route('procurement.delivered', $order), ['delivered_qty' => 60]);
        $this->assertSame(0, PrintBatch::count());

        $this->actingAs($this->admin)->post(route('procurement.approve', $order));
        $this->assertSame('APPROVED', $order->fresh()->status);

        // partial delivery: 60 of 100 (5 damaged) → PARTIALLY_DELIVERED, batch of 55 good units
        $this->actingAs($this->admin)->post(route('procurement.delivered', $order), ['delivered_qty' => 60, 'damaged_qty' => 5]);
        $order->refresh();
        $this->assertSame('PARTIALLY_DELIVERED', $order->status);
        $this->assertSame(60, (int) $order->delivered_total);
        $this->assertSame(55, (int) PrintBatch::first()->quantity);

        // balance arrives → DELIVERED with a second linked batch
        $this->actingAs($this->admin)->post(route('procurement.delivered', $order), ['delivered_qty' => 40]);
        $order->refresh();
        $this->assertSame('DELIVERED', $order->status);
        $this->assertSame(100, (int) $order->delivered_total);
        $this->assertSame(2, PrintBatch::where('procurement_order_id', $order->id)->count());
    }
}
