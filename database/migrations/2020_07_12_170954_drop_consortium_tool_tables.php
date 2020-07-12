<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class DropConsortiumToolTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('transaction_detail_temp');
        Schema::dropIfExists('transaction_master_detail');
        Schema::dropIfExists('consortium_member');
        Schema::dropIfExists('provider_details');
        Schema::dropIfExists('consortium_configuration');
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
