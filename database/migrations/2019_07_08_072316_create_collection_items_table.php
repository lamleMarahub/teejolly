<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('owner_id')->default(0);
            $table->integer('designer_id')->default(0);            
            $table->integer('collection_id')->default(0);

            $table->integer('design_id')->default(0);  
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('tags')->nullable();
            $table->string('color')->default('');    //dark, light
            
            //meta
            $table->string('image1')->nullable();   //publish
            $table->string('image2')->nullable();   //publish
            $table->string('image3')->nullable();   //publish
            $table->string('image4')->nullable();   //publish

            $table->integer('category_id')->nullable();
            $table->float('price')->nullable(); 
            $table->float('shipping_price')->nullable();             

            $table->softDeletes();
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
        Schema::dropIfExists('collection_items');
    }
}
