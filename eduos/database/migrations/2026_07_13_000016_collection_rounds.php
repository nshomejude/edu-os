<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // End-of-year collection cycle: every open assignment must come back or be declared lost
        Schema::create('collection_rounds', function (Blueprint $t) {
            $t->id();
            $t->string('academic_year', 9);
            $t->string('status', 20)->default('OPEN');   // OPEN | CLOSED
            $t->string('opened_by', 120);
            $t->timestamp('opened_at');
            $t->timestamp('closed_at')->nullable();
            $t->unsignedInteger('returned_count')->default(0);
            $t->unsignedInteger('lost_count')->default(0);
            $t->timestamps();
        });
    }
};
