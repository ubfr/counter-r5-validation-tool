<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportfilesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportfiles',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('reportfile_id')->unsigned();
                $table->integer('checkresult_id')->unsigned();
                $table->string('release')->nullable();
                $table->string('reportname')->nullable();
                $table->string('reportid')->nullable();
                $table->string('platform')->nullable();
                $table->string('institutionname')->nullable();
                $table->datetime('created')->nullable();
                $table->string('createdby')->nullable();
                $table->date('begindate')->nullable();
                $table->date('enddate')->nullable();

                $table->foreign('reportfile_id')
                    ->references('id')
                    ->on('storedfiles');
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
        Schema::dropIfExists('reportfiles');
    }
}
