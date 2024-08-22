<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_clocks', function (Blueprint $table) {
            $table->id();

            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            $table->unsignedSmallInteger('duration')->nullable();

            $table->string('type');

            $table->unsignedBigInteger('session_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_clocks');
    }
};
