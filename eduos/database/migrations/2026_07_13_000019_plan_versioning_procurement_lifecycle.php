<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PLAN: demand freeze + immutable-version amendments
        Schema::table('distribution_campaigns', function (Blueprint $t) {
            $t->timestamp('demand_frozen_at')->nullable();
            $t->string('formula_version', 20)->nullable();
            $t->unsignedInteger('version')->default(1);
            $t->unsignedBigInteger('parent_id')->nullable();
        });

        // PROC: approval step + partial deliveries
        Schema::table('procurement_orders', fn (Blueprint $t) => $t->unsignedInteger('delivered_total')->default(0));
        Schema::table('print_batches', fn (Blueprint $t) => $t->unsignedBigInteger('procurement_order_id')->nullable());
    }
};
