<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Final gap closure: enrolment rejection reasons, inspection follow-up,
// inter-warehouse transfers, warehouse cycle counts (FR-NWD-04).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {
            $table->string('rejection_reason', 300)->nullable();
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->string('corrective_action', 500)->nullable();
            $table->timestamp('resolved_at')->nullable();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses');
        });

        Schema::create('warehouse_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('textbook_title_id')->constrained();
            $table->unsignedInteger('ledger_qty');
            $table->unsignedInteger('counted_qty');
            $table->string('actor', 120);
            $table->string('note', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_counts');
        Schema::table('shipments', fn (Blueprint $t) => $t->dropConstrainedForeignId('destination_warehouse_id'));
        Schema::table('inspections', fn (Blueprint $t) => $t->dropColumn(['corrective_action', 'resolved_at']));
        Schema::table('enrolments', fn (Blueprint $t) => $t->dropColumn('rejection_reason'));
    }
};
