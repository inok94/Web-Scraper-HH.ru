<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIdentityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identity', function (Blueprint $table) {
            $table->increments('id');
            $table->string('resume_id')->unique()->default('null');
            $table->string('fio')->default('null');
            $table->string('gender')->default('null');
            $table->integer('age')->nullable();
            $table->dateTime('birthday')->nullable();
            $table->string('city')->default('null');
            $table->string('metro')->nullable();
            $table->string('relocation')->nullable();
            $table->string('phone')->default('null');
            $table->string('email')->default('null');
            $table->string('image')->nullable();
            $table->string('position')->default('null');
            $table->string('salary')->nullable();
            $table->string('experience')->default('null');

            $table->string('date_update_resume')->default('null');

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
        Schema::dropIfExists('identity');
    }
}
