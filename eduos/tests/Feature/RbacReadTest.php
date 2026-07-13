<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalogue\Models\TextbookTitle;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\Registry\Models\Student;
use App\Modules\SchoolOps\Models\ReplacementCharge;
use App\Modules\SchoolOps\Models\SchoolStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Read-side RBAC: people can VIEW only what their role requires — negative assertions included. */
class RbacReadTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $auditor;
    private User $admin;
    private School $schoolA;
    private School $schoolB;
    private TextbookTitle $title;

    protected function setUp(): void
    {
        parent::setUp();
        $region = Region::create(['code' => 'CE', 'name_en' => 'Centre', 'name_fr' => 'Centre', 'books_distributed' => 0]);
        $this->schoolA = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'GPS Alpha', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->schoolB = School::create(['nsid' => 'CM-SCH-CE-0101-BP-00002', 'name_official' => 'GPS Bravo', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $this->title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED']);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => 'x', 'role' => 'ADMIN']);
        $this->auditor = User::create(['name' => 'Aud', 'email' => 'aud@t.cm', 'password' => 'x', 'role' => 'AUDITOR']);
        $this->teacher = User::create(['name' => 'Tea', 'email' => 't@t.cm', 'password' => 'x', 'role' => 'TEACHER', 'school_id' => $this->schoolA->id]);
    }

    public function test_learner_records_are_scoped_to_the_operating_school(): void
    {
        $studentB = Student::create(['lsid' => 'CM-STU-0000001', 'name' => 'Child Bravo', 'sex' => 'M', 'class_level' => 'P1', 'school_id' => $this->schoolB->id, 'academic_year' => '2025/2026']);

        $this->actingAs($this->teacher)->get(route('schools.students', $this->schoolB))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('students.show', $studentB))->assertForbidden();
        $this->actingAs($this->teacher)->get(route('schools.students', $this->schoolA))->assertOk();
        $this->actingAs($this->auditor)->get(route('schools.students', $this->schoolB))->assertOk();   // oversight tier
    }

    public function test_audit_trail_is_for_oversight_tiers_only(): void
    {
        $this->actingAs($this->teacher)->get('/audit-trail')->assertForbidden();
        $this->actingAs($this->teacher)->get('/reports/audit.csv')->assertForbidden();
        $this->actingAs($this->auditor)->get('/audit-trail')->assertOk();
        $this->actingAs($this->admin)->get('/audit-trail')->assertOk();
    }

    public function test_user_directory_is_ministry_only(): void
    {
        $this->actingAs($this->teacher)->get('/users')->assertForbidden();
        $this->actingAs($this->auditor)->get('/users')->assertForbidden();
        $this->actingAs($this->admin)->get('/users')->assertOk();
    }

    public function test_teacher_cannot_operate_another_school(): void
    {
        SchoolStock::create(['school_id' => $this->schoolA->id, 'textbook_title_id' => $this->title->id, 'quantity' => 20, 'condition' => 'GOOD']);
        SchoolStock::create(['school_id' => $this->schoolB->id, 'textbook_title_id' => $this->title->id, 'quantity' => 20, 'condition' => 'GOOD']);

        // school B refused across every school-scoped mutation
        $this->actingAs($this->teacher)->post(route('schoolops.assign', $this->schoolB), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1', 'quantity' => 5,
        ])->assertForbidden();
        $this->actingAs($this->teacher)->post(route('schoolops.enrolment', $this->schoolB), [
            'class_level' => 'P1', 'boys' => 10, 'girls' => 10,
        ])->assertForbidden();
        $this->actingAs($this->teacher)->post(route('schoolops.requirement', $this->schoolB), [
            'textbook_title_id' => $this->title->id, 'quantity' => 10,
        ])->assertForbidden();
        $this->actingAs($this->teacher)->post(route('schools.students.store', $this->schoolB), [
            'name' => 'X', 'sex' => 'M', 'class_level' => 'P1',
        ])->assertForbidden();
        $this->actingAs($this->teacher)->post(route('collections.bulk'), [
            'school_id' => $this->schoolB->id, 'condition_on_return' => 'GOOD',
        ])->assertForbidden();

        // own school still works
        $this->actingAs($this->teacher)->post(route('schoolops.assign', $this->schoolA), [
            'textbook_title_id' => $this->title->id, 'class_level' => 'P1', 'quantity' => 5,
        ]);
        $this->assertSame(1, \App\Modules\SchoolOps\Models\Assignment::where('school_id', $this->schoolA->id)->count());
    }

    public function test_charges_ledger_is_scoped_for_school_roles(): void
    {
        ReplacementCharge::create(['school_id' => $this->schoolA->id, 'textbook_title_id' => $this->title->id, 'quantity' => 5, 'amount_fcfa' => 7500, 'academic_year' => '2025/2026']);
        ReplacementCharge::create(['school_id' => $this->schoolB->id, 'textbook_title_id' => $this->title->id, 'quantity' => 9, 'amount_fcfa' => 13500, 'academic_year' => '2025/2026']);

        $this->actingAs($this->teacher)->get('/charges')
            ->assertOk()->assertSee('GPS Alpha')->assertDontSee('GPS Bravo');
        $this->actingAs($this->admin)->get('/charges')
            ->assertOk()->assertSee('GPS Alpha')->assertSee('GPS Bravo');
    }

    public function test_sidebar_hides_gated_links_from_unauthorised_roles(): void
    {
        $page = $this->actingAs($this->teacher)->get('/');
        $page->assertOk()->assertDontSee('audit-trail');
        $this->assertStringNotContainsString('/users"', $page->getContent());
    }
}
