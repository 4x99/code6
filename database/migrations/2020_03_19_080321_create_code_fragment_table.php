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
            $table->string('repo_owner', 100)->comment('GitHub Repository Owner');
            $table->string('repo_name', 255)->comment('GitHub Repository Name');
            $table->char('blob', 40)->comment('GitHub File Blob');
            $table->string('path', 1000)->comment('GitHub File Path');
            $table->text('content')->comment('Code Fragment With Keyword');
            $table->index(['repo_owner', 'repo_name', 'blob', 'path']);
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
