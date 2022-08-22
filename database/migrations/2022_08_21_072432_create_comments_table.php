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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id', false, true)->nullable(false);
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('post_id', false, true)->nullable(false);
            $table->foreign('post_id')->references('id')->on('posts');
            $table->string('commenter_name', 255)->nullable(false);
            $table->text('message')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
