<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bounded context: Custody & Logistics (NWIDMS) — FRS EDUOS-FRS-NWD-001
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('wh_id', 16)->unique(); // CM-WH-{REG}-{SEQ}
            $table->string('name', 120);
            $table->enum('tier', ['NATIONAL', 'REGIONAL', 'DIVISIONAL']);
            $table->foreignId('region_id')->constrained();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no', 20)->unique(); // SHP-YYYY-NNNNNN
            $table->string('origin_name', 120);
            $table->string('destination_name', 120);
            $table->enum('status', [
                'DRAFT', 'CONFIRMED', 'LOADED', 'DISPATCHED', 'IN_TRANSIT',
                'ARRIVED', 'RECEIPT_IN_PROGRESS', 'RECEIVED_FULL',
                'RECEIVED_WITH_DISCREPANCY', 'CLOSED', 'CANCELLED', 'LOST_IN_TRANSIT',
            ])->default('DRAFT');
            $table->unsignedInteger('books');
            $table->date('shipped_on');
            $table->timestamps();
        });

        // Demo-stage national aggregates. Replaced by event-stream projections
        // (PassportEvent/CustodyEvent) when the sync engine lands — ADR-09.
        Schema::create('national_stats', function (Blueprint $table) {
            $table->string('key', 40)->primary();
            $table->unsignedBigInteger('value');
            $table->decimal('delta_pct', 5, 1)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('national_stats');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('warehouses');
    }
};
