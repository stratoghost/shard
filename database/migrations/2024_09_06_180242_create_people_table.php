<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();

            $table->string('type');

            $table->unsignedBigInteger('terminal_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
