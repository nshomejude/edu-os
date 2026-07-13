<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Production password lifecycle: temp passwords must be rotated at first login
        Schema::table('users', fn (Blueprint $t) => $t->boolean('must_change_password')->default(false));
    }
};
