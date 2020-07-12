<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DropReportnameTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('reportnames');
        Schema::dropIfExists('parentreports');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
