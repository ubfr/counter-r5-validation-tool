<?php
namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Storedfile;

class ShowController extends Controller {
    
    public $TransctionIdCurrent=0;

    public function __construct() {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('login');
        }
    }

    function checkview() {
        if (Session::has('user')) {
            return Redirect::intended('/filelist');
        } else {
            return view('index');
        }
    }

    function showvalidate() {
        $user = Session::get('user');

        $context = Input::get('context');
        if(!in_array($context, [ 'file', 'sushi'])) {
            $context = 'file';
        }
        
        $cleanupAfter = Carbon::now()->subDays(Config::get('c5tools.cleanupAfterDays'))->toDateTimeString();
        $fileReports = Storedfile::with('reportfile', 'reportfile.checkresult')->where('user_id', $user['id'])
            ->where('source', Storedfile::SOURCE_FILE_VALIDATE)
            ->where('type', Storedfile::TYPE_REPORT)
            ->where('created_at', '>', $cleanupAfter)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $sushiReports = Storedfile::with('reportfile', 'reportfile.checkresult')->where('user_id', $user['id'])
            ->where('source', Storedfile::SOURCE_SUSHI_VALIDATE)
            ->where('type', Storedfile::TYPE_REPORT)
            ->where('created_at', '>', $cleanupAfter)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $data = [];
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['fileReports'] = $fileReports;
        $data['sushiReports'] = $sushiReports;
        $data['context'] = $context;

        return view('welcome', $data);
    }

    public function fileHistory()
    {
        if(! Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $filehistoryQuery = Storedfile::with('user', 'reportfile', 'reportfile.checkresult', 'sushiresponse',
            'sushiresponse.checkresult');
        if($user['utype'] !== 'admin') {
            $filehistoryQuery->where('user_id', $user['id']);
        }
        $filehistoryQuery->where('type', Storedfile::TYPE_REPORT)
            ->whereIn('source', [
               Storedfile::SOURCE_FILE_VALIDATE,
                Storedfile::SOURCE_SUSHI_VALIDATE
            ]);
        $cleanupAfter = Carbon::now()->subDays(Config::get('c5tools.cleanupAfterDays'))->toDateTimeString();
        $filehistoryQuery->where('created_at', '>', $cleanupAfter);
        
        return view('file_history',
            [
                'userDisplayName' => $user['display_name'],
                'utype' => $user['utype'],
                'filehistory' => $filehistoryQuery->get()
            ]);
    }
}
