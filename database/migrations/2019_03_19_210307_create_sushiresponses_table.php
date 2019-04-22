<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSushiresponsesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sushiresponses',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('responsefile_id')->unsigned()->nullable();
                $table->integer('checkresult_id')->unsigned();
                $table->bigInteger('sushitransaction_id')->unsigned();

                $table->foreign('responsefile_id')
                    ->references('id')
                    ->on('storedfiles');
                $table->foreign('checkresult_id')
                    ->references('id')
                    ->on('checkresults');
                $table->foreign('sushitransaction_id')
                    ->references('id')
                    ->on('sushi_transaction');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sushiresponses');
    }
}
