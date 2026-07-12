<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 86-screen specification foundations: allocation planning (PLAN), logistics
// (LOG), stock transaction journal (INV-06/07), shipment approval (SHIP-06),
// ISBN (BOOK-05), verification evidence (VER-03), MFA (AUTH-04).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('academic_year', 9);
            $table->enum('status', ['DRAFT', 'REVIEW', 'APPROVED', 'EXECUTING', 'CLOSED'])->default('DRAFT');
            $table->string('created_by', 120);
            $table->string('approved_by', 120)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_campaign_id')->constrained();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->foreignId('shipment_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 20)->unique();
            $table->string('model', 80);
            $table->unsignedInteger('capacity_books')->default(10000);
            $table->enum('status', ['AVAILABLE', 'ON_TRIP', 'MAINTENANCE'])->default('AVAILABLE');
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('licence_no', 40)->unique();
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['AVAILABLE', 'ON_TRIP', 'SUSPENDED'])->default('AVAILABLE');
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained();
            $table->foreignId('vehicle_id')->nullable()->constrained();
            $table->foreignId('driver_id')->nullable()->constrained();
            $table->enum('status', ['PLANNED', 'EN_ROUTE', 'ARRIVED', 'INCIDENT'])->default('PLANNED');
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->string('incident_note', 500)->nullable();
            $table->timestamps();
        });

        // Stock transaction journal: every ledger mutation becomes a row (INV-07)
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->string('stock_class', 20);
            $table->integer('delta');
            $table->unsignedInteger('balance_after');
            $table->string('actor', 120)->nullable();
            $table->string('context', 160)->nullable();
            $table->timestamps();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->string('approved_by', 120)->nullable();
            $table->timestamp('approved_at')->nullable();
        });

        Schema::table('textbook_titles', function (Blueprint $table) {
            $table->string('isbn', 17)->nullable();
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->string('evidence_path')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('totp_secret', 64)->nullable();
            $table->boolean('mfa_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['totp_secret', 'mfa_enabled']));
        Schema::table('inspections', fn (Blueprint $t) => $t->dropColumn('evidence_path'));
        Schema::table('textbook_titles', fn (Blueprint $t) => $t->dropColumn('isbn'));
        Schema::table('shipments', fn (Blueprint $t) => $t->dropColumn(['approved_by', 'approved_at']));
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('allocations');
        Schema::dropIfExists('distribution_campaigns');
    }
};
