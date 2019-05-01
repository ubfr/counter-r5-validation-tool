<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use App\Storedfile;

class HistoryCleanup extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:cleanup {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the stored files and SUSHI requests';

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
        $days = $this->option('days');
        if ($days === null || $days === '') {
            $days = Config::get('c5tools.cleanupAfterDays');
        }
        if (! is_numeric($days) || $days != (int) $days || 0 > (int) $days) {
            print("days '{$days}' is invalid, must be a non-negative integer\n");
            return;
        }
        $olderthan = Carbon::now()->subDays($days)->toDateTimeString();
        $success = 0;
        $failed = 0;
        foreach (Storedfile::where('created_at', '<', $olderthan)->get() as $storedfile) {
            print("Deleting Storedfile {$storedfile->id} {$storedfile->filename} ... ");
            if ($storedfile->delete()) {
                print("done\n");
                $success ++;
            } else {
                print("FAILED\n");
                $failed ++;
            }
        }
        if ($success === 0 && $failed === 0) {
            print("No stored files to delete\n");
        } else {
            if ($success) {
                print("Successfully deleted {$success} stored files\n");
            }
            if ($failed) {
                print("FAILED to delete {$failed} stored files\n");
            }
        }
    }
}
