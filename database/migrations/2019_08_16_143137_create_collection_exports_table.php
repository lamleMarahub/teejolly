<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_exports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->default('');
            $table->integer('owner_id')->default(0);                        
            $table->integer('collection_id')->default(0);                        
            $table->string('type')->default('etsy');

            $table->string('filename')->nullable(); //dir + filename.extension
            $table->integer('size')->default(0);    //bytes
            $table->string('extension')->nullable();

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
        Schema::dropIfExists('collection_exports');
    }
}
