<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worklogs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('task_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worklogs');
    }
};
