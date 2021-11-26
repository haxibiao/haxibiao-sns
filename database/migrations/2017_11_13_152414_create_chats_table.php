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
                $table->string('icon')->nullable()->comment("群聊icon");
                $table->string('uids')->nullable()->comment('json_encode所有在组内的用户id');
                $table->unsignedInteger('user_id')->nullable()->index()->comment('发起人');
                $table->string('subject')->nullable()->comment('群聊组名');
                $table->string('number')->nullable()->comment('群号');
                $table->tinyInteger('status')->default(0)->comment('群聊状态：1公开0私密-1封禁');
                $table->integer('count_reports')->default(0);
                $table->string('introduction')->nullable()->comment('群介绍');
                $table->integer('rank')->nullable()->comment('群权重：推荐用');
                //FIXME::下面这个type属性很迷惑
                //描述应该是私聊、群聊、会议？
                $table->tinyInteger('type')->comment('0 - 私聊， 1 - 群聊， 2 - 约单')->default(0);
                $table->integer('privacy')->default(2)->comment('1无需审核2需要审核3不允许任何人进群');
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