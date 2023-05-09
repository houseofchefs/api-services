<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string("name")->length(30);
            $table->integer("type")->length(8);
            $table->integer("category_id")->length(30);
            $table->decimal('price', $precision = 8, $scale = 2);
            $table->integer("status")->length(8);
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
        Schema::dropIfExists('products');
    }
}
