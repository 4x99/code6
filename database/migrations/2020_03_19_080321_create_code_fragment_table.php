<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeFragmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 代码片段表
        Schema::create('code_fragment', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable();
            $table->char('uuid', 32)->comment('Unique ID:md5(repo_owner/repo_name/blob/path)');
            $table->text('content')->comment('Code Fragment With Keyword');
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('code_fragment');
    }
}
