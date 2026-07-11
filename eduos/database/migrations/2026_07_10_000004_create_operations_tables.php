<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Operational depth for Phase-I flows: batches, stock ledger, custody &
// passport events, school stock, enrolments, alerts. FRS 04/07/08.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no', 24)->unique();
            $table->foreignId('textbook_title_id')->constrained();
            $table->string('printer', 120);
            $table->unsignedInteger('quantity');
            $table->enum('qa_status', ['PENDING', 'PASSED', 'FAILED'])->default('PENDING');
            $table->unsignedInteger('received_qty')->default(0);
            $table->timestamps();
        });

        Schema::create('stock_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->enum('stock_class', ['AVAILABLE', 'RESERVED', 'IN_TRANSIT_OUT', 'DAMAGED', 'QUARANTINE'])->default('AVAILABLE');
            $table->unsignedInteger('quantity')->default(0);
            $table->unique(['warehouse_id', 'textbook_title_id', 'stock_class'], 'uq_stock');
            $table->timestamps();
        });

        Schema::create('custody_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained();
            $table->string('event_type', 40);   // CONFIRMED, LOADED, DISPATCHED, ARRIVED, RECEIVED, DISCREPANCY_OPENED...
            $table->string('actor', 120);
            $table->string('notes', 300)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('passport_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_batch_id')->constrained();
            $table->string('event_type', 40);   // PRINTED, QA_PASSED, WAREHOUSE_RECEIPT, DISPATCHED, SCHOOL_RECEIPT...
            $table->string('location', 120);
            $table->string('actor', 120);
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('school_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedInteger('quantity')->default(0);
            $table->enum('condition', ['GOOD', 'FAIR', 'POOR'])->default('GOOD');
            $table->timestamps();
        });

        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->string('academic_year', 9);   // 2025/2026
            $table->string('class_level', 4);
            $table->unsignedInteger('boys')->default(0);
            $table->unsignedInteger('girls')->default(0);
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
            $table->string('title', 160);
            $table->string('message', 500);
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('origin_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('destination_school_id')->nullable()->constrained('schools');
            $table->foreignId('textbook_title_id')->nullable()->constrained();
            $table->unsignedInteger('received_books')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('ADMIN');   // ADMIN, WAREHOUSE_OFFICER, SCHOOL_HEAD
            $table->string('ministry', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'ministry']);
        });
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['origin_warehouse_id', 'destination_school_id', 'textbook_title_id', 'received_books']);
        });
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('enrolments');
        Schema::dropIfExists('school_stocks');
        Schema::dropIfExists('passport_events');
        Schema::dropIfExists('custody_events');
        Schema::dropIfExists('stock_records');
        Schema::dropIfExists('print_batches');
    }
};
