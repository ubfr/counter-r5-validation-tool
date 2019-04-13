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
                    // ignore expected exceptio
                }
            }
            
            try {
                $reportfile = Reportfile::store($report, $file, $file->getClientOriginalName(), Storedfile::SOURCE_FILE_VALIDATE,
                    $checkResult, $user['id']);
            } catch (\Exception $e) {
                report($e);
                Session::flash('reportmsg', 'Error while storing validation result: ' . $e->getMessage());
                return Redirect::to('filelist');
            }
            
            $data = [
                'reportfile' => $reportfile,
                'checkResult' => $checkResult,
                'userDisplayName' => $user['display_name'],
                'utype' => $user['utype']
            ];
            return view('validate_report_ubfr', $data);
        } else if(Session::has('emailMsg')) {
            Session::flash('emailMsg', Session::get('emailMsg'));
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
    public function sushiValiate(Request $request)
    {
        // $allRequstVarible = $request->all();
        $allRequstVarible = Input::all();
        
      // echo "<pre>";print_r($allRequstVarible);die;
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
            if ($allRequstVarible['requestButton'] == 'getmembers') {
               
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = str_replace("?",'',$mainURL);
                $mainURL = $mainURL . "/members/" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey'],
                    'requestor_id' => $allRequstVarible['RequestorId']
                );
                $fields = (array_filter($fields));
                $url = $mainURL . http_build_query($fields, '', "&");
            } else if ($allRequstVarible['requestButton'] == 'getverify') {
                
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = str_replace("?",'',$mainURL);
                $mainURL = $mainURL . "/members/" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey'],
                    'requestor_id' => $allRequstVarible['RequestorId']
                );
                $fields = (array_filter($fields));
                $url = $mainURL . http_build_query($fields, '', "&");
                
               // echo "<pre>";print_r($url);die;
            }
            
            else if($allRequstVarible['requestButton'] == 'getstatus'){
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = str_replace("?",'',$mainURL);
                $mainURL = $mainURL . "/status" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey'],
                    'requestor_id' => $allRequstVarible['RequestorId']
                );
                $fields = (array_filter($fields));
                $url = $mainURL . http_build_query($fields, '', "&");
            }
            
            else if($allRequstVarible['requestButton'] == 'getsupportedreports'){
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = str_replace("?",'',$mainURL);
                $mainURL = $mainURL . "/reports" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey'],
                    'requestor_id' => $allRequstVarible['RequestorId']
                );
                $fields = (array_filter($fields));
                $url = $mainURL . http_build_query($fields, '', "&");
            }
            
            else if($allRequstVarible['requestButton'] == 'getall'){
                $mainURL = $allRequstVarible['Requestorurl'];
                $mainURL = str_replace("?",'',$mainURL);
                $mainURL = $mainURL . "/reports" . "?";
                $fields = array(
                    'customer_id' => $allRequstVarible['CustomerId'],
                    'apikey' => $allRequstVarible['APIkey'],
                    'requestor_id' => $allRequstVarible['RequestorId']
                );
                
                $fields = (array_filter($fields));
                $url = $mainURL . http_build_query($fields, '', "&");
                
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
            
            $file = time() . '_file.json';
            $curl = curl_init($url);
            
            curl_setopt($curl, CURLOPT_NOBODY, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
            
            $result = curl_exec($curl);
            
            if ($result !== false) {
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($statusCode == 404) {
                    $result = array("SUSHI URL does not exist");
                } else {

                    $destinationPath = public_path() . "/upload/json/";
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    File::put($destinationPath . $file, $result);
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
                    
                    DB::beginTransaction();
                    try {
                    Sushitransaction::create($DataForInsertion);
                    DB::commit();
                    } catch(Exception $exception) {
                        DB::rollback();
                    }

                    $headers = ['Content-Type: application/json'];
                    $newName = $file;
                    
                    //echo "<pre>";print_r($newName);die;
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
                curl_setopt($curl, CURLOPT_NOBODY, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
                $ErrorFlag = 0;
                $result = curl_exec($curl);
                
                if(curl_errno($curl)){
                    $ErrorMessage= curl_error($curl);
                    // echo "<pre>Message";print_r($ErrorMessage);die;
                    $ErrorFlag=1;
                }
                if ($result !== false || $ErrorFlag==1) {
                //if ($result !== false) {
                    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    if ($statusCode == 404) {
                        $result = array("SUSHI URL does not exist");
                    } else {
                        //$data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);

                        $destinationPath = public_path() . "/upload/json/";
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        
                        File::put($destinationPath . $file, $result);
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
                        
                      // echo "<pre>";print_r($DataForInsertion);die;
                        
                    DB::beginTransaction();
                    try {
                        Sushitransaction::create($DataForInsertion);
                        DB::commit();
                    } catch (Exception $exception) {
                        DB::rollback();
                    }
                        $headers = ['Content-Type: application/json'];
                        $newName = $file;
                        if($DataForInsertion['request_name'] == 'getverify'){
                            
                            $json = json_decode(($result), true);
                            // echo "<pre>";print_r($json);die;
                            
                            if(isset($json[0]['Data'])){
                                ?>
                                <script>
                                alert("The service being called requires a valid APIKey to access usage data and the key provided was not valid or not authorized for the data being requested.");
                                history.back();
                                </script>
                                <?php 
                                return ;
                                
                            } else {
                                
                                ?>
                                <script>
                                alert("Verified Credentials Successfully.");
                                history.back();
                                </script>
                                <?php
                                return ;
                                
                            }
                        } else {
                            
                        return response()->download($completePath, $file, $headers);
                        
                        }
                    }
                } else {
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
    public function sushiRequestParameter($Requestorurl='',$apikey='',$CustomerId='',$platform='',$RequestorIdInner='') {
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
        $data['RequestorIdInner'] = $RequestorIdInner;
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
        $mainURL = str_replace('~','/',$allRequstVarible['Requestorurl']);
        $ReportName = $allRequstVarible['ReportName'];
        /*if(substr($mainURL, -1)=='/')
            $mainURL    =    $mainURL."reports/".strtolower($ReportName)."/?";
        else*/
        $mainURL    =    $mainURL."/reports/".strtolower($ReportName)."/?"; 
        
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
            'apikey' => $allRequstVarible['APIkey']===0?'':$allRequstVarible['APIkey'],
            'customer_id' => $allRequstVarible['CustomerId']===0?'':$allRequstVarible['CustomerId'],
            'requestor_id' => $allRequstVarible['RequestorId']===0?'':$allRequstVarible['RequestorId'],
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
        //echo "<pre>";print_r($url);die;
        try{
        $file = time() . '_file.json';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
        $result = curl_exec($curl);
        // echo "<pre>";print_r($result);die;
        $ErrorFlag = 0;
        $ErrorMessage = '';
        if(curl_errno($curl)){
            $ErrorMessage= curl_error($curl);
            Session::flash('reportmsg', $ErrorMessage);
            return Redirect::intended('/filelist/');
        }
        if ($result !== false || $ErrorFlag==1) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 404) {
                $result = array("SUSHI URL does not exist");
                Session::flash('error', 'This URL is not compatible for selected report'.$ErrorMessage);
                return Redirect::intended('/filelist/');
            } else {
                
                $json = json_decode($result, true);

                
                $jsonReportHeader = $json['Report_Header']??array();
                // echo "<pre>";print_r($errorMes);die;
                //checking error in header
                if(isset($json['Report_Header'])){
                    
                  if(isset($json['Report_Header']['Exceptions'])){
                    if(count($json['Report_Header']['Exceptions'])>0){
                        $errorMessagage = $json['Report_Header']['Exceptions'][0]['Message']??''." and ". $json['Report_Header']['Exceptions'][0]['Data']??'';
                        Session::flash('reportmsg', $errorMessagage);
                        return Redirect::intended('/filelist/');
                    } 
                  }    
                  
                $currentReportID = $jsonReportHeader['Report_ID']??'';
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
                    //echo "<pre>";print_r($json);die;
                    $AllBodyReport = $json['Report_Items'];
                    $bodyResult = CommonController::jsonBodyValidate($AllBodyReport);
                }

                $user = Session::get('user');

                $dataIncludeHeader = $bodyResult['data_error'];

                if ($dataIncludeHeader == 0) {

                    $destinationPath = public_path() . "/upload/json/";
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    File::put($destinationPath . $file, $result);
                    //response()->download($destinationPath.$file);
                    $completePath = $destinationPath . $file;

                    $DataForInsertion = array(
                        'user_email' => $user['email'] ?? '',
                        'session_id' => $user['display_name'] ?? '',
                        'report_id' => $allRequstVarible['ReportName'] ?? '',
                        'report_format' => 'json',
                        'sushi_url' => str_replace("_", "/", $allRequstVarible['Requestorurl']),
                        'request_name' => 'getreportrequest',
                        'platform' => 'getreport',
                        'success' => 'Y',
                        'number_of_errors' => 0
                    );

                    DB::beginTransaction();
                    try {
                        Sushitransaction::create($DataForInsertion);
                        DB::commit();
                    } catch (Exception $exception) {
                        DB::rollback();
                    }
                        
                        $headers = ['Content-Type: application/json'];
                        $newName = $file;
                        return response()->download($completePath, $file, $headers);
                        
                    } else {
                    
                    $destinationPath=public_path()."/upload/json/";
                    if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
                    
                    File::put($destinationPath.$file,$result);
                    
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
                    DB::beginTransaction();
                    try {
                        Sushitransaction::create($DataForInsertion);
                        DB::commit();
                    } catch (Exception $exception) {
                        DB::rollback();
                    }
                    
                    $headers = ['Content-Type: application/json'];
                    $newName = $file;
                    return response()->download($completePath, $file, $headers);
                    
                }    
                
                } else if(isset($json[0])) {
                    
                    $errorMessagagee= $json[0]['Message']??'';
                    if(! empty($errorMessagagee)){
                        Session::flash('reportmsg', $errorMessagagee);
                        return Redirect::intended('/filelist/');
                    } else {
                        Session::flash('reportmsg', 'Requestor Not Authorized to Access Service');
                        return Redirect::intended('/filelist/');
                    }
                    
                } else {
                    
                    $errorMes= $json['Message']??'';
                    if(empty($errorMes)){
                        Session::flash('reportmsg', 'URL has been moved');
                        return Redirect::intended('/filelist/');
                    } else {
                        Session::flash('reportmsg', $errorMes);
                        return Redirect::intended('/filelist/');
                    }
                }
            } 
        
        }
        }catch(Exception $exception){
            report($exception);
            return parent::render($request, $exception);
        }
        
    }
}


