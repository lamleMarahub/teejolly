<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('owner_id')->default(0);
            $table->string('title')->default('');
            $table->text('description')->nullable();
            $table->string('tags')->nullable();    //use as keywords
            
            $table->string('uid')->nullable();
            $table->string('brand_name')->nullable();

            $table->string('image_url_1')->nullable();     //color chart
            $table->string('image_url_2')->nullable();     //size chart
            $table->string('image_url_3')->nullable();     //shipping time,policy

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
        Schema::dropIfExists('collections');
    }
}
