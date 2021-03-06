<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFavorableIdToFavoritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorites', function (Blueprint $table) {

            if (Schema::hasColumn('favorites', 'faved_id')
                && !Schema::hasColumn('favorites', 'favorable_id')) {
                $table->renameColumn('faved_id', 'favorable_id');
            }
            if (Schema::hasColumn('favorites', 'faved_type')
                && !Schema::hasColumn('favorites', 'favorable_type')) {
                $table->renameColumn('faved_type', 'favorable_type');
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
        Schema::table('favorites', function (Blueprint $table) {
            //
        });
    }
}
