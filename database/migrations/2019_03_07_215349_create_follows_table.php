<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('follows')) {
            return;
        }
        Schema::create('follows', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index()->comment('用户ID');
            $table->morphs('followable');
            $table->integer('status')->default(0)->index();
            $table->unsignedInteger('gold')->default(0)->comment('金币');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('follows');
    }
}
