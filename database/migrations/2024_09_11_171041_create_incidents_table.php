<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('description')->nullable();

            $table->dateTime('started_at');
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            $table->string('type');
            $table->string('grade');

            $table->unsignedSmallInteger('time_to_resolution')->nullable();

            $table->unsignedBigInteger('terminal_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
