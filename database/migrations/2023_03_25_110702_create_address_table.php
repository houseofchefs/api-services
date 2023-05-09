<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->id();
            $table->string('door_no')->length(30);
            $table->string('street_name')->length(30);
            $table->string('landmark')->nullable();
            $table->string('city')->length(30);
            $table->string('state')->length(30);
            $table->string('pincode')->length(6);
            $table->enum("status",[2,3]);
            $table->integer("created_by")->length(8);
            $table->integer("updated_by")->length(8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address');
    }
}


