<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeStatusToConfigTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_token', function (Blueprint $table) {
            DB::statement("ALTER TABLE `config_token` CHANGE `status` `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:Unknown 1:Normal 2:Abnormal'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_token', function (Blueprint $table) {
            DB::statement("ALTER TABLE `config_token` CHANGE `status` `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '0:Abnormal 1:Normal'");
        });
    }
}
