<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckdetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkdetails',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('checkresult_id')->unsigned();
                $table->integer('level');
                $table->integer('number');
                $table->text('message');

                $table->foreign('checkresult_id')
                    ->references('id')
                    ->on('checkresults');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkdetails');
    }
}
