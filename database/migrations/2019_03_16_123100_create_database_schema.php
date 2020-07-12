<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Parentreport;
use App\Reportname;

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
        $this->createParentreportsTable();
        $this->createReportnamesTable();
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

    protected function createParentreportsTable()
    {
        if (Schema::hasTable('parentreports')) {
            return;
        }
        Schema::create('parentreports',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('name');
                $table->enum('status', [
                    '0',
                    '1'
                ]);
                $table->timestamp('updates')->useCurrent();
            });
        $this->insertParentreportsData();
    }

    protected function createReportnamesTable()
    {
        if (Schema::hasTable('reportnames')) {
            return;
        }
        Schema::create('reportnames',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('report_name', 512);
                $table->string('report_code', 56);
                $table->integer('parent_id');
            });
        $this->insertReportnamesData();
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

    protected function insertParentreportsData()
    {
        $records = [
            [
                'name' => 'Platform Master Report (PR)',
                'status' => '1'
            ],
            [
                'name' => 'Title Master Report (TR)',
                'status' => '1'
            ],
            [
                'name' => 'Item Master Report (IR)',
                'status' => '1'
            ],
            [
                'name' => 'Database Master Report (DR)',
                'status' => '1'
            ]
        ];
        Parentreport::insert($records);
    }

    protected function insertReportnamesData()
    {
        $records = [
            [
                'id' => 6,
                'report_name' => 'Book Usage by Access Type',
                'report_code' => 'TR_B3',
                'parent_id' => 2
            ],
            [
                'id' => 7,
                'report_name' => 'Journal Requests (Excluding OA_Gold)',
                'report_code' => 'TR_J1',
                'parent_id' => 2
            ],
            [
                'id' => 8,
                'report_name' => 'Journal Access Denied',
                'report_code' => 'TR_J2',
                'parent_id' => 2
            ],
            [
                'id' => 9,
                'report_name' => 'Journal Usage by Access Type',
                'report_code' => 'TR_J3',
                'parent_id' => 2
            ],
            [
                'id' => 10,
                'report_name' => 'Journal Requests by YOP (Excluding OA_Gold)',
                'report_code' => 'TR_J4',
                'parent_id' => 2
            ],
            [
                'id' => 11,
                'report_name' => 'Journal Article Requests',
                'report_code' => 'IR_A1',
                'parent_id' => 3
            ],
            [
                'id' => 12,
                'report_name' => 'Multimedia Item Requests',
                'report_code' => 'IR_M1',
                'parent_id' => 3
            ],
            [
                'id' => 13,
                'report_name' => 'Platform Usage',
                'report_code' => 'PR_P1',
                'parent_id' => 1
            ],
            [
                'id' => 14,
                'report_name' => 'Database Search and Item Usage',
                'report_code' => 'DR_D1',
                'parent_id' => 4
            ],
            [
                'id' => 15,
                'report_name' => 'Database Access Denied',
                'report_code' => 'DR_D2',
                'parent_id' => 4
            ],
            [
                'id' => 16,
                'report_name' => 'Book Requests (Excluding OA_Gold)',
                'report_code' => 'TR_B1',
                'parent_id' => 2
            ],
            [
                'id' => 17,
                'report_name' => 'Book Access Denied',
                'report_code' => 'TR_B2',
                'parent_id' => 2
            ],
            [
                'id' => 18,
                'report_name' => 'Platform Master report',
                'report_code' => 'PR',
                'parent_id' => 0
            ],
            [
                'id' => 19,
                'report_name' => 'Title Master Report',
                'report_code' => 'TR',
                'parent_id' => 0
            ],
            [
                'id' => 20,
                'report_name' => 'Item Master Report',
                'report_code' => 'IR',
                'parent_id' => 0
            ],
            [
                'id' => 21,
                'report_name' => 'Database Master Report',
                'report_code' => 'DR',
                'parent_id' => 0
            ]
        ];
        Reportname::insert($records);
        $this->setAutoIncrementStart('reportnames', 22);
    }

    protected function setAutoIncrementStart($table, $start)
    {
        $dbDriver = Config::get('database.default');
        switch ($dbDriver) {
            case 'mysql':
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = {$start};");
                break;
            case 'pgsql':
                DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH {$start}");
                break;
            default:
                throw new RuntimeException("Database {$dbDriver} not support");
                break;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sushi_transaction');
        Schema::dropIfExists('reportnames');
        Schema::dropIfExists('parentreports');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
