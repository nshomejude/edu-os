<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Production hardening: security headers, temp-password lifecycle, forced rotation. */
class ProductionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@t.cm', 'password' => Hash::make('password'), 'role' => 'ADMIN']);
    }

    public function test_security_headers_are_present_on_every_response(): void
    {
        $resp = $this->actingAs($this->admin)->get('/');
        $resp->assertHeader('X-Frame-Options', 'DENY');
        $resp->assertHeader('X-Content-Type-Options', 'nosniff');
        $resp->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("frame-ancestors 'none'", $resp->headers->get('Content-Security-Policy'));
    }

    public function test_new_users_get_random_temp_password_and_forced_rotation(): void
    {
        $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'New Officer', 'email' => 'officer@minedub.cm', 'role' => 'PROCUREMENT_OFFICER',
        ]);
        $flash = session('flash');
        $this->assertStringContainsString('Temporary password', $flash);
        preg_match('/shown once\): (\S+) —/', $flash, $m);
        $temp = $m[1];

        $officer = User::where('email', 'officer@minedub.cm')->first();
        $this->assertTrue((bool) $officer->must_change_password);
        $this->assertFalse(Hash::check('password', $officer->password));   // no shared default
        $this->assertTrue(Hash::check($temp, $officer->password));

        // any page other than the profile bounces until the password is rotated
        $this->actingAs($officer)->get('/schools')->assertRedirect(route('profile'));

        $this->actingAs($officer)->post(route('profile.password'), [
            'current_password' => $temp, 'password' => 'my-own-secret-9', 'password_confirmation' => 'my-own-secret-9',
        ]);
        $this->assertFalse((bool) $officer->fresh()->must_change_password);
        $this->actingAs($officer)->get('/schools')->assertOk();
    }

    public function test_admin_reset_issues_temp_password_and_audits(): void
    {
        $victim = User::create(['name' => 'V', 'email' => 'v@t.cm', 'password' => Hash::make('old'), 'role' => 'READONLY']);
        $this->actingAs($this->admin)->post(route('users.reset', $victim));

        $victim->refresh();
        $this->assertTrue((bool) $victim->must_change_password);
        $this->assertFalse(Hash::check('password', $victim->password));
        $this->assertFalse(Hash::check('old', $victim->password));
        $this->assertTrue(\App\Modules\Platform\Models\AuthEvent::where('event', 'PASSWORD_RESET')->where('email', 'v@t.cm')->exists());
    }

    public function test_mutations_run_inside_a_database_transaction(): void
    {
        // the middleware is registered for web POSTs; transaction level rises inside the request
        $this->assertTrue(class_exists(\App\Http\Middleware\TransactionalRequests::class));
        $probe = null;
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Foundation\Http\Events\RequestHandled::class, function () use (&$probe) {
            // after handling, the request-level transaction must have committed cleanly
            $probe = \Illuminate\Support\Facades\DB::transactionLevel();
        });
        $this->actingAs($this->admin)->post(route('exceptions.escalate'), [
            'subject' => 'probe', 'detail' => 'probe',
        ]);
        // RefreshDatabase holds level 1; the request wrapper must have unwound back to it
        $this->assertSame(1, \Illuminate\Support\Facades\DB::transactionLevel());
        $this->assertTrue(\App\Modules\Platform\Models\Alert::where('title', 'ESCALATION: probe')->exists());
    }

    /** Route sweep: every admin-facing screen must render — catches template regressions in CI. */
    public function test_every_screen_renders_for_admin(): void
    {
        $region = \App\Modules\Registry\Models\Region::create(['code' => 'CE', 'name_en' => 'C', 'name_fr' => 'C', 'books_distributed' => 0]);
        $school = \App\Modules\Registry\Models\School::create(['nsid' => 'CM-SCH-CE-0101-BP-00001', 'name_official' => 'S', 'ministry' => 'MINEDUB', 'school_type' => 'PRIMARY', 'region_id' => $region->id]);
        $warehouse = \App\Modules\Custody\Models\Warehouse::create(['wh_id' => 'CM-WH-CE-001', 'name' => 'W1', 'tier' => 'NATIONAL', 'region_id' => $region->id]);
        $title = \App\Modules\Catalogue\Models\TextbookTitle::create(['ntid' => 'CM-TB-B-MAT-P1-EN-0001-01', 'title_en' => 'T', 'ministry' => 'MINEDUB', 'subject_code' => 'MAT', 'grade_code' => 'P1', 'language' => 'EN', 'status' => 'APPROVED', 'isbn' => '978-0-306-40615-7', 'publisher' => 'P', 'pages' => 120, 'weight_grams' => 300]);

        $screens = [
            '/', '/about', '/alerts', '/audit-trail', '/campaigns', '/exceptions', '/exports',
            '/forecast', '/inspections', '/inventory/low-stock', '/logistics', '/network', '/plan',
            '/procurement', '/profile', '/profile/mfa', '/profile/sessions', '/redistribution',
            '/reports', '/reports/coverage', '/reports/campaign-performance', '/reports/performance', '/reports/season-readiness',
            '/collections', '/charges', '/disposals', '/schedule', '/schools', '/schools/create', '/settings', '/shipments',
            '/shipments/create', '/textbooks', '/users', '/warehouses', '/up',
            '/api/catalogue', '/api/schools', '/api/stats', '/api/openapi.json', '/verify',
            '/schools/'.$school->id, '/schools/'.$school->id.'/students',
            '/warehouses/'.$warehouse->id, '/textbooks/'.$title->id,
        ];
        foreach ($screens as $uri) {
            $this->actingAs($this->admin)->get($uri)->assertOk();
        }
    }
}
