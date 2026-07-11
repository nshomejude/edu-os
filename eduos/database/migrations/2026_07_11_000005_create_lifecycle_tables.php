<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Full lifecycle depth: editions, per-copy passports, school operations,
// verification campaigns, redistribution — FRS 04 §4/§5, FR-NTR-12, FR-NWD-11.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('textbook_titles', function (Blueprint $table) {
            $table->enum('tracking_granularity', ['COPY', 'BATCH'])->default('BATCH');
        });

        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedTinyInteger('edition_no')->default(1);
            $table->string('effective_academic_year', 9);
            $table->string('changes_summary', 300)->nullable();
            $table->boolean('superseded')->default(false);
            $table->timestamps();
        });

        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->string('ncid', 48)->unique();
            $table->foreignId('print_batch_id')->constrained();
            $table->enum('lifecycle_state', [
                'PRINTED', 'IN_WAREHOUSE', 'IN_TRANSIT', 'AT_SCHOOL',
                'ASSIGNED', 'UNDER_REPAIR', 'LOST', 'RETIRED', 'DISPOSED',
            ])->default('PRINTED');
            $table->foreignId('current_school_id')->nullable()->constrained('schools');
            $table->enum('condition', ['NEW', 'GOOD', 'FAIR', 'POOR', 'UNUSABLE'])->default('NEW');
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->string('class_level', 4);              // class-level fallback, FR-NTR-SM-02
            $table->string('academic_year', 9);
            $table->unsignedInteger('quantity');
            $table->enum('status', ['ASSIGNED', 'RETURNED'])->default('ASSIGNED');
            $table->enum('condition_on_return', ['GOOD', 'FAIR', 'POOR', 'UNUSABLE'])->nullable();
            $table->string('actor', 120);
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('academic_year', 9);
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedInteger('expected');
            $table->unsignedInteger('counted');
            $table->string('submitted_by', 120);
            $table->timestamps();
            $table->unique(['campaign_id', 'school_id', 'textbook_title_id'], 'uq_campaign_school_title');
        });

        Schema::create('redistribution_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_school_id')->constrained('schools');
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->string('reason', 300);
            $table->enum('status', ['PROPOSED', 'APPROVED', 'REJECTED'])->default('PROPOSED');
            $table->foreignId('shipment_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('discrepancy_resolution', ['ACCEPT_SHORT', 'FOUND', 'WRITE_OFF'])->nullable();
            $table->timestamp('resolved_at')->nullable();
        });

        Schema::table('enrolments', function (Blueprint $table) {
            $table->enum('validation_status', ['SUBMITTED', 'VALIDATED', 'REJECTED'])->default('VALIDATED');
        });
    }

    public function down(): void
    {
        Schema::table('enrolments', fn (Blueprint $t) => $t->dropColumn('validation_status'));
        Schema::table('shipments', fn (Blueprint $t) => $t->dropColumn(['discrepancy_resolution', 'resolved_at']));
        Schema::dropIfExists('redistribution_proposals');
        Schema::dropIfExists('campaign_submissions');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('copies');
        Schema::dropIfExists('editions');
        Schema::table('textbook_titles', fn (Blueprint $t) => $t->dropColumn('tracking_granularity'));
    }
};
