<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('chat_id');
                $table->morphs('messageable');
                $table->string('type', 10)->default('text')->index()->comment('消息类型');
                $table->text('body')->comment('消息正文json');
                $table->timestamp('read_at')->nullable()->comment('阅读时间');
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
        Schema::dropIfExists('messages');
    }
}
