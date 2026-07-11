<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Literal copy↔shipment custody binding + user activation flag.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('copies', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->constrained();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_active'));
        Schema::table('copies', fn (Blueprint $t) => $t->dropConstrainedForeignId('shipment_id'));
    }
};
