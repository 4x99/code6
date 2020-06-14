<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexRepoOwnerAndRepoNameToCodeLeakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('code_leak', function (Blueprint $table) {
            $table->index('repo_owner');
            $table->index('repo_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('code_leak', function (Blueprint $table) {
            $table->dropIndex('code_leak_repo_owner_index');
            $table->dropIndex('code_leak_repo_name_index');
        });
    }
}
