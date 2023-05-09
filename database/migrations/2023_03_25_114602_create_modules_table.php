<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->integer("module_id")->length(8);
            $table->string("module_name")->length(30);
            $table->string("module_code")->length(30);
            $table->string("description")->length(180)->nullable();
            $table->integer("status")->length(8);
            $table->integer("created_by")->length(8);
            $table->integer("updated")->length(8);
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
        Schema::dropIfExists('modules');
    }
}
