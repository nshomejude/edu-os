<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Batch recall (post-distribution QA failure)
        Schema::table('print_batches', function (Blueprint $t) {
            $t->timestamp('recalled_at')->nullable();
            $t->string('recall_reason', 200)->nullable();
        });

        // Governed disposal: every disposed copy gets a certificate
        Schema::create('disposals', function (Blueprint $t) {
            $t->id();
            $t->string('ncid', 64);
            $t->foreignId('textbook_title_id');
            $t->string('reason', 200);
            $t->string('location', 160)->nullable();
            $t->string('actor', 120);
            $t->timestamps();
        });

        // Replacement fees for books declared lost at collection close
        Schema::create('replacement_charges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id');
            $t->foreignId('textbook_title_id');
            $t->unsignedInteger('quantity');
            $t->unsignedInteger('amount_fcfa');
            $t->string('academic_year', 9);
            $t->string('status', 20)->default('OUTSTANDING');   // OUTSTANDING | SETTLED
            $t->string('settled_by', 120)->nullable();
            $t->timestamp('settled_at')->nullable();
            $t->timestamps();
        });

        // EN/FR language-pair linkage between titles
        Schema::table('textbook_titles', fn (Blueprint $t) => $t->foreignId('counterpart_id')->nullable());
    }
};
