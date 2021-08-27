<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMockupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mockups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('owner_id')->default(0);                        
            $table->string('title')->default('');

            $table->integer('design_x')->default(0);                        
            $table->integer('design_y')->default(0);         
            $table->integer('design_width')->default(0);  //pixel
            $table->integer('design_height')->default(0);  //pixel               
            $table->integer('design_angle')->default(0);   //rotate angle in degrees to rotate the image counter-clockwise.
            $table->integer('design_opacity')->default(100); 
            
            $table->string('color')->default('dark'); // dark/light
            $table->string('type')->default('tee'); // tee, vneck, hoodie...
            $table->integer('width')->default(0);  //pixel
            $table->integer('height')->default(0);  //pixel               
            $table->integer('size')->default(0);    //bytes

            $table->boolean('is_shared')->default(0);      //share to everyone
            $table->boolean('is_active')->default(1);      //in use
            
            $table->string('filename')->nullable(); //dir + filename.extension
            $table->string('extension')->nullable(); //png, jpg
                
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
        Schema::dropIfExists('mockups');
    }
}
