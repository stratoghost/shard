<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->string('queue');
            $table->string('state');
            $table->tinyInteger('priority');
            $table->string('source');

            $table->string('source_key')->nullable();
            $table->string('source_url')->nullable();

            $table->unsignedSmallInteger('total_minutes_spent')->default(0);

            $table->unsignedBigInteger('terminal_id');

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
