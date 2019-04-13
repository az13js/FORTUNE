<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('自增ID');
            $table->unsignedInteger('version')->default(1)->comment('版本号');
            $table->string('news_key', 20)->default('')->comment('新闻key')->unique();
            $table->string('title', 50)->default('')->comment('标题');
            $table->string('url', 200)->default('')->comment('链接');
            $table->dateTime('public')->default('2000-01-01 00:00:00')->comment('发布时间');
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
        Schema::dropIfExists('news');
    }
}
