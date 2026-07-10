<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bounded context: Catalogue (NTR titles) — FRS EDUOS-FRS-NTR-001
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textbook_titles', function (Blueprint $table) {
            $table->id();
            $table->string('ntid', 32)->unique();  // CM-TB-{MIN}-{SUBJ}-{GRADE}-{LANG}-{SEQ}-{ED}
            $table->string('title_en', 300)->nullable();
            $table->string('title_fr', 300)->nullable();
            $table->enum('ministry', ['MINEDUB', 'MINESEC']);
            $table->string('subject_code', 3);
            $table->string('grade_code', 2);
            $table->enum('language', ['EN', 'FR', 'BI']);
            $table->enum('status', ['DRAFT', 'APPROVED', 'SUSPENDED', 'RETIRED'])->default('DRAFT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textbook_titles');
    }
};
