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
            $table->timestamp('create_at')->nullable();
            $table->char('sha', 40)->index()->comment('GitHub Blob Hash');
            $table->text('content')->comment('Code Fragment With Keyword');
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
