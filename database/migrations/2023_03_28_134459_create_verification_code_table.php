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
        Schema::create('verification_code', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number')->length(16);
            $table->string('type')->length(10);
            $table->string('otp')->length(6);
            $table->boolean("isVerified");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_code');
    }
};
