<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallanItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('challan_id');
            $table->unsignedBigInteger('product_id');
            $table->double('quantity')->nullable();
            $table->double('unit_price')->nullable();
            $table->double('total')->nullable();
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
        Schema::dropIfExists('challan_items');
    }
}
