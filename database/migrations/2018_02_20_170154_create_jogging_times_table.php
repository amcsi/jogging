<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJoggingTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jogging_times', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->date('day');
            $table->integer('distance_m');
            $table->integer('minutes');
            $table->unique(['user_id', 'day']);
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
        Schema::dropIfExists('jogging_times');
    }
}
