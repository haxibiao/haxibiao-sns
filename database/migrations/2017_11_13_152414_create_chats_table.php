<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('last_message_id')->nullable()->comment('最后一条消息id');
                $table->string('uids')->nullable()->comment('json_encode所有在组内的用户id');
                $table->unsignedInteger('user_id')->nullable()->index()->comment('发起人');
                $table->string('subject')->nullable()->comment('群聊组名');
                $table->tinyInteger('status')->default(0)->comment('群聊状态：1公开0私密');
                $table->string('introduction')->nullable()->comment('群介绍');
                //FIXME::下面这个type属性很迷惑
                //描述应该是私聊、群聊、会议？
                $table->tinyInteger('type')->comment('类型：支持语音，视频，图片消息')->default(0);
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
        Schema::dropIfExists('chats');
    }
}
