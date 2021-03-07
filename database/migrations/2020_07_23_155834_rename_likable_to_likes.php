<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\RenameColumn;
use Illuminate\Support\Facades\Schema;

class RenameLikableToLikes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('likes', function (Blueprint $table) {

            if (!Schema::hasColumn('likes', 'likable_type')) {
                if (Schema::hasColumn('likes', 'liked_type')) {
                    $table->renameColumn('liked_type', 'likable_type');
                } else {
                    $table->string('likable_type')->default('articles')->index();
                }
            }

            if (!Schema::hasColumn('likes', 'likable_id')) {
                if (Schema::hasColumn('likes', 'liked_id')) {
                    $table->renameColumn('liked_id', 'likable_id');
                } else {
                    $table->string('likable_id')->nullable()->index();
                }
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
        Schema::table('likes', function (Blueprint $table) {
            //
        });
    }
}
