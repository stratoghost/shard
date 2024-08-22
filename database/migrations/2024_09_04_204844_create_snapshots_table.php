<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('time_clock_id');
            $table->unsignedBigInteger('terminal_id');
            $table->unsignedBigInteger('session_id');

            $table->unsignedBigInteger('minutes_given')->nullable();
            $table->unsignedBigInteger('minutes_expected')->nullable();

            $table->bigInteger('balance')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
