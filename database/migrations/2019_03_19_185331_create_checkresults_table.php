<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckresultsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkresults',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('resultfile_id')->unsigned()->nullable();
                $table->string('sessionid');
                $table->double('checktime');
                $table->integer('checkmemory');
                $table->timestamps();

                $table->foreign('resultfile_id')
                    ->references('id')
                    ->on('storedfiles');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkresults');
    }
}
