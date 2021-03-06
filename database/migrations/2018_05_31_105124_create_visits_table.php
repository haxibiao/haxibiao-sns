<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('visits')) {
            Schema::create('visits', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->index();

                $table->unsignedInteger('visited_id')->index(); //TODO: rename morphs('visitable')
                $table->string('visited_type', 20)->index()
                    ->default('articles')
                    ->comment('浏览类型,默认浏览文章');

                $table->timestamps();
                $table->index('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits');
    }
}
