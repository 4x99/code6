<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigNotifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_notify', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('interval_min')->default(1);
            $table->boolean('enable')->default(1);
            $table->text('value')->nullable();
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
        Schema::dropIfExists('config_notify');
    }
}
