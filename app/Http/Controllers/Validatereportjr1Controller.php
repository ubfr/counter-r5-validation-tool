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

class Validatereportjr1Controller extends FilevalidateController
{
    static $valid=true;
	////////////////////validate file//////////////////////////////////
	function JournalReport1R4($sheet,$highestRow,$highestColumn,$error,$warning){
	    ini_set('max_execution_time', 0);
	    ini_set('memory_limit', '2048M');
	    ini_set('max_memory', '2048M');
	   // die();
		$string_check=0;
		$a=0;
		$b=0;
		$structure_error=0;
		$data_error=0;
		$structure_warning=0;
		$data_warning=0;
		$topMostMaxRowNo = 15;
		$MetricTypeColumn = '';
		
		
			
		///////////////////////////////////////////////////////////////////////////////////
		////////Check from Database each Row is range or not///////////////////////////////
		//$getreport=$sheet->rangeToArray('A1' . ':' .'A1',NULL, TRUE, FALSE);
		
		//getting all matrix filter
		$FilterReports = Filtertype::where(array())->orderBy('id', 'asc')->get()->toArray();
		//converting single array
		$AllArrayOfMatrix = array();
		foreach ($FilterReports as $MatrixFilter){
		    $AllArrayOfMatrix[] = strtolower($MatrixFilter['name']);
		   
		}
		//echo "<pre>";print_r($AllArrayOfMatrix);die;
		
		$getreport=$sheet->rangeToArray('B1' . ':' .'B1',NULL, TRUE, FALSE);
		$Reportname1  =   $getreport[0][0]??'BLANK';
		$reportdata= new Reportname;
		$ReportId=$reportdata::select('id')
		->where('report_name',trim($Reportname1))
		->first();
				
		
		
		$getreportCode=$sheet->rangeToArray('B2' . ':' .'B2',NULL, TRUE, FALSE);
		$getreportCode =  $getreportCode[0][0]??'BLANK';
		$ReportIdForCode=$reportdata::select('id')
		->where('report_code',trim($getreportCode))
		->first();
		
////////////////////////////////////////report_attribute/////////////////////////////////////////////////////////
		
		$Headervalue = $Header=$sheet->rangeToArray('A14' . ':' .'Q14',NULL, TRUE, FALSE);
		
		$headerdataarray=$Headervalue[0];
		
		if($getreportCode=='DR'|| $getreportCode=='IR'||$getreportCode=='TR'|| $getreportCode=='PR'){
		   
		   $attributeValue = $attribute=$sheet->rangeToArray('B8' . ':' .'B8',NULL, TRUE, FALSE);
		    
		    $Cellvalue = $attributeValue[0][0]??'';
		    
		   
		    $CellArrayvalue =  explode('=', $Cellvalue);
		    
		    
		    $attribute=$CellArrayvalue[1];
		   
		    
		    $attributearrayvalue =  explode('|',  $attribute);
		   
		    $flagvalue=0;
		    $invalidattribute=array();
		    
		    
		    //die($attributearrayvalue);
		    
		    //echo "<pre>";print_r($flagvalue);die;
		    foreach($attributearrayvalue as $singleattributevalue){
		        $Searchingvalue= trim($singleattributevalue);
		        //echo "<pre>";print_r($Searchingvalue);die;
		        
		        if (!in_array($Searchingvalue,$headerdataarray)) {
		            $flagvalue=1;
		            $invalidattribute[]=$Searchingvalue;
		        }
		       // die('sadfa');
		        
		    }
		    if($flagvalue==0){
		        $error[$b]["data"]=implode(",",$invalidattribute);
		        $error[$b]["error"]="Platform columns are missing in Cell A14";
		        $b++;
		        $data_error++;
		    }
		    
		   
		}
		
	// echo "<pre>";print_r($error);die;
		
	
	

		//checking matrix type
		$MatrixValue = $MatrixFilter=$sheet->rangeToArray('B6' . ':' .'B6',NULL, TRUE, FALSE);
		
		
		$valueOfCell = $MatrixValue[0][0]??'';
		$valueOfCellArray = array_map("trim", explode(';', $valueOfCell));
		$valueOfCellArray = array_map("strtolower", $valueOfCellArray);
		//removing space
		$flageForMatrixfilter = 0;
		$invalidMetric = array();
		//echo "<pre>";print_r($AllArrayOfMatrix);
		//echo "<pre>";print_r($valueOfCellArray);die;
		foreach($valueOfCellArray as $singleValue){
		    $searchvalue = trim($singleValue);
		    if(!(in_array(strtolower($searchvalue), $AllArrayOfMatrix)) && !empty($searchvalue)){
		        $flageForMatrixfilter=1;
		        $invalidMetric[]=$searchvalue;
		    }
		       
		}
		  //echo "<pre>";print_r($valueOfCell);die;
		
		
		
		
		
		if($ReportIdForCode['id']==''){
		    $error[$b]["data"]=$getreportCode??'';
		    $error[$b]["error"]="Platform code in report does not match any registered platforms in Cell B2";
		    $b++;
		    $data_error++;
		}
		//echo "<pre>".$Reportname1;print_r($actulareport);die;
		  else if($ReportId['id']==''){
		    $error[$b]["data"]=$Reportname1;
		    $error[$b]["error"]="Platform name in report does not match any registered platforms in Cell B1";
		    $b++;
		    $data_error++;
		}
	/*	else if($flageForMatrixfilter==1){
		    $invalidMetric = implode(";",$invalidMetric);
		    $error[$b]["data"]=$invalidMetric;
		    $error[$b]["error"]="Metric Type in report does not match any registered metric in Cell B6";
		    $b++;
		    $data_error++;
		}*/
		else{
		    $data = new Rowvalidaterule;
		    DB::connection()->enableQueryLog();
		    $highesstatictrow=$data::where('report_id',$ReportId['id'])
		    ->where('is_range','0')
		    ->max('row');
		   // $queries = DB::getQueryLog();
		    //echo "<pre>12345";print_r($queries);die;
		   // echo $ReportId['id'];die;
		/////////loop for static column///////////////////////////////////////////////////////////////
		$endrrow=$highestRow+1;
		$endrow1=$highestRow+2;
		
                
                //getting all master data for maximum row
                //$AllMaxRow = Validationrule::select(array('report_no','colname'))
                $AllMaxRow = Validationrule::select(array('report_no','colname'))
                                            ->addSelect(DB::raw('max(rowno) as maxrow'))
                                            ->groupBy(array('report_no','colname'))
                                            ->get()->toArray();
                //setting array
                $allMaxRowData = array();
                foreach($AllMaxRow as $maxRowLen){
                    $allMaxRowData[$maxRowLen['report_no']][$maxRowLen['colname']] = $maxRowLen['maxrow'];
                }
               //print_r($allMaxRowData);die;
		for($i=1;$i<=$highesstatictrow;$i++){
                     //   if($i>=17)
                         //  $xx=00;
                                            
			////////Check from Database Rules For Report/////////////////////////////////////////////
			$rowcount=new Validationrule;
			$columncount=$rowcount::where('report_no',$ReportId['id'])
									->where('rowno',$i)
									->count('id');
			//$queries = DB::getQueryLog();
			//echo "<pre>12345";print_r($queries);die;
			$min_error=$this->checkminColumn($sheet,$i,$highestColumn,'1');
			if($min_error==''){
			$coln='A';
			////////////////Call Function to Check Maximum Excel Row Length///////////////////////
			$highestcoln=$this->checkmaxColumn($sheet,$i,$highestColumn);
			////////////////While Loop From First Cell To Last Cell of a Row//////////////////////
			while($coln<=$highestcoln){
                                $newRowNo = $i;
                                //check code for row data max value
                                if(isset($allMaxRowData[$ReportId['id']])){
                                    $maxcolumnvalue = $allMaxRowData[$ReportId['id']][$coln]??'';
                                    //if($i>=$maxcolumnvalue){
                                        //$newRowNo=$maxcolumnvalue;
                                    //}
                                }
                            
				////////Select from Database Rules Column Wise For Report/////////////////////////////////////////////
				$columnval=$rowcount::select('ruletype','value','required','is_range','start_column','match_column')
									->where('report_no',$ReportId['id'])
									->where('rowno',$i)
									//->where('rowno',$newRowNo)
									->where('colname',$coln)
									->first();
                                if(!(isset($columnval['value'])) && ($i>$topMostMaxRowNo)){
                                   $columnval=$rowcount::select('ruletype','value','required','is_range','start_column','match_column')
									->where('report_no',$ReportId['id'])
									->where('rowno',$topMostMaxRowNo)
									//->where('rowno',$newRowNo)
									->where('colname',$coln)
									->first(); 
                                }
                              
                                
                                //$queries = DB::getQueryLog();
				$cellval=$sheet->getcell($coln.$i);
				//echo $cellval;die;
				if(trim($cellval)=="Metric_Type" &&  $i==14 ){
				    $MetricTypeColumn=$coln;
				}
				
				//checking metric type
				if($coln==$MetricTypeColumn &&  $i>14){
				    $MetricTypeValue = trim($cellval);
				    if(!in_array(strtolower($MetricTypeValue),$valueOfCellArray))
				    {
				        $error[$a]["data"]=$cellval;
				        $error[$a]["error"]=$cellval." Metric type Cell ". $coln.$i ." does not matched with header Metric Type";
				        $a++;
				        $data_error++;
				    }
				}
				
				if($columnval['is_range']=='1'){
					//echo "sds";die;
					$startcell=$coln;
					$lastcell=$highestcoln;
					//////////////////////looping start///////////////////////////////////
					while($startcell<=$lastcell){
						$cellval=$sheet->getcell($startcell.$i);
						//echo $startcell;die;
						if($columnval['required']=='0'){
							//////////////if rule type is ISSN////////////////
							if($columnval['ruletype']=='issn'){
								if($cellval==''){
									########do nothing
								}else{
									$check=$this->issn(strtoupper($cellval));
									if($check==true){
										#########do nothing
									}else{
										$error[$a]["data"]=$cellval;
										$error[$a]["error"]="Cell ". $startcell.$i ." should be valid ISSN";
										$a++;
										$data_error++;
									}
								}
							}
						}else if($columnval['required']=='1'){
							if($columnval['ruletype']==''){
								if($cellval==''){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$i ." should not blank";
									$a++;
									$data_error++;
								}
							}
							//////////////if ruletype is "Integer"////////////////////
							else if($columnval['ruletype']=='integer'){
								$cellint = $sheet->getCell($startcell.$i)->getCalculatedValue();
								if(Is_numeric($cellint)){
									##do nothing############
								}else{
									$error[$a]["data"]=$cellint;
									$error[$a]["error"]="Cell ". $startcell.$i ." should be Numeric";
									$a++;
									$data_error++;
								}
							}
							//////////////if ruletype is "text"////////////////////
							else if($columnval['ruletype']=='text'){
								$string_error=$this->checkString($cellval,$columnval['value']);
								if($string_error==1 )
								{
								    $error[$b]["data"]=$cellval;
								    $error[$b]["error"]="Cell ". $startcell.$i ." contains no proper space";
									$data_error++;
									$b++;
								}else if($string_error==2)
								{
									if($columnval['value']==''){
										$error_det='Null';
									}else{
										$error_det=$columnval['value'];
									}
									
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$i ." should be $error_det";
									$a++;
									$data_error++;
								}
							}
							///////////////////////////////////////////////////////
							//////////////if rule type is Date type////////////////
							else if($columnval['ruletype']=='date_format'){
								if($columnval['value']=='YYYY-MM-DD'){
									//echo "1";die;
									$getdate=$sheet->rangeToArray($startcell.$i . ':' .$startcell.$i,NULL, TRUE, FALSE);
									foreach($getdate as $dateval){
										$cellval=$dateval[0];
									}
									//echo $cellval;
									$x='';
									$readDate='';
									$date_month=explode("-",$cellval);
									$x = $this->checkDateType($cellval);
									//echo "asdasdas";
									if(is_numeric($cellval) and $cellval>0){
										//echo "sdsada";
										 $phpexcepDate = ($cellval)-25569; //to offset to Unix epoch
										$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
									}else{
										$readDate=$cellval;
									}
									//echo $readDate;die;
									if((count($date_month))=='3'){
										if(!$x){
											$warning[$b]["data"]=$readDate;
											$warning[$b]["error"]="Cell ".$startcell.$i ." should be Proper ".$columnval['value'] ." Format";
											$data_warning++;
											$b++;
										}
										else{
										}
									}
									else{
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$startcell.$i ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
								}else if($columnval['value']=='YYYY-MM-DD to YYYY-MM-DD'){
									$checkdate=$this->checkDateTypeTo($cellval);
									if($checkdate==true){
									}else{
										$warning[$b]["data"]=$cellval;
										$pos = strpos($date,'Begin');
										if ($pos === true) 
										   $warning[$b]["error"]="Cell ".$startcell.$i ." should contain  Begin_Date=YYYY-MM-DD; End_Date=YYYY-MM-DD";
                                        else
										  $warning[$b]["error"]="Cell ".$startcell.$i ." should contain ".$columnval['value']." format";
										$data_warning++;
										$b++;
									}
								}else{
									$getdate=$sheet->rangeToArray($startcell.$i . ':' .$startcell.$i,NULL, TRUE, FALSE);
									foreach($getdate as $dateval){
										$cellval=$dateval[0];
									}
									//echo $cellval;
									$x='';
									$readDate='';
									$date_month=explode("-",$cellval);
									$x = $this->validateDate($cellval);
									//echo "asdasdas";
									if(is_numeric($cellval) and $cellval>0){
										//echo "sdsada";
										 $phpexcepDate = ($cellval)-25569; //to offset to Unix epoch
										$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
									}else{
										$readDate=$cellval;
									}
									//echo $readDate;die;
									if((count($date_month))=='2'){
										if(!$x){
											$warning[$b]["data"]=$readDate;
											$warning[$b]["error"]="Cell ".$startcell.$i ." should be Proper ".$columnval['value'] ." Format";
											$data_warning++;
											$b++;
										}
										else{
										}
									}
									else{
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$startcell.$i ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
								}
							}
							//////////////if rule type is ISSN////////////////
							else if($columnval['ruletype']=='issn'){
								$check=$this->issn(strtoupper($cellval));
								if($check==true){
									#########do nothing
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$i ." should be valid ISSN";
									$a++;
									$data_error++;
								}
							}
							////////////////////////////////////////////////////////////
							/////////if rule type is ISBN//////////////////////////////
							else if($columnval['ruletype']=='isbn'){
								$checkisbn=$this->ValidateIsbn($cellval);
								
								if($checkisbn==false){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$i ." should be valid ISBN";
									$a++;
									$data_error++;
								}else{
									#########do nothing
								}
							}
							//////////////////////////////////////////////////////////
							//////////////Sum Column Wise////////////////////////////////////////////
							else if($columnval['ruletype']=='sum' ){
								$j=$i+1;
								$sheet->setCellValue($startcell.'1', "=sum(".$startcell."$j:".$startcell."$highestRow)");
								$cell = $sheet->getCell($startcell.'1')->getCalculatedValue();
								$sheet->setCellValue($startcell.'2', "=(".$startcell."$i<>".$startcell."1)");
								$cell1 = $sheet->getCell($startcell.'2')->getCalculatedValue();
								if($cell1=='1'){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$startcell.$i." Total Not Match";
									$a++;
									$data_error++;
								}
							}else if($columnval['ruletype']=='row sum'){
								$nextcoln=$highestColumn;
								++$nextcoln;
								$newnextcolumn=$nextcoln;
								++$newnextcolumn;
								$rowsum=$this->sum_row($sheet,$nextcoln,$newnextcolumn,$i,$columnval['start_column'],$highestColumn,$startcell);
								if($rowsum=='1'){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$startcell.$i." Total Not for ROW";
									$a++;
									$data_error++;
								}
							}
							
							///////////if rule type is sumif/////////////////////////////////
							else if($columnval['ruletype']=='sumif'){
								$endrrow++;
								$endrow1++;
								$calrow=$i+1;
								$sheet->setCellValue($startcell.$endrrow, "=sumif(".$columnval['start_column'].$calrow.":".$columnval['start_column']."$highestRow".",".'"'.$columnval['value'].'"'.",".$startcell."$calrow:".$startcell."$highestRow)");
								$cell = $sheet->getCell($startcell.$endrrow)->getCalculatedValue();
								$sheet->setCellValue($startcell.$endrow1, "=(".$startcell."$i<>".$startcell."$endrrow)");
								$cell1 = $sheet->getCell($startcell.$endrow1)->getCalculatedValue();
								if($cell1=='1')
								{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$coln.$i." Total Not Match";
									$a++;
									$data_error++;
								}
								$endrrow++;
								$endrow1++;
							} 
							/////////////////////////////////////////////////////////////////
							///////////if rule type is string/////////////////////////////////
							else if($columnval['ruletype']=='string'){
								$arr1=explode(',',$columnval['value']);
								if(in_array($cellval,$arr1)){
									########Do Nothing #################
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$coln.$i." Should be From $columnval[value]";
									$a++;
									$data_error++;
								}
							}
						/////////////////////////////////////////////////////////////////
							else{
								###########no logic
							}
						}
						$startcell++;
					}
					//echo $startcell;die;
					//echo $startcell;die;
					/////////////////////////////////////
					break;
				}elseif($columnval['is_range']=='0'){
					//echo "sdas";die;
					if($columnval['required']=='0'){
						//////////////if rule type is ISSN////////////////
						if($columnval['ruletype']=='issn'){
							if($cellval==''){
								########do nothing
							}else{
								$check=$this->issn(strtoupper($cellval));
								if($check==true){
									#########do nothing
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $coln.$i ." should be valid ISSN";
									$a++;
									$data_error++;
								}
							}
						}
					}else if($columnval['required']=='1'){
						if($columnval['ruletype']==''){
							if($cellval==''){
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ". $coln.$i ." should not blank";
								$a++;
								$data_error++;
							}
						}
						//////////////if ruletype is "Integer"////////////////////
						else if($columnval['ruletype']=='integer'){
							$cellint = $sheet->getCell($coln.$i)->getCalculatedValue();
							if(Is_numeric($cellint)){
								##do nothing############
							}else{
								$error[$a]["data"]=$cellint;
								$error[$a]["error"]="Cell ". $coln.$i ." should be Numeric";
								$a++;
								$data_error++;
							}
						}
						//////////////if ruletype is "text"////////////////////
						else if($columnval['ruletype']=='text'){
							$string_error=$this->checkString($cellval,$columnval['value']);
							if($string_error==1 )
							{
								$warning[$b]["data"]=$cellval;
								$warning[$b]["error"]="Cell ". $coln.$i ." contains no proper space";
								$data_warning++;
								$b++;
							}else if($string_error==2)
							{
								if($columnval['value']==''){
										$error_det='Null';
									}else{
										$error_det=$columnval['value'];
									}
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ". $coln.$i ." should be $error_det";
								$a++;
								$data_error++;
							}
						}
						///////////////////////////////////////////////////////
						//////////////if rule type is Date type////////////////
						else if($columnval['ruletype']=='date_format'){
							if($columnval['value']=='YYYY-MM-DD'){
								
								$getdate=$sheet->rangeToArray($coln.$i . ':' .$coln.$i,NULL, TRUE, FALSE);
								foreach($getdate as $dateval){
										$cellval=$dateval[0];
									}
								$x='';
								$readDate='';
								$date_month=explode("-",$cellval);
								$x = $this->checkDateType($cellval);
								if(is_numeric($cellval) and $cellval>0){
									 $phpexcepDate = ($cellval)-25569; //to offset to Unix epoch
									$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
								}else{
									$readDate=$cellval;
								}
								if((count($date_month))=='3'){
									if(!$x){
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$coln.$i ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
									else{
									}
								}
								else{
									$warning[$b]["data"]=$readDate;
									$warning[$b]["error"]="Cell ".$coln.$i ." should be Proper ".$columnval['value'] ." Format";
									$data_warning++;
									$b++;
								}
							}else if($columnval['value']=='YYYY-MM-DD to YYYY-MM-DD'){
								$checkdate=$this->checkDateTypeTo($cellval);
								if($checkdate==true){
									########do nothing############
								}else{
									$warning[$b]["data"]=$cellval;
									$warning[$b]["error"]="Cell ".$coln.$i ." should contain ".$columnval['value']." format";
									$data_warning++;
									$b++;
								}
							}else{
								$getdate=$sheet->rangeToArray($coln.$i . ':' .$coln.$i,NULL, TRUE, FALSE);
								foreach($getdate as $dateval){
									$cellval=$dateval[0];
								}
								$x='';
								$readDate='';
								$date_month=explode("-",$cellval);
								$x=$this->validateDate($cellval);
								if(is_numeric($cellval) and $cellval>0){
									$phpexcepDate = $cellval-25569; //to offset to Unix epoch
									$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
								}else{
									$readDate=$cellval;
								}
								if((count($date_month))=='2'){
									if(!$x){
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$coln.$i ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
									else{
									}
								}
								else{
									$warning[$b]["data"]=$readDate;
									$warning[$b]["error"]="Cell ".$coln.$i ." should be Proper ".$columnval['value'] ." Format";
									$data_warning++;
									$b++;
								}
							}
						}
						//////////////if rule type is Date type////////////////
						else if($columnval['ruletype']=='issn'){
							$check=$this->issn(strtoupper($cellval));
							if($check==true){
								#########do nothig
							}else{
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ". $coln.$i ." should be valid ISSN";
								$a++;
								$data_error++;
							}
						}
						//////////////////////////////////////////////////////////////////////
						/////////if rule type is ISBN//////////////////////////////
							else if($columnval['ruletype']=='isbn'){
								$checkisbn=$this->ValidateIsbn($cellval);
								if($checkisbn==false){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $coln.$i ." should be valid ISBN";
									$a++;
									$data_error++;
								}else{
									#########do nothing
								}
							}
							//////////////////////////////////////////////////////////
							///////////////////if rule type is sum///////////////////
						else if($columnval['ruletype']=='sum'){
							$j=$i+1;
							$sheet->setCellValue($coln.'1', "=sum(".$coln."$j:".$coln."$highestRow)");
							$cell = $sheet->getCell($coln.'1')->getCalculatedValue();
							$sheet->setCellValue($coln.'2', "=(".$coln."$i<>".$coln."1)");
							$cell1 = $sheet->getCell($coln.'2')->getCalculatedValue();
							if($cell1=='1'){
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ".$coln.$i." Total Not Match";
								$a++;
								$data_error++;
							}
						}
						////////////if rule type is row sum////////////////////////////
						else if($columnval['ruletype']=='row sum'){
							$nextcoln=$highestColumn;
							++$nextcoln;
							$newnextcolumn=$nextcoln;
							++$newnextcolumn;
							$rowsum=$this->sum_row($sheet,$nextcoln,$newnextcolumn,$i,$columnval['start_column'],$highestColumn,$coln);
							if($rowsum=='1'){
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ".$coln.$i." Total Not for ROW";
								$a++;
								$data_error++;
							}
						}
						else if($columnval['ruletype']=='sum-row-column'){
								//echo "2";die;
								++$endrrow;
								++$endrow1;
								$calrow=$i+1;
								$columnsum=$this->sum_column($sheet,$endrrow,$endrow1,$calrow,$columnval['match_column'],$coln,$highestRow,$columnval['value'],$i);
								if($columnsum=='1')
								{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$coln.$i." Total Not Match for Column";
									$a++;
									$data_error++;
								}
								$endrrow++;
								$endrow1++;
								$nextcoln=$highestColumn;
								++$nextcoln;
								$newnextcolumn=$nextcoln;
								++$newnextcolumn;
								$rowsum=$this->sum_row($sheet,$nextcoln,$newnextcolumn,$i,$columnval['start_column'],$highestColumn,$coln);
								if($rowsum=='1'){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$coln.$i." Total Not Match For Row";
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
						else if($columnval['ruletype']=='string'){
							$arr1=explode(',',$columnval['value']);
							if(in_array($cellval,$arr1)){
								########Do Nothing #################
							}else{
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ".$coln.$i." Should be From $columnval[value]";
								$a++;
								$data_error++;
							}
						}
						/////////////////////////////////////////////////////////////////
						else{
							###########no logic
						}
					}else{
						###########no logic
					}	
				}else{
					if($cellval==''){
						#######do nothing
					}else{
						$error[$a]["data"]=$cellval;
						$error[$a]["error"]="Cell ".$coln.$i." Should be Null";
						$a++;
						$data_error++;
					}
				}
				///////////////////////////////
				$coln++;
			}
			}else{
				$error[$a]["data"]="Structure Error";
				$error[$a]["error"]=$min_error;
				$a++;
				$structure_error++;
			}
		}
		//echo $ReportId['id'];die;		
		/////////////////////////////////////////////////////////////////////////////////
		//////////////loop for repeated column///////////////////////////////////////////////////////////////
		$dynamicdata = new Rowvalidaterule;
	    $highestdynamictrow=$dynamicdata::where('report_id',$ReportId['id'])
										->where('is_range','1')
										->max('row');
										//echo $highestdynamictrow."sdsad";die;
		$rowcount=new Validationrule;
		$columncount=$rowcount::where('report_no',$ReportId['id'])
							->where('rowno',$highestdynamictrow)
							->count('id');
							//echo $columncount;die;
		/////////loop for static column///////////////////////////////////////////////////////////////
		
		//echo $highestRow;
		for($j=$i;$j<=$highestRow;$j++){
			//echo $j."</br>";
			$min_error=$this->checkminColumn($sheet,$j,$highestColumn,'2');
			if($min_error==''){
			$coln='A';
			////////////////Call Function to Check Maximum Excel Row Length///////////////////////
			$highestcoln=$this->checkmaxColumn($sheet,$j,$highestColumn);
				while($coln<=$highestcoln){
				$columnval=$rowcount::select('ruletype','value','required','is_range','start_column')
									->where('report_no',$ReportId['id'])
									->where('rowno',$highestdynamictrow)
									->where('colname',$coln)
									->first();
                                
                                if(!(isset($columnval['value'])) && ($i>$topMostMaxRowNo)){
                                   $columnval=$rowcount::select('ruletype','value','required','is_range','start_column','match_column')
									->where('report_no',$ReportId['id'])
									->where('rowno',$topMostMaxRowNo)
									//->where('rowno',$newRowNo)
									->where('colname',$coln)
									->first(); 
                                }
                                
				$cellval=$sheet->getcell($coln.$j);
				
				//checking metric type
				if($coln==$MetricTypeColumn &&  $i>14){
				    $MetricTypeValue = trim($cellval);
				    if(!in_array(strtolower($MetricTypeValue),$valueOfCellArray))
				    {
				        $error[$a]["data"]=$cellval;
				        $error[$a]["error"]=$cellval." Metric type Cell ". $coln.$j ." does not matched with header Metric Type";
				        $a++;
				        $data_error++;
				    }
				}
				
				
				if($columnval['is_range']=='1'){
					$startcell=$coln;
					$lastcell=$highestcoln;
					//echo $columnval['is_range'];
					//////////////////////looping start///////////////////////////////////
					while($startcell<=$lastcell){
						$cellval=$sheet->getcell($startcell.$i);
						//echo $startcell;
						if($columnval['required']=='0'){
							//////////////if rule type is ISSN////////////////
							if($columnval['ruletype']=='issn'){
								if($cellval==''){
									########do nothing
								}else{
									$check=$this->issn(strtoupper($cellval));
									if($check==true){
										#########do nothig
									}else{
										$error[$a]["data"]=$cellval;
										$error[$a]["error"]="Cell ". $startcell.$j ." should be valid ISSN";
										$a++;
										$data_error++;
									}
								}
							}
						}else if($columnval['required']=='1'){
							if($columnval['ruletype']==''){
								if($cellval==''){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$j ." should not blank";
									$a++;
									$data_error++;
								}
							}
							//////////////if ruletype is "Integer"////////////////////
							else if($columnval['ruletype']=='integer'){
								$cellint = $sheet->getCell($startcell.$j)->getCalculatedValue();
								if(Is_numeric($cellint)){
									##do nothing############
								}else{
									$error[$a]["data"]=$cellint;
									$error[$a]["error"]="Cell ". $startcell.$j ." should be Numeric";
									$a++;
									$data_error++;
								}
							}
							//////////////if ruletype is "text"////////////////////
							else if($columnval['ruletype']=='text'){
								$string_error=$this->checkString($cellval,$columnval['value']);
								if($string_error==1 )
								{
								    $error[$b]["data"]=$cellval;
								    $error[$b]["error"]="Cell ". $startcell.$j ." contains no proper space";
									$data_error++;
									$b++;
								}else if($string_error==2)
								{
									if($columnval['value']==''){
										$error_det='Null';
									}else{
										$error_det=$columnval['value'];
									}
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$j ." should be $error_det";
									$a++;
									$data_error++;
								}
							}
							///////////////////////////////////////////////////////
							//////////////if rule type is Date type////////////////
							else if($columnval['ruletype']=='date_format'){
								if($columnval['value']=='YYYY-MM-DD'){
									$getdate=$sheet->rangeToArray($startcell.$i . ':' .$startcell.$i,NULL, TRUE, FALSE);
									foreach($getdate as $dateval){
										$cellval=$dateval[0];
									}
									//echo $cellval;
									$x='';
									$readDate='';
									$date_month=explode("-",$cellval);
									$x = $this->checkDateType($cellval);
									//echo "asdasdas";
									if(is_numeric($cellval) and $cellval>0){
										//echo "sdsada";
										 $phpexcepDate = ($cellval)-25569; //to offset to Unix epoch
										$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
									}else{
										$readDate=$cellval;
									}
									//echo $readDate;die;
									if((count($date_month))=='3'){
										if(!$x){
											$warning[$b]["data"]=$readDate;
											$warning[$b]["error"]="Cell ".$startcell.$i ." should be Proper ".$columnval['value'] ." Format";
											$data_warning++;
											$b++;
										}
										else{
										}
									}
									else{
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$startcell.$i ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
								}else if($columnval['value']=='YYYY-MM-DD to YYYY-MM-DD'){
									$checkdate=$this->checkDateTypeTo($cellval);
									if($checkdate==true){
									}else{
										$warning[$b]["data"]=$cellval;
										$warning[$b]["error"]="Cell ".$startcell.$j ." should contain ".$columnval['value']." format";
										$data_warning++;
										$b++;
									}
								}else{
									$getdate=$sheet->rangeToArray($startcell.$j . ':' .$startcell.$j,NULL, TRUE, FALSE);
									foreach($getdate as $dateval){
										$cellval=$dateval[0];
									}
									//echo $cellval;
									$x='';
									$readDate='';
									$date_month=explode("-",$cellval);
									$x = $this->validateDate($cellval);
									//echo "asdasdas";
									if(is_numeric($cellval) and $cellval>0){
										//echo "sdsada";
										$phpexcepDate = ($cellval)-25569; //to offset to Unix epoch
										$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
									}else{
										$readDate=$cellval;
									}
									//echo $readDate;die;
									if((count($date_month))=='2'){
										if(!$x){
											$warning[$b]["data"]=$readDate;
											$warning[$b]["error"]="Cell ".$startcell.$j ." should be Proper ".$columnval['value'] ." Format";
											$data_warning++;
											$b++;
										}
										else{
										}
									}
									else{
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$startcell.$j ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
								}
							}
							//////////////if rule type is Date type////////////////
							else if($columnval['ruletype']=='issn'){
								$check=$this->issn(strtoupper($cellval));
								if($check==true){
									#########do nothig
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$j ." should be valid ISSN";
									$a++;
									$data_error++;
								}
							}
							//////////////////////////////////////////////////////////
							/////////if rule type is ISBN//////////////////////////////
							else if($columnval['ruletype']=='isbn'){
								$checkisbn=$this->ValidateIsbn($cellval);
								if($checkisbn==false){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $startcell.$j ." should be valid ISBN";
									$a++;
									$data_error++;
								}else{
									#########do nothing
								}
							}
							//////////////////////////////////////////////////////////
							///////////////if rule type is sum////////////////////////
							else if($columnval['ruletype']=='sum'){
								$k=$j+1;
								$sheet->setCellValue($startcell.'1', "=sum(".$startcell."$k:".$startcell."$highestRow)");
								$cell = $sheet->getCell($startcell.'1')->getCalculatedValue();
								$sheet->setCellValue($startcell.'2', "=(".$startcell."$j<>".$startcell."1)");
								$cell1 = $sheet->getCell($startcell.'2')->getCalculatedValue();
								if($cell1=='1'){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$startcell.$j." Total Not Match";
									$a++;
									$data_error++;
								}
							}else if($columnval['ruletype']=='row sum'){
								$nextcoln=$highestColumn;
								++$nextcoln;
								$newnextcolumn=$nextcoln;
								++$newnextcolumn;
								$rowsum=$this->sum_row($sheet,$nextcoln,$newnextcolumn,$j,$columnval['start_column'],$highestColumn,$startcell);
								if($rowsum=='1'){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$startcell.$j." Total Not for ROW";
									$a++;
									$data_error++;
								}
							}
							
							///////////if rule type is string/////////////////////////////////
							else if($columnval['ruletype']=='string'){
								$arr1=explode(',',$columnval['value']);
								if(in_array($cellval,$arr1)){
									########Do Nothing #################
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ".$startcell.$i." Should be From $columnval[value]";
									$a++;
									$data_error++;
								}
							}
							/////////////////////////////////////////////////////////////////
							else{
								###########no logic
							}
						}
						$startcell++;
					}
					/////////////////////////////////////
					break;
				}else if($columnval['is_range']=='0'){
					//echo "sds";
					if($columnval['required']=='0'){
						//////////////if rule type is Date type////////////////
						 if($columnval['ruletype']=='issn'){
							if($cellval==''){
								######do nothing
							}else{
								
								$check=$this->issn(strtoupper($cellval));
								if($check==true){
									#########do nothig
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $coln.$j ." should be valid ISSN";
									$a++;
									$data_error++;
								}
							}
						}
					}else if($columnval['required']=='1'){
						if($columnval['ruletype']==''){
							if($cellval==''){
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ". $coln.$j ." should not blank";
								$a++;
								$data_error++;
							}
						}
						//////////////if ruletype is "Integer"////////////////////
							else if($columnval['ruletype']=='integer'){
								$cellint = $sheet->getCell($coln.$j)->getCalculatedValue();
								if(Is_numeric($cellint)){
									##do nothing############
								}else{
									$error[$a]["data"]=$cellint;
									$error[$a]["error"]="Cell ". $coln.$j ." should be Numeric";
									$a++;
									$data_error++;
								}
							}
						//////////////if ruletype is "text"////////////////////
						else if($columnval['ruletype']=='text'){
							$string_error=$this->checkString($cellval,$columnval['value']);
							if($string_error==1 )
							{
							    $error[$b]["data"]=$cellval;
							    $error[$b]["error"]="Cell ". $coln.$j ." contains no proper space";
							    $data_error++;
								$b++;
							}else if($string_error==2)
							{
								if($columnval['value']==''){
										$error_det='Null';
									}else{
										$error_det=$columnval['value'];
									}
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ". $coln.$j ." should be $error_det";
								$a++;
								$data_error++;
							}
						}
						///////////////////////////////////////////////////////
						//////////////if rule type is Date type////////////////
						else if($columnval['ruletype']=='date_format'){
							if($columnval['value']=='YYYY-MM-DD'){
								$getdate=$sheet->rangeToArray($coln.$i . ':' .$coln.$i,NULL, TRUE, FALSE);
									foreach($getdate as $dateval){
										$cellval=$dateval[0];
									}
									//echo $cellval;
									$x='';
									$readDate='';
									$date_month=explode("-",$cellval);
									$x = $this->checkDateType($cellval);
									//echo "asdasdas";
									if(is_numeric($cellval) and $cellval>0){
										//echo "sdsada";
										 $phpexcepDate = ($cellval)-25569; //to offset to Unix epoch
										$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
									}else{
										$readDate=$cellval;
									}
									//echo $readDate;die;
									if((count($date_month))=='3'){
										if(!$x){
											$warning[$b]["data"]=$readDate;
											$warning[$b]["error"]="Cell ".$coln.$i ." should be Proper ".$columnval['value'] ." Format";
											$data_warning++;
											$b++;
										}
										else{
										}
									}
									else{
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$coln.$i ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
							}else if($columnval['value']=='YYYY-MM-DD to YYYY-MM-DD'){
								$checkdate=$this->checkDateTypeTo($cellval);
								if($checkdate==true){
									########do nothing############
								}else{
									$warning[$b]["data"]=$cellval;
									$warning[$b]["error"]="Cell ".$coln.$j ." should contain ".$columnval['value']." format";
									$data_warning++;
									$b++;
								}
							}else{
								$getdate=$sheet->rangeToArray($coln.$j . ':' .$coln.$j,NULL, TRUE, FALSE);
								foreach($getdate as $dateval){
									$cellval=$dateval[0];
								}
								$x='';
								$readDate='';
								$date_month=explode("-",$cellval);
								$x=$this->validateDate($cellval);
								if(is_numeric($cellval) and $cellval>0){
									$phpexcepDate = $cellval-25569; //to offset to Unix epoch
									$readDate=date('m/d/Y',strtotime("+$phpexcepDate days", mktime(0,0,0,1,1,1970)));
								}else{
									$readDate=$cellval;
								}
								if((count($date_month))=='2'){
									if(!$x){
										$warning[$b]["data"]=$readDate;
										$warning[$b]["error"]="Cell ".$coln.$j ." should be Proper ".$columnval['value'] ." Format";
										$data_warning++;
										$b++;
									}
									else{
									}
								}
								else{
									$warning[$b]["data"]=$readDate;
									$warning[$b]["error"]="Cell ".$coln.$j ." should be Proper ".$columnval['value'] ." Format";
									$data_warning++;
									$b++;
								}
							}
						}
						//////////////if rule type is Date type////////////////
						else if($columnval['ruletype']=='issn'){
							
								
								$check=$this->issn(strtoupper($cellval));
								if($check==true){
									#########do nothig
								}else{
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $coln.$j ." should be valid ISSN";
									$a++;
									$data_error++;
								}
							
						}
						/////////if rule type is ISBN//////////////////////////////
							else if($columnval['ruletype']=='isbn'){
								$checkisbn=$this->ValidateIsbn($cellval);
								
								if($checkisbn==false){
									$error[$a]["data"]=$cellval;
									$error[$a]["error"]="Cell ". $coln.$j ." should be valid ISBN";
									$a++;
									$data_error++;
								}else{
									#########do nothing
								}
							}
							//////////////////////////////////////////////////////////
							///////////////if ruletype is row sum/////////////////////////
						else if($columnval['ruletype']=='row sum'){
							$nextcoln=$highestColumn;
							++$nextcoln;
							$newnextcolumn=$nextcoln;
							++$newnextcolumn;
							$rowsum=$this->sum_row($sheet,$nextcoln,$newnextcolumn,$j,$columnval['start_column'],$highestColumn,$coln);
							if($rowsum=='1'){
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ".$coln.$j." Total Not for ROW";
								$a++;
								$data_error++;
							}
						}
						
						///////////if rule type is string/////////////////////////////////
						else if($columnval['ruletype']=='string'){
							$arr1=explode(',',$columnval['value']);
							if(in_array($cellval,$arr1)){
								########Do Nothing #################
							}else{
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ".$startcell.$i." Should be From $columnval[value]";
								$a++;
								$data_error++;
							}
						}
						/////////////////////////////////////////////////////////////////
						/* ///////////if rule type is string/////////////////////////////////
						else if($columnval['ruletype']=='stringcheck'){
							$string_check=1;
							$arr1=explode(',',$columnval['value']);
							//print_r($arr1);
							if(in_array($cellval,$arr1)){
								
								$arr_index=array_search($cellval,$arr1);
								$unique_arr[$arr_index]=1;
							}else{
								$error[$a]["data"]=$cellval;
								$error[$a]["error"]="Cell ".$startcell.$i." Should be From $columnval[value]";
								$a++;
								$data_error++;
							}	
							
							
						}
						///////////////////////////////////////////////////////////////// */
						else{
							###########no logic
						}
					}else{
						###########no logic
					}
				}else if($columnval['is_range']=='2'){
					if($string_check==0)
					{
						$arr2=explode(',',$columnval['value']);
						$unique_arr_count=count($arr2);
						for($i=0;$i<$unique_arr_count;$i++){
							$unique_arr[$i]=0;
						}
					}
					///////////if rule type is string/////////////////////////////////
					if($columnval['ruletype']=='stringcheck'){
						$string_check=1;
						$arr1=explode(',',$columnval['value']);
						//print_r($arr1);
						if(in_array($cellval,$arr1)){
							$arr_index=array_search($cellval,$arr1);
							$unique_arr[$arr_index]=1;
						}else{
							$error[$a]["data"]=$cellval;
							$error[$a]["error"]="Cell ".$coln.$j." Should be From $columnval[value]";
							$a++;
							$data_error++;
						}
						
					}
					
					/////////////////////////////////////////////////////////////////
				}
				else{
					if($cellval==''){
						#######do nothing
					}else{
						$error[$a]["data"]=$cellval;
						$error[$a]["error"]="Cell ".$coln.$j." Should be Null";
						$a++;
						$data_error++;
					}
				}
				///////////////////////////////
				$coln++;
			}
			}else{
				$error[$a]["data"]="Structure Error";
				$error[$a]["error"]=$min_error;
				$a++;
				$structure_error++;
			}
		}
		}
		////////////check if stringcheck enabled///////////////////////////////////////////////
		if($string_check==1){
			for($i=0;$i<$unique_arr_count;$i++){
				if($unique_arr[$i]==0){
					$error[$a]["data"]="User Search Missing";
					$error[$a]["error"]=$arr2[$i]." missing for user activity";
					$a++;
					$data_error++;
				}
			}
		}
		
		///////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		//echo $asdas;
		//die;
		$data["warning"]=$warning;
		$data["error"]=$error;
		$data["structure_error"]=$structure_error;
		$data["data_error"]=$data_error;
		$data["structure_warning"]=$structure_warning;
		$data["data_warning"]=$data_warning;
		return $data;
	}
}