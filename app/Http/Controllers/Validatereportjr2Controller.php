<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
use PHPExcel_Cell_DefaultValueBinder;
use Illuminate\Support\Facades\Storage;

class Validatereportjr2Controller extends FilevalidateController
{
    static $valid=true;
	
	////////////////////validate file//////////////////////////////////
	
	function JournalReport2R4($sheet,$highestRow,$highestColumn,$error,$warning){
					$a=0;
					$b=0;
					$structure_error=0;
					$data_error=0;
					$structure_warning=0;
					$data_warning=0;
					//$abd=$sheet->rangeToArray('A1' . ':' . $highestColumn.$highestRow,NULL, TRUE, FALSE);
					//echo "<pre>";print_r($abd);
					//die;
					$rowData = $sheet->rangeToArray('A1' . ':' . 'B1',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=2;
					$row_no=1;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData as $detail)
						{
							$input_field=$detail['1'];
							$format_field='Access Denied to Full-text Articles by Month, Journal and Category';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail['1'];
								$warning[$b]["error"]="Cell B1 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail['1'];
								$error[$a]["error"]="Cell B1 should be Access Denied to Full-text Articles by Month, Journal and Category";
								$a++;
								$data_error++;
							}
							
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					///////////////////////////////////////////////////////////////////////////////////
					$column = 'C';
					while($column<=$highestColumn)
					{
						$cell = $sheet->getCell($column.'1');
						if($cell!='')
						{
							$error[$a]["data"]=$cell;
							$error[$a]["error"]="Cell ".$column.'1'." Should be Blank";
							$a++;
							$data_error++;
						}
						$column++;
					}
					$rowData2=$sheet->rangeToArray('A2' . ':' . 'B2',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=2;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData2 as $detail2){
							if($detail2['0']==''){
								$error[$a]["data"]=$detail2['0'];
								$error[$a]["error"]="Cell A2 should be Customer";
								$a++;
								$data_error++;
						   }else if(is_int($detail2['0'])){
								$error[$a]["data"]=$detai2['0'];
								$error[$a]["error"]="Cell A2 should be Customer";
								$a++;
								$data_error++;
							}else{
								
							}
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					$rowData3=$sheet->rangeToArray('A3' . ':' . 'B3',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=3;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData3 as $detail3){
							if($detail3['0']==''){
								$error[$a]["data"]=$detail3['0'];
								$error[$a]["error"]="Cell A3 should be Institute Identifier";
								$a++;
								$data_error++;
						   }else if(is_int($detail3['0'])){
								 $error[$a]["data"]=$detail3['0'];
							     $error[$a]["error"]="Cell A3 should be Institute Identifier";
								 $a++;
								 $data_error++;
						   }else{
							
						   }
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
					$rowData4=$sheet->rangeToArray('A4' . ':' . 'B4',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=4;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData4 as $detail4){
							$input_field=$detail4['0'];
							$format_field='Period covered by Report';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail4['0'];
								$warning[$b]["error"]="Cell A4 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail4['0'];
								$error[$a]["error"]="Cell A4 should be Period covered by Report";
								$a++;
								$data_error++;
							}
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
					$rowData5=$sheet->rangeToArray('A5' . ':' . 'B5',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=5;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData5 as $detail5){
							if($detail5[0]==''){
								$error[$a]["data"]=$detail5[0];
								$error[$a]["error"]="Cell A5 should not blank";
								$a++;
								$data_error++;
							}else{
							$dateval=explode(" ",$detail5[0]);
							//echo sizeof($dateval);
							//print_r($dateval);
							
							if(sizeof($dateval)!=3){
								$error[$a]["data"]=$detail5[0];
								$error[$a]["error"]="Cell A5 should be yyyy-mm-dd to yyyy-mm-dd";
								$a++;
								$data_error++;
							}else{ 
							$check1=$this->checkDateType($dateval[0]);
							$check2=$this->checkDateType($dateval[2]);
								if($check1==false || $check2==false){
								$error[$a]["data"]=$detail5[0];
								$error[$a]["error"]="Cell A5 should be yyyy-mm-dd date format";
								$a++;
								$data_error++;
							}else{
								
							}
							}
							}
							
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
					$rowData6=$sheet->rangeToArray('A6' . ':' . 'B6',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=6;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData6 as $detail6){
							if($detail6[0]==''){
								$error[$a]["data"]=$detail6[0];
								$error[$a]["error"]="Cell A6 should be Date run:";
								$a++;
								$data_error++;
							}
							else{
								$input_field=$detail6['0'];
								$format_field='Date run:';
								$string_error=$this->checkString($input_field,$format_field);
								if($string_error==1 )
								{
									$warning[$b]["data"]=$detail6['0'];
									$warning[$b]["error"]="Cell A6 contains no proper space";
									$data_warning++;
									$b++;
								}else if($string_error==2)
								{
									$error[$a]["data"]=$detail6['0'];
									$error[$a]["error"]="Cell A6 should be Date run:";
									$a++;
									$data_error++;
								}
							}
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
					$rowData7=$sheet->rangeToArray('A7' . ':' . 'B7',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=7;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData7 as $detail7){
							if($this->checkDateType($detail7[0])==false){
								$error[$a]["data"]=$detail7[0];
								$error[$a]["error"]="Cell A7 should be yyyy-mm-dd";
								$a++;
								$data_error++;
							}
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
						for($i=2;$i<=7;$i++)
						{
						$column = 'C';
						while($column<=$highestColumn)
						{
							
							$cell = $sheet->getCell($column.$i);
							if($cell!='')
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Should be Blank";
								$a++;
								$data_error++;
							}
							
							$column++;
						}
						}
						
					$rowData8=$sheet->rangeToArray('A8' . ':' .$highestColumn.'8',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=11;
					$row_no=8;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData8 as $detail8){
							$input_field=$detail8['0'];
							$format_field='Journal';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['0'];
								$warning[$b]["error"]="Cell A8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['0'];
								$error[$a]["error"]="Cell A8 should be Journal";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['1'];
							$format_field='Publisher';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['1'];
								$warning[$b]["error"]="Cell B8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['1'];
								$error[$a]["error"]="Cell B8 should be Publisher";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['2'];
							$format_field='Platform';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['2'];
								$warning[$b]["error"]="Cell C8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['2'];
								$error[$a]["error"]="Cell C8 should be Platform";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['3'];
							$format_field='Journal DOI';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['3'];
								$warning[$b]["error"]="Cell D8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['3'];
								$error[$a]["error"]="Cell D8 should be Journal DOI";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['4'];
							$format_field='Proprietary Identifier';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['4'];
								$warning[$b]["error"]="Cell E8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['4'];
								$error[$a]["error"]="Cell E8 should be Proprietary Identifier";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['5'];
							$format_field='Print ISSN';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['5'];
								$warning[$b]["error"]="Cell F8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['5'];
								$error[$a]["error"]="Cell F8 should be Print ISSN";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['6'];
							$format_field='Online ISSN';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['6'];
								$warning[$b]["error"]="Cell G8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['6'];
								$error[$a]["error"]="Cell G8 should be Online ISSN";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['7'];
							$format_field='Access Denied Category';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['7'];
								$warning[$b]["error"]="Cell H8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['7'];
								$error[$a]["error"]="Cell H8 should be Access Denied Category";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail8['8'];
							$format_field='Reporting Period Total';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail8['8'];
								$warning[$b]["error"]="Cell I8 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail8['8'];
								$error[$a]["error"]="Cell I8 should be Reporting Period Total";
								$a++;
								$data_error++;
							}
							
							$no_of_column=count($detail8);
							$column = 'J';
							while($column<=$highestColumn)
							{
								//echo $column;
							for($i=10;$i<$no_of_column;$i++)
							{
								$x='';
								$readDate='';
								
								$date_month=explode("-",$detail8[$i]);
								$x = $this->validateDate($detail8[$i]);
									//echo "adsfsdf<pre>".$x;
									//print_r($date_month);echo "<hr>";
									//return $x && $x->format('Y-m-d') === $date;
									//print_r($x);die;
									if(is_numeric($detail8[$i]) and $detail8[$i]>0){
									$phpexcepDate = $detail8[$i]-25569; //to offset to Unix epoch
									$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
								}else{
									$readDate=$detail8[$i];
								}
								//echo $date_month[0]."<br>";
								if((count($date_month))=='2')
								{
									//echo $readDate;
									
									if(!$x) 
									{
										$cell = $column.'8';
										//echo "12";die;
										
										$error[$a]["data"]=$readDate;
										$error[$a]["error"]="Cell $cell should be Proper MMM-YYYY Format";
										$a++;
										$data_error++;
										$column++;
									}
									else
									{
										$column++;
									}
								}
								else
								{
									$cell = $column.'8';
									//echo "123";die;
									$error[$a]["data"]=$readDate;
									$error[$a]["error"]="Cell $cell should be Proper MMM-YYYY Format";
									$a++;
									$data_error++;
									$column++;
								}
							}
							}
							//die;
						}
						//die;
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					//die;	
						
					$rowData9=$sheet->rangeToArray('A9' . ':' .$highestColumn.'9',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=9;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData9 as $detail9){
							$input_field=$detail9['0'];
							$format_field='Total for all journals';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail9['0'];
								$warning[$b]["error"]="Cell A9 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail9['0'];
								$error[$a]["error"]="Cell A9 should be Total for all journals";
								$a++;
								$data_error++;
							}
							
							if($detail9['1']==''){
								$error[$a]["data"]=$detail9['1'];
								$error[$a]["error"]="Cell B9 should not be Blank";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail9['2'];
							$format_field='Platform Z';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail9['2'];
								$warning[$b]["error"]="Cell C9 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail9['2'];
								$error[$a]["error"]="Cell C9 should be the name of the platform";
								$a++;
								$data_error++;
							}
							
							if($detail9['3']!=''){
								$error[$a]["data"]=$detail9['3'];
								$error[$a]["error"]="Cell D9 should be Blank";
								$a++;
								$data_error++;
							}
							if($detail9['4']!=''){
								$error[$a]["data"]=$detail9['4'];
								$error[$a]["error"]="Cell E9 should be Blank";
								$a++;
								$data_error++;
							}
							if($detail9['5']!=''){
								$error[$a]["data"]=$detail9['5'];
								$error[$a]["error"]="Cell F9 should be Blank";
								$a++;
								$data_error++;
							}
							if($detail9['6']!=''){
								$error[$a]["data"]=$detail9['6'];
								$error[$a]["error"]="Cell G9 should be Blank";
								$a++;
								$data_error++;
							}
							
							$column = 'F';
							while($column<='G')
							{
							for($i=10;$i<=$highestRow;$i++)
							{
							$cell = $sheet->getCell($column.$i);
							//echo $cell;
							$issn=explode('-',$cell);
							//print_r($issn);
							
							if((count($issn))=='2')
							{
								if(!Is_numeric($issn[0]) && !Is_numeric($issn[1]) )
								{
									$error[$a]["data"]=$cell;
									$error[$a]["error"]="Cell ".$column.$i." Should be numeric";
									$a++;
								$data_error++;
								}
							
							}
							elseif((count($issn))>'2')
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Contain More than One hypen";
								$a++;
								$data_error++;
							}
							else if($cell!='' && count($issn)<'2')
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Contain No hypen";
								$a++;
								$data_error++;
							}
							}
								$column++;
							}
							
							$input_field=$detail9['7'];
							$format_field='Access denied: concurrent/simultaneous user licence limit exceded';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail9['7'];
								$warning[$b]["error"]="Cell H9 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail9['7'];
								$error[$a]["error"]="Cell H9 should be the Access denied: concurrent/simultaneous user licence limit exceded";
								$a++;
								$data_error++;
							}
							
							$column = 'I';
							while($column<=$highestColumn)
							{
							for($i=9;$i<=$highestRow;$i++)
							{
							$cell = $sheet->getCell($column.$i)->getCalculatedValue();
							
							
							if(Is_numeric($cell))
							{
							}
							else
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Is not Numeric";
								$a++;
								$data_error++;
							}
							}
								$column++;
							}
							
							$column = 'I';
							while($column<=$highestColumn)
							{
							for($i=11;$i<=$highestRow;$i++)
							{
							$sheet->setCellValue($column.'7', "=sumif(".'H'."$i:".'H'."$highestRow,'Access denied: concurrent/simultaneous user licence limit exceded',".$column."$i:".$column."$highestRow)");
							$cell = $sheet->getCell($column.'7')->getCalculatedValue();
							
							$sheet->setCellValue($column.'6', "=(".$column."9<>".$column."7)");
							$cell1 = $sheet->getCell($column.'6')->getCalculatedValue();
							}
							if($cell1=='1')
							{
								$error[$a]["data"]=$cell1;
								$error[$a]["error"]="Cell ".$column.'9'." Total Not Match";
								$a++;
								$data_error++;
							}
							
								$column++;
							}
							
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
					$rowData10=$sheet->rangeToArray('A10' . ':' .$highestColumn.'10',NULL, TRUE, FALSE);
					////////////////Function to Check Minimum Required Row Length///////////////////////
					$min_limit=1;
					$row_no=10;
					$min_error=$this->checkminColumn($sheet,$row_no,$min_limit,$highestColumn);
					if($min_error=='')
					{
						foreach($rowData10 as $detail10){
							$input_field=$detail10['0'];
							$format_field='Total for all journals';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail10['0'];
								$warning[$b]["error"]="Cell A9 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail10['0'];
								$error[$a]["error"]="Cell A9 should be Total for all journals";
								$a++;
								$data_error++;
							}
							
							if($detail10['1']==''){
								$error[$a]["data"]=$detail10['1'];
								$error[$a]["error"]="Cell B9 should not be Blank";
								$a++;
								$data_error++;
							}
							
							$input_field=$detail10['2'];
							$format_field='Platform Z';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail10['2'];
								$warning[$b]["error"]="Cell C9 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail10['2'];
								$error[$a]["error"]="Cell C9 should be the name of the platform";
								$a++;
								$data_error++;
							}
							
							if($detail10['3']!=''){
								$error[$a]["data"]=$detail10['3'];
								$error[$a]["error"]="Cell D9 should be Blank";
								$a++;
								$data_error++;
							}
							if($detail10['4']!=''){
								$error[$a]["data"]=$detail10['4'];
								$error[$a]["error"]="Cell E9 should be Blank";
								$a++;
								$data_error++;
							}
							if($detail10['5']!=''){
								$error[$a]["data"]=$detail10['5'];
								$error[$a]["error"]="Cell F9 should be Blank";
								$a++;
								$data_error++;
							}
							if($detail10['6']!=''){
								$error[$a]["data"]=$detail10['6'];
								$error[$a]["error"]="Cell G9 should be Blank";
								$a++;
								$data_error++;
							}
							
							$column = 'F';
							while($column<='G')
							{
							for($i=10;$i<=$highestRow;$i++)
							{
							$cell = $sheet->getCell($column.$i);
							//echo $cell;
							$issn=explode('-',$cell);
							//print_r($issn);
							
							if((count($issn))=='2')
							{
								if(!Is_numeric($issn[0]) && !Is_numeric($issn[1]) )
								{
									$error[$a]["data"]=$cell;
									$error[$a]["error"]="Cell ".$column.$i." Should be numeric";
									$a++;
								$data_error++;
								}
							
							}
							elseif((count($issn))>'2')
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Contain More than One hypen";
								$a++;
								$data_error++;
							}
							else if($cell!='' && count($issn)<'2')
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Contain No hypen";
								$a++;
								$data_error++;
							}
							}
								$column++;
							}
							
							$input_field=$detail10['7'];
							$format_field='Access denied: concurrent/simultaneous user licence limit exceded';
							$string_error=$this->checkString($input_field,$format_field);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$detail10['7'];
								$warning[$b]["error"]="Cell H9 contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								$error[$a]["data"]=$detail10['7'];
								$error[$a]["error"]="Cell H9 should be the Access denied: concurrent/simultaneous user licence limit exceded";
								$a++;
								$data_error++;
							}
							
							$column = 'I';
							while($column<=$highestColumn)
							{
							for($i=9;$i<=$highestRow;$i++)
							{
							$cell = $sheet->getCell($column.$i)->getCalculatedValue();
							
							
							if(Is_numeric($cell))
							{
							}
							else
							{
								$error[$a]["data"]=$cell;
								$error[$a]["error"]="Cell ".$column.$i." Is not Numeric";
								$a++;
								$data_error++;
							}
							}
								$column++;
							}
							
							$column = 'I';
							$match_cell='Access denied: concurrent/simultaneous user licence limit exceded';
							while($column<=$highestColumn)
							{
							for($i=11;$i<=$highestRow;$i++)
							{
							$sheet->setCellValue($column.'7', "=sumif(".'H'."$i:".'H'."$highestRow,".$match_cell.",".$column."$i:".$column."$highestRow)");
							$cell = $sheet->getCell($column.'7')->getCalculatedValue();
							
							$sheet->setCellValue($column.'6', "=(".$column."9<>".$column."7)");
							$cell1 = $sheet->getCell($column.'6')->getCalculatedValue();
							}
							if($cell1=='1')
							{
								$error[$a]["data"]=$cell1;
								$error[$a]["error"]="Cell ".$column.'9'." Total Not Match";
								$a++;
								$data_error++;
							}
							
								$column++;
							}
							
						}
					}else{
						$error[$a]["data"]="Structure Error";
						$error[$a]["error"]=$min_error;
						$a++;
						$structure_error++;
					}
					
						
						
						for($i=11;$i<=$highestRow;$i++)
						{
							$column = 'A';
							while($column<=$highestColumn)
							{
								$cell = $sheet->getCell($column.$i)->getCalculatedValue();
								if($column=='D' || $column=='E'){
									
								}
								else{
								if($cell=='' && $cell!='0' )
									{
									$error[$a]["data"]=$cell;
									$error[$a]["error"]="Cell ".$column.$i." Should not be Null";
									$a++;
								$data_error++;
									} 
								}
								$column++;
							}
						}
						$data["warning"]=$warning;
						$data["error"]=$error;
						$data["structure_error"]=$structure_error;
						$data["data_error"]=$data_error;
						$data["structure_warning"]=$structure_warning;
						$data["data_warning"]=$data_warning;
						return $data;
						
			
					
	}
	

	function validateDate($date)
{
	$datavalue  = strtotime($date);
	//echo "Yes".$date."Hello".strtotime($date);
	$newDatevalue = date('M-Y',$datavalue);
	if($date==$newDatevalue){
			return 1;
	}else
		return '';
	//$format = 'M-Y';
    //$d = DateTime::createFromFormat($format, $date);
	
	//var_dump($format);
	//var_dump($date);
	//echo $date."infunciton".$d->format($format)."asdfsdf".$date;
    //$result = ($d && ($d->format($format) === $date));
	//echo "<pre>";print_r($d);
	//echo "asdfsdf##".$result."****";
}


}