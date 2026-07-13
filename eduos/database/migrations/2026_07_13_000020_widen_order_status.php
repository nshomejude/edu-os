<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PROC lifecycle: SUBMITTED → APPROVED → PARTIALLY_DELIVERED → DELIVERED needs a plain string
        Schema::table('procurement_orders', fn (Blueprint $t) => $t->string('status', 24)->default('SUBMITTED')->change());
    }
};
