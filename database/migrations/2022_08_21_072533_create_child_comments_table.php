<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('child_comments', function (Blueprint $table) {
            $table->bigInteger('parent_comment_id', false, true)->nullable(false);
            $table->foreign('parent_comment_id')->references('id')->on('comments');
            $table->bigInteger('child_comment_id', false, true)->nullable(false);
            $table->foreign('child_comment_id')->references('id')->on('comments');
            $table->unique(['parent_comment_id', 'child_comment_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('child_comments');
    }
};
