<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserAdd extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add {first_name} {last_name} {display_name} {email} {utype=user : Role of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = $this->arguments();
        unset($data['command']);
        if ($data['utype'] !== 'user' && $data['utype'] !== 'admin') {
            print('utype ' . $data['utype'] . " invalid (permitted values: user, admin)\n");
            return;
        }
        $data['password'] = $this->secret("Password:");
        $data['password_confirmation'] = $this->secret("Confirm Password:");

        $rules = array(
            'first_name' => 'required|min:1',
            'last_name' => 'required|min:1',
            'display_name' => 'required|min:1',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            print("Failed to create user:\n");
            foreach ($validator->messages()->toArray() as $field => $messages) {
                foreach ($messages as $message) {
                    print("  {$message}\n");
                }
            }
        } elseif (User::create($data)) {
            print("User created!\n");
        } else {
            print("Failed to create user!\n");
        }
    }
}
