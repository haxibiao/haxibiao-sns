<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWechatToMeetups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetups', function (Blueprint $table) {
            if (!Schema::hasColumn('meetups', 'title')) {
                $table->string('title')->nullable()->comment('标题')->after('id');
            }
            if (!Schema::hasColumn('meetups', 'wechat')) {
                $table->string('wechat')->nullable()->comment('微信')->after('phone');
            }
            if (!Schema::hasColumn('meetups', 'deleted_at')) {
                $table->softDeletes();
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
        Schema::table('meetups', function (Blueprint $table) {
            //
        });
    }
}
