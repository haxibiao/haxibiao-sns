<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->Increments('id');
            $table->unsignedInteger("user_id")->index();
            $table->morphs('blockable');
            // $table->unsignedBigInteger("user_block_id")->nullable()->comment("屏蔽用户id");
            // $table->bigInteger('article_block_id')->nullable()->comment("不感兴趣的动态id");
            // $table->Integer('article_report_id')->nullable()->comment("被举报的动态id");

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
        Schema::dropIfExists('user_blocks');
    }
}
