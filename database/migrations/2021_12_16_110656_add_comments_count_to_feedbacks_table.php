<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsCountToFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('feedback', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->comment('评论数');
            }
            if (!Schema::hasColumn('feedback', 'publish_comments_count')) {
                $table->unsignedInteger('publish_comments_count')->default(0)->comment('公开评论数');
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
        Schema::table('feedbacks', function (Blueprint $table) {
            //
        });
    }
}