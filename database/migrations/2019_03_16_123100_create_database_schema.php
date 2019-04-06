<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Filtertype;
use App\Parentreport;
use App\Reportname;
use App\Rowvalidaterule;
use App\Validationrule;

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
        $this->createFiltertypesTable();
        $this->createRowValidateRulesTable();
        $this->createValidationRulesTable();
        $this->createFilenamesTable();
        $this->createValidateerrorsTable();
        $this->createSushiTransactionTable();
        $this->createConsortiumConfigurationTable();
        $this->createProviderDetailsTable();
        $this->createConsortiumMemberTable();
        $this->createTransactionMasterDetailTable();
        $this->createTransactionDetailTempTable();
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

    protected function createFiltertypesTable()
    {
        if (Schema::hasTable('filtertypes')) {
            return;
        }
        Schema::create('filtertypes',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('name');
                $table->string('status');
            });
        $this->insertFiltertypesData();
    }

    protected function createRowValidateRulesTable()
    {
        if (Schema::hasTable('row_validate_rules')) {
            return;
        }
        Schema::create('row_validate_rules',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->integer('row');
                $table->integer('is_range');
                $table->integer('report_id');
            });
        $this->insertRowValidateRulesData();
    }

    protected function createValidationRulesTable()
    {
        if (Schema::hasTable('validation_rules')) {
            return;
        }
        Schema::create('validation_rules',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('colname');
                $table->integer('rowno');
                $table->enum('ruletype',
                    [
                        'integer',
                        'text',
                        'date_format',
                        'issn',
                        'sum',
                        'row sum',
                        'isbn',
                        'sumif',
                        'sum-row-column',
                        'string',
                        'stringcheck',
                        ''
                    ]);
                $table->string('value');
                $table->tinyInteger('required')->default(0);
                $table->integer('report_no')->nullable();
                $table->tinyInteger('is_range')->default(0);
                $table->string('start_column');
                $table->string('match_column');
            });
        $this->insertValidationRulesData();
    }

    protected function createFilenamesTable()
    {
        if (Schema::hasTable('filenames')) {
            return;
        }
        Schema::create('filenames',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('email')->nullable();
                $table->string('report_name')->nullable();
                $table->string('report_id')->nullable();
                $table->string('file_type');
                $table->integer('user_id')->unsigned();
                $table->text('filename');
                $table->timestamp('upload_date')->useCurrent();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users');
            });
    }

    protected function createValidateerrorsTable()
    {
        if (Schema::hasTable('validateerrors')) {
            return;
        }
        Schema::create('validateerrors',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('file_id')->unsigned();
                $table->string('type', 20);
                $table->text('error_data')->nullable();
                $table->text('error_remark')->nullable();
                $table->timestamp('entry_time')->useCurrent();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users');
                $table->foreign('file_id')
                    ->references('id')
                    ->on('filenames');
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

    protected function createConsortiumConfigurationTable()
    {
        if (Schema::hasTable('consortium_configuration')) {
            return;
        }
        Schema::create('consortium_configuration',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('configuration_name')->default('');
                $table->string('provider_name')->nullable();
                $table->string('provider_url')->nullable();
                $table->string('apikey')->nullable();
                $table->string('requestor_id')->nullable();
                $table->string('created_by')->nullable();
                $table->string('customer_id')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->string('remarks');
                $table->timestamp('time_stamp')->useCurrent();
                $table->integer('user_id')->nullable();

                $table->unique([
                    'configuration_name',
                    'provider_name'
                ]);
            });
    }

    protected function createProviderDetailsTable()
    {
        if (Schema::hasTable('provider_details')) {
            return;
        }
        Schema::create('provider_details',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('configuration_id')->default('');
                // TODO: Why are the following columns both in consortium_configuration and provider_details?
                $table->string('provider_name')->nullable();
                $table->string('provider_url')->nullable();
                $table->string('apikey')->nullable();
                $table->string('requestor_id')->nullable();
                $table->string('customer_id')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->string('remarks');
                $table->timestamp('time_stamp')->useCurrent();
            });
    }

    protected function createConsortiumMemberTable()
    {
        if (Schema::hasTable('consortium_member')) {
            return;
        }
        Schema::create('consortium_member',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id');
                $table->string('customer_id')->nullable();
                $table->string('requestor_id')->nullable();
                $table->string('name')->nullable();
                $table->string('notes')->nullable();
                $table->string('institution_id_type')->nullable();
                $table->string('institution_id_value')->nullable();
                $table->string('provider_id')->nullable();
            });
    }

    protected function createTransactionMasterDetailTable()
    {
        if (Schema::hasTable('transaction_master_detail')) {
            return;
        }
        Schema::create('transaction_master_detail',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                $table->increments('id'); // corrected ID => id
                $table->string('user_id', 128)->default('');
                $table->string('transaction_id', 128)->default('');
                $table->string('config_name', 128)->default('');
                $table->string('client_ip', 15)->default(''); // TODO: This doesn't work for IPv6.
                $table->string('provider_name', 128)->default('');
                $table->string('member_name', 128)->default('');
                $table->string('report_id', 55)->default('');
                $table->date('begin_date')->default('1970-01-01');
                $table->date('end_date')->default('1970-01-01');
                $table->integer('status')->default(0);
                $table->string('message', 128)->default('');
                $table->string('remarks')->default('');
                $table->string('exception')->default('');
                $table->string('details')->default('');
                $table->string('file_name')->default('');
                $table->integer('file_size')->default(0);
                $table->datetime('start_date_time')->default('1970-01-01 00:00:00');
                $table->datetime('end_date_time')->default('1970-01-01 00:00:00');
                $table->timestamp('time_stamp')->useCurrent();

                $table->unique(
                    [
                        'user_id',
                        'transaction_id',
                        'config_name',
                        'provider_name',
                        'member_name',
                        'report_id'
                    ]);
                $table->index('transaction_id');
            });
    }

    protected function createTransactionDetailTempTable()
    {
        if (Schema::hasTable('transaction_detail_temp')) {
            return;
        }
        Schema::create('transaction_detail_temp',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8';
                $table->collation = 'utf8_unicode_ci';

                // TODO: Why does this table replicate parts of the transaction_master_details table?
                $table->increments('id');
                $table->string('transaction_id', 128);
                $table->string('reports');
                $table->string('providers');
                $table->string('members');
                $table->string('begin_date');
                $table->string('end_date');
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

    protected function insertFiltertypesData()
    {
        $records = [
            [
                'name' => 'Searches_Automated',
                'status' => '1'
            ],
            [
                'name' => 'Searches_Federated',
                'status' => '1'
            ],
            [
                'name' => 'Searches_Platform',
                'status' => '1'
            ],
            [
                'name' => 'Searches_Regular',
                'status' => '1'
            ],
            [
                'name' => 'Total_Item_Investigations',
                'status' => '1'
            ],
            [
                'name' => 'Total_Item_Requests',
                'status' => '1'
            ],
            [
                'name' => 'Unique_Item_Investigations',
                'status' => '1'
            ],
            [
                'name' => 'Unique_Item_Requests',
                'status' => '1'
            ],
            [
                'name' => 'Unique_Title_investigations',
                'status' => '1'
            ],
            [
                'name' => 'Unique_Title_Requests',
                'status' => '1'
            ],
            [
                'name' => 'No_License',
                'status' => '1'
            ],
            [
                'name' => 'Limit_Exceeded',
                'status' => '1'
            ]
        ];
        Filtertype::insert($records);
    }

    protected function insertRowValidateRulesData()
    {
        $records = [
            [
                'id' => 1,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 2,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 3,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 4,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 5,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 6,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 7,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 8,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 9,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 1
            ],
            [
                'id' => 10,
                'row' => 10,
                'is_range' => 1,
                'report_id' => 1
            ],
            [
                'id' => 11,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 12,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 13,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 14,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 15,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 16,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 17,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 18,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 19,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 2
            ],
            [
                'id' => 20,
                'row' => 10,
                'is_range' => 1,
                'report_id' => 2
            ],
            [
                'id' => 21,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 22,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 23,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 24,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 25,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 26,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 27,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 28,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 29,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 3
            ],
            [
                'id' => 30,
                'row' => 10,
                'is_range' => 1,
                'report_id' => 3
            ],
            [
                'id' => 31,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 32,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 33,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 34,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 35,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 36,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 37,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 38,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 39,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 4
            ],
            [
                'id' => 40,
                'row' => 10,
                'is_range' => 1,
                'report_id' => 4
            ],
            [
                'id' => 41,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 42,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 43,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 44,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 45,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 46,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 47,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 48,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 49,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 5
            ],
            [
                'id' => 50,
                'row' => 10,
                'is_range' => 1,
                'report_id' => 5
            ],
            [
                'id' => 61,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 62,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 63,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 64,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 65,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 66,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 67,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 68,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 69,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 70,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 71,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 72,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 73,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 74,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 75,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 76,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 77,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 78,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 79,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 80,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 81,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 82,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 83,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 84,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 85,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 86,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 87,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 88,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 89,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 94,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 95,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 96,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 97,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 98,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 99,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 100,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 101,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 102,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 112,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 113,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 114,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 115,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 116,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 117,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 118,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 119,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 120,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 121,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 122,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 123,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 124,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 125,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 126,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 127,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 128,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 129,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 130,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 131,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 132,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 133,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 134,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 135,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 136,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 7
            ],
            [
                'id' => 139,
                'row' => 17,
                'is_range' => 0,
                'report_id' => 7
            ],
            [
                'id' => 140,
                'row' => 18,
                'is_range' => 1,
                'report_id' => 7
            ],
            [
                'id' => 141,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 142,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 143,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 8
            ],
            [
                'id' => 149,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 8
            ],
            [
                'id' => 151,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 159,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 160,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 161,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 162,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 163,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 164,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 9
            ],
            [
                'id' => 165,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 9
            ],
            [
                'id' => 166,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 167,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 168,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 169,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 170,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 10
            ],
            [
                'id' => 171,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 10
            ],
            [
                'id' => 172,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 173,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 174,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 13
            ],
            [
                'id' => 175,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 13
            ],
            [
                'id' => 176,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 177,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 178,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 179,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 12
            ],
            [
                'id' => 180,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 12
            ],
            [
                'id' => 191,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 192,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 193,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 194,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 195,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 196,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 197,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 198,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 199,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 200,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 201,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 202,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 203,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 204,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 205,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 17
            ],
            [
                'id' => 206,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 17
            ],
            [
                'id' => 209,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 216,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 217,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 218,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 219,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 220,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 221,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 222,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 223,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 224,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 225,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 226,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 227,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 228,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 229,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 16
            ],
            [
                'id' => 230,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 16
            ],
            [
                'id' => 231,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 232,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 233,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 234,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 235,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 236,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 237,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 238,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 239,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 240,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 241,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 242,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 243,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 244,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 245,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 6
            ],
            [
                'id' => 246,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 6
            ],
            [
                'id' => 248,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 249,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 250,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 251,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 252,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 253,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 254,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 255,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 256,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 257,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 258,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 259,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 260,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 261,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 262,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 11
            ],
            [
                'id' => 263,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 11
            ],
            [
                'id' => 264,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 265,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 266,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 267,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 268,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 269,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 270,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 271,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 272,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 273,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 274,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 275,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 276,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 277,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 278,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 14
            ],
            [
                'id' => 279,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 14
            ],
            [
                'id' => 280,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 281,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 282,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 283,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 284,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 285,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 286,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 287,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 288,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 289,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 290,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 291,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 292,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 293,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 294,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 15
            ],
            [
                'id' => 295,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 15
            ],
            [
                'id' => 299,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 300,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 301,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 302,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 303,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 304,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 305,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 306,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 307,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 308,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 309,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 310,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 311,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 312,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 313,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 19
            ],
            [
                'id' => 314,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 19
            ],
            [
                'id' => 316,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 317,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 318,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 319,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 320,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 321,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 322,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 323,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 324,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 325,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 326,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 327,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 328,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 329,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 330,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 20
            ],
            [
                'id' => 331,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 20
            ],
            [
                'id' => 332,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 333,
                'row' => 0,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 334,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 335,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 336,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 337,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 338,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 339,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 340,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 341,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 342,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 343,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 344,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 345,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 346,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 347,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 21
            ],
            [
                'id' => 348,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 21
            ],
            [
                'id' => 349,
                'row' => 1,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 350,
                'row' => 2,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 351,
                'row' => 3,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 352,
                'row' => 4,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 353,
                'row' => 5,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 354,
                'row' => 6,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 355,
                'row' => 7,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 356,
                'row' => 8,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 357,
                'row' => 9,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 358,
                'row' => 10,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 359,
                'row' => 11,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 360,
                'row' => 12,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 361,
                'row' => 13,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 362,
                'row' => 14,
                'is_range' => 0,
                'report_id' => 18
            ],
            [
                'id' => 363,
                'row' => 15,
                'is_range' => 1,
                'report_id' => 18
            ]
        ];
        Rowvalidaterule::insert($records);
        $this->setAutoIncrementStart('row_validate_rules', 364);
    }

    protected function insertValidationRulesData()
    {
        $records = [
            [
                'id' => 1,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Report 1 (R4)',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 2,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Number of Successful Full-text Article Requests by Month and Journal',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 3,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 4,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 5,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Period covered by Report:',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 6,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD to YYYY-MM-DD',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 7,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Date run:',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 8,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 9,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Journal',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 10,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 11,
                'colname' => 'C',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 12,
                'colname' => 'D',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Journal DOI',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 13,
                'colname' => 'E',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Proprietary Identifier',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 14,
                'colname' => 'F',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Print ISSN',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 15,
                'colname' => 'G',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Online ISSN',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 16,
                'colname' => 'H',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period Total',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 17,
                'colname' => 'I',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period HTML',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 18,
                'colname' => 'J',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period PDF',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 19,
                'colname' => 'K',
                'rowno' => 8,
                'ruletype' => 'date_format',
                'value' => 'MMM-YYYY',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 20,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Total for all journals',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 21,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 22,
                'colname' => 'C',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 23,
                'colname' => 'D',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 24,
                'colname' => 'E',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 25,
                'colname' => 'F',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 26,
                'colname' => 'G',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 27,
                'colname' => 'H',
                'rowno' => 9,
                'ruletype' => 'sum-row-column',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => 'K',
                'match_column' => ''
            ],
            [
                'id' => 28,
                'colname' => 'I',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 29,
                'colname' => 'J',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 30,
                'colname' => 'K',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 31,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 32,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 33,
                'colname' => 'C',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 34,
                'colname' => 'D',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 35,
                'colname' => 'E',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 36,
                'colname' => 'F',
                'rowno' => 10,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 37,
                'colname' => 'G',
                'rowno' => 10,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 38,
                'colname' => 'H',
                'rowno' => 10,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => 'K',
                'match_column' => ''
            ],
            [
                'id' => 39,
                'colname' => 'I',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 40,
                'colname' => 'J',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 41,
                'colname' => 'K',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 1,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 42,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Report 1 GOA (R4)',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 43,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Number of Successful Gold Open Access Full-Text Article Requests by Month and Journal',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 44,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 45,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 46,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Period covered by Report:',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 47,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD to YYYY-MM-DD',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 48,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Date run:',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 49,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 50,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Journal',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 51,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 52,
                'colname' => 'C',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 53,
                'colname' => 'D',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Journal DOI',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 54,
                'colname' => 'E',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Proprietary Identifier',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 55,
                'colname' => 'F',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Print ISSN',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 56,
                'colname' => 'G',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Online ISSN',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 57,
                'colname' => 'H',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period Total',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 58,
                'colname' => 'I',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period HTML',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 59,
                'colname' => 'J',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period PDF',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 60,
                'colname' => 'K',
                'rowno' => 8,
                'ruletype' => 'date_format',
                'value' => 'MMM-YYYY',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 61,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Total for all journals',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 62,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 63,
                'colname' => 'C',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 64,
                'colname' => 'D',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 65,
                'colname' => 'E',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 66,
                'colname' => 'F',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 67,
                'colname' => 'G',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 68,
                'colname' => 'H',
                'rowno' => 9,
                'ruletype' => 'sum-row-column',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => 'K',
                'match_column' => ''
            ],
            [
                'id' => 69,
                'colname' => 'I',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 70,
                'colname' => 'J',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 71,
                'colname' => 'K',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 72,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 73,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 74,
                'colname' => 'C',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 75,
                'colname' => 'D',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 76,
                'colname' => 'E',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 77,
                'colname' => 'F',
                'rowno' => 10,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 78,
                'colname' => 'G',
                'rowno' => 10,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 79,
                'colname' => 'H',
                'rowno' => 10,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => 'K',
                'match_column' => ''
            ],
            [
                'id' => 80,
                'colname' => 'I',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 81,
                'colname' => 'J',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 82,
                'colname' => 'K',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 2,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 83,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Book Report 1 (R4)',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 84,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Number Of Successful Title Requests by Month And Title',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 85,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 86,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 87,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Period covered by Report:',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 88,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD to YYYY-MM-DD',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 89,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Date run:',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 90,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 91,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 92,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 93,
                'colname' => 'C',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 94,
                'colname' => 'D',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Book DOI',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 95,
                'colname' => 'E',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Proprietary Identifier',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 96,
                'colname' => 'F',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 97,
                'colname' => 'G',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'ISSN',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 98,
                'colname' => 'H',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period Total',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 99,
                'colname' => 'I',
                'rowno' => 8,
                'ruletype' => 'date_format',
                'value' => 'MMM-YYYY',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 100,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Total for all titles',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 101,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 102,
                'colname' => 'C',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 103,
                'colname' => 'D',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 104,
                'colname' => 'E',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 105,
                'colname' => 'F',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 106,
                'colname' => 'G',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 107,
                'colname' => 'H',
                'rowno' => 9,
                'ruletype' => 'sum-row-column',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => 'I',
                'match_column' => ''
            ],
            [
                'id' => 108,
                'colname' => 'I',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 109,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 110,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 111,
                'colname' => 'C',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 112,
                'colname' => 'D',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 113,
                'colname' => 'E',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 114,
                'colname' => 'F',
                'rowno' => 10,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 115,
                'colname' => 'G',
                'rowno' => 10,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 116,
                'colname' => 'H',
                'rowno' => 10,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 0,
                'start_column' => 'I',
                'match_column' => ''
            ],
            [
                'id' => 117,
                'colname' => 'I',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 3,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 118,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Book Report 2 (R4)',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 119,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Number Of Successful Section Requests by Month and Title',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 120,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 121,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Section Type:',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 122,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 123,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 124,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Period covered by Report:',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 125,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD to YYYY-MM-DD',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 126,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Date run:',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 127,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 128,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 129,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 130,
                'colname' => 'C',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 131,
                'colname' => 'D',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Book DOI',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 132,
                'colname' => 'E',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Proprietary Identifier',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 133,
                'colname' => 'F',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 134,
                'colname' => 'G',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'ISSN',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 135,
                'colname' => 'H',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period Total',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 136,
                'colname' => 'I',
                'rowno' => 8,
                'ruletype' => 'date_format',
                'value' => 'MMM-YYYY',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 137,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Total for all titles',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 138,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 139,
                'colname' => 'C',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 140,
                'colname' => 'D',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 141,
                'colname' => 'E',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 142,
                'colname' => 'F',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 143,
                'colname' => 'G',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 144,
                'colname' => 'H',
                'rowno' => 9,
                'ruletype' => 'sum-row-column',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => 'I',
                'match_column' => ''
            ],
            [
                'id' => 145,
                'colname' => 'I',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 146,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 147,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 148,
                'colname' => 'C',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 149,
                'colname' => 'D',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 150,
                'colname' => 'E',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 151,
                'colname' => 'F',
                'rowno' => 10,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 152,
                'colname' => 'G',
                'rowno' => 10,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 153,
                'colname' => 'H',
                'rowno' => 10,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 0,
                'start_column' => 'I',
                'match_column' => ''
            ],
            [
                'id' => 154,
                'colname' => 'I',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 4,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 155,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Multimedia Report 1 (R4)',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 156,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Number Of Successful Multimedia Full Content Unit Requests by Month and Collection',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 157,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 158,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 159,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Period covered by Report:',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 160,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD to YYYY-MM-DD',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 161,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Date run:',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 162,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'date_format',
                'value' => 'YYYY-MM-DD',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 163,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Collection',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 164,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Content Provider',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 165,
                'colname' => 'C',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 166,
                'colname' => 'D',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Reporting Period Total',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 167,
                'colname' => 'E',
                'rowno' => 8,
                'ruletype' => 'date_format',
                'value' => 'MMM-YYYY',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 168,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Total for all Collections',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 169,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 170,
                'colname' => 'C',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 171,
                'colname' => 'D',
                'rowno' => 9,
                'ruletype' => 'sum-row-column',
                'value' => '',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => 'E',
                'match_column' => ''
            ],
            [
                'id' => 172,
                'colname' => 'E',
                'rowno' => 9,
                'ruletype' => 'sum',
                'value' => '',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 173,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 174,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 175,
                'colname' => 'C',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 176,
                'colname' => 'D',
                'rowno' => 10,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 0,
                'start_column' => 'E',
                'match_column' => ''
            ],
            [
                'id' => 177,
                'colname' => 'E',
                'rowno' => 10,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 5,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 226,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 227,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Requests (Excluding OA_Gold)',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 228,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 229,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 230,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 231,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 232,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 233,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 234,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 240,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 246,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 252,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 258,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 259,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Access Denied',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 260,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 261,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 262,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 263,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 264,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 265,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 266,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 306,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 307,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Usage by Access Type',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 308,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 309,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 310,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 311,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 312,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 313,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 342,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 343,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Requests by YOP  (Excluding OA_Gold)',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 344,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 345,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 346,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 347,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 348,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 349,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 350,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 356,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 380,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 381,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Multimedia Item Requests',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 382,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 383,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 384,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 385,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 386,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 387,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 388,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 397,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 406,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 407,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 415,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 416,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Platform Usage',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 417,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 418,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 419,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 420,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 421,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 422,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 423,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 433,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 444,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 445,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 454,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 455,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 469,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 1,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 470,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 471,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 472,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 473,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 474,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 477,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_J1',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 478,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 479,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 480,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 482,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 483,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 484,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 485,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 486,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 487,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 488,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 489,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 490,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 491,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 492,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 493,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 494,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 495,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 496,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 497,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 498,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 504,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 505,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 506,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 507,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 508,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 509,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 510,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 511,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 512,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 513,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 514,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => 'L',
                'match_column' => ''
            ],
            [
                'id' => 537,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 538,
                'colname' => 'A',
                'rowno' => 27,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 562,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 7,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 563,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_J2',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 564,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 565,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 566,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 567,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 568,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 570,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 573,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 574,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 575,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 576,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 577,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 578,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 579,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 580,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 581,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 582,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 583,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 584,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 585,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 586,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 587,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 592,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 593,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 594,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 595,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 596,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 597,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 598,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 599,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 600,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 601,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => 'L',
                'match_column' => ''
            ],
            [
                'id' => 602,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 8,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 603,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_J3',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 604,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 605,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 606,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 607,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 608,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 610,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 611,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 615,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 617,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 618,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 619,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 620,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 621,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 622,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 623,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 624,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 625,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 626,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 627,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 628,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 629,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 630,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 631,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 632,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 635,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Access_Type',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 636,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 637,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 638,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 639,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 640,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 641,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 642,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 643,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 644,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 645,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 647,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 648,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 649,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => 'M',
                'match_column' => ''
            ],
            [
                'id' => 650,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_J4',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 651,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 652,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 653,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 654,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => '',
                'value' => '',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 655,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 656,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 657,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 658,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 659,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 660,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 661,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 662,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 663,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 664,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 665,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 666,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 667,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 668,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 669,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 670,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 671,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 672,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'YOP',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 673,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 674,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 675,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 676,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 677,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 678,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 679,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 680,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 681,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 682,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 683,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 684,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 685,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'date_format',
                'value' => 'YYYY',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 686,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 687,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => 'M',
                'match_column' => ''
            ],
            [
                'id' => 688,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 10,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 689,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'PR_P1',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 690,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 691,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 692,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 693,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 694,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 696,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 697,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 698,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 699,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 700,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 701,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 702,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 703,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 704,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 705,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => 'D',
                'match_column' => ''
            ],
            [
                'id' => 706,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'IR_M1',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 707,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 708,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 709,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 710,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 711,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 712,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 713,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 714,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 715,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 716,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 717,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Item',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 718,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 719,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 720,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 721,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Article_Version',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 722,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 723,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 724,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 725,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 726,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 727,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 728,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 729,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 730,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 731,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 732,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 733,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 734,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 735,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 736,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => 'K',
                'match_column' => ''
            ],
            [
                'id' => 744,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 745,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Book Access Denied',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 746,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 747,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_B2',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 748,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 749,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 750,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 751,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 752,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 753,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 754,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 755,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 756,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 757,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 758,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 759,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 760,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 761,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 762,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 763,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 764,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 765,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 766,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 767,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 768,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 769,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 770,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 771,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 772,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 773,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 774,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 775,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 776,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 777,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 778,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 779,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 780,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 781,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 782,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 783,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 784,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 785,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 786,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 787,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 788,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 789,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 790,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 791,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => 'M',
                'match_column' => ''
            ],
            [
                'id' => 792,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 17,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 798,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 799,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 800,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Book Requests (Excluding OA_Gold)',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 801,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_B1',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 802,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 803,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 804,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 805,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 806,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 807,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 808,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 809,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 810,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 811,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 812,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 813,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 814,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 815,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 816,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 817,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 818,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 819,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 820,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 821,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 822,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 823,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 824,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 825,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 826,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 827,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 828,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 829,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 830,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 831,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 832,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 833,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 834,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 835,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 836,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 837,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 838,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 839,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 840,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 841,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 842,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 843,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 844,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 845,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => 'M',
                'match_column' => ''
            ],
            [
                'id' => 846,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 16,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 853,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 854,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Book Usage by Access Type',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 855,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 856,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 857,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 858,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 859,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR_B3',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 860,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 861,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 862,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 863,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 864,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 865,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 866,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 867,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 868,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 869,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 870,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 871,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 872,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 873,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 874,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 875,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 876,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 877,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 878,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 879,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 880,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 881,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 882,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 883,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 884,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 885,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 886,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 887,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Access_Type',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 888,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 889,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 890,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 891,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 892,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 893,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 894,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 895,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 896,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 897,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 898,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 899,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 900,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 901,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => 'N',
                'match_column' => ''
            ],
            [
                'id' => 902,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 903,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 904,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Journal Article Requests',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 905,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 906,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 907,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 908,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 909,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 910,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 911,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 912,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 913,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 914,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 915,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 916,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 917,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Item',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 918,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 919,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'IR_A1',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 920,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 921,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 922,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 923,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 924,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 925,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 926,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 927,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 928,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 929,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 930,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Authors',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 931,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publication_Date',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 932,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Article_Version',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 933,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 934,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 935,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 936,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 937,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 938,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 939,
                'colname' => 'N',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Title',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 940,
                'colname' => 'O',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Data_Type',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 941,
                'colname' => 'P',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_DOI',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 942,
                'colname' => 'Q',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Proprietary_ID',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 943,
                'colname' => 'R',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_ISBN',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 944,
                'colname' => 'S',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Print_ISSN',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 945,
                'colname' => 'T',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Online_ISSN',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 946,
                'colname' => 'U',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_URI',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 947,
                'colname' => 'V',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 948,
                'colname' => 'W',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 949,
                'colname' => 'X',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 950,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 951,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 952,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 953,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 954,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'date_format',
                'value' => 'YYYY',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 955,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 956,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 957,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 958,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 959,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 960,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 961,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 962,
                'colname' => 'N',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 963,
                'colname' => 'O',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 964,
                'colname' => 'P',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 965,
                'colname' => 'Q',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 966,
                'colname' => 'R',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 967,
                'colname' => 'S',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 968,
                'colname' => 'T',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 969,
                'colname' => 'U',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 970,
                'colname' => 'V',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 971,
                'colname' => 'W',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => 'X',
                'match_column' => ''
            ],
            [
                'id' => 972,
                'colname' => 'X',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 11,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 973,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 974,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 975,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 976,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 977,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 978,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 979,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 980,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 981,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 982,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 983,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 984,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 985,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 986,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Database',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 987,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 988,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Database Search and Item Usage',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 989,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'DR_D1',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 990,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 991,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 992,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 993,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 994,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 995,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 996,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 997,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 998,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 999,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1000,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1001,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1002,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1003,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1004,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1005,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1006,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1007,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1008,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1009,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1010,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => 'H',
                'match_column' => ''
            ],
            [
                'id' => 1011,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 14,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1012,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1013,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1014,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1015,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1016,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1017,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1018,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1019,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1020,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1021,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1022,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1023,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1024,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1025,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Database',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1026,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1027,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Database Access Denied',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1028,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'DR_D2',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1029,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1030,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1031,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1032,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1033,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1035,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1036,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1037,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1038,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1039,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1040,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1041,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1042,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1043,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1044,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1045,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1046,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1047,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1048,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1049,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1050,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => 'H',
                'match_column' => ''
            ],
            [
                'id' => 1051,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 15,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1052,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 13,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1053,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1054,
                'colname' => 'N',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1055,
                'colname' => 'N',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 6,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1058,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1059,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1060,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1061,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1062,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1063,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1064,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1065,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1066,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1067,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1068,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1069,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1070,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1071,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Title',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1072,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1074,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Title Master Report',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1075,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'TR',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1076,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1077,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1078,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1079,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1080,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1081,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1082,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1083,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1084,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1085,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1086,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1087,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1088,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1089,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1090,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1096,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1097,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1098,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'mmm-yyyy',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1099,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1100,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1101,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1102,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1103,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1104,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1105,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1107,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1113,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1114,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1115,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => 'M',
                'match_column' => ''
            ],
            [
                'id' => 1116,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1117,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1118,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1119,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1120,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1121,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1122,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1123,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1124,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1125,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1126,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1127,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1128,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1129,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Item',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1130,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1131,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1132,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1133,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1134,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1135,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1136,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1137,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1138,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1139,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1140,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1141,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1142,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1143,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1144,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Database',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1145,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1146,
                'colname' => 'A',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Report_Name',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1147,
                'colname' => 'A',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'Report_ID',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1148,
                'colname' => 'A',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => 'Release',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1149,
                'colname' => 'A',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => 'Institution_Name',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1150,
                'colname' => 'A',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => 'Institution_ID',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1151,
                'colname' => 'A',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => 'Metric_Types',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1152,
                'colname' => 'A',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => 'Report_Filters',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1153,
                'colname' => 'A',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => 'Report_Attributes',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1154,
                'colname' => 'A',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => 'Exceptions',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1155,
                'colname' => 'A',
                'rowno' => 10,
                'ruletype' => 'text',
                'value' => 'Reporting_Period',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1156,
                'colname' => 'A',
                'rowno' => 11,
                'ruletype' => 'text',
                'value' => 'Created',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1157,
                'colname' => 'A',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => 'Created_By',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1158,
                'colname' => 'A',
                'rowno' => 13,
                'ruletype' => '',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1159,
                'colname' => 'A',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1160,
                'colname' => 'A',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1162,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Database Master Report',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1163,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'DR',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1164,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1165,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1166,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1167,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1168,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1169,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1170,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1171,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1172,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1173,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1178,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1179,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1180,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'mmm-yyyy',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1181,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1182,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1183,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1184,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1189,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1190,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => 'H',
                'match_column' => ''
            ],
            [
                'id' => 1191,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 21,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1193,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Item Master Report',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1194,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'IR',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1195,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1196,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1197,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1198,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1199,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1200,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1201,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1202,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1203,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publisher_ID',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1204,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Platform',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1205,
                'colname' => 'E',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Authors',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1206,
                'colname' => 'F',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Publication_Date',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1207,
                'colname' => 'G',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Article_Version',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1208,
                'colname' => 'H',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'DOI',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1209,
                'colname' => 'I',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Proprietary_ID',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1210,
                'colname' => 'J',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'ISBN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1211,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Print_ISSN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1212,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Online_ISSN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1213,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'URI',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1214,
                'colname' => 'N',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Title',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1215,
                'colname' => 'O',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Data_Type',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1216,
                'colname' => 'P',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_DOI',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1217,
                'colname' => 'Q',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Proprietary_ID',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1218,
                'colname' => 'R',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_ISBN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1219,
                'colname' => 'S',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Print_ISSN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1220,
                'colname' => 'T',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_Online_ISSN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1221,
                'colname' => 'U',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Parent_URI',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1222,
                'colname' => 'V',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_Title',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1223,
                'colname' => 'W',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_Data_Type',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1224,
                'colname' => 'X',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_DOI',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1225,
                'colname' => 'Y',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_Proprietary_ID',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1226,
                'colname' => 'Z',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_ISBN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1227,
                'colname' => 'AA',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_Print_ISSN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1228,
                'colname' => 'AB',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_Online_ISSN',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1229,
                'colname' => 'AC',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Component_URI',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1238,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1239,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1240,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1241,
                'colname' => 'E',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1242,
                'colname' => 'F',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1243,
                'colname' => 'G',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1244,
                'colname' => 'H',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1245,
                'colname' => 'I',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1246,
                'colname' => 'J',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1247,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1248,
                'colname' => 'L',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1249,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1250,
                'colname' => 'N',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1251,
                'colname' => 'O',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1252,
                'colname' => 'P',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1253,
                'colname' => 'Q',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1254,
                'colname' => 'R',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1255,
                'colname' => 'S',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1256,
                'colname' => 'T',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1257,
                'colname' => 'U',
                'rowno' => 15,
                'ruletype' => 'string',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1258,
                'colname' => 'V',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1259,
                'colname' => 'W',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1260,
                'colname' => 'X',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1261,
                'colname' => 'Y',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1262,
                'colname' => 'Z',
                'rowno' => 15,
                'ruletype' => 'isbn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1263,
                'colname' => 'AA',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1264,
                'colname' => 'AB',
                'rowno' => 15,
                'ruletype' => 'issn',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1265,
                'colname' => 'AC',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1267,
                'colname' => 'AD',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1272,
                'colname' => 'AE',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => 'AF',
                'match_column' => ''
            ],
            [
                'id' => 1274,
                'colname' => 'B',
                'rowno' => 1,
                'ruletype' => 'text',
                'value' => 'Platform Master Report',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1275,
                'colname' => 'B',
                'rowno' => 2,
                'ruletype' => 'text',
                'value' => 'PR',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1276,
                'colname' => 'B',
                'rowno' => 3,
                'ruletype' => 'text',
                'value' => '5',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1277,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1278,
                'colname' => 'B',
                'rowno' => 5,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1279,
                'colname' => 'B',
                'rowno' => 10,
                'ruletype' => 'date_format',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1280,
                'colname' => 'B',
                'rowno' => 11,
                'ruletype' => 'date_format',
                'value' => 'yyyy-mm-ddThh:mm:ssZ',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1281,
                'colname' => 'B',
                'rowno' => 12,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1292,
                'colname' => 'B',
                'rowno' => 15,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1293,
                'colname' => 'C',
                'rowno' => 15,
                'ruletype' => 'row sum',
                'value' => '',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => 'D',
                'match_column' => ''
            ],
            [
                'id' => 1294,
                'colname' => 'B',
                'rowno' => 4,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1295,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1296,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1297,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1298,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1299,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1300,
                'colname' => 'B',
                'rowno' => 6,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1301,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1305,
                'colname' => 'B',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1306,
                'colname' => 'C',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1307,
                'colname' => 'D',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'Mmm-yyyy',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1308,
                'colname' => 'D',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 18,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1309,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1312,
                'colname' => 'AD',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Metric_Type',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1313,
                'colname' => 'AE',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1316,
                'colname' => 'AF',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1317,
                'colname' => 'K',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'mmm-yyyy',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1318,
                'colname' => 'K',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 12,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1319,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 19,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1320,
                'colname' => 'L',
                'rowno' => 14,
                'ruletype' => 'text',
                'value' => 'Reporting_Period_Total',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1321,
                'colname' => 'M',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'mmm-yyyy',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1322,
                'colname' => 'M',
                'rowno' => 15,
                'ruletype' => 'integer',
                'value' => '',
                'required' => 1,
                'report_no' => 9,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1323,
                'colname' => 'AF',
                'rowno' => 14,
                'ruletype' => 'date_format',
                'value' => 'mmm-yyyy',
                'required' => 1,
                'report_no' => 20,
                'is_range' => 1,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1324,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1325,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 21,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1326,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1327,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 14,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1328,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1329,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 15,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1330,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1331,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 20,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1332,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1333,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 11,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1334,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1335,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 12,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1336,
                'colname' => 'B',
                'rowno' => 7,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1337,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 18,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1338,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1339,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 13,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1340,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 19,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1341,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1342,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 16,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1343,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1344,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 17,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1345,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 6,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1347,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1348,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 7,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1349,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1350,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 8,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1351,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 9,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1352,
                'colname' => 'B',
                'rowno' => 8,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ],
            [
                'id' => 1353,
                'colname' => 'B',
                'rowno' => 9,
                'ruletype' => 'text',
                'value' => '',
                'required' => 0,
                'report_no' => 10,
                'is_range' => 0,
                'start_column' => '',
                'match_column' => ''
            ]
        ];
        ValidationRule::insert($records);
        $this->setAutoIncrementStart('validation_rules', 1354);
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
        Schema::dropIfExists('transaction_detail_temp');
        Schema::dropIfExists('transaction_master_detail');
        Schema::dropIfExists('consortium_member');
        Schema::dropIfExists('provider_details');
        Schema::dropIfExists('consortium_configuration');
        Schema::dropIfExists('sushi_transaction');
        Schema::dropIfExists('validateerrors');
        Schema::dropIfExists('filenames');
        Schema::dropIfExists('validation_rules');
        Schema::dropIfExists('row_validate_rules');
        Schema::dropIfExists('filtertypes');
        Schema::dropIfExists('reportnames');
        Schema::dropIfExists('parentreports');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
