<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColumnsToUserBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('user_blocks', "blockable_id")) {
                $table->morphs('blockable');
            }

            if (Schema::hasColumn('user_blocks', "user_block_id")) {
                $table->dropColumn("user_block_id");
            }
            if (Schema::hasColumn('user_blocks', "article_block_id")) {
                $table->dropColumn("article_block_id");
            }
            if (Schema::hasColumn('user_blocks', "article_report_id")) {
                $table->dropColumn("article_report_id");
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
        Schema::table('user_blocks', function (Blueprint $table) {
            //
        });
    }
}
