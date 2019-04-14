<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ValidatereportController;
use App\Http\Requests;
use App\Validateerror;
use App\Filename;
Use Excel;
Use Mail;
use DateTime;
use App\Http\Manager\SubscriptionManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use Illuminate\Support\Facades\Storage;
use SoapClient;
use SimpleXMLElement;
use App\Reportname;
use App\Filtertype;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\File;
use App\Sushitransaction;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Storedfile;
use App\Reportfile;
use App\Sushiresponse;
use App\Helper;
use Illuminate\Support\Facades\Config;

// phpinfo();die;
// set_error_handler(null);
// set_exception_handler(null);
class FilevalidateController extends CommonController
{

    public $ReportnameValue;

    public $ReportCodeValue;

    static $valid = true;

    protected $action_URL = "";

    // ///////////////////////////////////////validate file///////////////////////////////////////////////////////
    public function filevalidate_ubfr()
    {
        if(!Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        if (Input::hasFile('import_file')) {
            $file = Input::file('import_file');
            $extension = $file->getClientOriginalExtension();
            $report = null;
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
            
            try {
                $reportfile = Reportfile::store($report, $file, $file->getClientOriginalName(), Storedfile::SOURCE_FILE_VALIDATE,
                    $checkResult, $user['id']);
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
    
    public function filevalidate()
    {
        return $this->filevalidate_ubfr();

        $excel = [];
        $err = [];
        $warning = [];
        $extension = '';
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        $error = array();
        
        if (Input::hasFile('import_file')) {
            
            $extension = Input::File('import_file')->getClientOriginalExtension();
            
            if ($extension == 'json') {
                $reportdta = new Filtertype();
                $AllMatricArray = Filtertype::where(array())->orderBy('id', 'asc')
                    ->get()
                    ->toArray();
                // echo "<pre>";print_r($AllMatricArray);die;
                $AllArrayOfMatrix = array();
                foreach ($AllMatricArray as $MatrixVal) {
                    $AllArrayOfMatrix[] = $MatrixVal['name'];
                }
                
                $string_check = 0;
                $a = 0;
                $b = 0;
                $structure_error = 0;
                $data_error = 0;
                $structure_warning = 0;
                $data_warning = 0;
                
                // /////////////////////// start for json /////////////////////////////////////////////
                
                $jsonpath = Input::file('import_file')->getRealPath();
                $json = json_decode(file_get_contents($jsonpath), true);
                 // echo "<pre>";print_r($json);die;
                 if (isset($json['Report_Header'])){
                // /////// validate id and code
                $jsonReportHeader = $json['Report_Header'];
                 
                $currentMasterID = $json['Report_Header']['Report_ID'] ?? '';
                if($json === null) {
                    $currentReportName = 'Structure error in JSON file';
                }else{
                    $currentReportName = str_replace('"', '', $json['Report_Header']['Report_Name'] ?? '');
                }
                
                // /////// checking Code and ID first time
                $result = CommonController::jsonHeaderCodeIDValidate($currentReportName, $currentMasterID, $jsonReportHeader);
                
                if (isset($result[0])) {
                    $file = Input::file('import_file');
                    $upload_file = $this->fileupload($file);
                    
                    $user = Session::get('user');
                    
                    $data['warning_details'] =array();
                    $data['structure_error'] = $result['structure_error'] ?? '0';
                    $data['error_details'] = $result ?? '0';
                    $data['structure_warning'] = $result['structure_warning'] ?? '0';
                    $data['data_warning'] = $result['data_warning'] ?? '0';
                    $data['userDisplayName'] = $user['display_name'];
                    $data['utype'] = $user['utype'];
                    $data['uploaded_file'] = $upload_file ?? '';
                    $inserterror_warning = $this->inserterror('', $data['warning_details'], $user['id'], $upload_file, $extension, $currentReportName, $currentMasterID);
                    $data['file_id'] = $inserterror_warning;
                    //echo "<pre>REPORT";print_r($data);die;
                    return view('validate_report', $data);
                } 
                
                
                $jsonReportHeader = $json['Report_Header'];
                $reportid = $jsonReportHeader['Report_ID'];
                $reportname = $jsonReportHeader['Report_Name'];
                
                $result = CommonController::jsonHeaderValidate($jsonReportHeader);
                $file = Input::file('import_file');
                $upload_file = $this->fileupload($file);
                
                if ((isset($result['warning'][0])) && ($result['warning'][0]['error'] == 'Report id is invalid in Report Header' || $result['warning'][0]['error'] == 'Report name is invalid in Report Header')) {
                    $user = Session::get('user');
                    
                    $dataIncludeHeader['data_error'] = $result['data_error'];
                    $dataIncludeHeader['warning_details'] = $result['warning'] ?? '';
                    $dataIncludeHeader['structure_error'] = $result['structure_error'];
                    // $dataIncludeHeader['error_details'] = $result['error']??'';
                    $dataIncludeHeader['structure_warning'] = $result['structure_warning'];
                    $dataIncludeHeader['data_warning'] = $result['data_warning'];
                    $dataIncludeHeader['userDisplayName'] = $user['display_name'];
                    $dataIncludeHeader['utype'] = $user['utype'];
                    $dataIncludeHeader['uploaded_file'] = $upload_file;
                    $inserterror_warning = $this->inserterror('', $dataIncludeHeader['warning_details'], $user['id'], $upload_file, $extension, $reportname, $reportid);
                    $dataIncludeHeader['file_id'] = $inserterror_warning;
                    
                    return view('validate_report', $dataIncludeHeader);
                } else {
                    
                    $currentReportID = $jsonReportHeader['Report_ID'];
                    if ($currentReportID == 'DR_D1' || $currentReportID == 'DR_D2' || $currentReportID == 'DR') {
                        
                        
                        $AllDrReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonDrValidate($AllDrReport);
                        
                    } else if ($currentReportID == 'PR_P1' || $currentReportID == 'PR') {
                       
                            $AllPrReport = $json['Report_Items'];
                            $bodyResult = CommonController::jsonPrValidate($AllPrReport);
                       
                    } else if ($currentReportID == 'IR_A1' || $currentReportID == 'IR_M1' || $currentReportID == 'IR') {
                        
                            $AllIrReport = $json['Report_Items'];
                            $bodyResult = CommonController::jsonIrValidate($AllIrReport);
                        
                    } else {
                            $AllBodyReport = $json['Report_Items'];
                            $bodyResult = CommonController::jsonBodyValidate($AllBodyReport);
                    }
                    
                    
                    // /////////////////////////////// JSON FILE END //////////////////////////////
                    $user = Session::get('user');
                    
                    $dataIncludeHeader['data_error'] =  $bodyResult['data_error'] ?? '';
                    //$dataIncludeHeader['warning_details'] = $result['warning'] ?? '';
                    $dataIncludeHeader['warning_details'] = array();
                    $dataIncludeHeader['structure_error'] = $result['structure_error'] + $bodyResult['structure_error'] ?? '';
                    $dataIncludeHeader['error_details'] = array_merge($result['warning'],$bodyResult['error']);array_merge($result['warning'],$bodyResult['error']);
                    $dataIncludeHeader['structure_warning'] = $result['structure_warning'] + $bodyResult['structure_warning'];
                    $dataIncludeHeader['data_warning'] = $result['data_warning'] + $bodyResult['data_warning'];
                    $dataIncludeHeader['userDisplayName'] = $user['display_name'];
                    $dataIncludeHeader['utype'] = $user['utype'];
                    $dataIncludeHeader['uploaded_file'] = $upload_file;
                    $inserterror_warning = $this->inserterror($dataIncludeHeader['error_details'], $dataIncludeHeader['warning_details'], $user['id'], $upload_file, $extension, $reportname, $reportid);
                    $dataIncludeHeader['file_id'] = $inserterror_warning;
                    //echo "<pre>";print_r($dataIncludeHeader);die;
                    return view('validate_report', $dataIncludeHeader);
                }
                
                 } else {

// 01.03.2019
                     $user = Session::get('user'); 
                     $dataIncludeHeader['warning_details'] =array();
                     $dataIncludeHeader['structure_error'] = '0';
                     $dataIncludeHeader['error_details'] = array(array('data'=>'', 'error'=>'Report_Header is in incorrect format or not available in this Report'));
                     $dataIncludeHeader['structure_warning'] = '0';
                     $dataIncludeHeader['data_warning'] = 0;
                     $dataIncludeHeader['userDisplayName'] = $user['display_name'];
                     $dataIncludeHeader['utype'] = $user['utype'];
                     $dataIncludeHeader['uploaded_file'] = $upload_file ?? '';
                     $dataIncludeHeader['data_error'] = 'Header Missing';
                     //echo "<pre>REPORT!!!";print_r($data);die;
                     $inserterror_warning = $this->inserterror($dataIncludeHeader['error_details'], $dataIncludeHeader['warning_details'], $user['id'], $upload_file??'', $extension, $currentReportName??'', $currentMasterID??'');
                     $dataIncludeHeader['file_id'] =$inserterror_warning;
                     
                     return view('validate_report', $dataIncludeHeader); 
                 }
                 
            } else {
                
                if ($extension == 'tsv') {
                    
                    $tsvpath = Input::file('import_file')->getRealPath();
                    $path = $this->tsvconverttoxls($tsvpath);
                } // ///////////// special case for json ///////////////
                
                else {
                    $path = Input::file('import_file')->getRealPath();
                }
                
                try {
                    // Excel processing start here
                    Excel::load($path, function ($reader) use (&$excel, &$err, &$warning, &$validatereport, &$Reportname1, &$getreportCode) {
                    $error = array();
                    $reader->calculate();
                    $objExcel = $reader->getExcel();
                    $sheet = $objExcel->getSheet(0);
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();
                    for ($row = 1; $row <= 1; $row ++) {
                        // Read a row of data into an array
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . 'B' . $row, NULL, TRUE, FALSE);
                        
                        foreach ($rowData as $detail) {
                            $test = new ValidatereportController();
                            $validatereport = $test->validateExcelOrCsv($sheet, $highestRow, $highestColumn, $err, $warning, $Reportname1, $getreportCode); 
                        }
                    }
                    // Loop through each row of the worksheet in turn
                    $getreportname=$sheet->rangeToArray('B1' . ':' .'B1',NULL, TRUE, FALSE);
                    $this->ReportnameValue  =   $getreportname[0][0]??'BLANK';
                    $getreportcode=$sheet->rangeToArray('B2' . ':' .'B2',NULL, TRUE, FALSE);
                    $this->ReportCodeValue=$getreportcode[0][0]??'BLANK';
                }
            );
                    
            } catch (Exception $exception) {
                report($exception);
                return parent::render($request, $exception);
            }
                // //////////////////////////save file in public uploadfile folder//////////////
                
                $file = Input::file('import_file');
                $upload_file = $this->fileupload($file);
                
                // ////////////////////////////////////////////////////////////
                
                $user = Session::get('user');
                
                $data['error_details'] = $validatereport["error"];
                $data['warning_details'] = $validatereport["warning"];
                $data['structure_error'] = $validatereport["structure_error"];
                $data['data_error'] = $validatereport["data_error"];
                $data['structure_warning'] = $validatereport["structure_warning"];
                $data['data_warning'] = $validatereport["data_warning"];
                $data['userDisplayName'] = $user['display_name'];
                $data['utype'] = $user['utype'];
                $data['uploaded_file'] = $upload_file;
                $inserterror_warning = $this->inserterror($data['error_details'], $data['warning_details'], $user['id'], $upload_file, $extension, $this->ReportnameValue, $this->ReportCodeValue);
                $data['file_id'] = $inserterror_warning;
                return view('validate_report', $data);
            }
        }
    }

    // ///////////////////////object to array//////////////////////////////////////////////////////////
    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }

    // ///////////////////////////////xml to array//////////////////////////////////////////////////////
    public function array_to_xml($array, &$xml_user_info)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (! is_numeric($key)) {
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    // ////////////////////////////sushi Validation function/////////////////////////////////////////////////////
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
            
        $result = curl_exec($curl);
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
                
                $destinationPath = public_path() . "/upload/json/";
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file = time() . '_' . $user['id'] . '_file.json';
                File::put($destinationPath . $file, $result);
                $completePath = $destinationPath . $file;
            }
        }

        DB::beginTransaction();
        try {
            Sushitransaction::create($transaction);
            DB::commit();
        } catch(\Exception $exception) {
            DB::rollback();
        }

        if($sushi_error !== null) {
            Session::flash('sushi_error', $sushi_error);
            return Redirect::back()->withInput();
        }
        
        return response()->download($completePath, $file, [ 'Content-Type: application/json' ]);
    }
    
    public function sushiRequest() {
        if (Session::has('user')) {
            $user = Session::get('user');
            $UserType = $user['utype'];
            if($UserType==='admin'){
                $allSushiRequest = Sushitransaction::select()
                            //->where('user_email', $user['email'])
                            ->orderBy('date_time', 'desc')->get();
            }else{
                $allSushiRequest = Sushitransaction::select()
                            ->where('user_email', $user['email'])
                            ->orderBy('date_time', 'desc')->get();
            }
            //echo "<pre>####";print_r($allSushiRequest->toArray());die;
            $data['utype'] = $user['utype'];
            $data['userDisplayName'] = $user['display_name'];
            $data['sushi_detail'] = $allSushiRequest;
            return view('sushi_request_history', $data);
        } else {
            return Redirect::to('login');
        }
    }
    
    ///////////////////////
    function delete_sushi_request($id) {
        //  die('coming here');
        if (Session::has('user')) {
            
            $user = Session::get('user');
            $UserType = $user['utype'];
            if($UserType==='admin'){
                
                DB::beginTransaction();
                try {
                $alltransaction = Sushitransaction::where('id', $id)->delete();
                DB::commit();
                } catch(Exception $exception) {
                    DB::rollback();
                }
                
            }else{
                $alltransaction = Sushitransaction::where('id', $id)->delete();
                
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
        $user = Session::get('user');

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
    
    
    
    //downloading Sushi Report
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

        $result = curl_exec($curl);
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

        $filename = date('YmdHis') . '_' . parse_url($parameters['api_url'], PHP_URL_HOST);
        if($parameters['platform'] !== null && $parameters['platform'] !== '') {
            $filename .= '_' . $parameters['platform'];
        }
        $filename .= '_' . $parameters['ReportName'] . '_' . $parameters['customer_id'] . '.json';
        $extension = 'json';
        
        $report = null;
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
        
        try {
            $reportfile = Reportfile::store($report, $file, $filename, Storedfile::SOURCE_SUSHI_VALIDATE,
                $checkResult, $user['id']);
            Sushiresponse::store($reportfile->reportfile, $reportfile->checkresult, $sushitransaction);
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
