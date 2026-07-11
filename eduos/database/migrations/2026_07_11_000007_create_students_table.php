<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Light Student Registry: enables student-level assignment (FR-NTR-SM-02 upgrade path).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('lsid', 24)->unique();   // CM-STU-{SEQ}
            $table->string('name', 160);
            $table->enum('sex', ['M', 'F']);
            $table->string('class_level', 4);
            $table->foreignId('school_id')->constrained();
            $table->string('academic_year', 9)->default('2025/2026');
            $table->timestamps();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', fn (Blueprint $t) => $t->dropConstrainedForeignId('student_id'));
        Schema::dropIfExists('students');
    }
};
