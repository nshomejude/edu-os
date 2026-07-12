<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\StockTransaction;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Logistics\Models\Trip;
use App\Modules\Planning\Models\DistributionCampaign;
use App\Modules\Registry\Models\Enrolment;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Spec86Test extends TestCase
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
        $region = Region::create(['code' => 'CE', 'name_en' => 'C', 'name_fr' => 'C', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'S', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
        $this->programme = User::create(['name' => 'Prog', 'email' => 'p@t.cm', 'password' => 'x', 'role' => 'PROGRAMME_ADMIN']);
        Enrolment::create(['school_id' => $this->school->id, 'academic_year' => '2025/2026', 'class_level' => 'P1', 'boys' => 60, 'girls' => 60, 'validation_status' => 'VALIDATED']);
    }

    public function test_campaign_generates_allocations_enforces_creator_separation_and_executes_shipments(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 500);

        $this->actingAs($this->programme)->post(route('plan.store'), ['name' => 'Wave 1']);
        $campaign = DistributionCampaign::first();
        $this->assertSame(1, $campaign->allocations()->count());
        $this->assertSame(120, $campaign->allocations->first()->quantity);   // 120 learners, 0 stock

        $this->actingAs($this->programme)->post(route('plan.transition', $campaign), ['to' => 'REVIEW']);
        // creator cannot approve (separation of duties)
        $this->actingAs($this->programme)->post(route('plan.transition', $campaign), ['to' => 'APPROVED']);
        $this->assertSame('REVIEW', $campaign->fresh()->status);
        // admin approves and executes
        $this->actingAs($this->admin)->post(route('plan.transition', $campaign), ['to' => 'APPROVED']);
        $this->actingAs($this->admin)->post(route('plan.execute', $campaign));
        $campaign->refresh();
        $this->assertSame('EXECUTING', $campaign->status);
        $this->assertNotNull($campaign->allocations->first()->shipment_id);
        $this->assertSame(120, (int) StockRecord::where('stock_class', 'RESERVED')->sum('quantity'));
    }

    public function test_dispatch_creates_trip_and_incident_raises_alert(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 100);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = \App\Modules\Custody\Models\Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);

        $trip = Trip::first();
        $this->assertSame('EN_ROUTE', $trip->status);

        $this->actingAs($this->admin)->post(route('trips.incident', $trip), ['incident_note' => 'Bridge out at Edea']);
        $this->assertSame('INCIDENT', $trip->fresh()->status);
        $this->assertTrue(\App\Modules\Platform\Models\Alert::where('title', 'like', 'Transport incident%')->exists());

        // receipt closes the trip
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 50]);
        $this->assertSame('ARRIVED', $trip->fresh()->status);
    }

    public function test_stock_journal_records_every_ledger_mutation(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 300);
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', -50);
        $this->assertSame(2, StockTransaction::count());
        $this->assertSame(250, (int) StockTransaction::latest('id')->value('balance_after'));
    }

    public function test_mfa_enabled_user_is_challenged_before_login(): void
    {
        $this->admin->forceFill([
            'password' => Hash::make('password'),
            'mfa_enabled' => true, 'totp_secret' => 'JBSWY3DPEHPK3PXP',
        ])->save();
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password'])
            ->assertRedirect(route('mfa.challenge'));
        $this->assertGuest();
    }

    public function test_password_reset_flow_end_to_end(): void
    {
        $this->admin->forceFill(['password' => Hash::make('old')])->save();
        $resp = $this->post(route('password.email'), ['email' => $this->admin->email]);
        $link = session('flash');
        $this->assertStringContainsString('/reset-password/', $link);
        parse_str(parse_url($link, PHP_URL_QUERY), $qs);
        $token = collect(explode('/', parse_url($link, PHP_URL_PATH)))->last();

        $this->post(route('password.update'), [
            'email' => $this->admin->email, 'token' => $token,
            'password' => 'brand-new-pass1', 'password_confirmation' => 'brand-new-pass1',
        ])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('brand-new-pass1', $this->admin->fresh()->password));
    }

    public function test_audit_trail_and_exception_centre_render(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 10);
        $this->actingAs($this->admin)->get('/audit-trail')->assertOk()->assertSee('STOCK');
        $this->actingAs($this->admin)->get('/exceptions')->assertOk();
        $this->actingAs($this->admin)->get('/plan')->assertOk();
        $this->actingAs($this->admin)->get('/logistics')->assertOk();
        $this->actingAs($this->admin)->get('/exports')->assertOk();
        $this->actingAs($this->admin)->get('/schedule')->assertOk();
        $this->actingAs($this->admin)->get('/network')->assertOk();
    }
}
