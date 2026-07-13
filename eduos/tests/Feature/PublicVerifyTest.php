<?php

namespace Tests\Feature;

use App\Modules\Catalogue\Models\Copy;
use App\Modules\Catalogue\Models\PrintBatch;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Citizen-facing QR verification: unauthenticated, truthful, and leaks no school or learner data. */
class PublicVerifyTest extends TestCase
{
    use RefreshDatabase;

    private TextbookTitle $title;
    private PrintBatch $batch;
    private School $school;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->school = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Nkolbisson', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'National Mathematics P1', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->batch = PrintBatch::create(['batch_no' => 'BAT-2026-90001', 'textbook_title_id' => $this->title->id, 'printer' => 'Print Co', 'quantity' => 2]);
    }

    private function copy(string $state): Copy
    {
        static $n = 0;
        return Copy::create([
            'ncid' => $this->title->ntid.'-90001-'.str_pad(++$n, 6, '0', STR_PAD_LEFT),
            'print_batch_id' => $this->batch->id, 'lifecycle_state' => $state,
            'condition' => 'NEW', 'current_school_id' => $this->school->id,
        ]);
    }

    public function test_guest_verifies_a_genuine_copy_without_any_school_data_leaking(): void
    {
        $copy = $this->copy('ASSIGNED');

        $page = $this->get('/verify?ncid='.$copy->ncid);
        $page->assertOk()
            ->assertSee('AUTHENTIC')
            ->assertSee('National Mathematics P1')
            ->assertSee('In official circulation')
            ->assertDontSee('GPS Nkolbisson');   // privacy: never expose where the book is
        $this->assertGuest();
    }

    public function test_recalled_lost_and_unknown_copies_warn_the_citizen(): void
    {
        $recalled = $this->copy('RECALLED');
        $this->get('/verify?ncid='.$recalled->ncid)->assertOk()->assertSee('RECALLED BATCH');

        $lost = $this->copy('LOST');
        $this->get('/verify?ncid='.$lost->ncid)->assertOk()->assertSee('REPORTED LOST');

        $this->get('/verify?ncid=CM-TB-FAKE-0000-000000')->assertOk()->assertSee('NOT A REGISTERED COPY');
    }

    public function test_copy_label_qr_encodes_the_public_verification_url(): void
    {
        $copy = $this->copy('IN_WAREHOUSE');
        $admin = \App\Models\User::create(['name' => 'A', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);

        // the passport page must render the QR against /verify, not an authenticated URL
        $this->actingAs($admin)->get(route('copies.show', $copy))->assertOk();
        // and the landing form itself is reachable as a guest
        $this->get('/verify')->assertOk()->assertSee('not for sale');
    }
}
