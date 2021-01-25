<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYxspCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('comments')) {
            return;
        }
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index()->comments('用户ID');
            $table->text('content')->nullable()->comments('内容');
            $table->unsignedInteger('comment_id')->nullable()->index()->comment('评论ID');
            $table->unsignedInteger('reply_id')->index()->nullable()->comment('回复ID');

            $table->morphs('commentable');
            $table->integer('rank')->default(0)->comment('排名');
            $table->boolean('status')->default(1)->index()->comment('状态 -1:删除 0:个人可见 1:展示');

            $table->unsignedInteger('count_likes')->default(0)->index()->comment('点赞数');
            $table->tinyInteger('top')->default(0)->index()->comment('置顶');
            $table->unsignedInteger('reports_count')->default(0)->index()->comment('举报数');
            $table->unsignedInteger('comments_count')->default(0)->index()->comment('评论总数');

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
        Schema::dropIfExists('comments');
    }
}
