<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Closes the remaining catalog gaps: admin hierarchy (FRS-NSR §3), school GPS &
// infrastructure, suppliers + procurement (Problems 8–17), inspections (61–70),
// warehouse locations (118), tamper-evident event hashes (FR-NTR-DM-02).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained();
            $table->string('code', 4);
            $table->string('name', 80);
            $table->timestamps();
        });

        Schema::create('subdivisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained();
            $table->string('code', 4);
            $table->string('name', 80);
            $table->timestamps();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('subdivision_id')->nullable()->constrained();
            $table->decimal('gps_lat', 9, 6)->nullable();
            $table->decimal('gps_lon', 9, 6)->nullable();
            $table->boolean('gps_verified')->default(false);
            $table->unsignedSmallInteger('classrooms_total')->nullable();
            $table->boolean('storage_secure')->default(false);
            $table->enum('grid_power', ['GRID', 'SOLAR', 'NONE'])->default('GRID');
            $table->enum('connectivity', ['NONE', '2G', '3G', '4G'])->default('3G');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->constrained();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->enum('type', ['PRINTER', 'PUBLISHER', 'LOGISTICS']);
            $table->string('contact', 160)->nullable();
            $table->timestamps();
        });

        Schema::create('procurement_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 24)->unique();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_fcfa');
            $table->string('contract_ref', 60);
            $table->enum('status', ['ORDERED', 'IN_PRODUCTION', 'DELIVERED', 'CANCELLED'])->default('ORDERED');
            $table->foreignId('print_batch_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->string('inspector', 120);
            $table->date('inspected_on');
            $table->foreignId('textbook_title_id')->nullable()->constrained();
            $table->unsignedInteger('recorded_qty')->default(0);
            $table->unsignedInteger('counted_qty')->default(0);
            $table->enum('outcome', ['CONFORM', 'MINOR_FINDINGS', 'MAJOR_FINDINGS'])->default('CONFORM');
            $table->string('findings', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('zone', 20);   // e.g. A1, B2
            $table->unsignedInteger('capacity')->default(10000);
            $table->timestamps();
        });

        // Tamper-evident hash chains (FR-NTR-DM-02): each event stores the previous
        // event's hash for the same subject; a verify command walks the chains.
        Schema::table('passport_events', function (Blueprint $table) {
            $table->string('prev_hash', 64)->nullable();
            $table->string('hash', 64)->nullable();
        });
        Schema::table('custody_events', function (Blueprint $table) {
            $table->string('prev_hash', 64)->nullable();
            $table->string('hash', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('custody_events', fn (Blueprint $t) => $t->dropColumn(['prev_hash', 'hash']));
        Schema::table('passport_events', fn (Blueprint $t) => $t->dropColumn(['prev_hash', 'hash']));
        Schema::dropIfExists('storage_locations');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('procurement_orders');
        Schema::dropIfExists('suppliers');
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('school_id'));
        Schema::table('schools', fn (Blueprint $t) => $t->dropColumn([
            'subdivision_id', 'gps_lat', 'gps_lon', 'gps_verified',
            'classrooms_total', 'storage_secure', 'grid_power', 'connectivity',
        ]));
        Schema::dropIfExists('subdivisions');
        Schema::dropIfExists('divisions');
    }
};
