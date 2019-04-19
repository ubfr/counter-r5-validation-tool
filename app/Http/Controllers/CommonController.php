<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
Use Illuminate\Support\Facades\Session;
use App\Reportfile;
use App\Storedfile;

class CommonController extends Controller
{

    function downloadfile($storedfileId)
    {
        if(! Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $storedfile = Storedfile::where('id', $storedfileId)->firstOrFail();
        if($storedfile->user_id !== $user['id'] && $user['utype'] !== 'admin') {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(403, 'You are not authorized to download this validation result.');
        }
        if(! $storedfile->exists()) {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(404, 'Validation result not found.');
        }

        return $storedfile->download();
    }
    

    function emailfile($reportfileId)
    {
        if(! Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
    
        $context = Input::get('context');
        $redirectTo = null;
        if($context === null) {
            $flash = 'emailMsg';
        } else {
            $flash = $context . '_ok';
            if($context === 'file' || $context === 'sushi') {
                $redirectTo = 'filelist';
            }
        }
        
        $reportfile = Reportfile::where('id', $reportfileId)->firstOrFail();
        $resultfile = $reportfile->checkresult->resultfile;
        if($resultfile->user_id !== $user['id']) {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(403, 'You are not authorized to download this validation result.');
        }
        if(! $resultfile->exists()) {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(404, 'Validation result not found.');
        }

        $title = 'Hi ' . $user['display_name'] . ',';
        $content = 'here is the validation result for the report ' . $reportfile->reportfile->filename .
            ' validated on ' . $resultfile->created_at . '.';
        $emailTo = $user['email'];
        
        try {
            Mail::send('emails.result', [
                'title' => $title,
                'content' => $content
            ], function ($message) use ($resultfile, $emailTo) {
                $message->subject('COUNTER R5 Validation Tool Result');
                $message->attachData($resultfile->get(), $resultfile->filename, [
                    'mime' => $resultfile->getMimeType()
                ]);
                $message->to($emailTo);
            });
            Session::flash($flash, 'Email was sent to ' . $emailTo . '.');
        } catch (Exception $exception) {
            report($exception);
        }
        
        if($redirectTo !== null) {
            return Redirect::to($redirectTo);
        } else {
            return back();
        }
    }
    
    public function deleteReportfile($id)
    {
        if(! Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $reportfile = Reportfile::where('id', $id)->firstOrFail();
        if($reportfile->reportfile->user_id !== $user['id'] && $user['utype'] !== 'admin') {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(403, 'You are not authorized to delete this report file.');
        }
        $reportfile->delete();
        
        Session::flash('userupdatemsg', 'Uploaded report and validation result successfully deleted');
        return back();
    }

}