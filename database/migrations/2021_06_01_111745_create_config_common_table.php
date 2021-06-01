<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigCommon;

class CreateConfigCommonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_common', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255);
            $table->string('value', 255);
            $table->unique(['key', 'value']);
        });
        $this->migrate();
    }

    /**
     * 数据迁移
     */
    private function migrate()
    {
        $whitelistFile = DB::table('config_whitelist_file')->select('value')->get();
        $whitelistFile = json_decode(json_encode($whitelistFile), true);
        foreach ($whitelistFile as &$item) {
            $item['key'] = ConfigCommon::KEY_WHITELIST_FILE;
        }
        ConfigCommon::insert($whitelistFile);

        Schema::dropIfExists('config_whitelist_file');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_common');
    }
}
