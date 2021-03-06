<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('shares')){
            return;
        }
        Schema::create('shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('active')->default(1);
            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');
            $table->string('url')->nullable()->comment('分享地址');
            $table->unsignedInteger('user_id')->index()->comment('分享的用户');
            $table->char('uuid', 36)->index();
            $table->dateTime('expired_at')->nullable()->comment('过期时间');
            $table->json('uids')->nullable()->comment('已经浏览过的用户');
            $table->timestamps();
            $table->index(['shareable_type', 'shareable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shares');
    }
}
