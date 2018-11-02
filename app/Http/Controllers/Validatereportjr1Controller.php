<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Validateerror;
use App\Filename;
use App\Reportname;
use App\Validationrule;
use App\Rowvalidaterule;
Use Session;
Use Excel;
Use DB;
Use Mail;
use DateTime;
use App\Http\Manager\SubscriptionManager;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use PHPExcel_Cell_DefaultValueBinder;
use Illuminate\Support\Facades\Storage;
use App\Filtertype;

class Validatereportjr1Controller extends FilevalidateController {

    static $valid = true;

    ////////////////////validate file//////////////////////////////////



    public function createColumnsArray($end_column, $first_letters = '') {
        $columns = array();
        $length = strlen($end_column);
        $letters = range('A', 'Z');

        // Iterate over 26 letters.
        foreach ($letters as $letter) {
            // Paste the $first_letters before the next.
            $column = $first_letters . $letter;

            // Add the column to the final array.
            $columns[] = $column;

            // If it was the end column that was added, return the columns.
            if ($column == $end_column)
                return $columns;
        }

        // Add the column children.
        foreach ($columns as $column) {
            // Don't itterate if the $end_column was already set in a previous itteration.
            // Stop iterating if you've reached the maximum character length.
            if (!in_array($end_column, $columns) && strlen($column) < $length) {
                $new_columns = $this->createColumnsArray($end_column, $column);
                // Merge the new columns which were created with the final columns array.
                $columns = array_merge($columns, $new_columns);
            }
        }

        return $columns;
    }

    function JournalReport1R4($sheet, $highestRow, $highestColumn, $error, $warning) {
        $string_check = 0;
        $a = 0;
        $b = 0;
        $structure_error = 0;
        $data_error = 0;
        $structure_warning = 0;
        $data_warning = 0;
        $topMostMaxRowNo = 15;
        $MetricTypeColumn = '';

        ///////////////////////////////////////////////////////////////////////////////////
        ////////Check from Database each Row is range or not///////////////////////////////
        //$getreport=$sheet->rangeToArray('A1' . ':' .'A1',NULL, TRUE, FALSE);
        //getting all matrix filter
        $FilterReports = Filtertype::where(array())->orderBy('id', 'asc')->get()->toArray();
        //converting single array
        $AllArrayOfMatrix = array();
        foreach ($FilterReports as $MatrixFilter) {
            $AllArrayOfMatrix[] = strtolower($MatrixFilter['name']);
        }
        //echo "<pre>";print_r($AllArrayOfMatrix);die;

        $getreport = $sheet->rangeToArray('B1' . ':' . 'B1', NULL, TRUE, FALSE);
        $Reportname1 = $getreport[0][0] ?? 'BLANK';
        $reportdata = new Reportname;
        $ReportId = $reportdata::select('id')
                ->where('report_name', trim($Reportname1))
                ->first();



        $getreportCode = $sheet->rangeToArray('B2' . ':' . 'B2', NULL, TRUE, FALSE);
        $getreportCode = $getreportCode[0][0] ?? 'BLANK';
        $ReportIdForCode = $reportdata::select('id')
                ->where('report_code', trim($getreportCode))
                ->first();

////////////////////////////////////////report_attribute/////////////////////////////////////////////////////////

        $Headervalue = $Header = $sheet->rangeToArray('A14' . ':' . 'Q14', NULL, TRUE, FALSE);
        $headerdataarray = $Headervalue[0];

        //checking matrix type
        $MatrixValue = $MatrixFilter = $sheet->rangeToArray('B6' . ':' . 'B6', NULL, TRUE, FALSE);
        $valueOfCell = $MatrixValue[0][0] ?? '';
        $valueOfCellArray = array_map("trim", explode(';', $valueOfCell));
        $valueOfCellArray = array_map("strtolower", $valueOfCellArray);
        //removing space
        $flageForMatrixfilter = 0;
        $invalidMetric = array();
        foreach ($valueOfCellArray as $singleValue) {
            $searchvalue = trim($singleValue);
            if (!(in_array(strtolower($searchvalue), $AllArrayOfMatrix)) && !empty($searchvalue)) {
                $flageForMatrixfilter = 1;
                $invalidMetric[] = $searchvalue;
            }
        }
        if ($ReportIdForCode['id'] == '') {
            $error[$b]["data"] = $getreportCode ?? '';
            $error[$b]["error"] = "Platform code in report does not match any registered platforms in Cell B2";
            $b++;
            $data_error++;
        }
        else if ($ReportId['id'] == '') {
            $error[$b]["data"] = $Reportname1;
            $error[$b]["error"] = "Platform name in report does not match any registered platforms in Cell B1";
            $b++;
            $data_error++;
        }
        else {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '2048M');
            ini_set('max_memory', '2048M');
            ini_set('post_max_size', '32M');
            $data = new Rowvalidaterule;
            $highesstatictrow = $data::where('report_id', $ReportId['id'])
                    ->where('is_range', '0')
                    ->max('row');
            //connected with row_validate_rules and getting 
            if ($flageForMatrixfilter == 1) {
                $invalidMetric = implode(";", $invalidMetric);
                $error[$a]["data"] = $invalidMetric;
                $error[$a]["error"] = "Metric Type in report does not match any registered metric in Cell B6";
                $a++;
                $data_error++;
            }




            $Institution_Name = $sheet->rangeToArray('B4' . ':' . 'B4', NULL, TRUE, FALSE);
            $Institution_Name = $Institution_Name[0][0] ?? '';
            if (empty($Institution_Name)) {
                $error[$a]["data"] = 'NULL';
                $error[$a]["error"] = "$Institution_Name should not be blank in Cell B4";
                $a++;
                $data_error++;
            }
            $Institution_ID = $sheet->rangeToArray('B5' . ':' . 'B5', NULL, TRUE, FALSE);
            $Institution_ID = $Institution_ID[0][0] ?? '';
            if (empty($Institution_ID)) {
                $error[$a]["data"] = 'NULL';
                $error[$a]["error"] = "$Institution_ID should not be blank in Cell B5";
                $a++;
                $data_error++;
            }
            $Metric_Types = $sheet->rangeToArray('B6' . ':' . 'B6', NULL, TRUE, FALSE);
            $Metric_Types = $Metric_Types[0][0] ?? '';
            if (empty($Metric_Types)) {
                $error[$a]["data"] = 'NULL';
                $error[$a]["error"] = "Metric_Types should not be blank in Cell B6";
                $a++;
                $data_error++;
            }



            //now for B8 Rules

            $Attributes = $sheet->rangeToArray('B8' . ':' . 'B8', NULL, TRUE, FALSE);
            $AttributesValue = $Attributes[0][0] ?? '';
            //Stored Attributes to show for check in header report
            $CheckValidationAttribute = array();
            
            $AttributesWithoutPipe = explode('=', $AttributesValue);
            if(isset($AttributesWithoutPipe[0]) && $AttributesWithoutPipe[0]==='Attributes_To_Show'){
                $CheckValidationAttribute = explode('|', $AttributesWithoutPipe[1]);
            }
            if ($getreportCode === 'DR' || $getreportCode === 'PR') {
                if (empty($AttributesValue)) {
                    $error[$a]["data"] = 'NULL';
                    $error[$a]["error"] = "Report_Attributes should not be blank in Cell B8";
                    $a++;
                    $data_error++;
                } else {
                    $FirstFlage = in_array('YOP', $CheckValidationAttribute);
                    $SecondFlage = in_array('Access_Type', $CheckValidationAttribute);
                    if ($FirstFlage || $SecondFlage) {
                        $error[$a]["data"] = $AttributesValue;
                        $error[$a]["error"] = "Report_Attributes should not be have YOP or Access_Type in Cell B8";
                        $a++;
                        $data_error++;
                    }
                }
            }

            $Reporting_Period = $sheet->rangeToArray('B10' . ':' . 'B10', NULL, TRUE, FALSE);
            $Reporting_Period = $Reporting_Period[0][0] ?? '';
            if (empty($Reporting_Period)) {
                $error[$a]["data"] = 'NULL';
                $error[$a]["error"] = "Reporting_Period should not be blank in Cell B10";
                $a++;
                $data_error++;
            }


            $Created_By = $sheet->rangeToArray('B12' . ':' . 'B12', NULL, TRUE, FALSE);
            $Created_By = $Created_By[0][0] ?? '';
            if (empty($Created_By)) {
                $error[$a]["data"] = 'NULL';
                $error[$a]["error"] = "Created_By should not be blank in Cell B12";
                $a++;
                $data_error++;
            }
            
            //capturing filter type of report
            $ReportFilter = $sheet->rangeToArray('B7' . ':' . 'B7', NULL, TRUE, FALSE);
            $ReportFilter = $ReportFilter[0][0] ?? '';
            $ReportFilterValidation = array();
            $ReportFilterKeyAndIndex = array();
            if (!empty($ReportFilter)) {
                $allFilters = explode(';',$ReportFilter);
                
                if(is_array($allFilters)){
                    $allFilters=array_filter($allFilters);
                    foreach($allFilters as $FilterValue){
                        $FilterValueWithKey = explode('=', $FilterValue);
                        //echo "<pre>Image";print_r($FilterValueWithKey);
                        $FilterValue = explode("|",$FilterValueWithKey[1]??'');
                        $allFilters=array_filter($FilterValue);
                        foreach($allFilters as $SinglFilter){
                           $ReportFilterValidation[trim($FilterValueWithKey[0])][]= $SinglFilter;
                        }
                    }
                }
            }
            // $queries = DB::getQueryLog();
            /////////loop for static column///////////////////////////////////////////////////////////////
            $endrrow = $highestRow + 1;
            $endrow1 = $highestRow + 2;

            //getting all master data for maximum row
            //$AllMaxRow = Validationrule::select(array('report_no','colname'))
            $AllMaxRow = Validationrule::select(array('report_no', 'colname'))
                            ->addSelect(DB::raw('max(rowno) as maxrow'))
                            ->groupBy(array('report_no', 'colname'))
                            ->where(array('report_no'=>$ReportId['id']))
                            ->get()->toArray();
            //echo "<pre>";print_r($AllMaxRow);die;
            //setting array
            $allMaxRowData = array();
            foreach ($AllMaxRow as $maxRowLen) {
                $allMaxRowData[$maxRowLen['report_no']][$maxRowLen['colname']] = $maxRowLen['maxrow'];
            }
            //loop for highest row
            for ($i = 1; $i <= $highesstatictrow; $i++) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '2048M');
                ini_set('max_memory', '2048M');
                ini_set('post_max_size', '32M');
                
                //checcking attributes to show column exist or not and capturing error
                if($i==14){
                    $Differences=array();
                    $AllColumnsHeading = $sheet->rangeToArray('A'.$i . ':' .$highestColumn.$i , NULL, TRUE, FALSE);
                    $Differences = array_diff($CheckValidationAttribute,$AllColumnsHeading[0]);
                    if(count($Differences)>0){
                        $error[$a]["data"] = implode(",",$Differences);
                        $error[$a]["error"] = "Attributes to show column(s) does not exist in column list in row no 14.";
                        $a++;
                        $data_error++; 
                    }
                }
                
                ////////Check from Database Rules For Report/////////////////////////////////////////////
                $rowcount = new Validationrule;
                //counting no. of validation for row 1 return 2 for column A and B
                $columncount = $rowcount::where('report_no', $ReportId['id'])
                        ->where('rowno', $i)
                        ->count('id');
                
                //get Metric_Type index and previous index for capture column
                $PreviousToMetric_TypeCol='';
                $PreviousToMetric_TypeVal='';
                if($i==14 && (count($CheckValidationAttribute)>0)){
                    //DB::enableQueryLog();
                    $RulesValue = $columncount = $rowcount::select('id', 'colname','value')
                            ->where(array('report_no'=>$ReportId['id'],'rowno'=>$i))
                            ->where('id', '<=', $rowcount::select('id')->where(array('report_no'=>$ReportId['id'],'rowno'=>$i,'value'=>'Metric_Type'))->get()->first()->toArray())
                            ->orderBy('id','DESC')
                            ->limit(2)
                            ->get()
                            ->toArray();
                    //echo "<pre>";print_r(DB::getQueryLog());die;
                    $PreviousToMetric_TypeCol = $RulesValue[1]['colname']??'';
                    $PreviousToMetric_TypeVal = $RulesValue[1]['value']??'';
                    $postionofColumnInSheet = array_search('Metric_Type', $AllColumnsHeading[0]);
                    $countIndex='A';
                    for($iCountForIndex=0;$iCountForIndex<$postionofColumnInSheet;$iCountForIndex++)
                    {
                        $countIndex++;
                    }
                    $MetricTypeColumn = $countIndex;
                    
                    //updating Filters Column
                    if(count($ReportFilterValidation)>0){
                       $allIndexOfFiter = array_keys($ReportFilterValidation);
                       //echo "<pre>";print_r($allIndexOfFiter);
                       //echo "<pre>";print_r($AllColumnsHeading[0]);die;
                       foreach($allIndexOfFiter as $ColumnNameofFilter){
                            $postionofColumnInSheetFilter = array_search($ColumnNameofFilter, $AllColumnsHeading[0]);
                            $countIndexFilter='A';
                            for($iCountForIndexFilter=0;$iCountForIndexFilter<$postionofColumnInSheetFilter;$iCountForIndexFilter++)
                            {
                                $countIndexFilter++;
                            }
                            if($countIndexFilter!='A')
                                $ReportFilterKeyAndIndex[$ColumnNameofFilter] = $countIndexFilter;
                       }
                    }
                }        
                
                
                $min_error = $this->checkminColumn($sheet, $i, $highestColumn, '1');
                if ($min_error == '') {
                    $coln = 'A';
                    ////////////////Call Function to Check Maximum Excel Row Length///////////////////////
                    $highestcoln = $this->checkmaxColumn($sheet, $i, $highestColumn);
                    ////////////////While Loop From First Cell To Last Cell of a Row//////////////////////
                    $resultValueColumn = $this->createColumnsArray($highestcoln);
                    $ExtraLoopValue=0;
                    $Confliction = '';
                    $ConflictionFlag =0;
                    foreach ($resultValueColumn as $coln) {
                        if($i==14 && $coln=='E')
                            $x=10;
                        if (($i === 6 && $coln === 'B') || ($i === 8 && $coln === 'B')|| ($i === 7 && $coln === 'B')) {
                            continue;
                        }
                        
                        //checking for Matric type before column
                        if(((!empty($PreviousToMetric_TypeCol) && $coln>$PreviousToMetric_TypeCol)) &&  $coln<$MetricTypeColumn){
                                $ExtraLoopValue++;
                             if($ConflictionFlag==0){
                                $Confliction = $coln;
                                $ConflictionFlag=1;
                             }
                                continue;
                        }
                        ////////Select from Database Rules Column Wise For Report/////////////////////////////////////////////
                        if($ExtraLoopValue>0){
                            $newColumn = $Confliction;
                            $xcount = 0;
                            while($newColumn<$coln){
                               $xcount++; 
                               $newColumn++;
                            }
                            $MaxDecrement = ($xcount-$ExtraLoopValue);
                            $valueofdata = $Confliction;
                             for($counti=0;$counti<$MaxDecrement;$counti++){
                                $valueofdata++; 
                             }
                             
                             $columnval = $rowcount::select('ruletype', 'value', 'required', 'is_range', 'start_column', 'match_column')
                                ->where('report_no', $ReportId['id'])
                                ->where('rowno', $i)
                                ->where('colname', $valueofdata)
                                ->first();
                        
                        }else{
                          $columnval = $rowcount::select('ruletype', 'value', 'required', 'is_range', 'start_column', 'match_column')
                                ->where('report_no', $ReportId['id'])
                                ->where('rowno', $i)
                                ->where('colname', $coln)
                                ->first(); 
                        }
                        
                        if (!(isset($columnval['value'])) && ($i > $topMostMaxRowNo)) {
                            $columnval = $rowcount::select('ruletype', 'value', 'required', 'is_range', 'start_column', 'match_column')
                                    ->where('report_no', $ReportId['id'])
                                    ->where('rowno', $topMostMaxRowNo)
                                    ->where('colname', $coln)
                                    ->first();
                        }
                        
                        $cellval = $sheet->getcell($coln . $i) ?? '';
                        //checking metric type
                        if ($coln == $MetricTypeColumn && $i > 14) {
                            $MetricTypeValue = trim($cellval);
                            if (!in_array(strtolower($MetricTypeValue), $valueOfCellArray)) {
                                $error[$a]["data"] = $cellval;
                                $error[$a]["error"] = $cellval . " Metric type Cell " . $coln . $i . " does not matched with header Metric Type";
                                $a++;
                                $data_error++;
                            }
                        }

                        if ($columnval['is_range'] == '1') {
                            $startcell = $coln;
                            $lastcell = $highestcoln;
                            //////////////////////looping start///////////////////////////////////
                            while ($startcell <= $lastcell) {
                                $cellval = $sheet->getcell($startcell . $i);
                                if ($columnval['required'] == '0') {
                                    //////////////if rule type is ISSN////////////////
                                    if ($columnval['ruletype'] == 'issn') {
                                        if ($cellval == '') {
                                            ########do nothing
                                        } else {
                                            $check = $this->issn(strtoupper($cellval));
                                            if ($check == true) {
                                                #########do nothing
                                            } else {
                                                $error[$a]["data"] = $cellval;
                                                $error[$a]["error"] = "Cell " . $startcell . $i . " should be valid ISSN";
                                                $a++;
                                                $data_error++;
                                            }
                                        }
                                    }
                                } else if ($columnval['required'] == '1') {
                                    if ($columnval['ruletype'] == '') {
                                        if ($cellval == '') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " should not blank";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    //////////////if ruletype is "Integer"////////////////////
                                    else if ($columnval['ruletype'] == 'integer') {
                                        $cellint = $sheet->getCell($startcell . $i)->getCalculatedValue();
                                        if (Is_numeric($cellint)) {
                                            ##do nothing############
                                        } else {
                                            $error[$a]["data"] = $cellint;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " should be Numeric";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    //////////////if ruletype is "text"////////////////////
                                    else if ($columnval['ruletype'] == 'text') {
                                        $string_error = $this->checkString($cellval, $columnval['value']);
                                        if ($string_error == 1) {
                                            $error[$b]["data"] = $cellval;
                                            $error[$b]["error"] = "Cell " . $startcell . $i . " contains no proper space";
                                            $data_error++;
                                            $b++;
                                        } else if ($string_error == 2) {
                                            if ($columnval['value'] == '') {
                                                $error_det = 'Null';
                                            } else {
                                                $error_det = $columnval['value'];
                                            }

                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " should be $error_det";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    ///////////////////////////////////////////////////////
                                    //////////////if rule type is Date type////////////////
                                    else if ($columnval['ruletype'] == 'date_format') {
                                        if ($columnval['value'] == 'YYYY-MM-DD') {
                                            //echo "1";die;
                                            $getdate = $sheet->rangeToArray($startcell . $i . ':' . $startcell . $i, NULL, TRUE, FALSE);
                                            foreach ($getdate as $dateval) {
                                                $cellval = $dateval[0];
                                            }
                                            $x = '';
                                            $readDate = '';
                                            $date_month = explode("-", $cellval);
                                            $x = $this->checkDateType($cellval);
                                            if (is_numeric($cellval) and $cellval > 0) {
                                                $phpexcepDate = ($cellval) - 25569; //to offset to Unix epoch
                                                $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                            } else {
                                                $readDate = $cellval;
                                            }
                                            if ((count($date_month)) == '3') {
                                                if (!$x) {
                                                    $warning[$b]["data"] = $readDate;
                                                    $warning[$b]["error"] = "Cell " . $startcell . $i . " should be Proper " . $columnval['value'] . " Format";
                                                    $data_warning++;
                                                    $b++;
                                                } else {
                                                    
                                                }
                                            } else {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $startcell . $i . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            }
                                        } else if ($columnval['value'] == 'YYYY-MM-DD to YYYY-MM-DD') {
                                            $checkdate = $this->checkDateTypeTo($cellval);
                                            if ($checkdate == true) {
                                                
                                            } else {
                                                $warning[$b]["data"] = $cellval;
                                                $pos = strpos($date, 'Begin');
                                                if ($pos === true)
                                                    $warning[$b]["error"] = "Cell " . $startcell . $i . " should contain  Begin_Date=YYYY-MM-DD; End_Date=YYYY-MM-DD";
                                                else
                                                    $warning[$b]["error"] = "Cell " . $startcell . $i . " should contain " . $columnval['value'] . " format";
                                                $data_warning++;
                                                $b++;
                                            }
                                        }else {
                                            $getdate = $sheet->rangeToArray($startcell . $i . ':' . $startcell . $i, NULL, TRUE, FALSE);
                                            foreach ($getdate as $dateval) {
                                                $cellval = $dateval[0];
                                            }
                                            $x = '';
                                            $readDate = '';
                                            $date_month = explode("-", $cellval);
                                            $x = $this->validateDate($cellval);
                                            if (is_numeric($cellval) and $cellval > 0) {
                                                $phpexcepDate = ($cellval) - 25569; //to offset to Unix epoch
                                                $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                            } else {
                                                $readDate = $cellval;
                                            }
                                            if ((count($date_month)) == '2') {
                                                if (!$x) {
                                                    $warning[$b]["data"] = $readDate;
                                                    $warning[$b]["error"] = "Cell " . $startcell . $i . " should be Proper " . $columnval['value'] . " Format";
                                                    $data_warning++;
                                                    $b++;
                                                } else {
                                                    
                                                }
                                            } else {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $startcell . $i . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            }
                                        }
                                    }
                                    //////////////if rule type is ISSN////////////////
                                    else if ($columnval['ruletype'] == 'issn') {
                                        $check = $this->issn(strtoupper($cellval));
                                        if ($check == true) {
                                            #########do nothing
                                        } else {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " should be valid ISSN";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    ////////////////////////////////////////////////////////////
                                    /////////if rule type is ISBN//////////////////////////////
                                    else if ($columnval['ruletype'] == 'isbn') {
                                        $checkisbn = $this->ValidateIsbn($cellval);

                                        if ($checkisbn == false) {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " should be valid ISBN";
                                            $a++;
                                            $data_error++;
                                        } else {
                                            #########do nothing
                                        }
                                    }
                                    //////////////////////////////////////////////////////////
                                    //////////////Sum Column Wise////////////////////////////////////////////
                                    else if ($columnval['ruletype'] == 'sum') {
                                        $j = $i + 1;
                                        $sheet->setCellValue($startcell . '1', "=sum(" . $startcell . "$j:" . $startcell . "$highestRow)");
                                        $cell = $sheet->getCell($startcell . '1')->getCalculatedValue();
                                        $sheet->setCellValue($startcell . '2', "=(" . $startcell . "$i<>" . $startcell . "1)");
                                        $cell1 = $sheet->getCell($startcell . '2')->getCalculatedValue();
                                        if ($cell1 == '1') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " Total Not Match";
                                            $a++;
                                            $data_error++;
                                        }
                                    } else if ($columnval['ruletype'] == 'row sum') {
                                        $nextcoln = $highestColumn;
                                        ++$nextcoln;
                                        $newnextcolumn = $nextcoln;
                                        ++$newnextcolumn;
                                        $rowsum = $this->sum_row($sheet, $nextcoln, $newnextcolumn, $i, $columnval['start_column'], $highestColumn, $startcell);
                                        if ($rowsum == '1') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " Total Not for ROW";
                                            $a++;
                                            $data_error++;
                                        }
                                    }

                                    ///////////if rule type is sumif/////////////////////////////////
                                    else if ($columnval['ruletype'] == 'sumif') {
                                        $endrrow++;
                                        $endrow1++;
                                        $calrow = $i + 1;
                                        $sheet->setCellValue($startcell . $endrrow, "=sumif(" . $columnval['start_column'] . $calrow . ":" . $columnval['start_column'] . "$highestRow" . "," . '"' . $columnval['value'] . '"' . "," . $startcell . "$calrow:" . $startcell . "$highestRow)");
                                        $cell = $sheet->getCell($startcell . $endrrow)->getCalculatedValue();
                                        $sheet->setCellValue($startcell . $endrow1, "=(" . $startcell . "$i<>" . $startcell . "$endrrow)");
                                        $cell1 = $sheet->getCell($startcell . $endrow1)->getCalculatedValue();
                                        if ($cell1 == '1') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $coln . $i . " Total Not Match";
                                            $a++;
                                            $data_error++;
                                        }
                                        $endrrow++;
                                        $endrow1++;
                                    }
                                    /////////////////////////////////////////////////////////////////
                                    ///////////if rule type is string/////////////////////////////////
                                    else if ($columnval['ruletype'] == 'string') {
                                        $arr1 = explode(',', $columnval['value']);
                                        if (in_array($cellval, $arr1)) {
                                            ########Do Nothing #################
                                        } else {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $coln . $i . " Should be From $columnval[value]";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    /////////////////////////////////////////////////////////////////
                                    else {
                                        ###########no logic
                                    }
                                }
                                $startcell++;
                            }
                            /////////////////////////////////////
                            break;
                        } elseif ($columnval['is_range'] == '0') {
                            if ($columnval['required'] == '0') {
                                //////////////if rule type is ISSN////////////////
                                if ($columnval['ruletype'] == 'issn') {
                                    if ($cellval == '') {
                                        ########do nothing
                                    } else {
                                        $check = $this->issn(strtoupper($cellval));
                                        if ($check == true) {
                                            #########do nothing
                                        } else {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $coln . $i . " should be valid ISSN";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                }
                            } else if ($columnval['required'] == '1') {
                                if ($columnval['ruletype'] == '') {
                                    if ($cellval == '') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " should not blank";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                //////////////if ruletype is "Integer"////////////////////
                                else if ($columnval['ruletype'] == 'integer') {
                                    $cellint = $sheet->getCell($coln . $i)->getCalculatedValue();
                                    if (Is_numeric($cellint)) {
                                        ##do nothing############
                                    } else {
                                        $error[$a]["data"] = $cellint;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " should be Numeric";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                //////////////if ruletype is "text"////////////////////
                                else if ($columnval['ruletype'] == 'text') {
                                    //$string_error=$this->checkString($cellval,$columnval['value']);
                                    $cellValueNew = $sheet->rangeToArray($coln . $i . ':' . $coln . $i, NULL, TRUE, FALSE);
                                    $cellValueNew = $cellValueNew[0][0] ?? '';
                                    $string_error = $this->checkstringMendatory($cellValueNew, $columnval['value']);

                                    if ($string_error === 1) {
                                        $warning[$b]["data"] = $cellval;
                                        $warning[$b]["error"] = "Cell " . $coln . $i . " contains no proper space";
                                        $data_warning++;
                                        $b++;
                                    } else if ($string_error === 2) {
                                        if ($columnval['value'] == '') {
                                            $error_det = 'Null';
                                        } else {
                                            $error_det = $columnval['value'];
                                        }
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " should be $error_det";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                ///////////////////////////////////////////////////////
                                //////////////if rule type is Date type////////////////
                                else if ($columnval['ruletype'] == 'date_format') {
                                    if ($columnval['value'] == 'YYYY-MM-DD') {

                                        $getdate = $sheet->rangeToArray($coln . $i . ':' . $coln . $i, NULL, TRUE, FALSE);

                                        foreach ($getdate as $dateval) {
                                            $cellval = $dateval[0];
                                        }
                                        $x = '';
                                        $readDate = '';
                                        $date_month = explode("-", $cellval);
                                        $x = $this->checkDateType($cellval);
                                        if (is_numeric($cellval) and $cellval > 0) {
                                            $phpexcepDate = ($cellval) - 25569; //to offset to Unix epoch
                                            $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                        } else {
                                            $readDate = $cellval;
                                        }
                                        if ((count($date_month)) == '3') {
                                            if (!$x) {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $coln . $i . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            } else {
                                                
                                            }
                                        } else {
                                            $warning[$b]["data"] = $readDate;
                                            $warning[$b]["error"] = "Cell " . $coln . $i . " should be Proper " . $columnval['value'] . " Format";
                                            $data_warning++;
                                            $b++;
                                        }
                                    } else if ($columnval['value'] == 'YYYY-MM-DD to YYYY-MM-DD') {
                                        $checkdate = $this->checkDateTypeTo($cellval);
                                        if ($checkdate == true) {
                                            ########do nothing############
                                        } else {
                                            $warning[$b]["data"] = $cellval;
                                            $warning[$b]["error"] = "Cell " . $coln . $i . " should contain " . $columnval['value'] . " format";
                                            $data_warning++;
                                            $b++;
                                        }
                                    } else {
                                        $getdate = $sheet->rangeToArray($coln . $i . ':' . $coln . $i, NULL, TRUE, FALSE);
                                        foreach ($getdate as $dateval) {
                                            $cellval = $dateval[0];
                                        }
                                        $x = '';
                                        $readDate = '';
                                        $date_month = explode("-", $cellval);
                                        $x = $this->validateDate($cellval);
                                        if (is_numeric($cellval) and $cellval > 0) {
                                            $phpexcepDate = $cellval - 25569; //to offset to Unix epoch
                                            $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                        } else {
                                            $readDate = $cellval;
                                        }
                                        if ((count($date_month)) == '2') {
                                            if (!$x) {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $coln . $i . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            } else {
                                                
                                            }
                                        } else {
                                            $warning[$b]["data"] = $readDate;
                                            $warning[$b]["error"] = "Cell " . $coln . $i . " should be Proper " . $columnval['value'] . " Format";
                                            $data_warning++;
                                            $b++;
                                        }
                                    }
                                }
                                //////////////if rule type is Date type////////////////
                                else if ($columnval['ruletype'] == 'issn') {
                                    $check = $this->issn(strtoupper($cellval));
                                    if ($check == true) {
                                        #########do nothig
                                    } else {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " should be valid ISSN";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                //////////////////////////////////////////////////////////////////////
                                /////////if rule type is ISBN//////////////////////////////
                                else if ($columnval['ruletype'] == 'isbn') {
                                    $checkisbn = $this->ValidateIsbn($cellval);
                                    if ($checkisbn == false) {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " should be valid ISBN";
                                        $a++;
                                        $data_error++;
                                    } else {
                                        #########do nothing
                                    }
                                }
                                //////////////////////////////////////////////////////////
                                ///////////////////if rule type is sum///////////////////
                                else if ($columnval['ruletype'] == 'sum') {
                                    $j = $i + 1;
                                    $sheet->setCellValue($coln . '1', "=sum(" . $coln . "$j:" . $coln . "$highestRow)");
                                    $cell = $sheet->getCell($coln . '1')->getCalculatedValue();
                                    $sheet->setCellValue($coln . '2', "=(" . $coln . "$i<>" . $coln . "1)");
                                    $cell1 = $sheet->getCell($coln . '2')->getCalculatedValue();
                                    if ($cell1 == '1') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " Total Not Match";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                ////////////if rule type is row sum////////////////////////////
                                else if ($columnval['ruletype'] == 'row sum') {
                                    $nextcoln = $highestColumn;
                                    ++$nextcoln;
                                    $newnextcolumn = $nextcoln;
                                    ++$newnextcolumn;
                                    $rowsum = $this->sum_row($sheet, $nextcoln, $newnextcolumn, $i, $columnval['start_column'], $highestColumn, $coln);
                                    if ($rowsum == '1') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " Total Not for ROW";
                                        $a++;
                                        $data_error++;
                                    }
                                } else if ($columnval['ruletype'] == 'sum-row-column') {
                                    //echo "2";die;
                                    ++$endrrow;
                                    ++$endrow1;
                                    $calrow = $i + 1;
                                    $columnsum = $this->sum_column($sheet, $endrrow, $endrow1, $calrow, $columnval['match_column'], $coln, $highestRow, $columnval['value'], $i);
                                    if ($columnsum == '1') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " Total Not Match for Column";
                                        $a++;
                                        $data_error++;
                                    }
                                    $endrrow++;
                                    $endrow1++;
                                    $nextcoln = $highestColumn;
                                    ++$nextcoln;
                                    $newnextcolumn = $nextcoln;
                                    ++$newnextcolumn;
                                    $rowsum = $this->sum_row($sheet, $nextcoln, $newnextcolumn, $i, $columnval['start_column'], $highestColumn, $coln);
                                    if ($rowsum == '1') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " Total Not Match For Row";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                /////////////////////////////////////////////////////////////////
                                ///////////if rule type is sumif/////////////////////////////////
                                /* else if($columnval['ruletype']=='sumif'){
                                  $sheet->setCellValue($coln.'1', "=sumif(".$columnval['start_cell'].$i.":".$columnval['start_cell']."$highestRow".",".$columnval['value'].",".$coln."$i:".$coln."$highestRow)");
                                  $cell = $sheet->getCell($coln.'1')->getCalculatedValue();

                                  $sheet->setCellValue($coln.'2', "=(".$coln."$i<>".$coln."1)");
                                  $cell1 = $sheet->getCell($coln.'2')->getCalculatedValue();

                                  if($cell1=='1')
                                  {
                                  $error[$a]["data"]=$cell1;
                                  $error[$a]["error"]="Cell ".$coln.$i." Total Not Match";
                                  $a++;
                                  $data_error++;
                                  }
                                  } */
                                /////////////////////////////////////////////////////////////////
                                ///////////if rule type is string/////////////////////////////////
                                else if ($columnval['ruletype'] == 'string') {
                                    $arr1 = explode(',', $columnval['value']);
                                    if (in_array($cellval, $arr1)) {
                                        ########Do Nothing #################
                                    } else {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $i . " Should be From $columnval[value]";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                /////////////////////////////////////////////////////////////////
                                else {
                                    ###########no logic
                                }
                            } else {
                                ###########no logic
                            }
                        } else {
                            if ($cellval == '') {
                                #######do nothing
                            } else {
                                $error[$a]["data"] = $cellval;
                                $error[$a]["error"] = "Cell " . $coln . $i . " Should be Null";
                                $a++;
                                $data_error++;
                            }
                        }
                        ///////////////////////////////
                        //$coln++;
                    }
                } else {
                    $error[$a]["data"] = "Structure Error";
                    $error[$a]["error"] = $min_error;
                    $a++;
                    $structure_error++;
                }
            }
            //echo $ReportId['id'];die;		
            /////////////////////////////////////////////////////////////////////////////////
            //////////////loop for repeated column///////////////////////////////////////////////////////////////
            $dynamicdata = new Rowvalidaterule;
            $highestdynamictrow = $dynamicdata::where('report_id', $ReportId['id'])
                    ->where('is_range', '1')
                    ->max('row');
            //echo $highestdynamictrow."sdsad";die;
            $rowcount = new Validationrule;
            $columncount = $rowcount::where('report_no', $ReportId['id'])
                    ->where('rowno', $highestdynamictrow)
                    ->count('id');
            //echo $columncount;die;
            /////////loop for static column///////////////////////////////////////////////////////////////
            //echo $highestRow;
            for ($j = $i; $j <= $highestRow; $j++) {
                $ReportDataPart = 0;
                $ExtraLoopValueCol=0;
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '2048M');
                ini_set('max_memory', '2048M');
                ini_set('post_max_size', '64M');

                //echo $j."</br>";
                $min_error = $this->checkminColumn($sheet, $j, $highestColumn, '2');
                if ($min_error == '') {
                    $coln = 'A';
                    ////////////////Call Function to Check Maximum Excel Row Length///////////////////////
                    $highestcoln = $this->checkmaxColumn($sheet, $j, $highestColumn);
                    $resultValue = $this->createColumnsArray($highestcoln);
                    //while($coln<=$highestcoln){
                    $getConflictFlag = 0;
                    foreach ($resultValue as $coln) {
                        if($coln=='AC')
                            $x=0;
                        
                        
                        
                        
                        
                        if(((!empty($PreviousToMetric_TypeCol) && $coln>$PreviousToMetric_TypeCol)) &&  $coln<$MetricTypeColumn){
                                $ExtraLoopValueCol++;
                                $ReportDataPart=1;
                                
                            if($getConflictFlag==0){
                                $Confliction = $coln;
                                $getConflictFlag = 1;
                            }
                            
                            $cellval = $sheet->getcell($coln . $j);
                            //Filter Value Validation
                            //echo "<pre>";print_r($ReportFilterKeyAndIndex);
                            //echo "<pre>";print_r($ReportFilterValidation);die;
                            //echo "<pre>";print_r($ReportFilterValidation);die;
                            if(in_array($coln,$ReportFilterKeyAndIndex)){
                            $KeyOfFilter = array_search($coln, $ReportFilterKeyAndIndex);
                            $ValueOfFilter = $ReportFilterValidation[$KeyOfFilter];
                            if(!in_array($cellval,$ValueOfFilter)){
                                $error[$a]["data"] = $cellval;
                                $error[$a]["error"] = $cellval . " does not matched with Report_Filters in  " . $coln . $j . "";
                                $a++;
                                $data_error++;  
                            }
                            }
                            
                            
                            continue;
                        }
                        ////////Select from Database Rules Column Wise For Report/////////////////////////////////////////////
                        if($ExtraLoopValueCol>0 && $getConflictFlag==1){
                            $newColumn = $Confliction;
                            $xcount = 0;
                            while($newColumn<$coln){
                               $xcount++; 
                               $newColumn++;
                            }
                            $MaxDecrement = ($xcount-$ExtraLoopValueCol);
                            $valueofdata = $Confliction;
                             for($counti=0;$counti<$MaxDecrement;$counti++){
                                $valueofdata++; 
                             }
                             
                             $columnval = $rowcount::select('ruletype', 'value', 'required', 'is_range', 'start_column', 'match_column')
                                ->where('report_no', $ReportId['id'])
                                ->where('rowno', $highestdynamictrow)
                                ->where('colname', $valueofdata)
                                ->first();
                        
                        }else{
                          $columnval = $rowcount::select('ruletype', 'value', 'required', 'is_range', 'start_column', 'match_column')
                                ->where('report_no', $ReportId['id'])
                                ->where('rowno', $highestdynamictrow)
                                ->where('colname', $coln)
                                ->first(); 
                        }
                        
                        

                        if (!(isset($columnval['value'])) && ($i > $topMostMaxRowNo)) {
                            $columnval = $rowcount::select('ruletype', 'value', 'required', 'is_range', 'start_column', 'match_column')
                                    ->where('report_no', $ReportId['id'])
                                    ->where('rowno', $topMostMaxRowNo)
                                    ->where('colname', $coln)
                                    ->first();
                        }

                        $cellval = $sheet->getcell($coln . $j);
                        //Filter Value Validation
                        if(in_array($coln,$ReportFilterKeyAndIndex)){
                          $KeyOfFilter = array_search($coln, $ReportFilterKeyAndIndex);
                          $ValueOfFilter = $ReportFilterValidation[$KeyOfFilter];
                          if(!in_array($cellval,$ValueOfFilter)){
                                $error[$a]["data"] = $cellval;
                                $error[$a]["error"] = $cellval . " does not matched with Report_Filters in  " . $coln . $j . "";
                                $a++;
                                $data_error++;  
                          }
                        }
                        
                        
                        
                        

                        //checking metric type
                        if ($coln == $MetricTypeColumn && $i > 14) {
                            $MetricTypeValue = trim($cellval);
                            if (!in_array(strtolower($MetricTypeValue), $valueOfCellArray)) {
                                $error[$a]["data"] = $cellval;
                                $error[$a]["error"] = $cellval . " Metric type Cell " . $coln . $j . " does not matched with header Metric Type";
                                $a++;
                                $data_error++;
                            }
                        }


                        if ($columnval['is_range'] == '1') {
                            $startcell = $coln;
                            $lastcell = $highestcoln;
                            //////////////////////looping start///////////////////////////////////
                            while ($startcell <= $lastcell) {
                                $cellval = $sheet->getcell($startcell . $i);
                                //echo $startcell;
                                if ($columnval['required'] == '0') {
                                    //////////////if rule type is ISSN////////////////
                                    if ($columnval['ruletype'] == 'issn') {
                                        if ($cellval == '') {
                                            ########do nothing
                                        } else {
                                            $check = $this->issn(strtoupper($cellval));
                                            if ($check == true) {
                                                #########do nothig
                                            } else {
                                                $error[$a]["data"] = $cellval;
                                                $error[$a]["error"] = "Cell " . $startcell . $j . " should be valid ISSN";
                                                $a++;
                                                $data_error++;
                                            }
                                        }
                                    }
                                } else if ($columnval['required'] == '1') {
                                    if ($columnval['ruletype'] == '') {
                                        if ($cellval == '') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " should not blank";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    //////////////if ruletype is "Integer"////////////////////
                                    else if ($columnval['ruletype'] == 'integer') {
                                        $cellint = $sheet->getCell($startcell . $j)->getCalculatedValue();
                                        if (Is_numeric($cellint)) {
                                            ##do nothing############
                                        } else {
                                            $error[$a]["data"] = $cellint;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " should be Numeric";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    //////////////if ruletype is "text"////////////////////
                                    else if ($columnval['ruletype'] == 'text') {
                                        $string_error = $this->checkString($cellval, $columnval['value']);
                                        if ($string_error == 1) {
                                            $error[$b]["data"] = $cellval;
                                            $error[$b]["error"] = "Cell " . $startcell . $j . " contains no proper space";
                                            $data_error++;
                                            $b++;
                                        } else if ($string_error == 2) {
                                            if ($columnval['value'] == '') {
                                                $error_det = 'Null';
                                            } else {
                                                $error_det = $columnval['value'];
                                            }
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " should be $error_det";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    ///////////////////////////////////////////////////////
                                    //////////////if rule type is Date type////////////////
                                    else if ($columnval['ruletype'] == 'date_format') {
                                        if ($columnval['value'] == 'YYYY-MM-DD') {
                                            $getdate = $sheet->rangeToArray($startcell . $i . ':' . $startcell . $i, NULL, TRUE, FALSE);
                                            foreach ($getdate as $dateval) {
                                                $cellval = $dateval[0];
                                            }
                                            $x = '';
                                            $readDate = '';
                                            $date_month = explode("-", $cellval);
                                            $x = $this->checkDateType($cellval);
                                            if (is_numeric($cellval) and $cellval > 0) {
                                                $phpexcepDate = ($cellval) - 25569; //to offset to Unix epoch
                                                $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                            } else {
                                                $readDate = $cellval;
                                            }
                                            if ((count($date_month)) == '3') {
                                                if (!$x) {
                                                    $warning[$b]["data"] = $readDate;
                                                    $warning[$b]["error"] = "Cell " . $startcell . $i . " should be Proper " . $columnval['value'] . " Format";
                                                    $data_warning++;
                                                    $b++;
                                                } else {
                                                    
                                                }
                                            } else {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $startcell . $i . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            }
                                        } else if ($columnval['value'] == 'YYYY-MM-DD to YYYY-MM-DD') {
                                            $checkdate = $this->checkDateTypeTo($cellval);
                                            if ($checkdate == true) {
                                                
                                            } else {
                                                $warning[$b]["data"] = $cellval;
                                                $warning[$b]["error"] = "Cell " . $startcell . $j . " should contain " . $columnval['value'] . " format";
                                                $data_warning++;
                                                $b++;
                                            }
                                        } else {
                                            $getdate = $sheet->rangeToArray($startcell . $j . ':' . $startcell . $j, NULL, TRUE, FALSE);
                                            foreach ($getdate as $dateval) {
                                                $cellval = $dateval[0];
                                            }
                                            $x = '';
                                            $readDate = '';
                                            $date_month = explode("-", $cellval);
                                            $x = $this->validateDate($cellval);
                                            if (is_numeric($cellval) and $cellval > 0) {
                                                $phpexcepDate = ($cellval) - 25569; //to offset to Unix epoch
                                                $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                            } else {
                                                $readDate = $cellval;
                                            }
                                            if ((count($date_month)) == '2') {
                                                if (!$x) {
                                                    $warning[$b]["data"] = $readDate;
                                                    $warning[$b]["error"] = "Cell " . $startcell . $j . " should be Proper " . $columnval['value'] . " Format";
                                                    $data_warning++;
                                                    $b++;
                                                } else {
                                                    
                                                }
                                            } else {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $startcell . $j . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            }
                                        }
                                    }
                                    //////////////if rule type is Date type////////////////
                                    else if ($columnval['ruletype'] == 'issn') {
                                        $check = $this->issn(strtoupper($cellval));
                                        if ($check == true) {
                                            #########do nothig
                                        } else {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " should be valid ISSN";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    //////////////////////////////////////////////////////////
                                    /////////if rule type is ISBN//////////////////////////////
                                    else if ($columnval['ruletype'] == 'isbn') {
                                        $checkisbn = $this->ValidateIsbn($cellval);
                                        if ($checkisbn == false) {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " should be valid ISBN";
                                            $a++;
                                            $data_error++;
                                        } else {
                                            #########do nothing
                                        }
                                    }
                                    //////////////////////////////////////////////////////////
                                    ///////////////if rule type is sum////////////////////////
                                    else if ($columnval['ruletype'] == 'sum') {
                                        $k = $j + 1;
                                        $sheet->setCellValue($startcell . '1', "=sum(" . $startcell . "$k:" . $startcell . "$highestRow)");
                                        $cell = $sheet->getCell($startcell . '1')->getCalculatedValue();
                                        $sheet->setCellValue($startcell . '2', "=(" . $startcell . "$j<>" . $startcell . "1)");
                                        $cell1 = $sheet->getCell($startcell . '2')->getCalculatedValue();
                                        if ($cell1 == '1') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " Total Not Match";
                                            $a++;
                                            $data_error++;
                                        }
                                    } else if ($columnval['ruletype'] == 'row sum') {
                                        $nextcoln = $highestColumn;
                                        ++$nextcoln;
                                        $newnextcolumn = $nextcoln;
                                        ++$newnextcolumn;
                                        $rowsum = $this->sum_row($sheet, $nextcoln, $newnextcolumn, $j, $columnval['start_column'], $highestColumn, $startcell);
                                        if ($rowsum == '1') {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $j . " Total Not for ROW";
                                            $a++;
                                            $data_error++;
                                        }
                                    }

                                    ///////////if rule type is string/////////////////////////////////
                                    else if ($columnval['ruletype'] == 'string') {
                                        $arr1 = explode(',', $columnval['value']);
                                        if (in_array($cellval, $arr1)) {
                                            ########Do Nothing #################
                                        } else {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $startcell . $i . " Should be From $columnval[value]";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                    /////////////////////////////////////////////////////////////////
                                    else {
                                        ###########no logic
                                    }
                                }
                                $startcell++;
                            }
                            /////////////////////////////////////
                            break;
                        } else if ($columnval['is_range'] == '0') {
                            //echo "sds";
                            if ($columnval['required'] == '0') {
                                //////////////if rule type is Date type////////////////
                                if ($columnval['ruletype'] == 'issn') {
                                    if ($cellval == '') {
                                        ######do nothing
                                    } else {

                                        $check = $this->issn(strtoupper($cellval));
                                        if ($check == true) {
                                            #########do nothig
                                        } else {
                                            $error[$a]["data"] = $cellval;
                                            $error[$a]["error"] = "Cell " . $coln . $j . " should be valid ISSN";
                                            $a++;
                                            $data_error++;
                                        }
                                    }
                                }
                            } else if ($columnval['required'] == '1') {
                                if ($columnval['ruletype'] == '') {
                                    if ($cellval == '') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $j . " should not blank";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                //////////////if ruletype is "Integer"////////////////////
                                else if ($columnval['ruletype'] == 'integer') {
                                    $cellint = $sheet->getCell($coln . $j)->getCalculatedValue();
                                    if (Is_numeric($cellint)) {
                                        ##do nothing############
                                    } else {
                                        $error[$a]["data"] = $cellint;
                                        $error[$a]["error"] = "Cell " . $coln . $j . " should be Numeric";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                //////////////if ruletype is "text"////////////////////
                                else if ($columnval['ruletype'] == 'text') {


                                    $cellValueNew = $sheet->rangeToArray($coln . $j . ':' . $coln . $j, NULL, TRUE, FALSE);
                                    $cellValueNew = $cellValueNew[0][0] ?? '';
                                    $string_error = $this->checkstringMendatory($cellValueNew, $columnval['value']);
                                    //$string_error=$this->checkString($cellval,$columnval['value']);
                                    if ($string_error == 1) {
                                        $error[$b]["data"] = $cellval;
                                        $error[$b]["error"] = "Cell " . $coln . $j . " contains no proper space";
                                        $data_error++;
                                        $b++;
                                    } else if ($string_error == 2) {
                                        if ($columnval['value'] == '') {
                                            $error_det = 'Null';
                                        } else {
                                            $error_det = $columnval['value'];
                                        }
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $j . " should be $error_det";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                ///////////////////////////////////////////////////////
                                //////////////if rule type is Date type////////////////
                                else if ($columnval['ruletype'] == 'date_format') {
                                    if ($columnval['value'] == 'YYYY-MM-DD') {
                                        $getdate = $sheet->rangeToArray($coln . $i . ':' . $coln . $i, NULL, TRUE, FALSE);
                                        foreach ($getdate as $dateval) {
                                            $cellval = $dateval[0];
                                        }
                                        $x = '';
                                        $readDate = '';
                                        $date_month = explode("-", $cellval);
                                        $x = $this->checkDateType($cellval);
                                        if (is_numeric($cellval) and $cellval > 0) {
                                            $phpexcepDate = ($cellval) - 25569; //to offset to Unix epoch
                                            $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                        } else {
                                            $readDate = $cellval;
                                        }
                                        if ((count($date_month)) == '3') {
                                            if (!$x) {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $coln . $i . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            } else {
                                                
                                            }
                                        } else {
                                            $warning[$b]["data"] = $readDate;
                                            $warning[$b]["error"] = "Cell " . $coln . $i . " should be Proper " . $columnval['value'] . " Format";
                                            $data_warning++;
                                            $b++;
                                        }
                                    } else if ($columnval['value'] == 'YYYY-MM-DD to YYYY-MM-DD') {
                                        $checkdate = $this->checkDateTypeTo($cellval);
                                        if ($checkdate == true) {
                                            ########do nothing############
                                        } else {
                                            $warning[$b]["data"] = $cellval;
                                            $warning[$b]["error"] = "Cell " . $coln . $j . " should contain " . $columnval['value'] . " format";
                                            $data_warning++;
                                            $b++;
                                        }
                                    } else {
                                        $getdate = $sheet->rangeToArray($coln . $j . ':' . $coln . $j, NULL, TRUE, FALSE);
                                        foreach ($getdate as $dateval) {
                                            $cellval = $dateval[0];
                                        }
                                        $x = '';
                                        $readDate = '';
                                        $date_month = explode("-", $cellval);
                                        $x = $this->validateDate($cellval);
                                        if (is_numeric($cellval) and $cellval > 0) {
                                            $phpexcepDate = $cellval - 25569; //to offset to Unix epoch
                                            $readDate = date('m/d/Y', strtotime("+$phpexcepDate days", mktime(0, 0, 0, 1, 1, 1970)));
                                        } else {
                                            $readDate = $cellval;
                                        }
                                        if ((count($date_month)) == '2') {
                                            if (!$x) {
                                                $warning[$b]["data"] = $readDate;
                                                $warning[$b]["error"] = "Cell " . $coln . $j . " should be Proper " . $columnval['value'] . " Format";
                                                $data_warning++;
                                                $b++;
                                            } else {
                                                
                                            }
                                        } else {
                                            $warning[$b]["data"] = $readDate;
                                            $warning[$b]["error"] = "Cell " . $coln . $j . " should be Proper " . $columnval['value'] . " Format";
                                            $data_warning++;
                                            $b++;
                                        }
                                    }
                                }
                                //////////////if rule type is Date type////////////////
                                else if ($columnval['ruletype'] == 'issn') {


                                    $check = $this->issn(strtoupper($cellval));
                                    if ($check == true) {
                                        #########do nothig
                                    } else {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $j . " should be valid ISSN";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                /////////if rule type is ISBN//////////////////////////////
                                else if ($columnval['ruletype'] == 'isbn') {
                                    $checkisbn = $this->ValidateIsbn($cellval);

                                    if ($checkisbn == false) {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $j . " should be valid ISBN";
                                        $a++;
                                        $data_error++;
                                    } else {
                                        #########do nothing
                                    }
                                }
                                //////////////////////////////////////////////////////////
                                ///////////////if ruletype is row sum/////////////////////////
                                else if ($columnval['ruletype'] == 'row sum') {
                                    $nextcoln = $highestColumn;
                                    ++$nextcoln;
                                    $newnextcolumn = $nextcoln;
                                    ++$newnextcolumn;
                                    if($ReportDataPart==1){
                                        $StartIndex = $columnval['start_column'];
                                        for($counti=0;$counti<$ExtraLoopValueCol;$counti++){
                                            $StartIndex++;
                                        }
                                        
                                        $rowsum = $this->sum_row($sheet, $nextcoln, $newnextcolumn, $j, $StartIndex, $highestColumn, $coln);
                                    }else{
                                        $rowsum = $this->sum_row($sheet, $nextcoln, $newnextcolumn, $j, $columnval['start_column'], $highestColumn, $coln);
                                    }
                                            
                                    if ($rowsum == '1') {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $coln . $j . " Total Not for ROW";
                                        $a++;
                                        $data_error++;
                                    }
                                }

                                ///////////if rule type is string/////////////////////////////////
                                else if ($columnval['ruletype'] == 'string') {
                                    $arr1 = explode(',', $columnval['value']);
                                    if (in_array($cellval, $arr1)) {
                                        ########Do Nothing #################
                                    } else {
                                        $error[$a]["data"] = $cellval;
                                        $error[$a]["error"] = "Cell " . $startcell . $i . " Should be From $columnval[value]";
                                        $a++;
                                        $data_error++;
                                    }
                                }
                                else {
                                    ###########no logic
                                }
                            } else {
                                ###########no logic
                            }
                        } else if ($columnval['is_range'] == '2') {
                            if ($string_check == 0) {
                                $arr2 = explode(',', $columnval['value']);
                                $unique_arr_count = count($arr2);
                                for ($i = 0; $i < $unique_arr_count; $i++) {
                                    $unique_arr[$i] = 0;
                                }
                            }
                            ///////////if rule type is string/////////////////////////////////
                            if ($columnval['ruletype'] == 'stringcheck') {
                                $string_check = 1;
                                $arr1 = explode(',', $columnval['value']);
                                //print_r($arr1);
                                if (in_array($cellval, $arr1)) {
                                    $arr_index = array_search($cellval, $arr1);
                                    $unique_arr[$arr_index] = 1;
                                } else {
                                    $error[$a]["data"] = $cellval;
                                    $error[$a]["error"] = "Cell " . $coln . $j . " Should be From $columnval[value]";
                                    $a++;
                                    $data_error++;
                                }
                            }

                            /////////////////////////////////////////////////////////////////
                        } else {
                            if ($cellval == '') {
                                #######do nothing
                            } else {
                            }
                        }
                        ///////////////////////////////
                        //$coln++;
                    }
                } else {
                    $error[$a]["data"] = "Structure Error";
                    $error[$a]["error"] = $min_error;
                    $a++;
                    $structure_error++;
                }
            }
        }
        ////////////check if stringcheck enabled///////////////////////////////////////////////
        if ($string_check == 1) {
            for ($i = 0; $i < $unique_arr_count; $i++) {
                if ($unique_arr[$i] == 0) {
                    $error[$a]["data"] = "User Search Missing";
                    $error[$a]["error"] = $arr2[$i] . " missing for user activity";
                    $a++;
                    $data_error++;
                }
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////
        $data["warning"] = $warning;
        $data["error"] = $error;
        $data["structure_error"] = $structure_error;
        $data["data_error"] = $data_error;
        $data["structure_warning"] = $structure_warning;
        $data["data_warning"] = $data_warning;
        return $data;
    }

}