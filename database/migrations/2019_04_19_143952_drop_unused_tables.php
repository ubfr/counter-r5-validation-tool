<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DropUnusedTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('validateerrors');
        Schema::dropIfExists('filenames');
        Schema::dropIfExists('validation_rules');
        Schema::dropIfExists('row_validate_rules');
        Schema::dropIfExists('filtertypes');
        Schema::dropIfExists('allreportsnames');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // not supported
    }
}
