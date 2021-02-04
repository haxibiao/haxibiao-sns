<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUserIdToNotice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notices', function (Blueprint $table) {
            //
            if(!Schema::hasColumn('notices','to_user_id')){
                $table->unsignedInteger('to_user_id')->nullable()->comment('通知对象的用户ID');
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
        Schema::table('user_id_to_notice', function (Blueprint $table) {
            //
        });
    }
}
