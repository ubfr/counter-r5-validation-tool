<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ValidatereportController;
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
use App\Reportname;
use App\Filtertype;
use Exception;
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
        
        try{
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

    
    
   //////// function for check code and id only /////////
    
    public function jsonHeaderCodeIDValidate($reportname, $reportid)
    {
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;

        $reportdataa = new Reportname();
        $ReportName = $reportdataa::select('id')->where('report_name', trim($reportname))->first();
        
        
        if (! isset($ReportName->id)) {
            
            $warning[$b]["data"] = $reportname ?? '';
            if($reportname=='Structure error in JSON file')
                $warning[$b]["error"] = "Structure error of JSON File";
            else 
                $warning[$b]["error"] = "Report name is invalid in Master Report Header";
            $b ++;
            $data_warning ++;
            return $warning;
        }
        
        
        // Report Id validation
        $reportdata = new Reportname();
        $ReportId = $reportdata::select('id')->where('report_code', trim($reportid))->first();
        
        
        if (! isset($ReportId->id)) {
            
            
            $warning[$b]["data"] = $reportid ?? '';
            $warning[$b]["error"] = "Report id is invalid in Master Report Header";
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
        $reportNameFromJson = str_replace('"', '', ($jsonReportHeader['Report_Name']));
        $reportdataa = new Reportname();
        $ReportName = $reportdataa::select('id')->where('report_name', trim($reportNameFromJson))->first();
        if (! isset($ReportName->id)) {
            $warning[$b]["data"] = $reportNameFromJson ?? '';
            $warning[$b]["error"] = "Report name is invalid in Report Header";
            $b ++;
            $data_warning ++;
            
        }
        
        // Report Id validation
        $getjsonreportCode = $jsonReportHeader['Report_ID'];
        
        $reportdata = new Reportname();
        $ReportId = $reportdata::select('id')->where('report_code', trim($getjsonreportCode))->first();
        // echo "<pre>ddd";print_r($ReportId);die();
        if (! isset($ReportId->id)) {
            $warning[$b]["data"] = $getjsonreportCode ?? '';
            $warning[$b]["error"] = "Report id is invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Customer Id Validation
        $CustomerId = $jsonReportHeader['Customer_ID'] ?? '';
        
        if (empty($CustomerId) || ! (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $CustomerId))) {
            $warning[$b]["data"] = $CustomerId;
            $warning[$b]["error"] = "Cusotmer Id is invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Created Validation
        $Created = $jsonReportHeader['Created'] ?? '';
        if (empty($Created)) {
            $warning[$b]["data"] = $Created;
            $warning[$b]["error"] = "Created shouldn't be blank in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Created By Validation
        $CreatedBy = $jsonReportHeader['Created_By'] ?? '';
        if (empty($CreatedBy)) {
            $warning[$b]["data"] = $CreatedBy;
            $warning[$b]["error"] = " Created By shouldn't be blank in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Institution Name Validation
        $InstitutionName = $jsonReportHeader['Institution_Name'] ?? '';
        //echo "<pre>";print_r($InstitutionName);die;
        if (empty($InstitutionName)) {
            $warning[$b]["data"] = $InstitutionName;
            $warning[$b]["error"] = "Institution Name is invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Release no Validation
        $ReleaseNo = $jsonReportHeader['Release'] ?? '';
        if (empty($ReleaseNo) || ! (is_numeric($ReleaseNo))) {
            $warning[$b]["data"] = $ReleaseNo;
            $warning[$b]["error"] = " release no is invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // ISNI Value Validation
        $Value = $jsonReportHeader['Institution_ID'][0]['Value'] ?? '';
        if (empty($Value) || ! (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $Value))) {
            $warning[$b]["data"] = $Value;
            $warning[$b]["error"] = "ISNI Value is invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Begin date Validation
        $Bvalue = $jsonReportHeader['Report_Filters'][0]['Value'];
        if (empty($Bvalue) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $Bvalue))) {
            $warning[$b]["data"] = $Bvalue;
            $warning[$b]["error"] = "Begin date is invalid in report header";
            $b ++;
            $data_warning ++;
        }
        
        // End date Validation
        $Evalue = $jsonReportHeader['Report_Filters'][1]['Value']??$jsonReportHeader['Report_Filters'][0]['Value'];
        if (empty($Evalue) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $Evalue))) {
            $warning[$b]["data"] = $Evalue;
            $warning[$b]["error"] = "End date is invalid in Report Header";
            $b ++;
            $data_warning ++;
        }
        
        // Begin & End date comparision
        if ($Bvalue >= $Evalue) {
            $warning[$b]["data"] = $Bvalue;
            $warning[$b]["error"] = "Begin date shouldn't be grater than End date in Report Header";
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
            if (empty($platformJson)){
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            
            /* $yopJson = $datavalue['YOP'] ?? '';
            // echo "<pre>";print_r($yopJson); die;
            if (empty($yopJson)){
                $warning[$b]["data"] = $yopJson;
                $warning[$b]["error"] = "YOP is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            } */
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance']??'';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                $bdJson = $Performance['Period']['Begin_Date']??'';
                if (empty($bdJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson))) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report Items Index[ ".$key." ] Performance Index[ ".$k." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                $edJson = $Performance['Period']['End_Date']??'';
                if (empty($edJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson))) {
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_date in Report Items Index[ ".$key." ] Performance Index[ ".$k." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be grater than End_date in Report Items Index[ ".$key." ] Performance Index[ ".$k." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance']??'';
                foreach ($InstanceArray as $ki => $installValue) {
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is invalid in Report Items Index[ ".$key." ] performance index[ ".$ke." ] Instance index[ ".$ki." ]";
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
            $platformJson = $dataValue['Platform']??'';
            if (empty($platformJson)) {
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            
            $publisherJson = $dataValue['Publisher']??'';
            // echo "<pre>";print_r($dataValue); die;
            if (empty($publisherJson)) {
                $warning[$b]["data"] = $publisherJson;
                $warning[$b]["error"] = "Publisher is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            
           /* $publisherId = $dataValue['Publisher_ID'][0]['Value']??'';
           // $publisherId = explode(" ", $publisherIdd);
           // echo "<pre>";print_r($publisherId); die;
            if (empty($publisherId) || ! (is_numeric($publisherId))) {
                $warning[$b]["data"] = $publisherId;
                $warning[$b]["error"] = "Publisher Id is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            } */
            ///// for yop /////
            /* $yopJson = $datavalue['YOP']??'';
            // echo "<pre>";print_r($yopJson); die;
            if (empty($yopJson)){
                $warning[$b]["data"] = $yopJson;
                $warning[$b]["error"] = "YOP is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            } */
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance']??'';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                $bdJson = $Performance['Period']['Begin_Date'];
                if (empty($bdJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson))) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                $edJson = $Performance['Period']['End_Date']??'';
                if (empty($edJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson))) {
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be grater than End_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance']??'';
                foreach ($InstanceArray as $ki => $installValue) {
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is invalid in Report Items Index[ ".$key." ] performance index[ ".$ke." ] Instance index[ ".$ki." ]";
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
            $itemFromJson = $dataValue['Item']??'';
            if (empty($itemFromJson)) {
                $warning[$b]["data"] = $itemFromJson;
                $warning[$b]["error"] = "Item is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            // //////working for ITEM ID Values
            $itemDetailValues = array();
            $itemDetailValues = $dataValue['Item_ID']??'';
            foreach ($itemDetailValues as $k => $ItemData) {
                
                if (($ItemData['Type'] == 'DOI') && (empty($ItemData['Value']))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "DOI value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }
                if (($ItemData['Type'] == 'Proprietary_ID') && (empty($ItemData['Value']) || ! (is_numeric($ItemData['Value'])))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "Proprietary_ID value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }
                
                if ($ItemData['Type'] == 'ISBN') {
                    
                    if(empty($ItemData['Value'])){
                        $warning[$b]["data"] = $ItemData['Value'];
                        $warning[$b]["error"] = "ISBN value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                        $b ++;
                        $data_error ++;
                    }else{
                        $checkisbn = $this->ValidateIsbn($ItemData['Value']);
                        if ($checkisbn == false) {
                            $warning[$b]["data"] = $ItemData['Value'];
                            $warning[$b]["error"] = "ISBN value is Invalid in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                }
                
                if ($ItemData['Type'] == 'Print_ISSN' && (empty($ItemData['Value']))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "Print_ISSN value is Invalid in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }

                if ($ItemData['Type'] == 'Online_ISSN') {
                    
                    if(empty($ItemData['Value'])){
                        $warning[$b]["data"] = $ItemData['Value'];
                        $warning[$b]["error"] = "Online_ISSN value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                        $b ++;
                        $data_error ++;
                    }else{
                        $check = $this->issn(strtoupper($ItemData['Value']));
                        if ($check == false) {
                            $warning[$b]["data"] = $ItemData['Value'];
                            $warning[$b]["error"] = "Online_ISSN value is Invalid in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                }
                
                
                if ($ItemData['Type'] == 'URI' && empty($ItemData['Value'])) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "URI value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }
            }
            
            $platformJson = $dataValue['Platform']??'';
            if (empty($platformJson)) {
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            $publisherJson = $dataValue['Publisher']??'';
            if (empty($publisherJson)) {
                $warning[$b]["data"] = $publisherJson;
                $warning[$b]["error"] = "Publisher is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            
           /* $publisherId = $dataValue['Publisher_ID'][0]['Value']??'';
            if (empty($publisherId) || ! (is_numeric($publisherId))) {
                $warning[$b]["data"] = $publisherId;
                $warning[$b]["error"] = "Publisher Id is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }*/
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance']??'';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                $bdJson = $Performance['Period']['Begin_Date'];
                if (empty($bdJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson))) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                $edJson = $Performance['Period']['End_Date']??'';
                if (empty($edJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson))) {
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be grater than End_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance 
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance']??'';
                foreach ($InstanceArray as $ki => $installValue) { 
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is invalid in Instance index[ ".$ki." ] of performance index[ ".$ke." ] of Report Items Index[ ".$key." ]";
                        $b ++;
                        $data_warning ++;
                    }
                }
            }
            // performance closed
        }
        
        $data["error"] = $warning ?? array();
        //$data["error"]=$error ?? array();
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
        
        foreach ($AllBodyReport as $key => $dataValue) {
            $titleFromJson = $dataValue['Title']??'';
            if (empty($titleFromJson)) {
                $warning[$b]["data"] = $titleFromJson;
                $warning[$b]["error"] = "Title is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            // //////working for ITEM ID Values
            $itemDetailValues = array();
            $itemDetailValues = $dataValue['Item_ID']??array();
            foreach ($itemDetailValues as $k => $ItemData) {
                
                /*if (($ItemData['Type'] == 'DOI') && (empty($ItemData['Value']))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "DOI value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }*/
                /*if (($ItemData['Type'] == 'Proprietary_ID') && (empty($ItemData['Value']) || ! (is_numeric($ItemData['Value'])))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "Proprietary_ID value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }*/
                
               if ($ItemData['Type'] == 'ISBN') {
                        $checkisbn = $this->ValidateIsbn($ItemData['Value']);
                        if ($checkisbn == false) {
                            $warning[$b]["data"] = $ItemData['Value'];
                            $warning[$b]["error"] = "ISBN value is Invalid in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                    
                if ($ItemData['Type'] == 'Print_ISSN' && (empty($ItemData['Value']))) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "Print_ISSN value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }
           
                if ($ItemData['Type'] == 'Online_ISSN') {
                    
                    if(empty($ItemData['Value'])){
                        $warning[$b]["data"] = $ItemData['Value'];
                        $warning[$b]["error"] = "Online_ISSN value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                        $b ++;
                        $data_error ++;
                    }else{
                        $check = $this->issn(strtoupper($ItemData['Value']));
                        if ($check == false) {
                            $warning[$b]["data"] = $ItemData['Value'];
                            $warning[$b]["error"] = "Online_ISSN value is Invalid in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                            $b ++;
                            $data_error ++;
                        }
                    }
                }
                
                /*if ($ItemData['Type'] == 'URI' && empty($ItemData['Value'])) {
                    $warning[$b]["data"] = $ItemData['Value'];
                    $warning[$b]["error"] = "URI value is Empty in Report_Items Index[ ".$key." ] Item Id index[ ".$k." ]";
                    $b ++;
                    $data_warning ++;
                }*/
            }
            
            $platformJson = $dataValue['Platform']??'';
            if (empty($platformJson)) {
                $warning[$b]["data"] = $platformJson;
                $warning[$b]["error"] = "Platform is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            $publisherJson = $dataValue['Publisher']??'';
            // echo "<pre>";print_r($publisherJson);die;
            if (empty($publisherJson)) {
                $warning[$b]["data"] = $publisherJson;
                $warning[$b]["error"] = "Publisher is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
            
            /* $yopJson = $dataValue['YOP']??'';
            // echo "<pre>";print_r($yopJson);die;
            if (empty($yopJson)) {
                $warning[$b]["data"] = $yopJson;
                $warning[$b]["error"] = "YOP is Invalid in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }
 */            
           /*$publisherId = $dataValue['Publisher_ID'][0]['Value']??'';
            if (empty($publisherId) || ! (is_numeric($publisherId))) {
                $warning[$b]["data"] = $publisherId;
                $warning[$b]["error"] = "Publisher Id is Empty in Report_Items Index[ ".$key." ]";
                $b ++;
                $data_warning ++;
            }*/
            
            
            // /////checking for performance
            $itemDetailPerformance = array();
            $itemDetailPerformance = $dataValue['Performance']??'';
            foreach ($itemDetailPerformance as $ke => $Performance) {
                $bdJson = $Performance['Period']['Begin_Date']??'';
                if (empty($bdJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bdJson))) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Invalid Begin_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                $edJson = $Performance['Period']['End_Date']??'';
                if (empty($edJson) || ! (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $edJson))) {
                    $warning[$b]["data"] = $edJson;
                    $warning[$b]["error"] = "Invalid End_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                if ($bdJson >= $edJson) {
                    $warning[$b]["data"] = $bdJson;
                    $warning[$b]["error"] = "Begin_date shouldn't be grater than End_date in Report Items Index[ ".$key." ] performance index[ ".$ke." ] period index[ ".$ke." ]";
                    $b ++;
                    $data_warning ++;
                }
                
                // internal loop for instance
                $InstanceArray = array();
                $InstanceArray = $Performance['Instance']??'';
                foreach ($InstanceArray as $ki => $installValue) {
                    $MatricValue = trim(strtolower($installValue['Metric_Type']));
                    
                    if (empty($MatricValue) || ! in_array($MatricValue, $AllArrayOfMatrix)) {
                        $warning[$b]["data"] = $MatricValue ?? '';
                        $warning[$b]["error"] = "Metric Type is invalid in Report Items Index[ ".$key." ] performance index[ ".$ke." ] Instance index[ ".$ki." ]";
                        $b ++;
                        $data_warning ++;
                    }
                    
                }
            }
            // performance closed
        }
        
        $data["error"] = $warning ?? array();
        //$data["error"]=$error ?? array();
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        //echo "<pre>";print_r($data);die;
        return $data;
    }

    // //////////////////////////////////////////////////////////////////////
    // ////////////////////function for download file//////////////////////////
    function downloadfile($file_user_id, $filename)
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

    // /////////////////////////////////////////////////////////////////////
    // //////////function for send Email with attachment Error file/////////
    function emailfile($file_user_id)
    {
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
            
            //check file existence if not found create blank file
            if(!file_exists ($file_name )){
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
        $val ='';
        if(empty($chk_input_field)){
            $val=2; 
        }
        else if(empty($chk_format_field)){
            $val='';
        }
         // ///////////no error/////////////////
        else
        {
            $str_input=strtolower($chk_input_field);
            $str_format=strtolower($chk_format_field);
            //echo "56";die;
            $FirstString = str_replace(" ","",$str_input);
            $BufferString = $str_format;
            $SecondString = str_replace(" ","",$str_format);
            if($FirstString===$SecondString)
            {
                
                $val='';  ///////////////1 for warning/////////////
                
            }
            else {
                $val=2;  ///////////////2 for error/////////////
            }
            
        }
        return $val;
    }
    
    function checkstring($chk_input_field,$chk_format_field)
	{
		$val=0;   /////////////no error/////////////////
		if($chk_input_field==$chk_format_field)
		{
			//echo "12";die;
			###########do nothing#############
		}
		else
		{
			$str_input=strtolower($chk_input_field);
			$str_format=strtolower($chk_format_field);
			if($str_input==$str_format)
			{
				//echo "34";die;
				##########do nothing###########
			}
			else
			{
				//echo "56";die;
				if(str_replace(" ","",$str_input)==str_replace(" ","",$str_format))
				{
					
					$val=1;  ///////////////1 for warning/////////////
					
				}
				else
				{
					
					$val=2;  ///////////////2 for error/////////////
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
        
        if($get_row<=13)
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

    // ///////////////////////////////////////////////////////////////////////////////////////////////
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
        $pos = strpos($date,'Begin');
        
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
            $FirstDate = $checkdate[0]??'';
            //exploding first date in two parts
            $firstPartArray = explode("=", $FirstDate);
            $SecondDate = $checkdate[1]??'';
            $SecondPartArray = explode("=", $SecondDate);
            $startdatevalue = $firstPartArray[1]??'';
            $enddatevalue = $SecondPartArray[1]??'';
            $check1 = $this->checkDateType($startdatevalue);
            $check2 = $this->checkDateType($enddatevalue);
            if ($check1 == true && $check2 == true) {
                    return true;
                } else {
                    return false;
                }
            }
            
        
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
    function inserterror($error, $warning, $userid, $filename, $extension,$reportNameFromJson='',$getjsonreportCode='')
    {
        $file_id = '';
        $count = count($error);
        $warning_count = count($warning);
        if ($count > 0 || $warning_count > 0) {
            $file_id = $this->fileinsert($userid, $filename, $extension, $error, $warning, $reportNameFromJson, $getjsonreportCode);
        }
        if ($count > 0) {
            for ($i = 0; $i < $count; $i ++) {
                $validerror = new Validateerror();
                $validerror->file_id = $file_id;
                $validerror->user_id = $userid;
                $validerror->type = 'error';
                $validerror->error_data = $error[$i]["data"]??'ISBN ERROR';
                $validerror->error_remark = $error[$i]["error"]??'ISBN ERROR';
                $validerror->save();
            }
        }
        if ($warning_count > 0) {
            for ($i = 0; $i < $warning_count; $i ++) {
                $validerror = new Validateerror();
                $validerror->file_id = $file_id;
                $validerror->user_id = $userid;
                $validerror->type = 'warning';
                $validerror->error_data = $warning[$i]["data"]??'ISBN ERROR';
                $validerror->error_remark = $warning[$i]["error"]??'ISBN ERROR';
                $validerror->save();
            }
        }
        return $file_id;
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // ///////////////////insert filename////////////////////////////////////////////////////////////
    function fileinsert($userid, $filename, $extension, $error='',$warning='',$reportNameFromJson='', $getjsonreportCode='')
    {
        // $count=count($error);
        $user = Session::get('user');
        $file_detail = new Filename();
        $file_detail->file_type = $extension;
        $file_detail->email=$user->email;
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