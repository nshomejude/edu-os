<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Return chain: shipments can originate at a school (books flowing back up the network)
        Schema::table('shipments', fn (Blueprint $t) => $t->unsignedBigInteger('origin_school_id')->nullable());
    }
};
