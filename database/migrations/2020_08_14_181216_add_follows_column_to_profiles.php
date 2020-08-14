<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFollowsColumnToProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('profiles','followers_count')){
                $table->integer('followers_count')->default(0)->comment('粉丝数');

            }
            if (!Schema::hasColumn('profiles','follows_count')){
                $table->integer('follows_count')->default(0)->comment('关注数');

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
        Schema::table('profiles', function (Blueprint $table) {
            //
        });
    }
}
