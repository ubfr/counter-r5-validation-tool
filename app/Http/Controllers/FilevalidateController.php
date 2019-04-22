<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Helper;
use App\Reportfile;
use App\Reportname;
use App\Storedfile;
use App\Sushiresponse;
use App\Sushitransaction;

class FilevalidateController extends CommonController
{

    public function filevalidate()
    {
        if(!Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        if (Input::hasFile('import_file')) {
            $file = Input::file('import_file');
            $extension = $file->getClientOriginalExtension();
            $report = null;
            $starttime = microtime(true);
            try {
                $report = \ubfr\c5tools\Report::createFromFile($file->getRealPath(), $extension);
                $checkResult = $report->getcheckResult();
            } catch (Exception $e) {
                $checkResult = new \ubfr\c5tools\CheckResult();
                try {
                    $checkResult->fatalError($e->getMessage(), $e->getMessage());
                } catch (\ubfr\c5tools\ParseException $e) {
                    // ignore expected exception
                }
            }
            $endtime = microtime(true);
            $checktime = round($endtime - $starttime, 3);
            $checkmemory = (int)(memory_get_peak_usage(true) / 1024 / 1024);
            
            try {
                $reportfile = Reportfile::store($report, $file, $file->getClientOriginalName(), Storedfile::SOURCE_FILE_VALIDATE,
                    $checkResult, $user['id'], $checktime, $checkmemory);
            } catch (\Exception $e) {
                report($e);
                Session::flash('file_error', 'Error while storing validation result: ' . $e->getMessage());
                return Redirect::to('filelist');
            }
            
            $data = [
                'reportfile' => $reportfile,
                'checkResult' => $checkResult,
                'userDisplayName' => $user['display_name'],
                'utype' => $user['utype'],
                'context' => 'file'
            ];
            return view('validate_report_ubfr', $data);
        }
        
        return Redirect::to('filelist');
    }
    
    public function sushiValiate()
    {
        if(!Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $parameters = Input::all();
        $rules = [
            'api_url' => 'required|url|regex:/^https?:/|not_regex:/\?/',
            'customer_id' => 'required'
        ];
        $messages = [
            'url' => 'The COUNTER_SUSHI API URL must be a valid URL.',
            'regex' => 'The COUNTER SUSHI API must start with http or https',
            'not_regex' => 'The COUNTER_SUSHI API URL must not contain a ?.'
        ];
        $validator = Validator::make($parameters, $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator, 'welcome');
        }

        if(!in_array($parameters['method'], ['getstatus', 'getmembers', 'getreports'])) {
            abort(404);
        }
        
        foreach(['api_url', 'platform', 'customer_id', 'requestor_id', 'api_key'] as $key) {
            Session::put("sushi_validate.{$key}", $parameters[$key]);
        }
        
        $url = $parameters['api_url'];
        if(substr($url, -1) !== '/') {
            $url .= '/';
        }
        $url .= substr($parameters['method'], 3) . '?';
        $query = [
            'apikey' => $parameters['api_key'],
            'requestor_id' => $parameters['requestor_id'],
            'customer_id' => $parameters['customer_id'],
            'platform' => $parameters['platform']
        ];
        foreach($query as $key => $value) {
            Session::put("sushi_validate.{$key}", $value);
        }
        $query = array_filter($query, function($value) { return ($value !== ''); });
        $url .= http_build_query($query, '', '&');

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_USERAGENT, Config::get('c5tools.userAgent'));

        $starttime = microtime(true);
        $result = curl_exec($curl);
        $endtime = microtime(true);
        $responsetime = round($endtime - $starttime, 3);
        if(curl_errno($curl) || $result === false) {
            Session::flash('sushi_error', 'SUSHI request failed: ' . curl_error($curl));
            return Redirect::back()->withInput();
        }

        $transaction = [
            'user_email' => $user['email'],
            'session_id' => Session::getId(),
            'sushi_url' => $parameters['api_url'],
            'request_name' => $parameters['method'],
            'platform' => $parameters['platform'] ?? '',
            'success' => 'N'
        ];
        
        $sushi_error = null;
        $httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $transaction['number_of_errors'] = $httpCode; // temporary solution until the data model is improved
        if($httpCode !== 200) {
            $sushi_error = "The SUSHI server returned HTTP code {$httpCode} (" . Helper::getMessageForHttpCode($httpCode) . ')';
            try {
                $json = json_decode($result);
            } catch(\Exception $e) {
                $json = null;
            }
            if($json === null) {
                $sushi_error .= ' and no exception or no well-formed JSON.';
            } elseif(is_object($json) && isset($json->Code) && isset($json->Severity) && isset($json->Message)) {
                $sushi_error .= " and exception {$json->Severity} {$json->Code}: {$json->Message}";
                if(isset($json->Data)) {
                    $sushi_error .= " ({$json->Data})";
                }
                $sushi_error .= '.';
                $transaction['success'] = 'Y';
            } else {
                $sushi_error .= " and invalid JSON.";
            }
        } else {
            try {
                $json = json_decode($result);
            } catch(\Exception $e) {
                $json = null;
            }
            if($json === null) {
                $sushi_error = 'The SUSHI server returned HTTP code 200 (OK) but no well-formed JSON.';
            } else {
                $transaction['success'] = 'Y';

                $tmpFilename = tempnam(sys_get_temp_dir(), 'c5fv');
                File::put($tmpFilename, $result);
                $file = new \Illuminate\Http\File($tmpFilename);

                $filename = date('Ymd-His') . '-' . parse_url($parameters['api_url'], PHP_URL_HOST);
                if($parameters['platform'] !== null && $parameters['platform'] !== '') {
                    $filename .= '-' . $parameters['platform'];
                }
                $filename .= '-' . substr($parameters['method'], 3) . '-' . $parameters['customer_id'] . '.json';
            }
        }

        Sushitransaction::create($transaction);

        if($sushi_error !== null) {
            Session::flash('sushi_error', $sushi_error);
            return Redirect::back()->withInput();
        }
        
        return response()->download($file, $filename, [ 'Content-Type: application/json' ]);
    }
    
    public function sushiRequest() {
        if (Session::has('user')) {
            $user = Session::get('user');
            $UserType = $user['utype'];
            if($UserType==='admin'){
                $allSushiRequest = Sushitransaction::select()
                            ->orderBy('date_time', 'desc')->get();
            }else{
                $allSushiRequest = Sushitransaction::select()
                            ->where('user_email', $user['email'])
                            ->orderBy('date_time', 'desc')->get();
            }

            $data = [];
            $data['utype'] = $user['utype'];
            $data['userDisplayName'] = $user['display_name'];
            $data['sushi_detail'] = $allSushiRequest;
            return view('sushi_request_history', $data);
        } else {
            return Redirect::to('login');
        }
    }
    
    function delete_sushi_request($id) {
        if (Session::has('user')) {
            
            $user = Session::get('user');
            $UserType = $user['utype'];
            if($UserType==='admin'){
                
                DB::beginTransaction();
                try {
                Sushitransaction::where('id', $id)->delete();
                DB::commit();
                } catch(Exception $exception) {
                    DB::rollback();
                }
                
            }else{
                Sushitransaction::where('id', $id)->delete();
                
            }
            
            Session::flash('userdelmsg', 'Report successfully deleted');
            return Redirect::intended('/sushirequest');
            
        }
    }
    
    //sushi parameter form
    public function sushiRequestParameter()
    {
        if(!Session::has('user')) {
            return Redirect::to('login');
        }

        $parameters = Input::all();
        // TODO: validation?

        $data = [];
        $data['api_url'] = $parameters['api_url'];
        $data['platform'] = $parameters['platform'];
        $data['customer_id'] = $parameters['customer_id'];
        $data['requestor_id'] = $parameters['requestor_id'];
        $data['api_key'] = $parameters['api_key'];
        $data['allreports'] = Reportname::select([
            'report_code'
        ])->orderBy('report_code', 'asc')
            ->get()
            ->toArray();
        
        return view('sushi_parameter_view', $data);
    }
    
    public function getSushiReport() {
        if(!Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $parameters = Input::all();
        $rules = [
            'api_url' => 'required|url|regex:/^https?:/|not_regex:/\?/',
            'customer_id' => 'required'
        ];
        $messages = [
            'url' => 'The COUNTER_SUSHI API URL must be a valid URL.',
            'regex' => 'The COUNTER SUSHI API must start with http or https',
            'not_regex' => 'The COUNTER_SUSHI API URL must not contain a ?.'
        ];
        $validator = Validator::make($parameters, $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator, 'welcome');
        }
        
        foreach(['api_url', 'platform', 'customer_id', 'requestor_id', 'api_key'] as $key) {
            Session::put("sushi_validate.{$key}", $parameters[$key]);
        }
        
        $url = $parameters['api_url'];
        if(substr($url, -1) !== '/') {
            $url .= '/';
        }
        $url .= 'reports/' . strtolower($parameters['ReportName']) . '?';
        
        $query = [
            'api_key' => $parameters['api_key'],
            'requestor_id' => $parameters['requestor_id'],
            'customer_id' => $parameters['customer_id'],
            'platform' => $parameters['platform'],
            'begin_date' => \DateTime::createFromFormat('m-Y', $parameters['startmonth'])->format('Y-m-01'),
            'end_date' => \DateTime::createFromFormat('m-Y', $parameters['endmonth'])->format('Y-m-t'),
            'metric_type' => implode('|', $parameters['metricType'] ?? array()),
            'data_type' => implode('|', $parameters['data_type'] ?? array()),
            'access_type' => implode('|', $parameters['accessType'] ?? array()),
            'access_method' => implode('|', $parameters['accessMethod'] ?? array()),
            'yop' => $parameters['yop']
        ];
        foreach($query as $key => $value) {
            Session::put("sushi_validate.{$key}", $value);
        }
        $query = array_filter($query, function($value) { return ($value !== ''); });
        $url .= http_build_query($query, '', '&');

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_USERAGENT, Config::get('c5tools.userAgent'));

        $starttime = microtime(true);
        $result = curl_exec($curl);
        $endtime = microtime(true);
        $responsetime = round($endtime - $starttime, 3);
        if(curl_errno($curl) || $result === false) {
            Session::flash('sushi_error', 'SUSHI request failed: ' . curl_error($curl));
            return Redirect::back()->withInput();
        }
        
        $transaction = [
            'user_email' => $user['email'],
            'session_id' => Session::getId(),
            'sushi_url' => $parameters['api_url'],
            'report_id' => $parameters['ReportName'],
            'report_format' => 'json',
            'request_name' => 'getreport',
            'platform' => $parameters['platform'],
            'success' => 'N'
        ];
        
        $sushi_error = null;
        $httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $transaction['number_of_errors'] = $httpCode; // temporary solution until the data model is improved
        if($httpCode !== 200) {
            $sushi_error = "The SUSHI server returned HTTP code {$httpCode} (" . Helper::getMessageForHttpCode($httpCode) . ')';
            try {
                $json = json_decode($result);
            } catch(\Exception $e) {
                $json = null;
            }
            if($json === null) {
                $sushi_error .= ' and no exception.';
            } elseif(is_object($json) && isset($json->Code) && isset($json->Severity) && isset($json->Message)) {
                $sushi_error .= " and exception {$json->Severity} {$json->Code}: {$json->Message}";
                if(isset($json->Data)) {
                    $sushi_error .= " ({$json->Data})";
                }
                $sushi_error .= '.';
                $transaction['success'] = 'Y';
            } else {
                $sushi_error .= " and invalid JSON.";
                Session::flash('sushi_error', $sushi_error);
                return Redirect::back()->withInput();
            }

            Sushitransaction::create($transaction);

            Session::flash('sushi_error', $sushi_error);
            return Redirect::back()->withInput();
        }
        
        $transaction['success'] = 'Y';
        $sushitransaction = Sushitransaction::create($transaction);

        // validation currently requires a file 
        $tmpFilename = tempnam(sys_get_temp_dir(), 'c5fv');
        File::put($tmpFilename, $result);
        $file = new \Illuminate\Http\File($tmpFilename);

        $filename = date('Ymd-His') . '-' . parse_url($parameters['api_url'], PHP_URL_HOST);
        if($parameters['platform'] !== null && $parameters['platform'] !== '') {
            $filename .= '-' . $parameters['platform'];
        }
        $filename .= '-' . $parameters['ReportName'] . '-' . $parameters['customer_id'] . '.json';
        $extension = 'json';
        
        $report = null;
        $starttime = microtime(true);
        try {
            $report = \ubfr\c5tools\Report::createFromFile($tmpFilename, $extension);
            $checkResult = $report->getcheckResult();
        } catch (Exception $e) {
            $checkResult = new \ubfr\c5tools\CheckResult();
            try {
                $checkResult->fatalError($e->getMessage(), $e->getMessage());
            } catch (\ubfr\c5tools\ParseException $e) {
                // ignore expected exception
            }
        }
        $endtime = microtime(true);
        $checktime = round($endtime - $starttime, 3);
        $checkmemory = (int)(memory_get_peak_usage(true) / 1024 / 1024);
        
        try {
            $reportfile = Reportfile::store($report, $file, $filename, Storedfile::SOURCE_SUSHI_VALIDATE,
                $checkResult, $user['id'], $checktime, $checkmemory);
            Sushiresponse::store($reportfile->reportfile, $reportfile->checkresult, $sushitransaction, $responsetime);
        } catch (\Exception $e) {
            report($e);
            Session::flash('sushi_error', 'Error while storing validation result: ' . $e->getMessage());
            return Redirect::to('filelist');
        }
        
        $data = [
            'reportfile' => $reportfile,
            'checkResult' => $checkResult,
            'userDisplayName' => $user['display_name'],
            'utype' => $user['utype'],
            'context' => 'sushi'
        ];
        return view('validate_report_ubfr', $data);
    }
}
