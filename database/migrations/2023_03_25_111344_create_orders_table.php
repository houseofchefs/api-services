<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string("order_no")->length(30);
            $table->integer("customer_id")->length(8);
            $table->integer("rider_id")->length(8)->nullable();
            $table->decimal('price', $precision = 8, $scale = 2);
            $table->integer('discount')->length(30)->nullable();
            $table->integer('coupon')->length(30)->nullable();
            $table->date('order_at');
            $table->date('cook_picked_at')->nullable();
            $table->date('rider_picked_at')->nullable();
            $table->date('cook_deliver_at')->nullable();
            $table->date('rider_deliver_at')->nullable();
            $table->decimal('latitude', $precision = 10, $scale = 8);
            $table->decimal('longtitude', $precision = 11, $scale = 6);
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
        Schema::dropIfExists('orders');
    }
}
