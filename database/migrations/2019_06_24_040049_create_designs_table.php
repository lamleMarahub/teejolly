<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('owner_id')->default(0);
            $table->integer('designer_id')->default(0);            
            $table->string('title')->nullable();
            $table->string('title80')->nullable();
            $table->string('description')->nullable();
            $table->string('tags')->nullable();
            $table->string('color')->default('light');    //dark, light
            $table->string('type')->default('tee'); // tee, vneck, hoodie...
            $table->boolean('is_shared')->default(1);      //share to everyone

            $table->string('filename')->nullable(); //dir + filename.extension            
            $table->string('extension')->nullable(); //png, jpg
            $table->integer('size')->nullable();  //bytes
            $table->integer('width')->nullable();  //pixel
            $table->integer('height')->nullable();  //pixel

            $table->string('thumbnail')->nullable(); //dir + filename.extension     

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
        Schema::dropIfExists('designs');
    }
}
