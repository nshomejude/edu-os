<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\Copy;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Custody\Models\StockRecord;
use App\Modules\Custody\Models\Warehouse;
use App\Modules\Platform\Models\ExceptionCase;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Support\CheckDigit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/** Wave 1: migration imports, NCID check digits, label export, persistent exception cases. */
class Wave1Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Region $region;
    private School $school;
    private Warehouse $warehouse;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $this->region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Alpha', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $this->region->id]);
        $this->warehouse = Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $this->region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED', 'tracking_granularity' => 'COPY']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
    }

    public function test_minted_ncids_carry_a_valid_mod37_check_digit(): void
    {
        $this->actingAs($this->admin)->post(route('textbooks.batches.store', $this->title), [
            'printer' => 'Print Co', 'quantity' => 5,
        ]);
        $copy = Copy::first();
        $this->assertNotNull($copy);
        $this->assertMatchesRegularExpression('/-[0-9A-Z*]$/', $copy->ncid);
        $this->assertTrue(CheckDigit::validate($copy->ncid));

        // a single-character typo must fail validation
        $tampered = substr($copy->ncid, 0, -1).($copy->ncid[-1] === '7' ? '8' : '7');
        $this->assertFalse(CheckDigit::validate($tampered));
    }

    public function test_label_file_exports_one_row_per_copy(): void
    {
        $this->actingAs($this->admin)->post(route('textbooks.batches.store', $this->title), [
            'printer' => 'Print Co', 'quantity' => 7,
        ]);
        $batch = \App\Modules\Catalogue\Models\PrintBatch::first();
        $csv = $this->actingAs($this->admin)->get(route('batches.labels', $batch))->assertOk()->getContent();
        $this->assertSame(8, substr_count(trim($csv), "\n") + 1);   // header + 7 copies
        $this->assertStringContainsString($this->title->ntid, $csv);
    }

    public function test_school_import_commits_valid_rows_reports_defects_and_is_idempotent(): void
    {
        $csv = "name_official,ministry,school_type,region_code\n"
            ."GPS Import One,MINEDUB,PRIMARY,CE\n"
            ."GPS Import Two,MINESEC,GEN_SEC,CE\n"
            ."Bad School,NOPE,PRIMARY,CE\n";
        $file = UploadedFile::fake()->createWithContent('schools.csv', $csv);

        $this->actingAs($this->admin)->post(route('imports.schools'), ['file' => $file]);
        $report = session('import_report');
        $this->assertSame(2, $report['created']);
        $this->assertCount(1, $report['defects']);
        $this->assertSame(3, School::count());   // 1 seed + 2 imported

        // idempotent re-import: nothing new
        $this->actingAs($this->admin)->post(route('imports.schools'), ['file' => UploadedFile::fake()->createWithContent('schools.csv', $csv)]);
        $this->assertSame(2, session('import_report')['skipped']);
        $this->assertSame(3, School::count());
    }

    public function test_brownfield_stock_import_posts_ledgers_and_registers_lineage(): void
    {
        $csv = "target_type,target_id,ntid,quantity,condition\n"
            ."WAREHOUSE,CM-WH-CE-001,{$this->title->ntid},1200,\n"
            ."SCHOOL,CM-SCH-CE-0101-BP-00001,{$this->title->ntid},80,FAIR\n"
            ."SCHOOL,CM-SCH-XX-9999-ZZ-99999,{$this->title->ntid},10,\n";
        $this->actingAs($this->admin)->post(route('imports.stock'), [
            'file' => UploadedFile::fake()->createWithContent('stock.csv', $csv),
        ]);
        $report = session('import_report');
        $this->assertSame(2, $report['created']);
        $this->assertCount(1, $report['defects']);
        $this->assertSame(1200, (int) StockRecord::where('stock_class', 'AVAILABLE')->sum('quantity'));
        $this->assertSame(80, (int) \App\Modules\SchoolOps\Models\SchoolStock::sum('quantity'));
        $this->assertSame(2, \App\Modules\Catalogue\Models\PrintBatch::where('printer', 'like', 'BROWNFIELD%')->count());
    }

    public function test_variance_opens_persistent_case_with_severity_sla_and_governed_transitions(): void
    {
        StockRecord::post($this->warehouse->id, $this->title->id, 'AVAILABLE', 100);
        $this->actingAs($this->admin)->post(route('shipments.store'), [
            'origin_warehouse_id' => $this->warehouse->id, 'destination_school_id' => $this->school->id,
            'textbook_title_id' => $this->title->id, 'books' => 50,
        ]);
        $shipment = \App\Modules\Custody\Models\Shipment::latest('id')->first();
        $this->actingAs($this->admin)->post(route('shipments.approve', $shipment));
        $this->actingAs($this->admin)->post(route('shipments.dispatch', $shipment), ['carrier' => 'C', 'waybill' => 'W']);
        $this->actingAs($this->admin)->post(route('shipments.receive', $shipment), ['received_books' => 45]);

        $case = ExceptionCase::first();
        $this->assertNotNull($case);
        $this->assertMatchesRegularExpression('/^EXC-\d{4}-\d{4}$/', $case->case_no);
        $this->assertSame('HIGH', $case->severity);
        $this->assertSame(24, $case->slaHours());   // severity default

        // resolution without a reason is refused
        $this->actingAs($this->admin)->post(route('cases.transition', $case), ['to' => 'INVESTIGATING']);
        $this->actingAs($this->admin)->post(route('cases.transition', $case), ['to' => 'RESOLVED']);
        $this->assertSame('INVESTIGATING', $case->fresh()->status);
        $this->actingAs($this->admin)->post(route('cases.transition', $case), ['to' => 'RESOLVED', 'reason' => 'Books found at depot']);
        $this->assertSame('RESOLVED', $case->fresh()->status);

        // HIGH closure is ministry-only
        $keeper = User::create(['name' => 'K', 'email' => 'k@t.cm', 'password' => 'x', 'role' => 'STOREKEEPER']);
        $this->actingAs($keeper)->post(route('cases.transition', $case), ['to' => 'CLOSED', 'reason' => 'done']);
        $this->assertSame('RESOLVED', $case->fresh()->status);
        $this->actingAs($this->admin)->post(route('cases.transition', $case), ['to' => 'CLOSED', 'reason' => 'Reconciled and archived']);
        $this->assertSame('CLOSED', $case->fresh()->status);

        $this->actingAs($this->admin)->get(route('cases.show', $case))->assertOk()->assertSee($case->case_no);
        $this->actingAs($this->admin)->get('/exceptions')->assertOk()->assertSee('Case registry');
    }
}
