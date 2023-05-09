<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->length(30);
            $table->string('email')->length(60)->unique();
            $table->string('mobile')->length(16)->unique();
            $table->string("password")->length(250);
            $table->integer('gender')->length(8)->nullable();
            $table->integer('address_id')->length(8)->nullable();
            $table->integer('bank_id')->length(8)->nullable();
            $table->integer('vehicle_id')->length(8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
