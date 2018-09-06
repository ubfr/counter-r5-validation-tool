<?php
namespace App\Http\Controllers;

use App\Allreportsname;
use App\Filename;
use App\User;
use App\Reportname;
use App\Validateerror;
use App\Validationrule;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Parentreport;
use App\Filtertype;
use App\Consortium;

class ShowController extends Controller
{

    public function __construct()
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('login');
        }
    }

    function checkview()
    {
        
        // dd('you are in after welcome');
        if (Session::has('user')) {
            
            $user = Session::get('user');
            // dd($user);
            // /////if usertype is user////////////////////
            
            return Redirect::intended('/filelist');
        } else {
            return view('login');
        }
    }

    
    function showvalidate()
    {
        
        // ///////////show file upload list/////////////
        $user = Session::get('user');
        
        $filename = Filename::where('user_id', $user['id'])->orderBy('id', 'desc')
            ->take(10)
            ->get();
        
       
           // $i=0;
      //  $FilterReports = Filtertype::where(array())->orderBy('id', 'asc')->get()->toArray();
       // foreach( $FilterReports as $filterReport)
      //  {
          //  $value[$i] ['name']=$filterReport['name'];
            //echo "<pre>";print_r($value); die;
          //  $i++;
       // } 
        // echo "<pre>";print_r($value); die;
        
        
     
        $AllReports = Reportname::where(array())->orderBy('report_name', 'asc')->get();
        // echo "<pre>sdfdf";print_r($AllReports);die;
        $data['reportsname'] = $AllReports;
        $AllParents = Parentreport::get()->toArray();
        // making sinle array for parent category
        foreach ($AllParents as $parentSingle) {
            $data['parentInfo'][$parentSingle['id']] = $parentSingle['name'];
        }
        
        // //////////////////////////////////////////////
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['file_detail'] = $filename;
        return view('welcome', $data);
    }

    //////////////// /////////////////
    
    function harvetsingvalidate()
    {
        $user = Session::get('user');
        
        
        
        $Consortiums = Consortium::where(array())->orderBy('configuration_name', 'asc')
        ->get();
        
        
        $data['consortium_config'] = $Consortiums;
        
        
        ////////////////////////////////////////////////
        $data['userDisplayName']= $user['display_name'];
        $data['utype']=$user['utype'];
        $data['file_detail']=$Consortiums;
        return view('consortium', $data);
    }
    
    /////////////////////////////////
    
    function saveConsortiumConfig()
    {
       
       //die("Hello"); 
       // print_r("dsfhudh");die;
       
        
        $data=Input::all();
        // echo "<pre>";print_r($data);die;
       
        // Applying validation rules.
        $rules = array(
            'configuration_name' => 'required',
            'remarks' =>'required', 
            'provider_name' =>'required',
            'provider_url' =>'required',
            'apikey' =>'required',
            'customer_id' =>'required',
            'requestor_id' =>'required',
        );
        
        $validator = Validator::make($data, $rules);
        if ($validator->fails()){
            
            // If validation fails redirect back to login.
            
            return Redirect::back()->withInput($data)->withErrors($validator,'consortium');
            
        } else {
            
            // die("cdsvj");
          
           // echo "<pre>";print_r($data);die;
            //unset($data['_token']);
            //echo "<pre>";print_r($data);die;
            // $data1 = array('remarks'=>'config');
             $newUser = Consortium::create($data);
            //die("Hello");
             if($newUser){
                
                 Session::flash('RegisterMsg', 'Configuration Added Successfully');
                 return $this->harvetsingvalidate();
             }
        }
    }
    
    //////////////////////////////////////

    // //////////Function For Report History//////////
    function showreport()
    {
        // ///////////show file upload list/////////////
        $user = Session::get('user');
        
        $filename = Filename::join('users', 'users.id', '=', 'filenames.user_id')->select('filenames.id', 'filenames.filename', 'filenames.file_type','filenames.report_name','filenames.report_id', 'filenames.filename', 'users.email')
            ->orderBy('id', 'desc')
            ->get();
        // echo "<pre>";print_r($filename);die;
        $AllSushiReports = Reportname::where(array())->orderBy('report_code', 'asc')
            ->take(100)
            ->get();
        $data['reportsname'] = $AllSushiReports;
        // echo "<pre>1234";print_r($AllSushiReports);die;
        // //////////////////////////////////////////////
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['file_detail'] = $filename;
        return view('report_history', $data);
    }
    
   
    // ///////////////////////////////////////////////
    
    
    function uploaded_report(){
        /////////////show file upload list/////////////
        $user = Session::get('user');
        
        $filename=Filename::join('users', 'users.id', '=', 'filenames.user_id')
        ->select('filenames.id','filenames.filename','filenames.file_type','filenames.report_name','filenames.report_id','filenames.filename','users.email')
        ->orderBy('id','desc')->get();
        //echo "<pre>";print_r($filename);die;
        // $AllSushiReports = Allreportsname::where(array())->orderBy('id','desc')->take(100)->get();
        // $data['sushireports']=$AllSushiReports;
        //echo "<pre>1234";print_r($AllSushiReport);die;
        ////////////////////////////////////////////////
        $data['userDisplayName']= $user['display_name'];
        $data['utype']=$user['utype'];
        $data['file_detail']=$filename;
        return view('Uploaded_report',$data);
    }
    
    
    // ///////////show Rule Management Page////////////
    function show_rule_manage()
    {
        $user = Session::get('user');
        // $reportname=Reportname::all();
        $reportname = Validationrule::all();
        
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['rule_detail'] = $reportname;
        return view('rule_manage_view', $data);
    }

    // ///////////////////////////////////////////////
    // /////////delete report///////////////////////////////
    function delete_report($id)
    {
         // echo "1";die;
        if (Session::has('user')) {
            
            $AllSushiReports = Reportname::where('id', $id)->delete();
            $Filedelete = Validateerror::where('id', $id)->delete();
            Session::flash('userdelmsg', 'Report successfully deleted');
            return Redirect::intended('/reporthistory');
            // echo "<pre>";print_r($id);die;
        }
    }

    // /////////delete uploaded report///////////////////////////////
    function delete_upload_report($id)
    {
   // echo "2";die;
        if (Session::has('user')) {
            //$user = Session::get('user');
            $AlluploadReports = Filename::where('id', $id)->delete();
            $Filedelete = Validateerror::where('id', $id)->delete();
            Session::flash('userupdatemsg', 'File successfully deleted');
            return Redirect::intended('/uploadedreports');
            // echo "<pre>";print_r($id);die;
        }
    }
    // ///////////////////edit report//////////////////////////////////
    function edit_report($id)
    {
        
        // echo "1".$id;die;
        if (Session::has('user')) {
            $user = Session::get('user');
            $Reportdetail = Reportname::select('id', 'report_name','report_code')->where('id', $id)->get();
            
            $data['utype'] = $user['utype'];
            $data['report_detail'] = $Reportdetail;
            $data['userDisplayName'] = $user['display_name'];
            
            // echo "<pre>";print_r($data);die;
            
            return view('edit_report', $data);
        } else {
            return Redirect::to('login');
        }
    }

    function update_report()
    {
        if (session::has('user')) {
            
            $data = Input::all();
            // echo "<pre>";print_r($data);die;
            $user = Session::get('user');
            $updatearray['report_name'] = $data['report_name'];
            $updatearray['report_code'] = $data['report_code'];
            $Reportdetail = Reportname::where('id', $data['Id'])->update($updatearray);
            
            $data['utype'] = $user['utype'];
            $data['userDisplayName'] = $user['display_name'];
            $data['report_detail'] = $Reportdetail;
            Session::flash('userupdatemsg', 'Report successfully updated');
            return Redirect::to('reporthistory');
        } else {
            return Redirect::to('login');
        }
    }
}



           





