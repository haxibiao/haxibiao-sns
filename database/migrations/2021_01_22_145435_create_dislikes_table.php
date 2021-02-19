<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDislikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('dislikes')) {
			return;
		}
        if(Schema::hasTable('not_likes')){
            Schema::table('not_likes',function (Blueprint $table){
                if(!Schema::hasColumn('not_likes','dislikeable_type')){
                    $table->renameColumn('not_likable_type','dislikeable_type');
                }
                if(!Schema::hasColumn('not_likes','dislikeable_id')){
                    $table->renameColumn('not_likable_id','dislikeable_id');
                }
            });
			Schema::rename('not_likes','dislikes');
			return;
        }
        Schema::create('dislikes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->morphs('dislikeable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dislikes');
    }
}
