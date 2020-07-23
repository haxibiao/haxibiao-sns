<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFavoritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('favorites')) {
            return;
        }
         Schema::create('favorites', function (Blueprint $table) {
             $table->increments('id');

             $table->unsignedInteger('user_id');
             $table->morphs('favorable');

             $table->timestamps();
             $table->softDeletes();

             $table->index('user_id');
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favorites');
    }
}
