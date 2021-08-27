<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEtsyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etsy_shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('owner_id')->default(0);            
            $table->string('shop_url')->nullable();
            $table->string('shop_name')->nullable();
            $table->integer('shop_sales')->default(0); 

            $table->string('key_string')->nullable();
            $table->string('share_secret')->nullable();   
            $table->string('access_token')->nullable(); 
            $table->string('access_token_secret')->nullable(); 

            $table->string('shipping_template_id')->nullable(); 
            $table->float('price')->default(16.95); 
            $table->integer('quantity')->default(99); 

            $table->string('image_url_1')->nullable();
            $table->string('image_url_2')->nullable();
            $table->string('image_url_3')->nullable();

            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('etsy_shops');
    }
}
