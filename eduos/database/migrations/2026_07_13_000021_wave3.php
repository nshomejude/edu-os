<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // INV-08 / FR-NWD-04: adjustments as governed requests (Requested → Approved/Rejected → Posted)
        Schema::create('stock_adjustments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('warehouse_id');
            $t->foreignId('textbook_title_id');
            $t->integer('delta');
            $t->string('reason', 20);
            $t->string('note', 200)->nullable();
            $t->string('requested_by', 120);
            $t->string('status', 12)->default('REQUESTED');   // REQUESTED | APPROVED | REJECTED
            $t->string('decided_by', 120)->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
        });

        // SHIP: PARTIALLY_RECEIVED joins the state machine (enum → string)
        Schema::table('shipments', fn (Blueprint $t) => $t->string('status', 28)->default('DRAFT')->change());

        // VER numbering series
        Schema::table('inspections', fn (Blueprint $t) => $t->string('ver_no', 16)->nullable());
    }
};
