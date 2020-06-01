<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 令牌配置表
        Schema::create('config_token', function (Blueprint $table) {
            $table->id();
            $table->char('token', 40)->unique()->comment('GitHub Personal Access Token');
            $table->timestamps();
            $table->tinyInteger('status')->unsigned()->default(1)->comment('0:Abnormal 1:Normal');
            $table->integer('api_limit')->unsigned()->comment('GitHub API X-RateLimit-Limit');
            $table->integer('api_remaining')->unsigned()->comment('GitHub API X-RateLimit-Remaining');
            $table->timestamp('api_reset_at')->nullable()->comment('GitHub API X-RateLimit-Reset');
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
        Schema::dropIfExists('config_token');
    }
}
