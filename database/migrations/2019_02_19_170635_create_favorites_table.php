<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->string('tag')->nullable()->comment('区分追剧和收藏');
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