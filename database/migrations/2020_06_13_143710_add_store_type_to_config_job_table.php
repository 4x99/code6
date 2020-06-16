<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreTypeToConfigJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_job', function (Blueprint $table) {
            $table->tinyInteger('store_type')->unsigned()->after('scan_interval_min')->comment('0:All 1:File Store Once 2:Repo Store Once');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_job', function (Blueprint $table) {
            $table->dropColumn('storage_type');
        });
    }
}
