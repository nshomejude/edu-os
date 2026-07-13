<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // EXC: persistent case registry with ownership, severity and a governed status machine
        Schema::create('exception_cases', function (Blueprint $t) {
            $t->id();
            $t->string('case_no', 20)->unique();          // EXC-{YEAR}-{SEQ}
            $t->string('type', 20);                        // DISCREPANCY | INCIDENT | INSPECTION | ESCALATION
            $t->unsignedBigInteger('source_id')->nullable();
            $t->string('title', 200);
            $t->string('severity', 10);                    // LOW | MEDIUM | HIGH | CRITICAL
            $t->string('status', 20)->default('OPEN');
            $t->string('assigned_to', 120)->nullable();
            $t->string('opened_by', 120);
            $t->string('reason', 300)->nullable();         // resolution / rejection reason
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });
    }
};
