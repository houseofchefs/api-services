<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->length(30);
            $table->string('holder_name')->length(50);
            $table->string('account_number')->length(20);
            $table->string('ifsc_code')->length(10);
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
        Schema::dropIfExists('banks');
    }
}
