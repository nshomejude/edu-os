<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ADM-02 system configuration + LOG-04 route notes.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->string('value', 200);
            $table->timestamps();
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->string('route_note', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trips', fn (Blueprint $t) => $t->dropColumn('route_note'));
        Schema::dropIfExists('app_settings');
    }
};
