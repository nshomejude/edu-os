<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PROC-06/07: damaged units rejected at supplier-delivery verification
        Schema::table('procurement_orders', fn (Blueprint $t) => $t->unsignedInteger('damaged_qty')->default(0));
    }
};
