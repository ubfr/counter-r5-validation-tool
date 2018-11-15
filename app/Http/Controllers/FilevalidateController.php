<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ValidatereportController;
use App\Http\Requests;
use App\Validateerror;
use App\Filename;
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
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

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
    public function filevalidate()
    {
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
                
                // /////// validate id and code
                $currentMasterID = $json['Report_Header']['Report_ID'] ?? '';
                if($json === null) {
                    $currentReportName = 'Structure error in JSON file';
                }else{
                    $currentReportName = str_replace('"', '', $json['Report_Header']['Report_Name'] ?? '');
                }
                
                // /////// checking Code and ID first time
                $result = CommonController::jsonHeaderCodeIDValidate($currentReportName, $currentMasterID);
                
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
                    } 
                    else if ($currentReportID == 'PR_P1' || $currentReportID == 'PR') {
                        $AllPrReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonPrValidate($AllPrReport);
                    } 
                    else if ($currentReportID == 'IR_A1' || $currentReportID == 'IR_M1' || $currentReportID == 'IR') {
                        $AllIrReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonIrValidate($AllIrReport);
                    } 
                    else {
                        
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
                
                if ($extension == 'tsv') {
                    $tsvpath = Input::file('import_file')->getRealPath();
                    $path = $this->tsvconverttoxls($tsvpath);
                } // ///////////// special case for json ///////////////
                
                else {
                    $path = Input::file('import_file')->getRealPath();
                }
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
                            $validatereport = $test->JournalReport1R4($sheet, $highestRow, $highestColumn, $err, $warning, $Reportname1, $getreportCode); 
                        }
                    }
                    // Loop through each row of the worksheet in turn
                    $getreportname=$sheet->rangeToArray('B1' . ':' .'B1',NULL, TRUE, FALSE);
                    $this->ReportnameValue  =   $getreportname[0][0]??'BLANK';
                    $getreportcode=$sheet->rangeToArray('B2' . ':' .'B2',NULL, TRUE, FALSE);
                    $this->ReportCodeValue=$getreportcode[0][0]??'BLANK';
                }
            
            );
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
    public function sushiValiate(Request $request)
    {
        // $allRequstVarible = $request->all();
        $allRequstVarible = Input::all();
        $user = Session::get('user');
        
        $rules = array(
            'Requestorurl' => 'required',
            'CustomerId' => 'required',
            //'APIkey' => 'required',
                //'ReportName' => 'required|not_in:0',
                //'Release' => 'required',
                //'startmonth' => 'required',
                //'endmonth' => 'required'
        );
        $validator = Validator::make($allRequstVarible, $rules);
        if ($validator->fails()) {
            // If validation falis redirect back to login.
            return Redirect::back()->withInput(array())->withErrors($validator, 'welcome');
        } else {
            if ($allRequstVarible['requestButton'] == 'getmembers' || $allRequstVarible['requestButton'] == 'getverify') {
                //$members = 'https://c5beta.mpsinsight.com/insightc5api/services/members?apikey=rsc::94d66f4a0f51dcd8d68924dda75e9ad9&customer_id=mu-0000033';
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = $mainURL . "/members" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey']
                );
                $url = $mainURL . http_build_query($fields, '', "&");
            }
            else if($allRequstVarible['requestButton'] == 'getsupportedreports'){
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = $mainURL . "/reports" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey']
                );
                $url = $mainURL . http_build_query($fields, '', "&");
            }
            else if($allRequstVarible['requestButton'] == 'getstatus'){
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = $mainURL . "/status" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey']
                );
                $url = $mainURL . http_build_query($fields, '', "&");
            }
            
            else if($allRequstVarible['requestButton'] == 'getall'){
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = $mainURL . "/reports" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey']
                );
                $url = $mainURL . http_build_query($fields, '', "&");
                
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
            
            $file = time() . '_file.json';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            $result = curl_exec($curl);
            if ($result !== false) {
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($statusCode == 404) {
                    $result = array("SUSHI URL doest not exist");
                } else {
                    //$data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);
                    $opts = [
                        "http" => [
                            "method" => "GET",
                            "header" => "Accept-language: en\r\n" .
                            "apikey: " . $allRequstVarible['APIkey']
                        ]
                    ];

                    $context = stream_context_create($opts);

                    // Open the file using the HTTP headers set above
                    //$file = file_get_contents('https://c5.mpsinsight.com/insightc5api/services/reports/tr_j1?customer_id=11&begin_date=2018-01-01&end_date=2018-06-30', false, $context);
                    $data = file_get_contents($url, false, $context);

                    $destinationPath = public_path() . "/upload/json/";
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    File::put($destinationPath . $file, $data);
                    $completePath = $destinationPath . $file;

                    //data insertsion for sushi transaction 
                    //echo "<pre>";print_r($user);die;
                    $DataForInsertion = array(
                                    'user_email'=>$user['email']??'',
                                    'session_id'=>$user['display_name']??'',
                                    'report_id'=>$allRequstVarible['ReportName']??'',
                                    'report_format'=>'json',
                                    'sushi_url'=>substr($mainURL, 0, -1)??'',
                                    'request_name'=>$allRequstVarible['requestButton']??'',
                                    'platform'=>$allRequstVarible['requestButton']??'',
                                    'success'=>'Y',
                                    'number_of_errors'=>0
                                );
                    //echo "<pre>ddd";print_r($DataForInsertion);die;
                    Sushitransaction::create($DataForInsertion);


                    $headers = ['Content-Type: application/json'];
                    $newName = $file;
                    return response()->download($completePath, $file, $headers);
                
            }
            } 
            }
            
            
            
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
            
            $file = time() . '_file.json';
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_NOBODY, true);
                $result = curl_exec($curl);
                if ($result !== false) {
                    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    if ($statusCode == 404) {
                        $result = array("SUSHI URL doest not exist");
                    } else {
                        //$data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);
                        $opts = [
                            "http" => [
                                "method" => "GET",
                                "header" => "Accept-language: en\r\n" .
                                "apikey: " . $allRequstVarible['APIkey']
                            ]
                        ];

                        $context = stream_context_create($opts);

                        // Open the file using the HTTP headers set above
                        //$file = file_get_contents('https://c5.mpsinsight.com/insightc5api/services/reports/tr_j1?customer_id=11&begin_date=2018-01-01&end_date=2018-06-30', false, $context);
                        $data = file_get_contents($url, false, $context);

                        $destinationPath = public_path() . "/upload/json/";
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        
                        File::put($destinationPath . $file, $data);
                        $completePath = $destinationPath . $file;
                        
                        //data insertsion for sushi transaction 
                        //echo "<pre>";print_r($user);die;
                        $DataForInsertion = array(
                                        'user_email'=>$user['email']??'',
                                        'session_id'=>$user['display_name']??'',
                                        'report_id'=>$allRequstVarible['ReportName']??'',
                                        'report_format'=>'json',
                                        'sushi_url'=>substr($mainURL, 0, -1)??'',
                                        'request_name'=>$allRequstVarible['requestButton']??'',
                                        'platform'=>$allRequstVarible['requestButton']??'',
                                        'success'=>'Y',
                                        'number_of_errors'=>0
                                    );
                        //echo "<pre>ddd";print_r($DataForInsertion);die;
                        Sushitransaction::create($DataForInsertion);
                        
                        
                        $headers = ['Content-Type: application/json'];
                        $newName = $file;
                        return response()->download($completePath, $file, $headers);
                        
                    }
                } else {
                    //die;
                    $result = array("SUSHI URL seems to invalid");
                }
                //use App\Sushitransaction
            $statusArray = array();
            $statusArray['success'] = 1;
            $statusArray['uploaded_file'] = $file;
            $statusArray['utype'] = $user['utype'];
            $statusArray['userDisplayName'] = $user['display_name'];
            $statusArray['error_report'] = '';
            if (isset($result[0]) and count($result[0]) > 0)
                $statusArray['success'] = 0;
            $statusArray['inputvalues'] = $allRequstVarible;
            $statusArray['error_report'] = $result;
            return view('validate_sushi_report', $statusArray);
        }
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
                $alltransaction = Sushitransaction::where('id', $id)->delete();
            }else{
                $alltransaction = Sushitransaction::where('id', $id)->delete();
            }
            
            Session::flash('userdelmsg', 'Report successfully deleted');
            return Redirect::intended('/sushirequest');
            
        }
    }
    
    
    
    //sushi parameter form
    public function sushiRequestParameter($Requestorurl='',$apikey='',$CustomerId='',$platform='') {
        //get Report list
         $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $user = Session::get('user');
        $data['Requestorurl'] = $Requestorurl;
        $data['apikey'] = $apikey;
        $data['CustomerId'] = $CustomerId;
        $data['platform'] = $platform;
        $AllReportCodes = Reportname::select(array(
                                    'report_code'
                                ))->orderBy('report_code', 'asc')
                                ->get()
                                ->toArray();
        $data['allreports'] = $AllReportCodes;
        return view('sushi_parameter_view', $data);
    }
    
    
    
    
    //downloading Sushi Report
    public function getSushiReport() {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $allRequstVarible = Input::all();
        $result = "";
        $mainURL = str_replace('_','/',$allRequstVarible['Requestorurl']);
        $ReportName = $allRequstVarible['ReportName'];
        $mainURL    =    $mainURL."/reports/".strtolower($ReportName)."?";
        
        $begin_date = explode("-", $allRequstVarible['startmonth']);
        $begin_date = $begin_date[1] . "-" . $begin_date[0] . "-01";
        $end_date = explode("-", $allRequstVarible['endmonth']);
        $d = cal_days_in_month(CAL_GREGORIAN, $end_date[0], $end_date[1]);
        $end_date = $end_date[1] . "-" . $end_date[0] . "-" . $d;
        
        
        //metricType paremeter
        $metricType = implode(',',$allRequstVarible['metricType']??array());
        $accessType = implode(',',$allRequstVarible['accessType']??array());
        $accessMethod = implode(',',$allRequstVarible['accessMethod']??array());
        $data_type = implode(',',$allRequstVarible['data_type']??array());
        $plateformValue =  isset($allRequstVarible['platform']) ? $allRequstVarible['platform']:'';
        $fields = array(
            'apikey' => $allRequstVarible['APIkey']==0?'':$allRequstVarible['APIkey'],
            'customer_id' => $allRequstVarible['CustomerId'],
            'begin_date' => $begin_date,
            'end_date' => $end_date,
            'metric_type' => $metricType,
            'access_type' => $accessType,
            'access_method' => $accessMethod,
            'data_type' => $data_type,
            'platform' => $plateformValue
            );
        $fields = array_filter($fields);
        $url = $mainURL. http_build_query($fields, '', "&");
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "https://" . $url;
        }
        $file = time() . '_file.json';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
            if ($result !== false) {
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($statusCode == 404) {
                    $result = array("SUSHI URL does not exist");
                } else {
                    $opts = [
                        "http" => [
                            "method" => "GET",
                            "header" => "Accept-language: en\r\n" .
                            "apikey: ".$allRequstVarible['APIkey']
                        ]
                    ];

                    $context = stream_context_create($opts);

                    // Open the file using the HTTP headers set above
                    //$file = file_get_contents('https://c5.mpsinsight.com/insightc5api/services/reports/tr_j1?customer_id=11&begin_date=2018-01-01&end_date=2018-06-30', false, $context);
                    $data = file_get_contents($url, false, $context);
                    $json = json_decode($data, true);
                    
                    
                    $jsonReportHeader = $json['Report_Header'];
                    
                    $currentReportID = $jsonReportHeader['Report_ID'];
                    if ($currentReportID == 'DR_D1' || $currentReportID == 'DR_D2' || $currentReportID == 'DR') {
                        
                        $AllDrReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonDrValidate($AllDrReport);
                    }
                    else if ($currentReportID == 'PR_P1' || $currentReportID == 'PR') {
                        $AllPrReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonPrValidate($AllPrReport);
                    }
                    else if ($currentReportID == 'IR_A1' || $currentReportID == 'IR_M1' || $currentReportID == 'IR') {
                        $AllIrReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonIrValidate($AllIrReport);
                    }
                    else {
                        
                        $AllBodyReport = $json['Report_Items'];
                        $bodyResult = CommonController::jsonBodyValidate($AllBodyReport);
                    }
                    
                    $user = Session::get('user');
                    
                    $dataIncludeHeader =  $bodyResult['data_error'];
                    
                    if($dataIncludeHeader == 0){
                        
                        $destinationPath=public_path()."/upload/json/";
                        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
                        
                      
                        File::put($destinationPath.$file,$data);
                        //response()->download($destinationPath.$file);
                        $completePath = $destinationPath.$file;
                        
                        $DataForInsertion = array(
                            'user_email'=>$user['email']??'',
                            'session_id'=>$user['display_name']??'',
                            'report_id'=>$allRequstVarible['ReportName']??'',
                            'report_format'=>'json',
                            'sushi_url'=>str_replace("_","/", $allRequstVarible['Requestorurl']),
                            'request_name'=>'getreportrequest',
                            'platform'=>'getreport',
                            'success'=>'Y',
                            'number_of_errors'=>0
                        );
                        
                        Sushitransaction::create($DataForInsertion);
                        
                        $headers = ['Content-Type: application/json'];
                        $newName = $file;
                        return response()->download($completePath, $file, $headers);
                        
                    } else {
                    
                    $destinationPath=public_path()."/upload/json/";
                    if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
                    
                    File::put($destinationPath.$file,$data);
                    
                    $completePath = $destinationPath.$file;
                   
                    $DataForInsertion = array(
                        'user_email'=>$user['email']??'',
                        'session_id'=>$user['display_name']??'',
                        'report_id'=>$allRequstVarible['ReportName']??'',
                        'report_format'=>'json',
                        'sushi_url'=>str_replace("_","/", $allRequstVarible['Requestorurl']),
                        'request_name'=>'getreportrequest',
                        'platform'=>'fail',
                        'success'=>'N',
                        'number_of_errors'=>$dataIncludeHeader
                    );
                    
                    Sushitransaction::create($DataForInsertion);
                    
                    $headers = ['Content-Type: application/json'];
                    $newName = $file;
                    return response()->download($completePath, $file, $headers);
                    
                }    
            } 
        
}
        
    }
}


