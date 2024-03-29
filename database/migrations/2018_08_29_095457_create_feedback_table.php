<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('feedback')) {
            Schema::create('feedback', function (Blueprint $table) {
                $table->increments('id');
                $table->text('content')
                    ->nullable()
                    ->comment('留言');

                $table->unsignedInteger('user_id')
                    ->nullable()
                    ->index()
                    ->comment('用户');

                $table->string('contact')
                    ->nullable()
                    ->comment('联系方式');

                $table->string('contact_type')
                    ->nullable()
                    ->comment('手机,邮箱');

                $table->unsignedInteger('status')->default(0)->comment('0待处理 1已驳回 2已处理');
                $table->timestamp('top_at')->nullable()->comment('置顶时间');
                $table->unsignedInteger('rank')->default(0)->comment('排名');
                $table->unsignedInteger('video_id')->index()->nullable()->comment('反馈关联的视频');
                $table->unsignedInteger('comments_count')->default(0)->comment('评论数');
                $table->unsignedInteger('publish_comments_count')->default(0)->comment('公开评论数');

                $table->timestamps();
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
        Schema::dropIfExists('feedback');
    }
}