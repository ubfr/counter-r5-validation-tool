<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Validatereportjr1Controller;
use App\Http\Controllers\Validatereportjr1goaController;
use App\Http\Controllers\Validatereportjr2Controller;
use App\Http\Requests;
use App\Validateerror;
use App\Filename;
Use Session;
Use Excel;
Use Mail;
use DateTime;
use App\Http\Manager\SubscriptionManager;
use Validator;
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
use File;

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
                            $test = new Validatereportjr1Controller();
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
                // / /////////////////////////save file in public/uploadfile folder//////////////
                
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
            // 'Email' => 'required|email',
            'CustomerId' => 'required',
            'APIkey' => 'required',
            'ReportName' => 'required|not_in:0',
            'Release' => 'required',
            'month' => 'required',
            'endmonth' => 'required'
        );
        $validator = Validator::make($allRequstVarible, $rules);
        if ($validator->fails()) {
            // If validation falis redirect back to login.
            return Redirect::back()->withInput(array())->withErrors($validator, 'welcome');
        } else {
            
            //make URL for call
            
           // echo "<pre>1234";print_r($allRequstVarible);die;
            $result = "";
            $mainURL = $allRequstVarible['Requestorurl'];
            $ReportName = $allRequstVarible['ReportName'];
            $mainURL    =    $mainURL."/".$ReportName."?";
            
            $fields = array('customer_id' => $allRequstVarible['CustomerId'],
                'begin_date' => $allRequstVarible['month'],
                'end_date' => $allRequstVarible['endmonth']
                );

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
                    $result = array("Shushi URL doest not exist");
                } else {
                    //$data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);
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

                    $destinationPath=public_path()."/upload/json/";
                    if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
                    //die("asdfdf");
                    
                    File::put($destinationPath.$file,$data);
                    response()->download($destinationPath.$file);
                }
            } else {
                $result = array("Shushi URL doest not exist");
            }


            //echo "<pre>####";print_r($url);die;
            
            
            //$json = file_get_contents($url);
                
            //$result = $json;

            
            
            
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
}
