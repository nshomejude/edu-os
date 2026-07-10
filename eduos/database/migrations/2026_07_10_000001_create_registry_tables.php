<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bounded context: Registry (NSR) — FRS EDUOS-FRS-NSR-001
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();   // AD, CE, EN, ES, LT, NO, NW, OU, SU, SW
            $table->string('name_en', 60);
            $table->string('name_fr', 60);
            $table->unsignedBigInteger('books_distributed')->default(0); // demo aggregate; event-derived later
            $table->timestamps();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('nsid', 24)->unique();  // CM-SCH-{REG}-{DIV}{SUB}-{MIN}{TYPE}-{SEQ}
            $table->string('name_official', 300);
            $table->enum('ministry', ['MINEDUB', 'MINESEC']);
            $table->enum('school_type', ['NURSERY', 'PRIMARY', 'GEN_SEC', 'TECH_SEC', 'COMBINED']);
            $table->foreignId('region_id')->constrained();
            $table->enum('status', ['PROPOSED', 'AUTHORIZED', 'OPERATIONAL', 'TEMPORARILY_CLOSED', 'CLOSED'])->default('OPERATIONAL');
            $table->enum('accessibility_class', ['URBAN', 'RURAL_ROAD', 'RURAL_SEASONAL', 'REMOTE'])->default('URBAN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
        Schema::dropIfExists('regions');
    }
};
