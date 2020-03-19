<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 任务配置表
        Schema::create('config_job', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 255)->unique()->comment('GitHub Scan Keyword');
            $table->timestamps();
            $table->tinyInteger('scan_page')->unsigned()->comment('GitHub Scan Page');
            $table->integer('scan_interval_min')->unsigned()->comment('GitHub Scan Interval(minute)');
            $table->timestamp('last_scan_at')->nullable()->comment('Last Scan Time');
            $table->string('description', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_job');
    }
}
