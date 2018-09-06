<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App\Filename;
use App\Validateerror;
use App\Reportname;
use App\Validationrule;
use App\Parentreport;
use App\Rowvalidaterule;
use Validator;
Use Illuminate\Support\Facades\Session;
Use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
Use Illuminate\Auth\Passwords\TokenRepositoryInterface;

class RulemanagementController extends Controller

{
	public function __construct(){
		$user = Session::get('user');
		if($user['email']==''){
		   return Redirect::to('login');
		}
    }
    
    
	public function rulemanagement(){
		 if (Session::has('user')){
			 
		 $user = Session::get('user');
         $data['userDisplayName']= $user['display_name'];
         $data['utype']=$user['utype'];
         $AllReports = Reportname::where(array())->orderBy('report_code','asc')->get();
         //echo "<pre>sdfdf";print_r($AllReports);die;
         $data['reportsname']=$AllReports;
         $AllParents = Parentreport::get()->toArray();
         //making sinle array for parent category
         foreach($AllParents as $parentSingle){
             $data['parentInfo'][$parentSingle['id']]=$parentSingle['name'];
         }
         return view('rulemanagement',$data);
		 }
		 else{
			return Redirect::to('login');
		 }
	}
	
	
	public function ajaxCall($id){
	$AllRows = Rowvalidaterule::where(array())->where('report_id', '=', $id )->get();	
		 $i=1;
		foreach($AllRows as $rows){
			$arr[$i]=$rows->row;
			$i++;
		}
		$newarr=array();
		 for($i=1;$i<=count($arr);$i++){
			 $j=0;
			 $m=0;
			$validationrule=new Validationrule;
			$Allcolumn =$validationrule::where('rowno',$arr[$i])
						->where('report_no',$id)
						->get();
			foreach($Allcolumn as $colname1){
				$k[$i][$j]=$m;
				$m++;
			}
			
			foreach($Allcolumn as $colname){
			     if($colname->required=='1'){
					$required="Yes";
				}
				else{
					$required="No";
				}
				
				 if($colname->is_range=='1'){
					$is_range="Yes";
				}
				else{
					$is_range="No";
				}
				$delete='';
				$newarr[$i][$j][]=$colname->colname;
				$newarr[$i][$j][]=$colname->rowno;
				$newarr[$i][$j][]=$colname->ruletype??'';
				$newarr[$i][$j][]=$colname->value??'';
				$newarr[$i][$j][]=$required;
				$newarr[$i][$j][]=$is_range;
				$newarr[$i][$j][]=$colname->start_column??'';
				$newarr[$i][$j][]=$colname->match_column??'';
				//if($k[$i][0] !='0'){
			     //if($j==$k[$i][0]) {
					   $delete="<a class='fa fa-trash' onclick='show_confirm(".$colname->id.",".$id.");'>&nbsp;</a>";
					// }
				//}
				$newarr[$i][$j][]=$delete."<a  class='travel-pop fa fa-pencil-square-o' href='loadvalue/".$colname->id."'> </a>";
				$newarr[$i][$j][]=$colname->report_no;
				$j++;
			}	
		} 
	      print_r(json_encode($newarr));
	}
	
	
	public function getEditScreen($id){
  //echo $id;
	$data = array('name'=>'Vishal',
      'address'=>'aligarh',
      'newvalue'=>'3456',

      );
	$data['userDisplayName']= 'Rahul';
	$data['utype']= '1';
	return view('editrule',$data);
	}
	
	
	public function getColumnData($id){
		//echo "vishal";die;
		//echo $cid='2333';
		//echo $id;
		//$newarr=array();
		
		 $validationrule=new Validationrule;
		$columndata=$validationrule::where('id',$id)->get();
	  $i=0;
		//die("<b>I m here</b>");
		foreach($columndata as $colname){
			
				$newarr["id"]=$colname->id;
				$newarr["colname"]=$colname->colname;
				$newarr["rowno"]=$colname->rowno;
				$newarr["ruletype"]=$colname->ruletype??'';
				$newarr["value"]=$colname->value??'';
				$newarr["required"]=$colname->required??'';
				$newarr["report_no"]=$colname->report_no;
				$newarr["is_range"]=$colname->is_range;
				$newarr["start_column"]=$colname->start_column??'';
				$newarr["match_column"]=$colname->match_column??'';
			$i++;
		} 
		$data['newarr']=$newarr;
		//echo "<pre>";print_r($data);die;
		return view('editrule',$data);
		
	}
	

	public function updatecolumn($id){
		
		$user = Session::get('user');
		$data = Input::all();
		$validationrule=new Validationrule;
		$Columndetail=$validationrule::where('id',$id)
						->update( 
							array( 
								"colname" => $data['col_name'],
								"rowno" => $data['row_no'],
								"ruletype" => $data['rule_type']??'',
								"value" => $data['col_value']??'',
								"required" => $data['col_req'],
								"is_range" => $data['col_range'],
								"start_column" => $data['start_column']??'',
								"match_column" => $data['match_column']??'',
							)
						);
		$data['utype']=$user['utype'];
		$data['userDisplayName']= $user['display_name'];
		$data['user_detail']=$Columndetail;
		Session::flash('colupdatemsg', 'Column Record Successfully updated');
// 		Session::set('report_no', $data['report_no']);
		Session(['report_no'=>$data['report_no']]);
		//session()->put('report_no', $data[$report_no]);
		return Redirect::intended('rulemanagement');
		//return Redirect::route('rulemanagement', array('report_id' => 6));
	
	}
	
	
	public function addColumnView($row_no,$report_id){
		
		$validationrule=new Validationrule;
		$Columndetail=$validationrule::select('colname')
									->where('report_no',$report_id)
									->where('rowno',$row_no)
									->orderBy('colname', 'desc')
									->first();
		
		$newarr['id']=$row_no;
		$newarr['col_name']=$Columndetail['colname'];
		$data['newarr']=$newarr;
		return view('addrule',$data);
	}
	
	
	public function addnewcolumn($id){
		$newarr['id']=$id;
		$data = Input::all();
		$validationruleupdate=Validationrule::where('colname',$data['previous_col'])->where('rowno',$data['row_no'])->where('report_no',$data['report_no'])
						->update( 
							array( 
								"is_range" => 0
							)
						);
		$valdationruleinsert=Validationrule::insert(
												array(
													'colname'		=>	$data['col_name'],
													'rowno'			=>	$data['row_no'],
													'ruletype'		=>	$data['rule_type']??'',
													'value'			=>	$data['col_value']??'',
													'required'		=>	$data['col_req'],
													'report_no'		=>	$data['report_no'],
													'is_range'		=>	$data['col_range'],
													'start_column'	=>	$data['start_column']??'',
													'match_column'	=>	$data['match_column']??''
													)
												);
		//$data['newarr']=$newarr;
		//echo $data['report_no'];
		Session::flash('colupdatemsg', 'New Column Record Added Successfully');
		//Session::set('report_no', $data['report_no']);
		Session(['report_no'=>$data['report_no']]);
		
		return Redirect::intended('rulemanagement');
	}
	
	
	public function deleterowFuntion($id,$reportid){
		$validationrule=new Validationrule;
		$rowvalidationrule=new Rowvalidaterule;
		
		$rows = DB::table('validation_rules')->select('rowno')->where('id', '=', $id)->get();
		
		$rownumber=$rows[0]->rowno;
	    
		$row_count = DB::table('validation_rules')->select(DB::raw('count(*) as row_count'))->where('rowno',$rownumber)->where('report_no',$reportid)
		->get();	
        $row_count=$row_count[0]->row_count;		
	   
		//print_r($row_count);exit;	
		if($row_count < 2){
			$rowdelete=Rowvalidaterule::where('row',$rownumber)->where('report_id',$reportid)->delete();
			$newrownumber=$rownumber-1;
			$rowupdate=$rowvalidationrule::where('row',$newrownumber)->where('report_id',$reportid)
						->update( 
							array( 
								"is_range" => 1
							)
						);
		}
		 $Filedelete=Validationrule::where('id',$id)->delete();
		Session::flash('colupdatemsg', 'Column Record Successfully Deleted');
		//Session::set('report_no', $reportid);
		session()->put('report_no', $reportid);
		return Redirect::intended('rulemanagement');
	}
	
	
	public function is_lastcolumn(){
		$data = Input::all();
		//print_r($data);die;
		$validationrule=new Validationrule;
		$Columndetail=$validationrule::select('colname')
									->where('report_no',$data['report_no'])
									->where('rowno',$data['row_no'])
									->orderBy('colname', 'desc')
									->first();
		$lastColName  = $Columndetail['colname'];	
		echo $lastColName;
		
	    
		
	}
	
	
	
	public function addRowFuntion($reportno){
		$rowvalidationrule=new Rowvalidaterule;
		$Allcolumn =$rowvalidationrule::where('report_id',$reportno)
						->where('is_range',1)
						->get();
						$newarr=array();
						//echo"<pre>";print_r($Allcolumn);die;
		foreach($Allcolumn as $colname){
				$newarr["id"]=$colname->id;
				$newarr["row"]=$colname->row;
			
		} 
		
		$Columndetail=$rowvalidationrule::where('id',$newarr["id"])
						->update( 
							array( 
								"is_range" => 0
							)
						);
		
		$row=$newarr["row"]+1;
		$insert=DB::insert('insert into row_validate_rules (row,is_range,report_id) values(?,?,?)',[$row,'1',$reportno]);
		$insert=DB::insert('insert into validation_rules (colname,rowno,ruletype,value,required,report_no,is_range,start_column,match_column) values(?,?,?,?,?,?,?,?,?)',['A',$row,'','','0',$reportno,'0','','']);
		Session::flash('colupdatemsg', 'Row Added Successfully');
		//Session::set('report_no', $reportno);
		//Session(['report_no'=>['$reportno']]);
		session()->put('report_no', $reportno);
		
		return Redirect::intended('rulemanagement');
	} 
	
 }

