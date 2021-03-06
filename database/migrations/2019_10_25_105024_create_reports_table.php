<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('user_id')->comment('举报人id')->index();
                $table->string('reason')->comment('举报理由');
                $table->morphs('reportable');
                $table->tinyInteger('status')->default(0)->comment('举报的状态');
                $table->timestamps();

                $table->softDeletes();
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
        Schema::dropIfExists('reports');
    }
}
