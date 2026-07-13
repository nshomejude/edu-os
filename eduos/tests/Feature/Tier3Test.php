<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\Copy;
use App\Modules\Catalogue\Models\CurriculumVersion;
use App\Modules\Catalogue\Models\Disposal;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Platform\Models\Alert;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\SchoolOps\Models\ReplacementCharge;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Batch recall, disposal certificates, replacement fees, curriculum retirement, EN/FR pairing. */
class Tier3Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $school;
    private TextbookTitle $title;
    private PrintBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Nkolbisson', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->batch = PrintBatch::create(['batch_no' => 'BAT-2026-90001', 'textbook_title_id' => $this->title->id, 'printer' => 'Print Co', 'quantity' => 3]);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
    }

    private function copy(string $state, ?int $schoolId = null): Copy
    {
        static $n = 0;
        return Copy::create([
            'ncid' => $this->title->ntid.'-90001-'.str_pad(++$n, 6, '0', STR_PAD_LEFT),
            'print_batch_id' => $this->batch->id, 'lifecycle_state' => $state,
            'condition' => 'NEW', 'current_school_id' => $schoolId,
        ]);
    }

    public function test_batch_recall_pulls_copies_and_writes_down_school_stock(): void
    {
        $this->copy('AT_SCHOOL', $this->school->id);
        $this->copy('ASSIGNED', $this->school->id);
        $this->copy('IN_WAREHOUSE');
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 10, 'condition' => 'GOOD']);

        $this->actingAs($this->admin)->post(route('batches.recall.post', $this->batch), ['reason' => 'Binding defect — pages detach']);

        $this->assertSame(3, Copy::where('lifecycle_state', 'RECALLED')->count());
        $this->assertSame(8, (int) SchoolStock::first()->quantity);   // two school-held copies written down
        $this->assertNotNull($this->batch->fresh()->recalled_at);
        $this->assertTrue(Alert::where('title', 'like', 'Batch recall%')->exists());

        $this->actingAs($this->admin)->get(route('batches.recall', $this->batch))
            ->assertOk()->assertSee('Binding defect')->assertSee('RECALLED');
    }

    public function test_disposal_requires_ministry_tier_and_issues_certificate(): void
    {
        $copy = $this->copy('RETIRED');

        // storekeeper tier cannot dispose
        $keeper = User::create(['name' => 'K', 'email' => 'k@t.cm', 'password' => 'x', 'role' => 'STOREKEEPER']);
        $this->actingAs($keeper)->post(route('copies.transition', $copy), ['to' => 'DISPOSED', 'reason' => 'x']);
        $this->assertSame('RETIRED', $copy->fresh()->lifecycle_state);

        // ministry tier disposes with a reason → certificate
        $this->actingAs($this->admin)->post(route('copies.transition', $copy), [
            'to' => 'DISPOSED', 'reason' => 'Water damaged beyond repair',
        ])->assertRedirect();
        $this->assertSame('DISPOSED', $copy->fresh()->lifecycle_state);

        $disposal = Disposal::first();
        $this->assertSame($copy->ncid, $disposal->ncid);
        $this->actingAs($this->admin)->get(route('disposals.cert', $disposal))
            ->assertOk()->assertSee('CERTIFICATE OF DISPOSAL')->assertSee('Water damaged beyond repair');
        $this->actingAs($this->admin)->get('/disposals')->assertOk()->assertSee($copy->ncid);
    }

    public function test_collection_close_raises_replacement_charges_and_settlement(): void
    {
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 20, 'condition' => 'GOOD']);
        $this->actingAs($this->admin)->post(route('schoolops.assign', $this->school), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1', 'quantity' => 5,
        ]);
        $this->actingAs($this->admin)->post(route('collections.open'));
        $round = \App\Modules\SchoolOps\Models\CollectionRound::first();
        $this->actingAs($this->admin)->post(route('collections.close', $round));

        $charge = ReplacementCharge::first();
        $this->assertSame(5, (int) $charge->quantity);
        $this->assertSame(7500, (int) $charge->amount_fcfa);   // 5 × 1 500 FCFA default fee
        $this->assertSame('OUTSTANDING', $charge->status);

        $this->actingAs($this->admin)->get('/charges')->assertOk()->assertSee('7,500');
        $this->actingAs($this->admin)->post(route('charges.settle', $charge));
        $this->assertSame('SETTLED', $charge->fresh()->status);
        $this->assertSame('Admin', $charge->fresh()->settled_by);
    }

    public function test_curriculum_retirement_flags_mapped_titles(): void
    {
        $cv = CurriculumVersion::first();   // seeded ACTIVE
        $this->title->forceFill(['curriculum_version_id' => $cv->id])->save();

        $this->actingAs($this->admin)->post(route('curricula.retire', $cv));
        $this->assertSame('RETIRED', $cv->fresh()->status);
        $this->assertTrue(Alert::where('title', 'like', 'Curriculum retired%')->exists());

        $this->actingAs($this->admin)->get(route('textbooks.show', $this->title))
            ->assertOk()->assertSee('has been retired');
    }

    public function test_language_counterparts_link_both_directions(): void
    {
        $fr = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-FR-0001-01', 'title_fr' => 'Maths CP', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'FR', 'status' => 'APPROVED']);

        $this->actingAs($this->admin)->post(route('textbooks.counterpart', $this->title), ['counterpart_id' => $fr->id]);
        $this->assertSame($fr->id, (int) $this->title->fresh()->counterpart_id);
        $this->assertSame($this->title->id, (int) $fr->fresh()->counterpart_id);

        $this->actingAs($this->admin)->get(route('textbooks.show', $this->title))
            ->assertOk()->assertSee('CM-TB-B-MAT-P1-FR-0001-01');

        // same-language pairing refused
        $en2 = TextbookTitle::create(['ntid' => 'CM-TB-B-ENG-P1-EN-0001-01', 'title_en' => 'E', 'ministry' => 'MINEDUB', 'subject_code' => 'ENG', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->actingAs($this->admin)->post(route('textbooks.counterpart', $this->title), ['counterpart_id' => $en2->id]);
        $this->assertSame($fr->id, (int) $this->title->fresh()->counterpart_id);   // unchanged
    }

    public function test_settling_a_charge_issues_replacement_or_queues_requirement(): void
    {
        // charge 1: free school stock exists → replacement assignment issued immediately
        SchoolStock::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id, 'quantity' => 10, 'condition' => 'GOOD']);
        $c1 = ReplacementCharge::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id,
            'quantity' => 5, 'amount_fcfa' => 7500, 'academic_year' => '2025/2026']);
        $this->actingAs($this->admin)->post(route('charges.settle', $c1));
        $repl = \App\Modules\SchoolOps\Models\Assignment::where('class_level', 'REPL')->first();
        $this->assertNotNull($repl);
        $this->assertSame(5, (int) $repl->quantity);

        // charge 2: not enough free stock → queued as a requirement for the next campaign
        $c2 = ReplacementCharge::create(['school_id' => $this->school->id, 'textbook_title_id' => $this->title->id,
            'quantity' => 50, 'amount_fcfa' => 75000, 'academic_year' => '2025/2026']);
        $this->actingAs($this->admin)->post(route('charges.settle', $c2));
        $req = \App\Modules\Planning\Models\SchoolRequirement::first();
        $this->assertNotNull($req);
        $this->assertSame(50, (int) $req->quantity);
        $this->assertSame('SUBMITTED', $req->status);
    }

    public function test_forecast_prefills_a_procurement_order(): void
    {
        $this->actingAs($this->admin)->get('/procurement?title='.$this->title->id.'&qty=777')
            ->assertOk()->assertSee('value="777"', false)->assertSee('selected', false);
    }
}
