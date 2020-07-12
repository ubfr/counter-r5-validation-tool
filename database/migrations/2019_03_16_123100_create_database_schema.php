<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabaseSchema extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createUsersTable();
        $this->createPasswordResetsTable();
        $this->createSushiTransactionTable();
    }

    protected function createUsersTable()
    {
        if (Schema::hasTable('users')) {
            return;
        }
        Schema::create('users',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('display_name');
                $table->string('utype');
                $table->string('email')->unique();
                $table->enum('gender', [
                    'M',
                    'F'
                ])->default('M');
                $table->string('password');
                $table->tinyInteger('newsletter')->default(0);
                $table->tinyInteger('commercial')->default(0);
                $table->rememberToken();
                $table->timestamps();
                $table->integer('no_of_times')->default(10);
                $table->tinyInteger('status')->default(1);
            });

        // TODO: artisan command for creating user
    }

    protected function createPasswordResetsTable()
    {
        if (Schema::hasTable('password_resets')) {
            return;
        }
        Schema::create('password_resets',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->string('email')->index();
                $table->string('token')->index();
                $table->timestamp('created_at')->useCurrent();
            });
    }

    protected function createSushiTransactionTable()
    {
        if (Schema::hasTable('sushi_transaction')) {
            return;
        }
        Schema::create('sushi_transaction',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->bigIncrements('id');
                $table->string('user_email');
                $table->string('session_id');
                $table->string('sushi_url', 1024)->nullable();
                $table->string('request_name')->nullable();
                $table->string('platform')->nullable();
                $table->string('report_id')->nullable();
                $table->string('report_format')->nullable();
                $table->enum('success', [
                    'Y',
                    'N'
                ])->default('Y');
                $table->unsignedInteger('number_of_errors')->nullable();
                $table->timestamp('date_time')
                    ->nullable()
                    ->useCurrent();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sushi_transaction');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
