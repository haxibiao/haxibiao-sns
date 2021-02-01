<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangMorphColumnToLikes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('likes')) {
            Schema::table('likes', function (Blueprint $table) {
                if (Schema::hasColumn('likes', 'liked_id') &&
                    !Schema::hasColumn('likes', 'likable_id')) {
                    $table->renameColumn('liked_id', 'likable_id');
                    $table->renameColumn('liked_type', 'likable_type');
                }
                if (!Schema::hasColumn('likes', 'deleted_at')) {
                    $table->softDeletes();
                }
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
        Schema::table('likes', function (Blueprint $table) {
            //
        });
    }
}
