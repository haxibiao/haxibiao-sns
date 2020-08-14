<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            if(!Schema::hasColumn('comments','content')){
                $table->text('content')->nullable()->comments('内容');
            }
            if(!Schema::hasColumn('comments','comment_id')){
                $table->unsignedInteger('comment_id')->nullable()->index()->comment('评论ID');
            }
            if(!Schema::hasColumn('comments','reply_id')){
                $table->unsignedInteger('reply_id')->index()->nullable()->comment('回复ID');
            }
            if(!Schema::hasColumn('comments','commentable_id')){
                $table->morphs('commentable');
            }
            if(!Schema::hasColumn('comments','rank')){
                $table->integer('rank')->default(0)->comment('排名');
            }
            if(!Schema::hasColumn('comments','count_likes')){

                $table->unsignedInteger('count_likes')->default(0)->index()->comment('点赞数');
            }
            if(!Schema::hasColumn('comments','top')){

                $table->tinyInteger('top')->default(0)->index()->comment('置顶');
            }
            if(!Schema::hasColumn('comments','reports_count')){

                $table->unsignedInteger('reports_count')->default(0)->index()->comment('举报数');
            }
            if(!Schema::hasColumn('comments','comments_count')){

                $table->unsignedInteger('comments_count')->default(0)->index()->comment('评论总数');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            //
        });
    }
}
