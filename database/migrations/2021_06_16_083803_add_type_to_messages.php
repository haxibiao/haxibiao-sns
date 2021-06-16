<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0)->comment('消息类型');
            if (Schema::hasColumn('messages', 'message')) {
                $table->renameColumn('message', 'body');
            }
            if (Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->comment('阅读时间');
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
        Schema::table('messages', function (Blueprint $table) {
            //
        });
    }
}
