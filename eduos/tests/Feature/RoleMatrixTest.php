<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Registry\Models\Region;
use App\Modules\Registry\Models\School;
use App\Modules\Catalogue\Models\TextbookTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create(['name' => $role, 'email' => strtolower($role).'@t.cm', 'password' => 'x', 'role' => $role]);
    }

    public function test_full_frs_role_matrix_separation_of_duties(): void
    {
        $region = Region::create(['code' => 'CE', 'name_en' => 'C', 'name_fr' => 'C', 'books_distributed' => 0]);
        School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'S', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $title = TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN']);

        // Curriculum officer approves titles; procurement officer cannot
        $this->actingAs($this->user('CURRICULUM_OFFICER'))->post(route('textbooks.transition', $title), ['to' => 'APPROVED']);
        $this->assertSame('APPROVED', $title->fresh()->status);
        $this->actingAs($this->user('PROCUREMENT_OFFICER'))->post(route('textbooks.transition', $title), ['to' => 'RETIRED'])->assertForbidden();

        // Procurement officer reaches procurement; curriculum officer cannot
        $this->actingAs(User::where('role', 'PROCUREMENT_OFFICER')->first())->get('/procurement')->assertOk();
        $this->actingAs(User::where('role', 'CURRICULUM_OFFICER')->first())->get('/procurement')->assertForbidden();

        // Inspector reaches inspections; storekeeper cannot; inspector cannot approve titles
        $this->actingAs($this->user('INSPECTOR'))->get('/inspections')->assertOk();
        $this->actingAs($this->user('STOREKEEPER'))->get('/inspections')->assertForbidden();
        $this->actingAs(User::where('role', 'INSPECTOR')->first())->post(route('textbooks.store'), [])->assertForbidden();

        // Auditor and read-only: pages OK, mutations forbidden
        foreach (['AUDITOR', 'READONLY'] as $ro) {
            $u = $this->user($ro);
            $this->actingAs($u)->get('/shipments')->assertOk();
            $this->actingAs($u)->post(route('textbooks.store'), [])->assertForbidden();
            $this->actingAs($u)->post(route('shipments.store'), [])->assertForbidden();
        }

        // Division officer validates enrolment routes; teacher cannot
        $this->actingAs($this->user('TEACHER'))->post(route('redistribution.generate'))->assertForbidden();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $u = $this->user('ADMIN');
        $u->forceFill(['password' => \Illuminate\Support\Facades\Hash::make('password'), 'is_active' => false])->save();
        $this->post('/login', ['email' => $u->email, 'password' => 'password']);
        $this->assertGuest();
    }
}
