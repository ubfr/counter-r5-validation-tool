<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ValidatereportController;
use App\Http\Requests;
use App\Validateerror;
use App\Filename;
Use Illuminate\Support\Facades\Session;
Use Excel;
use Illuminate\Support\Facades\Mail;
use DateTime;
use App\Http\Manager\SubscriptionManager;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use Illuminate\Support\Facades\Storage;
use App\Reportname;
use App\Filtertype;
use Exception;
use Illuminate\Support\Facades\Response;
use App\Reportfile;
use App\Storedfile;

// use App\Http\Controllers\CommonController\emailfile;
class CommonController extends Controller
{

    static $valid = true;

    public function __construct()
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('login');
        }
    }

    // //////////////////file upload////////////////////
    public function fileupload($file)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        ini_set('max_memory', '2048M');
        $user = Session::get('user');
        
        try {
            // echo app_path();die;
            $destinationPath = app_path() . '/public/uploadfile/';
            $filename = $user['id'] . '_' . date('m-d-Y_hisa') . '_' . $file->getClientOriginalName();
            $uploadSuccess = $file->move($destinationPath, $filename);
            return $filename;
        } catch (Exception $exception) {
            report($exception);
            
            return parent::render($request, $exception);
        }
    }

    // ////// function for check code and id only /////////
    public function jsonHeaderCodeIDValidate($reportname, $reportid, $jsonReportHeader)
    {
        
        // echo "<pre>ddd";print_r($jsonReportHeader);die;
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        
        if (isset($jsonReportHeader)) {
            
            if (null == $jsonReportHeader) {
                $warning[$b]["data"] = '';
                $warning[$b]["error"] = "Report Header Missing";
                $b ++;
                $data_warning ++;
                return $warning;
            }
            
            $reportdataa = new Reportname();
            $ReportName = $reportdataa::select('id')->where('report_name', trim($reportname))->first();
            
            if (! isset($ReportName->id)) {
                
                $warning[$b]["data"] = $reportname ?? '';
                if ($reportname == 'Structure error in JSON file')
                    $warning[$b]["error"] = "Structure error of JSON File";
                else
                    $warning[$b]["error"] = "Report_Name is Invalid in Master Report Header";
                $b ++;
                $data_warning ++;
                return $warning;
            }
            
            // Report Id validation
            $reportdata = new Reportname();
            $ReportId = $reportdata::select('id')->where('report_code', trim($reportid))->first();
            
            if (! isset($ReportId->id)) {
                
                $warning[$b]["data"] = $reportid ?? '';
                $warning[$b]["error"] = "Report_ID is Invalid in Master Report Header";
                $b ++;
                $data_warning ++;
                return $warning;
            }
            
            // if report_id and report_name mismatch
            $reportdata = new Reportname();
            $ReportId = $reportdata::select('id')->where(array(
                'report_code' => trim($reportid),
                'report_name' => trim($reportname)
            ))->first();
            
            if (! isset($ReportId->id)) {
                
                $warning[$b]["data"] = $reportid . ' & ' . $reportname ?? '';
                $warning[$b]["error"] = "Report_ID and Report_Name mismatched in Report Header";
                $b ++;
                $data_warning ++;
                return $warning;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "'Report_Header' is Invalid in the Report";
            $b ++;
            $data_warning ++;
            return $warning;
        }
    }

    // ////////////////////json file Header Check/////////////////////////
    public function jsonHeaderValidate($jsonReportHeader)
    {
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        
        // //////////////Header Section Validation///////////////////
        // /// Report Name validation
        
        $HeaderKeys = array_keys($jsonReportHeader);
        // echo "<pre>";print_r($HeaderKeys);die();
        
        $ReportHeaderArray = array(
            'Created',
            'Created_By',
            'Customer_ID',
            'Report_ID',
            'Release',
            'Report_Name',
            'Institution_Name',
            'Institution_ID',
            'Report_Filters',
            'Report_Attributes',
            'Exceptions'
        );
        
        $result = array_diff($HeaderKeys, $ReportHeaderArray);
        $final = array_values($result);
        
        // echo "<pre>";print_r($final);die();
        
        foreach ($final as $ki => $arrayHead) {
            // $arrayHead = trim($arrayHead[0]);
            $warning[$b]["data"] = $arrayHead ?? '';
            $warning[$b]["error"] = '"' . $arrayHead . '"' . " shouldn't be there in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        if (isset($jsonReportHeader['Report_Name'])) {
            
            $reportNameFromJson = str_replace('"', '', ($jsonReportHeader['Report_Name']));
            $reportdataa = new Reportname();
            $ReportName = $reportdataa::select('id')->where('report_name', trim($reportNameFromJson))->first();
            if (! isset($ReportName->id)) {
                $warning[$b]["data"] = $reportNameFromJson ?? '';
                $warning[$b]["error"] = "Report_Name is Invalid in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Report_Name is Invalid in report header";
            $b ++;
            $data_warning ++;
        }
        // Report Id validation
        if (isset($jsonReportHeader['Report_ID'])) {
            $getjsonreportCode = $jsonReportHeader['Report_ID'];
            
            $reportdata = new Reportname();
            $ReportId = $reportdata::select('id')->where('report_code', trim($getjsonreportCode))->first();
            // echo "<pre>ddd";print_r($ReportId);die();
            if (! isset($ReportId->id)) {
                $warning[$b]["data"] = $getjsonreportCode ?? '';
                $warning[$b]["error"] = "Report_ID is Invalid in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Report_ID is Invalid in report header";
            $b ++;
            $data_warning ++;
        }
        
        // Customer Id Validation
        if (isset($jsonReportHeader['Customer_ID'])) {
            $CustomerId = trim($jsonReportHeader['Customer_ID'] ?? '');
            // $CustomerId = (string)$CustomerId;
            
            if ((empty($CustomerId)) || (trim($CustomerId) && strpos($CustomerId, ' '))) {
                $warning[$b]["data"] = $CustomerId;
                $warning[$b]["error"] = "Customer_ID is Invalid in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Customer_ID is Invalid in report header";
            $b ++;
            $data_warning ++;
        }
        
        // Created Validation
        if (isset($jsonReportHeader['Created'])) {
            $Created = $jsonReportHeader['Created'] ?? '';
            
            // echo "<pre>";print_r($Created);die;
            
            if ((! $this->checkUTCDateFormat($Created)) || empty($Created)) {
                $warning[$b]["data"] = $Created;
                $warning[$b]["error"] = "Created is Invalid in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Created is Invalid in report header";
            $b ++;
            $data_warning ++;
        }
        
        // Created By Validation
        if (isset($jsonReportHeader['Created_By'])) {
            $CreatedBy = $jsonReportHeader['Created_By'] ?? '';
            if (empty($CreatedBy)) {
                $warning[$b]["data"] = $CreatedBy;
                $warning[$b]["error"] = "Created_By shouldn't be blank in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Created_By is Invalid in report header";
            $b ++;
            $data_warning ++;
        }
        
        // Release no Validation
        if (isset($jsonReportHeader['Release'])) {
            $ReleaseNo = $jsonReportHeader['Release'] ?? '';
            $ReleaseNo = (string) $ReleaseNo;
            
            if (! ($ReleaseNo === '5') || empty($ReleaseNo)) {
                $warning[$b]["data"] = $ReleaseNo;
                $warning[$b]["error"] = "Release Number is Invalid in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Release Number is Invalid in report header";
            $b ++;
            $data_warning ++;
        }
        
        // Institution Name Validation
        if (isset($jsonReportHeader['Institution_Name'])) {
            $InstitutionName = $jsonReportHeader['Institution_Name'] ?? '';
            // echo "<pre>";print_r($InstitutionName);die;
            if (empty($InstitutionName)) {
                $warning[$b]["data"] = $InstitutionName;
                $warning[$b]["error"] = "Institution_Name is Invalid in Report Header";
                $b ++;
                $data_warning ++;
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "Institution_Name is Missing in report header";
            $b ++;
            $data_warning ++;
        }
        
        // Institution_ID / ISNI Value Validation
        if (isset($jsonReportHeader['Institution_ID'])) {
            $Value = $jsonReportHeader['Institution_ID'][0]['Value'] ?? '';
            
            if (empty($Value)) {
                
                $error[$a]["data"] = $Value;
                $error[$a]["error"] = "Institution_ID is Invalid in report header";
                $b ++;
                $data_warning ++;
            }
        } 
        
        // Report_Filters Validation
        if (isset($jsonReportHeader['Report_Filters'])) {
            
            $Filters = $jsonReportHeader['Report_Filters'];
            
            $AllArrayOffilters = array();
            foreach ($Filters as $FiltersVal) {
                $AllArrayOffilters[] = $FiltersVal['Name'];
            }
            
            $ReportFilterArray = array(
                'Begin_Date',
                'End_Date',
                'Metric_Type',
                'Data_Type',
                'Access_Method',
                'Access_Type'
            );
            
            $Filtersresult = array_diff($AllArrayOffilters, $ReportFilterArray);
            $finalFilters = array_values($Filtersresult);
            
            
            foreach ($finalFilters as $ki => $AllfinalFilters) {
                // $arrayHead = trim($arrayHead[0]);
                $warning[$b]["data"] = $AllfinalFilters ?? '';
                $warning[$b]["error"] = '"' . $AllfinalFilters . '"' . " shouldn't be there in Report_Filters";
                $b ++;
                $data_warning ++;
            }
            
            // echo "<pre>";print_r($AllArrayOffilters);die;
            
            if (isset($jsonReportHeader['Report_Filters'][0])) {
                // Begin date Validation
                $Bname = $jsonReportHeader['Report_Filters'][0]['Name'];
                if ((empty($Bname)) || (! ($Bname === 'Begin_Date'))) {
                    $warning[$b]["data"] = $Bname;
                    $warning[$b]["error"] = "Name should be 'Begin_Date' in Report_Filters index[0] of report header";
                    $b ++;
                    $data_warning ++;
                }
                
                $Bvalue = $jsonReportHeader['Report_Filters'][0]['Value'];
                
                $BeginDatawithEndDAte = explode("-", $Bvalue??'');
                // echo "<pre>";print_r($BeginDatawithEndDAte);die;
               
                if(isset($BeginDatawithEndDAte[2])){
                    $BeginDAteValue = $BeginDatawithEndDAte[2]??'';
                    // $ts1 = strtotime($BeginDAteValue);
                    
                    $monthoftheday = substr($BeginDAteValue,-2);
                    if($monthoftheday!='01'){
                        $warning[$b]["data"] = $Bvalue;
                        $warning[$b]["error"] = "The date should start from the first day of the month in report header";
                        $b ++;
                        $data_warning ++;
                    }
                } 
                
                
                $begindatea = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $Bvalue);
                $begindateb = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $Bvalue);
                
                if ($begindatea || $begindateb) {} else if (empty($Bvalue)) {
                    
                    $warning[$b]["data"] = $Bvalue;
                    $warning[$b]["error"] = "Begin_Date is Invalid in report header";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $Bvalue;
                    $warning[$b]["error"] = "Begin_Date is Invalid in report header";
                    $b ++;
                    $data_warning ++;
                }
            }
            
            // End date Validation
            if (isset($jsonReportHeader['Report_Filters'][1])) {
                $Ename = $jsonReportHeader['Report_Filters'][1]['Name'];
                
                if(empty($Ename) || (! ($Ename === 'End_Date'))) {
                    $warning[$b]["data"] = $Ename;
                    $warning[$b]["error"] = "Name should be 'End_Date' in Report_Filters index[1] of report header";
                    $b ++;
                    $data_warning ++;
                }
                
                $Evalue = $jsonReportHeader['Report_Filters'][1]['Value'];
                
                $EndDAte = explode("-",$Evalue??'');
                
                
                if(isset($EndDAte[1])){
                    
                $EndDateCheckForMonth = $EndDAte[1];
                
                $EndDateArrayOdd = array(
                    '01',
                    '03',
                    '05',
                    '07',
                    '08',
                    '10',
                    '12'
                    );
                
                $EndDateArrayEven = array(
                    '04',
                    '06',
                    '09',
                    '11',
                );
                
                $EndDateArrayFeb = array(
                    '02'
                );
                
                if(in_array($EndDateCheckForMonth, $EndDateArrayOdd)){
                    if(isset($EndDAte[2])){
                        $EndDAteValue = $EndDAte[2];
                        $monthoftheday = substr($EndDAteValue,-2);
                        if(! ($monthoftheday === '31')){
                            $warning[$b]["data"] = $Evalue;
                            $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                            $b ++;
                            $data_warning ++;
                        }
                    } 
                } else if(in_array($EndDateCheckForMonth, $EndDateArrayEven)) {
                    
                    if(isset($EndDAte[2])){
                        $EndDAteValue = $EndDAte[2];
                        $monthoftheday = substr($EndDAteValue,-2);
                        if(! ($monthoftheday === '30')){
                            $warning[$b]["data"] = $Evalue;
                            $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                            $b ++;
                            $data_warning ++;
                        }
                    } 
                } else if(in_array($EndDateCheckForMonth, $EndDateArrayFeb)) {
                    if(isset($EndDAte[2])){
                        $EndDAteValue = $EndDAte[2];
                        $monthoftheday = substr($EndDAteValue,-2);
                        if(! ($monthoftheday === '28')){
                            $warning[$b]["data"] = $Evalue;
                            $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                            $b ++;
                            $data_warning ++;
                        }
                    }
                } 
                
                }else{
                   
                    $warning[$b]["data"] = $Ename;
                    $warning[$b]["error"] = "Name should be 'End_Date' in Report_Filters index[1] of report header";;
                    $b ++;
                    $data_warning ++;
                    
                    
                    
                }
                 
                if(isset($EndDAte[2])){
                    $EndDAteValue = $EndDAte[2]??'';
                    
                    $monthoftheday = substr($EndDAteValue,-2);
                    if(! ($monthoftheday === '28' || $monthoftheday=== '29' || $monthoftheday === '30' || $monthoftheday === '31')){
                        $warning[$b]["data"] = $Evalue;
                        $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                        $b ++;
                        $data_warning ++;
                    }
                  } 
                
                $enddatea = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $Evalue);
                $enddateb = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $Evalue);
                
                if ($enddatea || $enddateb) {} else if (empty($Evalue)) {
                    
                    $warning[$b]["data"] = $Evalue;
                    $warning[$b]["error"] = "End_Date is Invalid in report header";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $Evalue;
                    $warning[$b]["error"] = "End_Date is Invalid in report header";
                    $b ++;
                    $data_warning ++;
                }
                
                // Begin & End date comparision
                if ($Bvalue >= $Evalue) {
                    $warning[$b]["data"] = $Bvalue;
                    $warning[$b]["error"] = "Begin_Date shouldn't be greater than End date in Report Header";
                    $b ++;
                    $data_warning ++;
                }
            }
            // Access_Method
            if (isset($jsonReportHeader['Report_Filters'][2])) {
                $Access_Methodname = $jsonReportHeader['Report_Filters'][2]['Name'];
                
                if (! ($Access_Methodname === 'Access_Method') || empty($Access_Methodname)) {
                    $warning[$b]["data"] = $Access_Methodname;
                    $warning[$b]["error"] = "Access_Method Name is Invalid in Report Header";
                    $b ++;
                    $data_warning ++;
                }
                
                $Access_Methodvalue = $jsonReportHeader['Report_Filters'][2]['Value'];
                $AccessMethodArray = array(
                    'Regular',
                    'TDM'
                );
                
                if(!in_array($Access_Methodvalue, $AccessMethodArray)){
                    $warning[$b]["data"] = $Access_Methodvalue;
                    $warning[$b]["error"] = "Access_Method Value is Invalid in Report Header";
                    $b ++;
                    $data_warning ++;
                }
            }
            // Access_Type
            if (isset($jsonReportHeader['Report_Filters'][3])) {
                
                $AccessTypename = $jsonReportHeader['Report_Filters'][3]['Name'];
                // echo "<pre>";print_r($publisherJson);die;
                if (! ($AccessTypename === 'Access_Type') || empty($AccessTypename)) {
                    $warning[$b]["data"] = $AccessTypename;
                    $warning[$b]["error"] = "Access_Type Name is Invalid in Report Header";
                    $b ++;
                    $data_warning ++;
                }
                
                $AccessTypevalue = $jsonReportHeader['Report_Filters'][3]['Value'];
                $AccessTypevalue1 = (explode("|", $AccessTypevalue));
                
                foreach ($AccessTypevalue1 as $NewAccessTypevalue1){
                    
                    $ALlAccessTypeArray = array(
                        'Controlled',
                        'OA_Gold',
                        'Other_Free_To_Read'
                    );
                    
                    if (! in_array($NewAccessTypevalue1, $ALlAccessTypeArray)) {
                        $warning[$b]["data"] = $NewAccessTypevalue1;
                        $warning[$b]["error"] = "Access_Type Value "."'$NewAccessTypevalue1'"." is Invalid in Report Header";
                        $b ++;
                        $data_warning ++;
                    }
                }
            }
            
            // Data_Type
            if (isset($jsonReportHeader['Report_Filters'][4])) {
                $Data_Typename = $jsonReportHeader['Report_Filters'][4]['Name'];
                
                if (! ($Data_Typename === 'Data_Type') || empty($Data_Typename)) {
                    $warning[$b]["data"] = $Data_Typename;
                    $warning[$b]["error"] = "Data_Type Name is Invalid in Report Header";
                    $b ++;
                    $data_warning ++;
                }
                
                $Data_Typevalue = $jsonReportHeader['Report_Filters'][4]['Value'];
                // echo "<pre>";print_r($Data_Typevalue);die;
                $DataTypeArray = array(
                    'Article',
                    'Book',
                    'Book Segment',
                    'Collection',
                    'Database',
                    'Dataset',
                    'Journal',
                    'Multimedia',
                    'Newspaper',
                    'Newsletter',
                    'Platform',
                    'Repository Item',
                    'Dissertation',
                    'Thesis',
                    'Other'
                );
                
                if(!in_array($Data_Typevalue, $DataTypeArray)){
                    $warning[$b]["data"] = $Data_Typevalue;
                    $warning[$b]["error"] = "Data_Type Value is Invalid in Report Header";
                    $b ++;
                    $data_warning ++;
                }
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "'Report_Filters' is not Available in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Report_Attributes validate
        if (isset($jsonReportHeader['Report_Attributes'])) {
            
            $ReportAt = $jsonReportHeader['Report_Attributes'];
            $reportAttributesMasters = array('Attributes_To_Show');
            foreach ($ReportAt as $ki => $ReportAttributes) {
                
                $ravalueName = $ReportAttributes['Name'];
                if(!in_array($ravalueName,$reportAttributesMasters)){
                    $warning[$b]["data"] = $ravalueName;
                    $warning[$b]["error"] = "Name of Report_Attributes is Invalid in Report Header";
                    $b ++;
                    $data_warning ++;
                }
                
                $errorFlageAcess =0;
                $ravalue = $ReportAttributes['Value'];
                if($ravalueName=='Attributes_To_Show'){
                    $ravalueExploded = explode("|", $ravalue);
                    
                    $testarray = array(
                        'YOP',
                        'Data_Type',
                        'Access_Type',
                        'Access_Method'
                    );
                
                    $stringOfMessage = array();
                    if (is_array($ravalueExploded)) {
                        foreach ($ravalueExploded as $key => $aravalue) {
                            if(!in_array($aravalue,$testarray)){
                                $errorFlageAcess =1;
                                $stringOfMessage[]=$aravalue;
                            }
                        }
                    } else {
                        if(!in_array($ravalueExploded,$testarray)){
                            $errorFlageAcess =1;
                            $stringOfMessage[]=$ravalueExploded;
                        }
                    }
                    

                    if($errorFlageAcess==1 && $ravalueName=='Attributes_To_Show' ){
                        $warning[$b]["data"] = implode(",",$stringOfMessage);
                        $warning[$b]["error"] = "Value of Report_Attributes index[".$ki."] is Invalid in Report Header";
                        $b ++;
                        $data_warning ++;
                    }
                }
                 
            }
        } else {
            $warning[$b]["data"] = '';
            $warning[$b]["error"] = "'Report_Attributes' is Invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        $data["warning"] = $warning ?? array();
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        return $data;
    }

    // ///////////////////-Body Part Pr Validation-///////////////////////
    public function jsonPrValidate($AllPrReport)
    {
        $reportdta = new Filtertype();
        $AllMatricArray = Filtertype::where(array())->orderBy('id', 'asc')
            ->get()
            ->toArray();
        $AllArrayOfMatrix = array();
        foreach ($AllMatricArray as $MatrixVal) {
            $AllArrayOfMatrix[] = strtolower($MatrixVal['name']);
        }
        
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        $jsonReportHeader = 0;
        
        foreach ($AllPrReport as $key => $dataValue) {
            $platformJson = $dataValue['Platform'] ?? '';
            
            $i = 0;
            if (empty($platformJson)) {
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $yopJson = $datavalue['YOP'] ?? '';
            // echo "<pre>";print_r($yopJson); die;
            if (empty($yopJson)) {
                $warning[$b]["data"] = $yopJson;
                $warning[$b]["error"] = "YOP is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $DataType = $dataValue['Data_Type'] ?? '';
            
            $DataTypeArray = array(
                'Article',
                'Book',
                'Book Segment',
                'Collection',
                'Database',
                'Dataset',
                'Journal',
                'Multimedia',
                'Newspaper',
                'Newsletter',
                'Platform',
                'Repository Item',
                'Dissertation',
                'Thesis',
                'Other'
            );
            
            // echo "<pre>";print_r($publisherJson);die;
            if (!in_array($DataType, $DataTypeArray)) {
                $warning[$b]["data"] = $DataType;
                $warning[$b]["error"] = "Data_Type is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            // Section_Type
            $Section_Type = $dataValue['Section_Type'] ?? '';
            // echo "<pre>";print_r($publisherJson);die;
            $Section_Typearray = array(
                'Article',
                'Book',
                'Chapter',
                'Section',
                'Other'
            );
            
            if (! in_array($Section_Type, $Section_Typearray)) {
                $warning[$b]["data"] = $Section_Type;
                $warning[$b]["error"] = "Section_Type is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $AccessType = $dataValue['Access_Type'] ?? '';
            // echo "<pre>";print_r($publisherJson);die;
            $AccessTypevalue1 = (explode("|", $AccessType));
            // echo "<pre>";print_r($AccessTypevalue);die;
            
            foreach ($AccessTypevalue1 as $NewAccessTypevalue1){
                
                $ALlAccessTypeArray = array(
                    'Controlled',
                    'OA_Gold',
                    'Other_Free_To_Read'
                );
                
                if (! in_array($NewAccessTypevalue1, $ALlAccessTypeArray)) {
                    $warning[$b]["data"] = $NewAccessTypevalue1;
                    $warning[$b]["error"] = "Access_Type Value "."'$NewAccessTypevalue1'"." is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
            }
            
            $AccessMethod = $dataValue['Access_Method'] ?? '';
            // echo "<pre>";print_r($publisherJson);die;
            $AccessMethodArray = array(
                'Regular',
                'TDM'
            );
            
            if (! in_array($AccessMethod, $AccessMethodArray)) {
                $warning[$b]["data"] = $AccessMethod;
                $warning[$b]["error"] = "Access_Method is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance'] ?? '';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                
                // begin_date
                $bdJson = $Performance['Period']['Begin_Date'] ?? '';
                
                $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $bdJson);
                $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson);
                
                if ($a || $b) {} else if (empty($bdJson)) {
                    
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date is Invalid in Report_Items Index[ " . $key . " ] Performance Index[ " . $k . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report_Items Index[ " . $key . " ] Performance Index[ " . $k . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // end_date
                $edJson = $Performance['Period']['End_Date'] ?? '';
                
                $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $edJson);
                $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson);
                
                if ($a || $b) {} else if (empty($edJson)) {
                    
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "End_date is Empty in Report_Items Index[ " . $key . " ] Performance Index[ " . $k . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_date in Report_Items Index[ " . $key . " ] Performance Index[ " . $k . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be greater than End_date in Report_Items Index[ " . $key . " ] Performance Index[ " . $k . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance'] ?? '';
                foreach ($InstanceArray as $ki => $installValue) {
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is Invalid in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] Instance index[ " . $ki . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                }
            }
            // performance closed
        }
        
        $data["error"] = $warning ?? array();
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        return $data;
    }

    // ///////////////////-Body Part Dr Validation-///////////////////////
    public function jsonDrValidate($AllDrReport)
    {
        
        
        // die('fjkgh');
        
        $reportdta = new Filtertype();
        $AllMatricArray = Filtertype::where(array())->orderBy('id', 'asc')
            ->get()
            ->toArray();
        $AllArrayOfMatrix = array();
        foreach ($AllMatricArray as $MatrixVal) {
            $AllArrayOfMatrix[] = strtolower($MatrixVal['name']);
        }
        
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        $jsonReportHeader = 0;
        
        foreach ($AllDrReport as $key => $dataValue) {
           
            $platformJson = $dataValue['Platform'] ?? '';
            if (empty($platformJson)) {
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Empty in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $publisherJson = $dataValue['Publisher'] ?? '';
            // echo "<pre>";print_r($dataValue); die;
            if (empty($publisherJson)) {
                $warning[$b]["data"] = $publisherJson;
                $warning[$b]["error"] = "Publisher is Empty in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $DataType = $dataValue['Data_Type'] ?? '';
            
            $DataTypeArray = array(
                'Article',
                'Book',
                'Book Segment',
                'Collection',
                'Database',
                'Dataset',
                'Journal',
                'Multimedia',
                'Newspaper',
                'Newsletter',
                'Platform',
                'Repository Item',
                'Dissertation',
                'Thesis',
                'Other'
            );
            
            // echo "<pre>";print_r($publisherJson);die;
            if (!in_array($DataType, $DataTypeArray)) {
                $warning[$b]["data"] = $DataType;
                $warning[$b]["error"] = "Data_Type is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            // Section_Type
            $Section_Type = $dataValue['Section_Type'] ?? '';
            // echo "<pre>";print_r($Section_Type);die;
            $Section_Typearray = array(
                'Article',
                'Book',
                'Chapter',
                'Section',
                'Other'
            );
            
            if (! in_array($Section_Type, $Section_Typearray)) {
                $warning[$b]["data"] = $Section_Type;
                $warning[$b]["error"] = "Section_Type is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $AccessType = $dataValue['Access_Type'] ?? '';
            // echo "<pre>";print_r($publisherJson);die;
            $AccessTypevalue1 = (explode("|", $AccessType));
            // echo "<pre>";print_r($AccessTypevalue);die;
            
            foreach ($AccessTypevalue1 as $NewAccessTypevalue1){
                
                $ALlAccessTypeArray = array(
                    'Controlled',
                    'OA_Gold',
                    'Other_Free_To_Read'
                );
                
                if (! in_array($NewAccessTypevalue1, $ALlAccessTypeArray)) {
                    $warning[$b]["data"] = $NewAccessTypevalue1;
                    $warning[$b]["error"] = "Access_Type Value "."'$NewAccessTypevalue1'"." is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
            }
            
            $AccessMethod = $dataValue['Access_Method'] ?? '';
            $AccessMethodArray = array(
                'Regular',
                'TDM'
            );
            
            if (! in_array($AccessMethod, $AccessMethodArray)) {
                $warning[$b]["data"] = $AccessMethod;
                $warning[$b]["error"] = "Access_Method is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
              /* $publisherId = $dataValue['Publisher_ID'][0]['Value']??'';
              // echo "<pre>";print_r($publisherId); die;
              if (empty($publisherId) || ! (is_numeric($publisherId))) {
              $warning[$b]["data"] = $publisherId;
              $warning[$b]["error"] = "Publisher_ID is Empty in Report_Items Index[ ".$key." ]";
              $b ++;
              $data_warning ++;
              } */
             
            // /// for yop /////
            $yopJson = $datavalue['YOP'] ?? '';
            // echo "<pre>";print_r($yopJson); die;
            if (empty($yopJson)) {
                $warning[$b]["data"] = $yopJson;
                $warning[$b]["error"] = "YOP is Empty in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance'] ?? '';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                
                // begin_date
                $bdJson = $Performance['Period']['Begin_Date'] ?? '';
                
                // echo"<pre>";print_r($bdJson);die;
                
                $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $bdJson);
                $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson);
                
                if ($a || $b) {} else if (empty($bdJson)) {
                    
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_Date is Empty in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // end_date
                $edJson = $Performance['Period']['End_Date'] ?? '';
                
                $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $edJson);
                $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson);
                
                if ($a || $b) {} else if (empty($edJson)) {
                    
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "End_Date is Empty in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_Date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // comparision
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be greater than End_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance'] ?? '';
                foreach ($InstanceArray as $ki => $installValue) {
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is Invalid in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] Instance index[ " . $ki . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                }
            }
            // performance closed
        }
        
        $data["error"] = $warning ?? array();
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        return $data;
    }

    // ///////////////////-Body Part IR Validation-///////////////////////
    public function jsonIrValidate($AllIrReport)
    {
        $reportdta = new Filtertype();
        $AllMatricArray = Filtertype::where(array())->orderBy('id', 'asc')
            ->get()
            ->toArray();
        $AllArrayOfMatrix = array();
        foreach ($AllMatricArray as $MatrixVal) {
            $AllArrayOfMatrix[] = strtolower($MatrixVal['name']);
        }
        
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        $jsonReportHeader = 0;
        
        foreach ($AllIrReport as $key => $dataValue) {
            
            $requiredreportitemsarray = array(
                'Title',
                'Item_ID',
                'Platform',
                'Publisher',
                'Publisher_ID',
                'Data_Type',
                'Section_Type',
                'Access_Type',
                'Access_Method',
                'Performance'
            );
            
            $Report_ItemsKeys = array_keys($dataValue);
            $Report_Itemsdiff = array_diff($Report_ItemsKeys, $requiredreportitemsarray);
            
            // echo "<pre>";print_r($Report_Itemsdiff);die;
            foreach($Report_Itemsdiff as $ki => $RItemKeys){
                $warning[$b]["data"] = $RItemKeys ?? '';
                $warning[$b]["error"] = '"' . $RItemKeys . '"' ."shouldn't be there Invalid in Report_Items";
                $b ++;
                $data_warning ++;
            }
            
            $itemFromJson = $dataValue['Item'] ?? '';
            if (empty($itemFromJson)) {
                $warning[$b]["data"] = $itemFromJson;
                $warning[$b]["error"] = "Item is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            // //////working for ITEM ID Values
            $itemDetailValues = array();
            $itemDetailValues = $dataValue['Item_ID'] ?? '';
            foreach ($itemDetailValues as $k => $ItemData) {
                // echo "<pre>";print_r($ItemData);die;
                
                if (! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $ItemData['Value'])) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "DOI value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                if (($ItemData['Type'] == 'Proprietary_ID') && (empty($ItemData['Value']) || ! (is_numeric($ItemData['Value'])))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "Proprietary_ID value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                if ($ItemData['Type'] == 'ISBN') {
                    
                    if (empty($ItemData['Value'])) {
                        $warning[$b]["data"] = $ItemData['Value'];
                        $warning[$b]["error"] = "ISBN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                        $b ++;
                        $data_error ++;
                    } else {
                        $checkisbn = $this->ValidateIsbn($ItemData['Value']);
                        if ($checkisbn == false) {
                            $warning[$b]["data"] = $ItemData['Value'];
                            $warning[$b]["error"] = "ISBN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                }
                
                if ($ItemData['Type'] == 'Print_ISSN' && (empty($ItemData['Value']))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "Print_ISSN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                if ($ItemData['Type'] == 'Online_ISSN') {
                    
                    if (empty($ItemData['Value'])) {
                        $warning[$b]["data"] = $ItemData['Value'];
                        $warning[$b]["error"] = "Online_ISSN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                        $b ++;
                        $data_error ++;
                    } else {
                        $check = $this->issn(strtoupper($ItemData['Value']));
                        if ($check == false) {
                            $warning[$b]["data"] = $ItemData['Value'];
                            $warning[$b]["error"] = "Online_ISSN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                }
                
                if ($ItemData['Type'] == 'URI' && empty($ItemData['Value'])) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "URI value is Empty in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                    $b ++;
                    $data_warning ++;
                }
            }
            
            $platformJson = $dataValue['Platform'] ?? '';
            if (empty($platformJson)) {
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $publisherJson = $dataValue['Publisher'] ?? '';
            if (empty($publisherJson)) {
                $warning[$b]["data"] = $publisherJson;
                $warning[$b]["error"] = "Publisher is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            /* $publisherId = $dataValue['Publisher_ID'][0]['Value']??'';
              if (empty($publisherId) || ! (is_numeric($publisherId))) {
              $warning[$b]["data"] = $publisherId;
              $warning[$b]["error"] = "Publisher_Id is Invalid in Report_Items Index[ ".$key." ]";
              $b ++;
              $data_warning ++;
              } */
             
            
            $DataType = $dataValue['Data_Type'] ?? '';
            
            $DataTypeArray = array(
                'Article',
                'Book',
                'Book Segment',
                'Collection',
                'Database',
                'Dataset',
                'Journal',
                'Multimedia',
                'Newspaper',
                'Newsletter',
                'Platform',
                'Repository Item',
                'Dissertation',
                'Thesis',
                'Other'
            );
            
            // echo "<pre>";print_r($publisherJson);die;
            if (!in_array($DataType, $DataTypeArray)) {
                $warning[$b]["data"] = $DataType;
                $warning[$b]["error"] = "Data_Type is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            // Section_Type
            $Section_Type = $dataValue['Section_Type'] ?? '';
            $Section_Typearray = array(
                'Article',
                'Book',
                'Chapter',
                'Section',
                'Other'
            );
            
            if (! in_array($Section_Type, $Section_Typearray)) {
                $warning[$b]["data"] = $Section_Type;
                $warning[$b]["error"] = "Section_Type is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            $AccessType = $dataValue['Access_Type'] ?? '';
            $AccessTypevalue1 = (explode("|", $AccessType));
            // echo "<pre>";print_r($AccessTypevalue);die;
            
            foreach ($AccessTypevalue1 as $NewAccessTypevalue1){
                
                $ALlAccessTypeArray = array(
                    'Controlled',
                    'OA_Gold',
                    'Other_Free_To_Read'
                );
                
                if (! in_array($NewAccessTypevalue1, $ALlAccessTypeArray)) {
                    $warning[$b]["data"] = $NewAccessTypevalue1;
                    $warning[$b]["error"] = "Access_Type Value "."'$NewAccessTypevalue1'"." is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
            }
            
            $AccessMethod = $dataValue['Access_Method'] ?? '';
            // echo "<pre>";print_r($publisherJson);die;
            $AccessMethodArray = array(
                'Regular',
                'TDM'
            );
            
            if (! in_array($AccessMethod, $AccessMethodArray)) {
                $warning[$b]["data"] = $AccessMethod;
                $warning[$b]["error"] = "Access_Method is Invalid in Report_Items Index[ " . $key . " ]";
                $b ++;
                $data_warning ++;
            }
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance'] ?? '';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                
                // begin_date
                $bdJson = $Performance['Period']['Begin_Date'] ?? '';
                
                $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $bdJson);
                $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson);
                
                if ($a || $b) {} else if (empty($bdJson)) {
                    
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date is Empty in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // end_date
                $edJson = $Performance['Period']['End_Date'] ?? '';
                
                $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $edJson);
                $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson);
                
                if ($a || $b) {} else if (empty($edJson)) {
                    
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "End_date is Empty in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                } else {
                    
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be greater than End_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance'] ?? '';
                foreach ($InstanceArray as $ki => $installValue) {
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is Invalid in Instance index[ " . $ki . " ] of performance index[ " . $ke . " ] of Report_Items Index[ " . $key . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                }
            }
            // performance closed
        }
        
        $data["error"] = $warning ?? array();
        // $data["error"]=$error ?? array();
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        return $data;
    }

    // ///////////////////-Body Part TR Validation-///////////////////////
    public function jsonBodyValidate($AllBodyReport)
    {
        $reportdta = new Filtertype();
        $AllMatricArray = Filtertype::where(array())->orderBy('id', 'asc')
            ->get()
            ->toArray();
        $AllArrayOfMatrix = array();
        foreach ($AllMatricArray as $MatrixVal) {
            $AllArrayOfMatrix[] = strtolower($MatrixVal['name']);
        }
        
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        $jsonReportHeader = 0;
        
        if (is_array($AllBodyReport) && count($AllBodyReport) > 0) {
            
            foreach ($AllBodyReport as $key => $dataValue) {
                
                $requiredreportitemsarray = array(
                    'Title',
                    'Item_ID',
                    'Platform',
                    'Publisher',
                    'Publisher_ID',
                    'Data_Type',
                    'Section_Type',
                    'Access_Type',
                    'Access_Method',
                    'Performance'
                );
                
                $Report_ItemsKeys = array_keys($dataValue);
                $Report_Itemsdiff = array_diff($Report_ItemsKeys, $requiredreportitemsarray);
                
                // echo "<pre>";print_r($Report_Itemsdiff);die;
                foreach($Report_Itemsdiff as $ki => $RItemKeys){
                        $warning[$b]["data"] = $RItemKeys ?? '';
                        $warning[$b]["error"] = '"' . $RItemKeys . '"' ."shouldn't be there Invalid in Report_Items";
                        $b ++;
                        $data_warning ++;
                }
                
                $titleFromJson = $dataValue['Title'] ?? '';
                if (empty($titleFromJson)) {
                    $warning[$b]["data"] = $titleFromJson;
                    $warning[$b]["error"] = "Title is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
                // //////working for ITEM ID Values
                $itemDetailValues = array();
                $itemDetailValues = $dataValue['Item_ID'] ?? array();
                
                // echo "<pre>";print_r($itemDetailValues);die;
                $RequiredtestArrays = array(
                    'DOI',
                    'Proprietary_Id',
                    'ISBN',
                    'Print_ISSN',
                    'Online_ISSN',
                    'URI'
                );
                
                foreach ($itemDetailValues as $k => $ItemData) {
                    
                    $Reportitemtype = $ItemData['Type'];
                    $ReportitemValue = $ItemData['Value'];
                    
                    // echo "<pre>";print_r($Reportitemtype);die;
                    
                    if(!in_array($Reportitemtype, $RequiredtestArrays)){
                        $warning[$b]["data"] = $Reportitemtype;
                        $warning[$b]["error"] = "Type is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                    
                    if($Reportitemtype === 'DOI') {
                        if (! preg_match('/\b(10[.][0-9]{4,}(?:[.][0-9]+)*\/(?:(?!["&\'<>])\S)+)\b/', $ReportitemValue)) {
                            $warning[$b]["data"] = $ReportitemValue;
                            $warning[$b]["error"] = "DOI value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                            $b ++;
                            $data_warning ++;
                        }
                    }
                    
                    /*
                     * if (($ItemData['Type'] == 'Proprietary_ID') && (empty($ItemData['Value']) || ! (is_numeric($ItemData['Value'])))) {
                     * $warning[$b]["data"] = $ItemData['Value'];
                     * $warning[$b]["error"] = "Proprietary_ID value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                     * $b ++;
                     * $data_warning ++;
                     * }
                     */
                    
                    if ($Reportitemtype === 'ISBN') {
                        $checkisbn = $this->ValidateIsbn($ReportitemValue);
                        if ($checkisbn == false) {
                            $warning[$b]["data"] = $ReportitemValue;
                            $warning[$b]["error"] = "ISBN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                    
                    if ($Reportitemtype === 'Print_ISSN' && (empty($ReportitemValue))) {
                        $warning[$b]["data"] = $ReportitemValue;
                        $warning[$b]["error"] = "Print_ISSN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                    
                    if ($Reportitemtype === 'Online_ISSN') {
                        
                        if (empty($ReportitemValue)) {
                            $warning[$b]["data"] = $ReportitemValue;
                            $warning[$b]["error"] = "Online_ISSN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                            $b ++;
                            $data_error ++;
                        } else {
                            $check = $this->issn(strtoupper($ReportitemValue));
                            if ($check == false) {
                                $warning[$b]["data"] = $ReportitemValue;
                                $warning[$b]["error"] = "Online_ISSN value is Invalid in Report_Items Index[ " . $key . " ] Item Id index[ " . $k . " ]";
                                $b ++;
                                $data_error ++;
                            }
                        }
                    }
                }
                
                $platformJson = $dataValue['Platform'] ?? '';
                if (empty($platformJson)) {
                    $warning[$b]["data"] = $platformJson;
                    $warning[$b]["error"] = "Platform is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
                $publisherJson = $dataValue['Publisher'] ?? '';
                // echo "<pre>";print_r($publisherJson);die;
                if (empty($publisherJson)) {
                    $warning[$b]["data"] = $publisherJson;
                    $warning[$b]["error"] = "Publisher is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
 
                /* $publisherId = $dataValue['Publisher_ID'][0]['Value']??'';
                if (empty($publisherId) || ! (is_numeric($publisherId))) {
                    $warning[$b]["data"] = $publisherId;
                    $warning[$b]["error"] = "Publisher_Id is Invalid in Report_Items Index[ ".$key." ]";
                    $b ++;
                    $data_warning ++;
                } */
                
                
                // Data_Type
                $DataType = $dataValue['Data_Type'] ?? '';
                $DataTypeArray = array(
                    
                    'Article',
                    'Book',
                    'Book Segment',
                    'Collection',
                    'Database',
                    'Dataset',
                    'Journal',
                    'Multimedia',
                    'Newspaper',
                    'Newsletter',
                    'Platform',
                    'Repository Item',
                    'Dissertation',
                    'Thesis',
                    'Other'
                );
                
                // echo "<pre>";print_r($publisherJson);die;
                if (!in_array($DataType, $DataTypeArray)) {
                    $warning[$b]["data"] = $DataType;
                    $warning[$b]["error"] = "Data_Type is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // Section_Type
                $Section_Type = $dataValue['Section_Type'] ?? '';
                 // echo "<pre>";print_r($Section_Type);die;
                 $Section_Typearray = array(
                     'Article',
                     'Book',
                     'Chapter',
                     'Section',
                     'Other'
                 );
                 
                 if (! in_array($Section_Type, $Section_Typearray)) {
                    $warning[$b]["data"] = $Section_Type;
                    $warning[$b]["error"] = "Section_Type is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // Access_Type
                $AccessType = $dataValue['Access_Type'] ?? '';
                $AccessTypevalue1 = (explode("|", $AccessType));
                // echo "<pre>";print_r($AccessTypevalue);die;
                
                foreach ($AccessTypevalue1 as $NewAccessTypevalue1){
                    
                    $ALlAccessTypeArray = array(
                        'Controlled',
                        'OA_Gold',
                        'Other_Free_To_Read'
                    );
                    
                    if (! in_array($NewAccessTypevalue1, $ALlAccessTypeArray)) {
                        $warning[$b]["data"] = $NewAccessTypevalue1;
                        $warning[$b]["error"] = "Access_Type Value "."'$NewAccessTypevalue1'"." is Invalid in Report_Items Index[ " . $key . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                }
                
                // Access_Method
                $AccessMethod = $dataValue['Access_Method'] ?? '';
                // echo "<pre>";print_r($AccessMethod);die;
                $AccessMethodArray = array(
                    'Regular',
                    'TDM'
                );
                
                if (! in_array($AccessMethod, $AccessMethodArray)) {
                    $warning[$b]["data"] = $AccessMethod;
                    $warning[$b]["error"] = "Access_Method is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                $yopJson = $dataValue['YOP'] ?? '';
                // echo "<pre>";print_r($yopJson);die;
                if (empty($yopJson)) {
                    $warning[$b]["data"] = $yopJson;
                    $warning[$b]["error"] = "YOP is Invalid in Report_Items Index[ " . $key . " ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // /////checking for performance
                $itemDetailPerformance = array();
                $itemDetailPerformance = $dataValue['Performance'] ?? '';
                // echo "<pre>";print_r($itemDetailPerformance);die;
               
                
                foreach ($itemDetailPerformance as $ke => $Performance) {
                  
                    // begin_date
                    $bdJson = $Performance['Period']['Begin_Date'] ?? '';
                    
                    $BeginDatawithEndDAte = explode("-", $bdJson??'');
                    
                    if(isset($BeginDatawithEndDAte[2])){
                        $BeginDAteValue = $BeginDatawithEndDAte[2]??'';
                        // $ts1 = strtotime($BeginDAteValue);
                        
                        $monthoftheday = substr($BeginDAteValue,-2);
                        if($monthoftheday!='01'){
                            $warning[$b]["data"] = $bdJson;
                            $warning[$b]["error"] = "The date should start from the first day of the month in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                            $b ++;
                            $data_warning ++;
                        }
                    } 
                    
                    $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $bdJson);
                    $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson);
                    
                    if ($a || $b) {
                        
                    } else if (empty($bdJson)) {
                        
                        $warning[$b]["data"] = $bdJson;
                        $warning[$b]["error"] = "Begin_date is Invalid in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                        $b ++;
                        $data_warning ++;
                        
                    } else {
                        
                        $warning[$b]["data"] = $bdJson;
                        $warning[$b]["error"] = "Invalid Begin_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                    
                    // end_date
                    $edJson = $Performance['Period']['End_Date'] ?? '';
                    
                    $EndDAte = explode("-",$edJson??'');
                    $EndDateCheckForMonth = $EndDAte[1];
                    // echo "<pre>";print_r($EndDateCheckForMonth);die;
                    $EndDateArrayOdd = array(
                        '01',
                        '03',
                        '05',
                        '07',
                        '08',
                        '10',
                        '12'
                    );
                    
                    $EndDateArrayEven = array(
                        '04',
                        '06',
                        '09',
                        '11',
                    );
                    
                    $EndDateArrayFeb = array(
                        '02'
                    );
                    
                    if(in_array($EndDateCheckForMonth, $EndDateArrayOdd)){
                        if(isset($EndDAte[2])){
                            $EndDAteValue = $EndDAte[2];
                            $monthoftheday = substr($EndDAteValue,-2);
                            if(! ($monthoftheday === '31')){
                                $warning[$b]["data"] = $edJson;
                                $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                                $b ++;
                                $data_warning ++;
                            }
                        }
                    } else if(in_array($EndDateCheckForMonth, $EndDateArrayEven)) {
                        
                        if(isset($EndDAte[2])){
                            $EndDAteValue = $EndDAte[2];
                            $monthoftheday = substr($EndDAteValue,-2);
                            if(! ($monthoftheday === '30')){
                                $warning[$b]["data"] = $edJson;
                                $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                                $b ++;
                                $data_warning ++;
                            }
                        }
                    } else if(in_array($EndDateCheckForMonth, $EndDateArrayFeb)) {
                        if(isset($EndDAte[2])){
                            $EndDAteValue = $EndDAte[2];
                            $monthoftheday = substr($EndDAteValue,-2);
                            if(! ($monthoftheday === '28')){
                                $warning[$b]["data"] = $edJson;
                                $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                                $b ++;
                                $data_warning ++;
                            }
                        }
                    }
                    
                    
                    if(isset($EndDAte[2])){
                        $EndDAteValue = $EndDAte[2]??'';
                        
                        $monthoftheday = substr($EndDAteValue,-2);
                        if(! ($monthoftheday === '28' || $monthoftheday=== '29' || $monthoftheday === '30' || $monthoftheday === '31')){
                            $warning[$b]["data"] = $Evalue;
                            $warning[$b]["error"] = "The End_Date should be the last day of the month in report header";
                            $b ++;
                            $data_warning ++;
                        }
                    }
                    
                    
                    $a = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $edJson);
                    $b = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson);
                    
                    if ($a || $b) {
                        
                    } else if (empty($edJson)) {
                        $warning[$b]["data"] = $edJson;
                        $warning[$b]["error"] = "End_date is Invalid in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                        $b ++;
                        $data_warning ++;
                    } else {
                        $warning[$b]["data"] = $edJson;
                        $warning[$b]["error"] = "Invalid End_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                    
                    if ($bdJson >= $edJson) {
                        // die($bdJson);
                        $warning[$b]["data"] = $bdJson;
                        $warning[$b]["error"] = "Begin_date shouldn't be greater than End_date in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] period index[ " . $ke . " ]";
                        $b ++;
                        $data_warning ++;
                    }
                    
                    // internal loop for instance
                    $InstanceArray = array();
                    $InstanceArray = $Performance['Instance'] ?? '';
                    
                    $Performanceinstancesarrays = array(
                        'Metric_Type',
                        'Count'
                    );
                   
                    foreach ($InstanceArray as $ki => $installValue) {
                    $PerformanceInstancekeys = array_keys($installValue);
                    
                    foreach ($PerformanceInstancekeys as $Performanceinstances ){
                        if(!in_array($Performanceinstances, $Performanceinstancesarrays)){
                            $warning[$b]["data"] = $Performanceinstances;
                            $warning[$b]["error"] = "'$Performanceinstances' shouldn't be there in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] Instance index[ " . $ki . " ]";
                                $b ++;
                                $data_warning ++;
                            }
                        }
                        
                        $MatricValue = trim(strtolower($installValue['Metric_Type']));
                        
                        if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                            $warning[$b]["data"] = $MatricValue ?? '';
                            $warning[$b]["error"] = "Metric Type is invalid in Report_Items Index[ " . $key . " ] performance index[ " . $ke . " ] Instance index[ " . $ki . " ]";
                            $b ++;
                            $data_warning ++;
                        }
                    }
                } // performance closed
            }
        }
        $data["error"] = $warning ?? array();
        // $data["error"]=$error ?? array();
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        // echo "<pre>";print_r($data);die;
        return $data;
    }

    // //////////////////////////////////////////////////////////////////////
    // ////////////////////function for download file//////////////////////////
    
    function downloadfile_ubfr($storedfileId)
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
        if(! Storage::exists($storedfile->location)) {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(404, 'Validation result not found.');
        }

        return Storage::download($storedfile->location, $storedfile->filename,
            [ 'Content-Type: ' . $storedfile->getMimeType() ]);
    }
    
    function downloadfile($storedfileId)
    {
        return $this->downloadfile_ubfr($storedfileId);
        
        $user = Session::get('user');
        // Execute the query used to retrieve the data. In this example
        // we're joining hypothetical users and payments tables, retrieving
        // the payments table's primary key, the user's first and last name,
        // the user's e-mail address, the amount paid, and the payment
        // timestamp.
        $payments = Filename::join('validateerrors', 'validateerrors.file_id', '=', 'filenames.id')->select('validateerrors.type', 'validateerrors.error_data', 'validateerrors.error_remark', 'validateerrors.entry_time')
            ->where('validateerrors.file_id', '=', $file_user_id)
            ->get();
        
        // Initialize the array which will be passed into the Excel
        // generator.
        $paymentsArray = [];
        
        // Define the Excel spreadsheet headers
        $paymentsArray[] = [
            'Error type',
            'Error data',
            'Error Remark',
            'Validation Date & Time'
        ];
        
        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($payments as $payment) {
            $paymentsArray[] = $payment->toArray();
        }
        
        try {
            // Generate and return the spreadsheet
            Excel::create($filename, function ($excel) use ($paymentsArray) {
                
                // Set the spreadsheet title, creator, and description
                $excel->setTitle('Error Report');
                $excel->setCreator('Laravel')->setCompany('Counter Project');
                $excel->setDescription('Error Report file');
                
                // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function ($sheet) use ($paymentsArray) {
                    $sheet->fromArray($paymentsArray, null, 'A1', false, false);
                });
            })->download('xlsx');
        } catch (Exception $exception) {
            report($exception);
            
            return parent::render($request, $exception);
        }
    }

    function downloadfileFront($file_user_id, $filename)
    {
        $user = Session::get('user');
        // Execute the query used to retrieve the data. In this example
        // we're joining hypothetical users and payments tables, retrieving
        // the payments table's primary key, the user's first and last name,
        // the user's e-mail address, the amount paid, and the payment
        // timestamp.
        $payments = Filename::join('validateerrors', 'validateerrors.file_id', '=', 'filenames.id')->select('validateerrors.type', 'validateerrors.error_data', 'validateerrors.error_remark', 'validateerrors.entry_time')
            ->where('validateerrors.file_id', '=', $file_user_id)
            ->get();
        
        // Initialize the array which will be passed into the Excel
        // generator.
        $paymentsArray = [];
        
        // Define the Excel spreadsheet headers
        $paymentsArray[] = [
            'Error type',
            'Error data',
            'Error Remark',
            'Validation Date & Time'
        ];
        
        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($payments as $payment) {
            $paymentsArray[] = $payment->toArray();
        }
        
        try {
            // Generate and return the spreadsheet
            Excel::create($filename, function ($excel) use ($paymentsArray) {
                
                // Set the spreadsheet title, creator, and description
                $excel->setTitle('Error Report');
                $excel->setCreator('Laravel')->setCompany('Counter Project');
                $excel->setDescription('Error Report file');
                
                // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function ($sheet) use ($paymentsArray) {
                    $sheet->fromArray($paymentsArray, null, 'A1', false, false);
                });
            })->download('xlsx');
        } catch (Exception $exception) {
            report($exception);
            
            return parent::render($request, $exception);
        }
    }

    // 01/03/2019
    function downloadfileFrontforid($file_user_id)
    {
         // die('herererereer');
        $user = Session::get('user');
        // Execute the query used to retrieve the data. In this example
        // we're joining hypothetical users and payments tables, retrieving
        // the payments table's primary key, the user's first and last name,
        // the user's e-mail address, the amount paid, and the payment
        // timestamp.
        $payments = Filename::join('validateerrors', 'validateerrors.file_id', '=', 'filenames.id')->select('validateerrors.type', 'validateerrors.error_data', 'validateerrors.error_remark', 'validateerrors.entry_time')
            ->where('validateerrors.file_id', '=', $file_user_id)
            ->get();
        
        
       //  echo "<pre>";print_r($payments);die;
        // Initialize the array which will be passed into the Excel
        // generator.
        $paymentsArray = [];
        
        // Define the Excel spreadsheet headers
        $paymentsArray[] = [
            'Error type',
            'Error data',
            'Error Remark',
            'Validation Date & Time'
        ];
        
        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($payments as $payment) {
            $paymentsArray[] = $payment->toArray();
        }
        
        // echo "<pre>";print_r($paymentsArray);die;
        
        $filename = $file_user_id.time();
        try {
            // Generate and return the spreadsheet
            Excel::create($filename, function ($excel) use ($paymentsArray) {
                
                // Set the spreadsheet title, creator, and description
                $excel->setTitle('Error Report');
                $excel->setCreator('Laravel')->setCompany('Counter Project');
                $excel->setDescription('Error Report file');
                
                // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function ($sheet) use ($paymentsArray) {
                    $sheet->fromArray($paymentsArray, null, 'A1', false, false);
                });
            })->download('xlsx');
        } catch (Exception $exception) {
            report($exception);
            
            return parent::render($request, $exception);
        }
    }
    
    
    
    
    // /////////////////////////////////////////////////////////////////////
    // //////////function for send Email with attachment Error file/////////
    function emailfile_ubfr($reportfileId)
    {
        if(! Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $reportfile = Reportfile::where('id', $reportfileId)->firstOrFail();
        $resultfile = $reportfile->checkresult->resultfile;
        if($resultfile->user_id !== $user['id']) {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(403, 'You are not authorized to download this validation result.');
        }
        if(! Storage::exists($resultfile->location)) {
            // TODO: exception is not rendered, user is redirected to /filelist
            abort(404, 'Validation result not found.');
        }

        $title = 'Hi ' . $user['display_name'] . ',';
        $content = 'here is the validation result for the report ' . $reportfile->reportfile->filename .
            ' uploaded on ' . $resultfile->created_at . '.';
        $emailTo = $user['email'];
        
        try {
            Mail::send('emails.result', [
                'title' => $title,
                'content' => $content
            ], function ($message) use ($resultfile, $emailTo) {
                $message->subject('COUNTER Release 5 Report Validation Result');
                $message->attach(storage_path('app' . DIRECTORY_SEPARATOR . $resultfile->location), [
                    'as' => $resultfile->filename,
                    'mime' => $resultfile->getMimeType()
                ]);
                $message->to($emailTo);
            });
            Session::flash('emailMsg', 'Email was sent to ' . $emailTo . '.');
        } catch (Exception $exception) {
            report($exception);
        }
        
        return Redirect::to('filelist');
    }
    
    function emailfile($reportfileId)
    {
        return $this->emailfile_ubfr($reportfileId);
        
        // echo $file_user_id;die;
        $user = Session::get('user');
        // Execute the query used to retrieve the data. In this example
        // we're joining hypothetical users and payments tables, retrieving
        // the payments table's primary key, the user's first and last name,
        // the user's e-mail address, the amount paid, and the payment
        // timestamp.
        $payments = Filename::join('validateerrors', 'validateerrors.file_id', '=', 'filenames.id')->select('validateerrors.type', 'validateerrors.error_data', 'validateerrors.error_remark', 'validateerrors.entry_time')
            ->where('validateerrors.file_id', '=', $file_user_id)
            ->get();
        
        // Initialize the array which will be passed into the Excel
        // generator.
        $paymentsArray = [];
        
        // Define the Excel spreadsheet headers
        $paymentsArray[] = [
            'Error Type',
            'Error Data',
            'Error Remark',
            'Validation Date & Time'
        ];
        
        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($payments as $payment) {
            $paymentsArray[] = $payment->toArray();
        }
        // Generate and Save the spreadsheet
        
        $filename = $user['id'] . '_' . date('m-d-Y_hisa') . '_' . 'Error_report';
        
        try {
            Excel::create($filename, function ($excel) use ($paymentsArray) {
                
                // Set the spreadsheet title, creator, and description
                $excel->setTitle('Error Report');
                $excel->setCreator('Laravel')->setCompany('Counter Project');
                $excel->setDescription('Error Report file');
                
                // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function ($sheet) use ($paymentsArray) {
                    $sheet->fromArray($paymentsArray, null, 'A1', false, false);
                });
            })->store('xlsx', app_path() . '/public/downloadfile/');
        } catch (Exception $exception) {
            report($exception);
            return parent::render($request, $exception);
        }
        
        chmod(app_path() . '/public/downloadfile/' . $filename . '.' . 'xlsx', 0777);
        // die(app_path().'/public/downloadfile/'.$filename.'.'.'xlsx');
        // ///////////////////Send Mail//////////////////////////////////////
        $title = "Hi $user[display_name],";
        $content = 'Please Find The Attachment Of Error Report';
        $email_to = $user['email'];
        
        try {
            Mail::send('emails.send', [
                'title' => $title,
                'content' => $content
            ], function ($message) use ($filename, $email_to) {
                
                $attachfile = app_path() . '/public/downloadfile/' . $filename . '.' . 'xlsx';
                
                $message->subject('File Validate Error Report');
                $message->from('countermps@gmail.com', 'support');
                $message->attach($attachfile);
                $message->to($email_to);
            });
        } catch (Exception $exception) {
            report($exception);
            return parent::render($request, $exception);
        }
        
        Session::flash('emailMsg', 'Email Sent Successfully');
        return Redirect::to('filelist');
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
    
    // /////////////////////////////////////////////////////////////////////
    // ///////////////For TSV File Reading//////////////////////////////
    public function tsvconverttoxls($filename)
    {
        $path = app_path() . '/public/tsvuploadfile/';
        $handle = fopen($filename, "r");
        $row = 1;
        
        if (($handle = fopen($filename, "r")) !== FALSE) {
            $file_name = $path . 'file' . date('m-d-Y_hisa') . '.xls';
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                $num = count($data);
                $row ++;
                $fp = fopen($file_name, 'a+', $path);
                
                fputcsv($fp, $data);
                
                fclose($fp);
            }
            
            // check file existence if not found create blank file
            if (! file_exists($file_name)) {
                $data = array();
                $fp = fopen($file_name, 'a+', $path);
                fputcsv($fp, $data);
            }
            fclose($handle);
        }
        
        return $file_name;
    }

    // //////////////////////////////////////////////////////////////////
    
    // ////////////////////////Function for find extra space,lower case and value not match////////////////////////////
    function checkstringMendatory($chk_input_field, $chk_format_field)
    {
        $val = '';
        if (empty($chk_input_field)) {
            $val = 2;
        } else if (empty($chk_format_field)) {
            $val = '';
        } // ///////////no error/////////////////
        else {
            // $str_input=strtolower($chk_input_field);
            // $str_format=strtolower($chk_format_field);
            $str_input = ($chk_input_field);
            $str_format = ($chk_format_field);
            // echo "56";die;
            $FirstString = str_replace(" ", "", $str_input);
            $BufferString = $str_format;
            $SecondString = str_replace(" ", "", $str_format);
            if ($FirstString === $SecondString) {
                
                $val = ''; // /////////////1 for warning/////////////
            } else {
                $val = 2; // /////////////2 for error/////////////
            }
        }
        return $val;
    }

    function checkstring($chk_input_field, $chk_format_field)
    {
        $val = 0; // ///////////no error/////////////////
        if ($chk_input_field == $chk_format_field) {
            // echo "12";die;
            // ##########do nothing#############
        } else {
            $str_input = ($chk_input_field);
            $str_format = ($chk_format_field);
            if ($str_input === $str_format) {
                // echo "34";die;
                // #########do nothing###########
            } else {
                // echo "56";die;
                if (str_replace(" ", "", $str_input) === str_replace(" ", "", $str_format)) {
                    
                    $val = 1; // /////////////1 for warning/////////////
                } else {
                    
                    $val = 2; // /////////////2 for error/////////////
                }
            }
        }
        return $val;
    }

    // ///////////////////Function For Checking Minimum Cell In a Row//////////////////////////////////
    function checkminColumn($sheet, $get_row, $high_column, $id)
    {
        if ($id == 1) {
            $get_min_value = 0;
        } else {
            $get_min_value = 1;
        }
        
        $abc = $sheet->rangeToArray('A' . $get_row . ':' . $high_column . $get_row, NULL, TRUE, FALSE);
        $i = 0;
        $count = 0;
        $column = 'A';
        while ($column <= $high_column) {
            foreach ($abc as $detail) {
                if ((isset($detail[$i])) && ($detail[$i] != '')) {
                    $count = $count + 1;
                    $i ++;
                } else {
                    $i ++;
                }
            }
            $column ++;
        }
        if ($get_min_value > $count) {
            $error = "Row " . $get_row . " does not contain proper column";
            return $error;
        }
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////
    // ///////////////////Function For Checking Cell value In a particaular Row//////////////////////////////////
    function checkmaxColumn($sheet, $get_row, $high_column)
    {
        if ($get_row <= 13)
            return 'B';
        $abc = $sheet->rangeToArray('A' . $get_row . ':' . $high_column . $get_row, NULL, TRUE, FALSE);
        $arr1 = array_reverse($abc);
        $arr2 = $this->Reverse_Array($arr1);
        $i = 0;
        
        $column = 'A';
        while ($high_column >= $column) {
            foreach ($arr2 as $detail) {
                
                if ($detail[$i] == '') {
                    
                    $high_column = chr(ord($high_column) - 1);
                    
                    $i ++;
                } else {
                    return $high_column;
                    exit();
                }
            }
        }
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////
    // ///////////////////Function For Reverse Array//////////////////////////////////////////////////
    function Reverse_Array($array)
    {
        $index = 0;
        foreach ($array as $subarray) {
            if (is_array($subarray)) {
                $subarray = array_reverse($subarray);
                $arr = $this->Reverse_Array($subarray);
                $array[$index] = $arr;
            } else {
                $array[$index] = $subarray;
            }
            $index ++;
        }
        return $array;
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////Function For checking Issn No.////////////////////////////////////
    function issn($issn)
    {
        if (! preg_match("/^([0-9]{4})-([0-9]{3}[0-9X])$/", $issn, $matches)) {
            return false;
        } else {
            $issn = $matches[1] . $matches[2];
        }
        
        $chksum = 0;
        
        for ($i = 0; $i < strlen($issn) - 1; ++ $i) {
            $chksum += $issn[$i] * (8 - $i);
        }
        
        $chksum += $issn[7] == "X" ? 10 : $issn[7];
        
        if ($chksum % 11) {
            return false;
        } else {
            return true;
        }
    }

    // /////////////////////////////////////Function for Checking DOI///////////////////////
    function checkdoi($doi)
    {
        preg_match('/\b(10[.][0-9]{4,}(?:[.][0-9]+)*\/(?:(?!["&\'<>])\S)+)\b/', $doi, $output_array);
        if (count($output_array) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // ///////////function for URI/////////////
    function checkuri($uri)
    {
        if (preg_match('/^(http|https):\\/\\/[a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.[a-z]{2,5}' . '((:[0-9]{1,5})?\\/.*)?$/i', $uri, $output_array1)) {
            if (count($output_array1) > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    // /////////////////function For checking ISBN (10/13) number/////////////////////////////////////
    function ValidateIsbn($str)
    {
        $regex = '/\b(?:ISBN(?:: ?| ))?((?:97[89])?\d{9}[\dx])\b/i';
        
        if (preg_match($regex, str_replace('-', '', $str), $matches)) {
            return (10 === strlen($matches[1])) ? 1 : // ISBN-10
            2; // ISBN-13
        }
        return false; // No valid ISBN found
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////Function For check Date Format(yyyy-mm-dd)////////////////////////////////////
    function checkDateType($date)
    {
        // echo $date;
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
            // echo "12";die;
            return true;
        } else {
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////Function For check Date Format(yyyy-mm-dd to yyyy-mm-dd)////////////////////////////////////
    function checkDateTypeTo($date)
    {
        $pos = strpos($date, 'Begin');
        
        if ($pos === false) {
            $checkdate = explode(" ", $date);
            if (count($checkdate) == 3) {
                $check1 = $this->checkDateType($checkdate[0]);
                $check2 = $this->checkDateType($checkdate[2]);
                
                if ($check1 == true && $check2 == true) {
                    if ($checkdate[1] == 'to') {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            
            $checkdate = explode(";", $date);
            $FirstDate = $checkdate[0] ?? '';
            // exploding first date in two parts
            $firstPartArray = explode("=", $FirstDate);
            $SecondDate = $checkdate[1] ?? '';
            $SecondPartArray = explode("=", $SecondDate);
            $startdatevalue = $firstPartArray[1] ?? '';
            $enddatevalue = $SecondPartArray[1] ?? '';
            $check1 = $this->checkDateType($startdatevalue);
            $check2 = $this->checkDateType($enddatevalue);
            if ($check1 == true && $check2 == true) {
                return true;
            } else {
                return false;
            }
        }
    }

    // /////////////////Function For check Date Format("yyyy-mm-ddThh:mm:ssZ")///////////////////////////////////
    function checkUTCDateFormat($Date = '')
    {
        $Flag = true;
        $DataFlage = true;
        $FinalFlag = false;
        try {
            $dt = new DateTime($Date);
            $breakdate = explode("T", $Date);
            $Timevalue = substr($breakdate[1], 0, - 1);
            
            if ((preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $breakdate[0])) && (preg_match("/^(?:(?:([01]\d|2[0-3]):)?([0-5]\d):)?([0-5]\d)$/", $Timevalue))) {
                $DataFlage = true;
                
                if (substr($Date, - 1) == 'Z')
                    $Flage = true;
                else
                    $Flage = false;
            } else {
                
                $DataFlage = false;
                $Flage = false;
            }
            if ($DataFlage && $Flage) {
                $FinalFlag = true;
            }
        } catch (Exception $exception) {
            
            $FinalFlag = false;
        }
        return $FinalFlag;
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////Function For check Date Format(yyyy-mm-dd to yyyy-mm-dd)////////////////////////////////////
    function validateDate($date)
    {
        $datavalue = strtotime($date);
        // echo "Yes".$date."Hello".strtotime($date);
        $newDatevalue = date('M-Y', $datavalue);
        if ($date == $newDatevalue) {
            return "1";
        } else
            return "";
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////////Function for checking sum column-wise////////////////////////////
    function sum_column($sheet, $endrrow, $endrow1, $calrow, $match_column, $coln, $highestRow, $value, $rowno)
    {
        if ($value == '') {
            // $rowno=$rowno+1;
            $sheet->setCellValue($coln . '1', "=sum(" . $coln . "$calrow:" . $coln . "$highestRow)");
            $cell = $sheet->getCell($coln . '1')->getCalculatedValue();
            $sheet->setCellValue($coln . '2', "=(" . $coln . "$rowno<>" . $coln . "1)");
            $cell1 = $sheet->getCell($coln . '2')->getCalculatedValue();
            return $cell1;
        } else {
            $sheet->setCellValue($coln . $endrrow, "=sumif(" . $match_column . $calrow . ":" . $match_column . "$highestRow" . "," . '"' . $value . '"' . "," . $coln . "$calrow:" . $coln . "$highestRow)");
            // echo $coln.$endrrow."<br>";
            $cell = $sheet->getCell($coln . $endrrow)->getCalculatedValue();
            $sheet->setCellValue($coln . $endrow1, "=(" . $coln . "$rowno<>" . $coln . "$endrrow)");
            // echo $coln.$endrow1."<br>";
            $cell1 = $sheet->getCell($coln . $endrow1)->getCalculatedValue();
            return $cell1;
        }
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////Function for checking sum row-wise///////////////////////////////////////////////
    function sum_row($sheet, $nextcoln, $newnextcolumn, $rowno, $start_column, $highestColumn, $coln)
    {
        $sheet->setCellValue($nextcoln . $rowno, "=sum(" . $start_column . "$rowno:" . $highestColumn . "$rowno)");
        $cell = $sheet->getCell($nextcoln . $rowno)->getCalculatedValue();
        $sheet->setCellValue($newnextcolumn . $rowno, "=(" . $coln . "$rowno<>" . $nextcoln . "$rowno)");
        $cell1 = $sheet->getCell($newnextcolumn . $rowno)->getCalculatedValue();
        return $cell1;
    }

    // ///////////////////////////////////////////////////////////////////////////////////////////////////
    // ///////////////////error insert into database///////////////////////////////////////////////////////
    function inserterror($error, $warning, $userid, $filename, $extension, $reportNameFromJson = '', $getjsonreportCode = '')
    {
        $file_id = '';
        $count = count(array(
            $error
        ));
        $warning_count = count($warning) ?? '';
        if ($count > 0 || $warning_count > 0) {
            $file_id = $this->fileinsert($userid, $filename, $extension, $error, $warning, $reportNameFromJson, $getjsonreportCode);
        }
        if ($count > 0) {
            for ($i = 0; $i < $count; $i ++) {
                $validerror = new Validateerror();
                $validerror->file_id = $file_id;
                $validerror->user_id = $userid;
                $validerror->type = 'error';
                $validerror->error_data = $error[$i]["data"] ?? 'ISBN ERROR';
                $validerror->error_remark = $error[$i]["error"] ?? 'ISBN ERROR';
                $validerror->save();
            }
        }
        if ($warning_count > 0) {
            for ($i = 0; $i < $warning_count; $i ++) {
                $validerror = new Validateerror();
                $validerror->file_id = $file_id;
                $validerror->user_id = $userid;
                $validerror->type = 'warning';
                $validerror->error_data = $warning[$i]["data"] ?? 'ISBN ERROR';
                $validerror->error_remark = $warning[$i]["error"] ?? 'ISBN ERROR';
                $validerror->save();
            }
        }
        return $file_id;
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // ///////////////////insert filename////////////////////////////////////////////////////////////
    function fileinsert($userid, $filename, $extension, $error = '', $warning = '', $reportNameFromJson = '', $getjsonreportCode = '')
    {
        // $count=count($error);
        $user = Session::get('user');
        $file_detail = new Filename();
        $file_detail->file_type = $extension;
        $file_detail->email = $user->email;
        $file_detail->user_id = $userid;
        $file_detail->filename = $filename;
        $file_detail->report_name = $reportNameFromJson;
        $file_detail->report_id = $getjsonreportCode;
        $file_detail->save();
        $getid = $file_detail->id;
        return $getid;
    }
    // //////////////////////////////////////////////////////////////////////////////////////////////
}