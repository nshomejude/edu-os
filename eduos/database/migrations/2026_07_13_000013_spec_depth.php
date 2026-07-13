<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AUTH-01 §M: every authentication event is auditable
        Schema::create('auth_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable();
            $t->string('email', 160);
            $t->string('event', 30);   // LOGIN_OK, LOGIN_FAIL, LOGIN_LOCKOUT, MFA_OK, MFA_FAIL, LOGOUT, PASSWORD_RESET
            $t->string('ip', 60)->nullable();
            $t->string('user_agent', 200)->nullable();
            $t->timestamps();
        });

        // PLAN-03: schools submit their own textbook requirements
        Schema::create('school_requirements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id');
            $t->foreignId('textbook_title_id');
            $t->string('academic_year', 9);
            $t->unsignedInteger('quantity');
            $t->string('note', 200)->nullable();
            $t->string('submitted_by', 120);
            $t->string('status', 20)->default('SUBMITTED');   // SUBMITTED | CONSIDERED
            $t->timestamps();
        });

        // VER-01: verification queue with assignment and due dates
        Schema::create('inspection_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id');
            $t->foreignId('inspector_id');
            $t->date('due_on');
            $t->string('status', 20)->default('ASSIGNED');   // ASSIGNED | DONE
            $t->string('assigned_by', 120);
            $t->timestamps();
        });

        // BOOK-04: curriculum versions titles map to
        Schema::create('curriculum_versions', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('cycle', 20);   // PRIMARY | SECONDARY
            $t->unsignedSmallInteger('year');
            $t->string('status', 20)->default('ACTIVE');
            $t->timestamps();
        });

        Schema::table('users', fn (Blueprint $t) => $t->text('recovery_codes')->nullable());
        Schema::table('trips', fn (Blueprint $t) => $t->string('route_stops', 400)->nullable());
        Schema::table('shipments', function (Blueprint $t) {
            $t->string('discrepancy_category', 30)->nullable();
            $t->string('discrepancy_evidence_path')->nullable();
            $t->string('received_signature', 120)->nullable();
        });
        Schema::table('textbook_titles', function (Blueprint $t) {
            $t->string('publisher', 160)->nullable();
            $t->unsignedSmallInteger('pages')->nullable();
            $t->unsignedInteger('weight_grams')->nullable();
            $t->foreignId('curriculum_version_id')->nullable();
        });

        DB::table('curriculum_versions')->insert([
            ['name' => 'Harmonised Primary Curriculum', 'cycle' => 'PRIMARY', 'year' => 2018, 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Competency-Based Secondary Curriculum', 'cycle' => 'SECONDARY', 'year' => 2021, 'status' => 'ACTIVE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
