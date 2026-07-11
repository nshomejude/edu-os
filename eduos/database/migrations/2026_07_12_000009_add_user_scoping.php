<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Row-level scoping anchors for the full FRS role matrix.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->foreignId('division_id')->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropConstrainedForeignId('division_id');
        });
    }
};
