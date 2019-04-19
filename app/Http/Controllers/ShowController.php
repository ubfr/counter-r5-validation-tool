<?php
namespace App\Http\Controllers;

use DateTime;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Consortium;
use App\Members;
use App\Provider;
use App\Reportname;
use App\Transactionmasterdetail;
use App\Storedfile;
use App\User;

class ShowController extends Controller {
    
    public $TransctionIdCurrent=0;

    public function __construct() {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('login');
        }
    }

    function checkview() {

        // dd('you are in after welcome');
        if (Session::has('user')) {

            $user = Session::get('user');
            // dd($user);
            // /////if usertype is user////////////////////

            return Redirect::intended('/filelist');
        } else {
            return view('index');
        }
    }

    // ///////////show file upload list/////////////
    function showvalidate() {
        // ///////////show file upload list/////////////
        $user = Session::get('user');

        $context = Input::get('context');
        if(!in_array($context, [ 'file', 'sushi'])) {
            $context = 'file';
        }
        
        $fileReports = Storedfile::with('reportfile', 'reportfile.checkresult')->where('user_id', $user['id'])
            ->where('source', Storedfile::SOURCE_FILE_VALIDATE)
            ->where('type', Storedfile::TYPE_REPORT)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $sushiReports = Storedfile::with('reportfile', 'reportfile.checkresult')->where('user_id', $user['id'])
            ->where('source', Storedfile::SOURCE_SUSHI_VALIDATE)
            ->where('type', Storedfile::TYPE_REPORT)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $data = [];
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['fileReports'] = $fileReports;
        $data['sushiReports'] = $sushiReports;
        $data['context'] = $context;

        return view('welcome', $data);
    }

    function downloadExcelConfig($configurationId='')
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        
       
        $Configurations = Provider::select('configuration_id','provider_name','provider_url','apikey','requestor_id','customer_id')
        ->where('configuration_id', $configurationId)
        ->get()->toArray();
        
        $Configurationname=Consortium::select('configuration_name','remarks') 
        ->where('id', $configurationId)
        ->get()->first()->toArray();
        $ConfigurationsDetail[0][] = 'Configuration Name';
        $ConfigurationsDetail[0][] = $Configurationname['configuration_name'];
        $ConfigurationsDetail[0][] = 'Remarks';
        $ConfigurationsDetail[0][] = $Configurationname['remarks'];
        // Initialize the array which will be passed into the Excel
        // generator.
        $Newarray = [];
        
        // Define the Excel spreadsheet headers
        $Newarray[] = [
            'configuration_id',
            'provider_name',
            'provider_url',
            'apikey',
            'requestor_id',
            'customer_id', 
        ];
        
        // Convert each member of the returned collection into an array,
        // and append it to the  array.
        $Newarray = array_merge($ConfigurationsDetail,$Newarray);
        $Configurations = array_merge($Newarray,$Configurations);
        // Generate and return the spreadsheet
        $filename="hc_".$Configurationname['configuration_name'];
        
        
        
        try{
            
        Excel::create($filename, function ($excel) use ($Configurations) {
            
            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Provider Report');
            $excel->setCreator('Laravel')->setCompany('Counter Project');
            $excel->setDescription('Provider Report file');
            
            // Build the spreadsheet, passing in the array
            
            $excel->sheet('sheet1', function ($sheet) use ($Configurations) {
                $styleArray = array(
                    'font' => array(
                        'bold' => true
                    )
                );
                $sheet->fromArray($Configurations, null, 'A1', false, false);
                $sheet->getStyle('B1')->applyFromArray($styleArray);
                $sheet->getStyle('D1')->applyFromArray($styleArray);
            });
           
        })->download('xlsx');
        
        } catch (Exception $exception) {
            report($exception);
            
            return parent::render($request, $exception);
        }
    }

    // ////////////// start for Consortium/////////////////
    function harvetsingvalidate($id = 0) {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        //collect all reports for show in page
        $AllReportCodes = Reportname::select(array(
                                    'report_code'
                                ))->orderBy('id', 'asc')
                                ->get()
                                ->toArray();
        
        
        if ($id > 0) {
            $ConsortiumsSingleValue = Consortium::where(array(
                        'id' => $id
                    ))->get()
                    ->first()
                    ->toArray();
            if (isset($ConsortiumsSingleValue['id'])) {
                // echo "<pre>";print_r($ConsortiumsSingleValue);die;
                $data['singleDetail'] = $ConsortiumsSingleValue;
            }
        }

        $Consortiums = Consortium::where(array('created_by'=>$user['email']))->orderBy('id', 'desc')
                ->get()
                ->toArray();
        $providerMaster = array();
        foreach ($Consortiums as $key => $configValue) {
            $providerMaster[$key] = $configValue;
            $AllProvidersList = Provider::where(array(
                        'configuration_id' => $configValue['id']
                    ))->get()->toArray();
            // get providers name comma separted
            $newProvderList = array();
            foreach ($AllProvidersList as $Providerevalue) {
                $newProvderList[] = '<a title="Click here to get members detail." href="member/'.$Providerevalue['id'].'">'.$Providerevalue['provider_name']."</a>";
            }
            $providernamecommaseparted = implode(", ", $newProvderList);
            $providerMaster[$key]['providers'] = $providernamecommaseparted;
        }
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['file_detail'] = $providerMaster;
        
        $TransactionDetail = Transactionmasterdetail::
        select(array('config_name','transaction_id', 'provider_name', 'begin_date','end_date','message','status'))
        
        ->addSelect(DB::raw('count(ID) as count'))
        ->where('user_id', $user['email'])
        ->groupBy('transaction_id')
        ->groupBy('provider_name')
        ->groupBy('begin_date')
        ->groupBy('end_date')
        ->groupBy('config_name')
        ->groupBy('message')
        ->groupBy('status')
        ->orderBy('transaction_id', 'desc')
        ->get()->toArray();
        
        $data['alltransaction'] = $TransactionDetail;
        $data['allreports'] = $AllReportCodes;
        //echo "<pre>222";print_r($data);die;
        return view('consortium', $data);
    }
    

  //////////////// delete transaction /////////////  
    function delete_transaction($id) {
      // die('coming here');
        if (Session::has('user')) {
            
            $user = Session::get('user');
            $UserType = $user['utype'];
            if($UserType==='admin'){
                DB::beginTransaction();
                try {
                $alltransaction = Transactionmasterdetail::where('transaction_id', $id)->delete();
                DB::commit();
                } catch(Exception $exception) {
                    DB::rollback();
                }
            } else {
                DB::beginTransaction();
                try {
                $alltransaction = Transactionmasterdetail::where('transaction_id', $id)->delete();
                DB::commit();
                } catch(Exception $exception) {
                    DB::rollback();
                }
            }
            
            Session::flash('colupdatemsg', 'Transaction successfully deleted');
            Session::put('keyconsortium', 'delete');
            return Redirect::intended('/consortium');
            
        }
    }
    
  /////////////Members Listing////////////////////
    function memberListing($id='') {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $ProviderDetail = Provider::where(array(
            'id' => $id
        ))->get()
        ->first();
                
        $Allmembers = Members::select()
        ->where('provider_id', $id)
        ->get()->toArray();
          
        $data['allmember'] = $Allmembers;
        $data['SingleProvder'] = $ProviderDetail;;
        
        return view('members', $data);

    }
    //////////////////Member Delete/////////////
    
    function deleteMembers($id='',$provider='') {
       
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        DB::beginTransaction();
        try {
            $Allmember = Members::where('id', $id)->delete();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
        }
            Session::flash('memberdeletemsg', 'Member details successfully deleted');
            return Redirect::intended('/member/'.$provider);
        
        }
    
        //////////////////Member Retrieving after deletion/////////////
        function refreshMembers($provider='') {
          // die('bdfhhfhd');
            $user = Session::get('user');
            if ($user['email'] == '') {
                return Redirect::to('/');
            }
            $data=Provider::select()
            ->where('id',$provider)
            ->get()
            ->first()
            ->toarray();
                        
            $mainURL =$data['provider_url'];
            $fields = array(
                'apikey' => $data['apikey'],
                'customer_id' => $data['customer_id'],
                'requestor_id' => $data['requestor_id']
            );
            $fields = array_filter($fields);
            
            if(substr($mainURL,-1)=='/')
                $url = $mainURL . "members?" . http_build_query($fields, '', "&");
            else
                $url = $mainURL . "/members?" . http_build_query($fields, '', "&");
            
            
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
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            //if(empty($result)){
            if($statusCode===404){
              Session::flash('error', 'Member service URL is Wrong.');
              return Redirect::intended('/member/'.$provider);
            } else { 
                $MembersList = json_decode($result);
                if(empty($MembersList)){
                 Session::flash('error', 'This URL has been moved.');
                 return Redirect::intended('/member/'.$provider);
                } else {
                if(count($MembersList)>0)
                {
                    Members::where('provider_id', $provider)->delete();
                }
                foreach($MembersList as $Member){
                    $CustomerId = $Member->Customer_ID??'';
                    if(!empty($Member->Requestor_ID)){
                        $RequestorId = $Member->Requestor_ID??'';
                    }else{
                        $RequestorId = $data['requestor_id']??'';
                    }
                    $Name = $Member->Name??'';
                    $Notes = $Member->Notes??'';
                    $InstitutionIdType = $Member->Institution_ID[0]->Type??'';

                    $InstitutionIdvalue = $Member->Institution_ID[0]->Value??'';
                    $ProviderId = $provider;

                    $MembersValue= array(
                        'customer_id' => $CustomerId,
                        'requestor_id' => $RequestorId,
                        'name' => $Name,
                        'notes' => $Notes,
                        'institution_id_type' => $InstitutionIdType,
                        'institution_id_value' => $InstitutionIdvalue,
                        'provider_id' => $ProviderId,
                    );

                    if(!empty($MembersValue['customer_id']))
                    {

                            $SaveMemberNew = Members::create($MembersValue);

                    }
                    }
              }

                }
                
            
            Session::flash('memberfreshmsg', 'Member details successfully refreshed');
            return Redirect::intended('/member/'.$provider);
        }
    // ///////////////////////////////
    function saveConsortiumConfig() {
        
        // die('die here');
        
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $data = Input::all();
        $data['created_by']=$user['email'];

        $rules = array(
            'configuration_name' => 'required',
            'remarks' => 'required'
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {

            // If validation fails redirect back to login.

            return Redirect::back()->withInput($data)->withErrors($validator, 'consortium');
        } else {

            // checking of create or update
            if (isset($data['currentId'])) {
                $updatearray['configuration_name'] = $data['configuration_name'];
                $updatearray['remarks'] = $data['remarks'];
                
                $Reportdetail = Consortium::where('id', $data['currentId'])->update($updatearray);
               
                Session::flash('colupdatemsg', 'Configuration Updated Successfully');
                return Redirect::intended('/consortium');
            } else {
                
                DB::beginTransaction();
                try {
                $newUser = Consortium::create($data);
                DB::commit();
                } catch(Exception $exception) {
                    DB::rollback();
                }
            }

            if ($newUser) {
                Session::flash('colupdatemsg', 'Configuration Added Successfully');
                // return Redirect::intended('/consortium');
                return Redirect::intended('/add_provider/' . $newUser->id);
            }
        }
    }

    // ////////////////Edit Consortium////////////////////
    function edit_consortium($id) {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        if (Session::has('user')) {
            $user = Session::get('user');
            
            $Consortiums = Consortium::select('id', 'configuration_name', 'Remarks')->where('id', $id)->get();
            $data['file_detail'] = $Consortiums;
            // echo "<pre>";print_r($data);die;
            return view('consortium', $data);
        } else {
            return Redirect::to('login');
        }
    }

    function update_consortium() {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        if (session::has('user')) {

            $data = Input::all();
            // echo "<pre>";print_r($data);die;
            $user = Session::get('user');
            $updatearray['configuration_name'] = $data['configuration_name'];
            $updatearray['remarks'] = $data['remarks'];
            
            DB::beginTransaction();
            try {
            $Consortiums = Consortium::where('id', $data['Id'])->update($updatearray);
            DB::commit();
            } catch(Exception $exception) {
                DB::rollback();
            }
            
            $data['utype'] = $user['utype'];
            $data['userDisplayName'] = $user['display_name'];
            $data['file_detail'] = $Consortiums;

            Session::flash('userupdatemsg', 'Report successfully updated');
            return Redirect::to('consortium');
        } else {
            return Redirect::to('login');
        }
    }

    public function fileHistory()
    {
        if(! Session::has('user')) {
            return Redirect::to('login');
        }
        $user = Session::get('user');
        
        $filehistoryQuery = Storedfile::with('user', 'reportfile', 'reportfile.checkresult', 'sushiresponse',
            'sushiresponse.checkresult');
        if($user['utype'] !== 'admin') {
            $filehistoryQuery->where('user_id', $user['id']);
        }
        $filehistoryQuery->where('type', Storedfile::TYPE_REPORT)
            ->whereIn('source', [
               Storedfile::SOURCE_FILE_VALIDATE,
                Storedfile::SOURCE_SUSHI_VALIDATE
            ]);
        
        return view('file_history',
            [
                'userDisplayName' => $user['display_name'],
                'utype' => $user['utype'],
                'filehistory' => $filehistoryQuery->get()
            ]);
    }
    
    ///////////////downloading Transaction Lists////////////
    public function TransactionListsDownload($id=0) {
        
        $user = Session::get('user');
        if (Session::has('user')) {
            if($user['utype']=='admin'){
                $AllArray = Transactionmasterdetail::where(array())->orderBy('time_stamp', 'desc')
                ->get()
                ->toArray();
               
                if($AllArray == array()){
                    Session::flash('error', 'No Reports Available');
                    return Redirect::intended('/consortium');
                }
            } else {
                
                // echo "<pre>";print_r($user);die;
                
                $AllArray = Transactionmasterdetail::where('user_id', $user['email'])->orderBy('time_stamp', 'desc')
                ->get()
                ->toArray();
                
                // echo "<pre>";print_r($AllUserArray);die;
                
                 if($AllArray == array()){
                    Session::flash('error', 'No Reports Available');
                    return Redirect::intended('/consortium');
                }
                
                
            }
            
            $reportHeader[] = array_keys($AllArray[0]);
            $arr1 = array_merge($reportHeader,$AllArray);
            $destinationPath = public_path() . "/upload/transaction_lists/" ;
            $file = time() . '_transaction';
            
            try{
                
                return Excel::create($file, function ($excel) use ($arr1) {
                    
                    // Build the spreadsheet, passing in the $dataValue
                    $excel->sheet('sheet1', function ($sheet) use ($arr1) {
                        $sheet->fromArray($arr1, null, 'A1', false, false);
                    });
                })->store('xlsx', $destinationPath)->download();
                
            } catch (Exception $exception) {
                report($exception);
                return parent::render($request, $exception);
            }
        }else{
            return Redirect::to('login');
        }
    }
    
    // delete providers value
    function deleteConsortium($id) {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        if (Session::has('user')) {

            DB::beginTransaction();
            try {
            Consortium::where('id', $id)->delete();
            DB::commit();
            } catch(Exception $exception) {
            DB::rollback();
            }
            
            DB::beginTransaction();
            try {
            Provider::where('configuration_id', $id)->delete();
            DB::commit();
            } catch(Exception $exception) {
                DB::rollback();
            }
            
            Session::flash('colupdatemsg', 'Configuration successfully deleted');
            return Redirect::intended('/consortium');
            // echo "<pre>";print_r($id);die;
        }
    }

    // delete providers value
    function deleteProvider($id = 0, $configid = 0) {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        if (Session::has('user')) {
            
            DB::beginTransaction();
            try {
            Provider::where(array(
                'id' => $id,
                'configuration_id' => $configid
            ))->delete();
            DB::commit();
            } catch(Exception $exception) {
                DB::rollback();
            }
            
            DB::beginTransaction();
            try {
            Members::where(array(
                'provider_id'=> $id,
            ))->delete();
            DB::commit();
            } catch(Exception $exception) {
            DB::rollback();
            }
            
            Session::flash('colupdatemsg', 'Provider and its Member successfully deleted');
            return Redirect::intended('/add_provider/' . $configid);
            // echo "<pre>";print_r($id);die;
        }
    }

    public function showConsortiumProgressnew(int $configurationId=0){
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $user = Session::get('user');
        $AllReportCodes = Reportname::select(array(
                                    'report_code'
                                ))->orderBy('id', 'asc')
                                ->get()
                                ->toArray();
        
        $Consortiums = Consortium::where('id',$configurationId)
                ->get()->first()
                ->toArray();
            $AllProvidersList = Provider::where(array(
                        'configuration_id' => $Consortiums['id']
                    ))->get()->toArray();
            
            $allMembers = array();
            //echo "<pre>";print_r($AllProvidersList);die;
            foreach ($AllProvidersList as $singlProvider) {
                $allMembers[$singlProvider['id']] = Members::where(array(
                        'provider_id' => $singlProvider['id']
                    ))->get()->toArray();
            }
            //echo "<pre>";print_r($allMembers);die;
            //converting signle array removing duplicates
            $UniquMembervalue = array();
            foreach ($allMembers as $MemberValue) {
                foreach ($MemberValue as $Mvalue) {
                    $UniquMembervalue[$Mvalue['customer_id']] = $Mvalue['name'];
                }
            }
            
            // get providers name comma separted
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['allreports'] = $AllReportCodes;
        $data['allproviders'] = $AllProvidersList;
        $data['configuration_id'] = $configurationId;
        $data['all_members'] = $UniquMembervalue;
        return view('show_consortium_progressnew', $data);
    }
    //show progress bar for data download
    function showConsortiumProgress() {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        
        $Postdata = Input::all();
       
        $id = $Postdata['configurationid']??0;
        
        $email = $user['email'];
        
        if($user['utype']!='admin'){
            $noFtimes = User::select('no_of_times')->where(array('utype'=>'user','email'=>$email))->get()->first()->toArray();
            $Limit = $noFtimes['no_of_times'];
            // echo "<pre>";print_r($Limit);die;
            $TotalRequest = Transactionmasterdetail::where('user_id',$email)->distinct('transaction_id')->count('transaction_id');
            if( $TotalRequest >  $Limit  &&  $user['utype']!='admin'){
                Session::flash('error', 'Limit Exceeded. Please Contact Administrator');
                return Redirect::intended('/consortium');
                
            } 
        }
        
        //get configuration Name
        $Configurationname = Consortium::where('id',$id)->get()->first()->toArray();

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $user = Session::get('user');
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];

        $data['id'] = $Postdata['configurationid'];
        $data['begin_date'] = $Postdata['begin_date'];
        $data['end_date'] = $Postdata['end_date'];
        $data['selectedReports'] = implode(",", $Postdata['reports']);
        $data['selectedProviders'] = implode(",", $Postdata['providers']);
        $data['selectedMembers'] = implode(",", $Postdata['members']);
        $data['selectedFormat'] =  $Postdata['format'];
        $data['success'] = 1;
        $data['uploaded_file'] = "a.zip";
        $data['configuration_name'] = $Configurationname['configuration_name'];
        //echo "<pre>";print_r($data);die;
        return view('show_consortium_progress', $data);
        
    }

    //show current Record progress File
    function showConsortiumProgressForRecord($id = 0,$TransactionId='',$begin_date = '', $end_date = '',$selectedReports='') {
        //$TransactionId = $this->getTransctionId();
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $user = Session::get('user');
        $AllProvidersList = Provider::where(array(
                    'configuration_id' => $id
                ))->get()->toArray();

        //convert all providers in signle array
        foreach ($AllProvidersList as $providerSingle) {
            $allProvidersListValue[$providerSingle['id']] = $providerSingle['provider_name'];
        }
        
        $TransactionDetail = Transactionmasterdetail::
                        select(array('transaction_id', 'provider_name', 'member_name'))
                        ->addSelect(DB::raw('count(ID) as count'))
                        ->where('transaction_id', $TransactionId)
                        ->groupBy('transaction_id')
                        ->groupBy('provider_name')
                        ->groupBy('member_name')
                        ->get()->toArray();
             
        $dataToShow = array();
        $statusMaster = array('1' => 'Completed');
        $OutputString = "<table class='table table-striped table-bordered dataTable'  width='100%'>";
        $OutputString = $OutputString."<tr><th>Sl. No.</th><th>Provider Name</th><th>Member Name</th><th>Reports</th><th>Processed Report</th></tr>";
        
        $i = 1;
        foreach ($TransactionDetail as $keyValue => $TransactionSingle) {
            $OutputString = $OutputString . "<tr>";
            $OutputString = $OutputString . "<td>" . $i++ . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['provider_name'] . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['member_name'] . "</td>";
            $OutputString = $OutputString . "<td>" . $selectedReports . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['count'] . "</td>";
            $OutputString = $OutputString . "</tr>";
        }
        $OutputString = $OutputString . "</table>";
        die($OutputString);
        //die("Processed  Done for no of records.");
    }
    
    ////////////Run Consortium///////////////////////////////
    function runConsortium($id = 0, $TransactionId = '', $begin_date = '', $end_date = '', $selectedReport = '', $selectedProviders = '', $selectedMembers = '', $selectedFormat = '')
    {
      
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $SelectedReport = explode(",", $selectedReport);
        $SelectedProviders = explode(",", $selectedProviders);
        $SelectedMembers = explode(",", $selectedMembers);
        
        $ConfigurationName = Consortium::where('id', $id)->get()
            ->first()
            ->toArray();
        
        $ConfigurationName = $ConfigurationName['configuration_name'] ?? '';
        $begin_date = explode("-", $begin_date);
        $begin_date = $begin_date[1] . "-" . $begin_date[0] . "-01";
        $end_date = explode("-", $end_date);
        $d = cal_days_in_month(CAL_GREGORIAN, $end_date[0], $end_date[1]);
        $end_date = $end_date[1] . "-" . $end_date[0] . "-" . $d;
        
        $data = Input::all();
        $user = Session::get('user');
        $startdatetimstamp = date('Y-m-d H:i:s');
        
        if (Session::has('user')) {
            
            $data = Consortium::select('id', 'configuration_name', 'remarks')->where('id', $id)
                ->get()
                ->first()
                ->toArray();
            $Remarks = $data['remarks'];
            $AllProvidersList = Provider::where(array(
                'configuration_id' => $data['id']
            ))->get()->toArray();
            $userId = $user['id'];
            $ClientIp = $this->get_client_ip();
            $ConfigurationId = $AllProvidersList[0]['configuration_id'];
            
            foreach ($AllProvidersList as $ProviderDetail) {
                if (! in_array($ProviderDetail['id'], $SelectedProviders))
                    continue;
//              echo "<pre>";print_r($ProviderDetail);die;
//                extract($ProviderDetail);
               $id = $ProviderDetail['id'];
               $provider_name =  $ProviderDetail['provider_name'];
               $provider_url = $ProviderDetail['provider_url'];
               $apikey=$ProviderDetail['apikey'];
               $requestor_id=$ProviderDetail['requestor_id'];
               $customer_id=$ProviderDetail['customer_id'];
                       
                $ProviderDetailID = $ProviderDetail['id'];
                $mainURL = $provider_url;
                
                
                
                $file = time() . '_file.json';
                $AllReportCodes = Reportname::select(array(
                            'report_code'
                        ))->orderBy('id', 'asc')
                            ->get()
                            ->toArray();
                $json = Members::where('provider_id', $id)->orderBy('id', 'asc')
                            ->get()
                            ->toArray();
                if (count($json)>0) {
                        // member detail creating file
                        $providerNameFolder = str_replace(" ", "_", $provider_name);
                        $destinationPath = public_path() . "/upload/json/" . $TransactionId . "/";
                        $destinationPathCopy = public_path() . "/upload/json/" . $TransactionId . "/";
                        $file = $providerNameFolder . "_members.json";
                        if (! is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        
                        $dataValue = $json;
                        
                        
                        $filex = $providerNameFolder . "_members";
                        
                        
                        if($selectedFormat==='JSON'){
                            
                            foreach ($dataValue as $keyofreport => $reportvalue) {
                                $dataValue1[] = array(
                                    $keyofreport
                                );
                            }
                        } else if($selectedFormat==='XLSX'){
                            
                            try{
                            Excel::create($filex, function ($excel) use ($dataValue) {
                            
                            $excel->setTitle('Error Report');
                            $excel->setCreator('Laravel')->setCompany('Counter Project');
                            $excel->setDescription('xlsx report file');
                            
                            // Build the spreadsheet, passing in the $dataValue
                            $excel->sheet('sheet1', function ($sheet) use ($dataValue) {
                                $sheet->fromArray($dataValue, null, 'A1', false, false);
                            });
                        })->store('xlsx', $destinationPath);
                            } catch (Exception $exception) {
                                report($exception);
                                
                                return parent::render($request, $exception);
                            }
                        
                        //delete member json file
                        if(file_exists($destinationPath.$file)){
                            unlink($destinationPath.$file);
                        }
                        }
                       
                        else if($selectedFormat==='TSV'){
                            // tsv creation start
                            try {
                            $filenametsv = $providerNameFolder . "_members.tsv";
                            $FileNameForSize = $filenametsv;
                            $filenametsv = $destinationPath . $filenametsv;
                            $TsvContentValue = '';
                            $myfile = fopen($filenametsv, "w") or die("Unable to open file!");
                            $headerarray = array_keys($dataValue[0]);
                            //echo "<pre>";print_r($dataValue);die;
                            $TsvContentHeader = implode("\t", $headerarray) . "\n";
                            
                            fwrite($myfile, $TsvContentHeader);
                            foreach ($dataValue as $KeyOfMember=>$ContentOfTSV) {
                                if(isset($ContentOfTSV['Institution_ID'])){
                                    $ContentOfTSV['Institution_ID'] = $ContentOfTSV['Institution_ID'][0]['Type'].':'.$ContentOfTSV['Institution_ID'][0]['Value'];
                                }else{
                                    $ContentOfTSV['Institution_ID'] = 'N/A';
                                }
                                $TsvContentValue = implode("\t", $ContentOfTSV) . "\n";
                                fwrite($myfile, $TsvContentValue);
                            }
                            
                            fclose($myfile);
                            //deletion of member json
                            if(file_exists($destinationPath.$file)){
                                unlink($destinationPath.$file);
                            }
                            
                            } catch (Exception $exception) {
                                report($exception);
                                
                                return parent::render($request, $exception);
                            }
                        }
                        
                        // Now Checking for each Report related to Member
                        // try {
                        foreach ($json as $Member) {
                            $FileNameForSize = '';
                            if (! in_array($Member['customer_id'], $SelectedMembers))
                                continue;
                            // loop for reprt code
                            foreach ($AllReportCodes as $ReportCode) {
                                if (! in_array($ReportCode['report_code'], $SelectedReport))
                                    continue;
//                                 extract($Member);
                                    $customer_id = $Member['customer_id'];
                                    $requestor_id = $Member['requestor_id'];
                                    $name =  $Member['name'];
                                $Mfields = array(
                                    'apikey' => $apikey,
                                    'customer_id' => $customer_id,
                                    'begin_date' => $begin_date,
                                    'requestor_id' => $requestor_id,
                                    'end_date' => $end_date
                                );
                                $Mfields  =  array_filter($Mfields);
                                $startdatetimstamp = date('Y-m-d H:i:s');
                                
                                
                                $Murl = $mainURL . "/reports/" . strtolower($ReportCode['report_code']) . "?" . http_build_query($Mfields, '', "&");
                                if (! preg_match("~^(?:f|ht)tps?://~i", $Murl)) {
                                    $Murl = "https://" . $Murl;
                                }
                                $Mfile = $customer_id . '_file.json';
                                
                                
                                
                                
                                
                                $Mcurl = curl_init($Murl);
                                curl_setopt($Mcurl, CURLOPT_NOBODY, false);
                                curl_setopt($Mcurl, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($Mcurl,CURLOPT_RETURNTRANSFER,1);
                                curl_setopt($Mcurl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
        
                                sleep(1);
                                $Mresult = curl_exec($Mcurl);
                                // echo "<pre>";print_r ($Mresult);die;
                                
                                if ($Mresult !== false) {
                                    $statusCode = curl_getinfo($Mcurl, CURLINFO_HTTP_CODE);
                                    if ($statusCode == 404) {
                                        $Mresult = array(
                                            "Member URL does not exist"
                                        );
                                        $SaveTransaction = array(
                                            'user_id' => $user->email,
                                            'transaction_id' => $TransactionId,
                                            'config_name' => $ConfigurationName,
                                            'client_ip' => $ClientIp,
                                            'provider_name' => $provider_name,
                                            'member_name' => $name,
                                            'report_id' => $ReportCode['report_code'],
                                            'begin_date' => $begin_date,
                                            'end_date' => $end_date,
                                            'status' => 0,
                                            'message' => 'failed',
                                            'remarks' => $Remarks,
                                            'exception' => '',
                                            'details' => 'Incomplete:',
                                            'file_name' => 'Test.json',
                                            'file_size' => 0,
                                            'start_date_time' => $startdatetimstamp
                                        );
                                        
                                        DB::beginTransaction();
                                        try {
                                        $SaveReort = Transactionmasterdetail::create($SaveTransaction);
                                        DB::commit();
                                        } catch(Exception $exception) {
                                        DB::rollback();
                                        }
                                    } else {
                                        
                                        
                                        
                                        $SaveTransaction = array(
                                            'user_id' => $user->email,
                                            'transaction_id' => $TransactionId,
                                            'config_name' => $ConfigurationName,
                                            'client_ip' => $ClientIp,
                                            'provider_name' => $provider_name,
                                            'member_name' => $name,
                                            'report_id' => $ReportCode['report_code'],
                                            'begin_date' => $begin_date,
                                            'end_date' => $end_date,
                                            'status' => 1,
                                            'message' => 'success',
                                            'remarks' => $Remarks,
                                            'exception' => '',
                                            'details' => 'complete:',
                                            'file_name' => 'Test.json',
                                            'file_size' => 0,
                                            'start_date_time' => $startdatetimstamp
                                        );

                                        DB::beginTransaction();
                                        try {
                                        $SaveReort = Transactionmasterdetail::create($SaveTransaction);
                                        DB::commit();
                                        } catch(Exception $exception) {
                                        DB::rollback();
                                        }
                                        
                                        
                                        
                                        // $data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);
                                        $startdatetimstampEnd = date('Y-m-d H:i:s');
                                        $rundate = date('m-d-Y-His A e');
                                        $file = $provider_name . "_" . $Member['customer_id'] . "_" . $ReportCode['report_code'] . "_5_" . $begin_date . "_" . $end_date . "_" .$rundate. ".json";
                                        // $destinationPath = $destinationPathCopy . "/" . $Member['Customer_ID'] . "/";
                                        $destinationPath = $destinationPathCopy;
                                        // die($destinationPath);
                                        
                                        if (! is_dir($destinationPath)) {
                                            mkdir($destinationPath, 0777, true);
                                        }
                                        
                                        File::put($destinationPath . $file, $Mresult);
                                        $dataValue = json_decode($Mresult, true);
                                       
                                        // die($selectedFormat);
                                        
                                        if($selectedFormat!='JSON'){
                                            
                                        if(isset($dataValue1) && is_array($dataValue1))
                                            unset($dataValue1);
                                        
                                            
                                       // excel start here 
                                        
                                        // $filename = $user['id'] . '_' . date('m-d-Y_hisa') . '_' . 'xlsx';
                                        $filename = $provider_name . "_" . $Member['customer_id'] . "_" . $ReportCode['report_code'] . "_5_" . $begin_date . "_" . $end_date . "_" .$rundate. "";
                                        $FileNameForSize = $filename;
                                        // echo "<pre>";print_r ($filename);die;
                                        
                                        $headerSection = $dataValue['Report_Header'];
                                       
                                        foreach ($headerSection as $keyofreport => $reportvalue) {
                                            if (is_array($reportvalue)) {
                                                if ($keyofreport == 'Institution_ID') {
                                                    $reportvalueforexcel = $reportvalue[0]['Type'] . ':' . $reportvalue[0]['Value'];
                                                } else if ($keyofreport == 'Report_Filters') {
                                                    if(isset($reportvalue[0])){
                                                        $reportvalueforexcel = $reportvalue[0]['Name'] . '=' . $reportvalue[0]['Value'] . ';' . $reportvalue[1]['Name'] . '=' . $reportvalue[1]['Value'];
                                                    }else{
                                                       $reportvalueforexcel = ''; 
                                                    } 
                                                } else if ($keyofreport == 'Report_Attributes') {
                                                    $valuekey = $reportvalue[0]['Name'] ?? '';
                                                    $valueofAttr = $reportvalue[0]['Value'] ?? '';
                                                    if (empty($valuekey) && empty($valueofAttr)) {
                                                        $reportvalueforexcel = '';
                                                    } else {
                                                        $reportvalueforexcel = $valuekey . ':' . $valueofAttr;
                                                    }
                                                } else if ($keyofreport == 'Exceptions') {
                                                    $reportvalueforexcel = $reportvalue[0]['Code'] ?? '';
                                                }
                                            } else {
                                                $reportvalueforexcel = $reportvalue;
                                            }
                                            // $dataValue1[] = array();
                                            $dataValue1[] = array(
                                                $keyofreport,
                                                $reportvalueforexcel
                                            );
                                        }
                                        
                                        // setting header of report
                                        $currentReportID = $headerSection['Report_ID'];
                                        $ReportBody = $dataValue['Report_Items']??'';
                                        // $ReportHeader = $datavalue['Report_Header'];
                                        // echo "<pre>";print_r ($ReportBody);die;
                                        if (empty($ReportBody)) {
                                            $dataValue1[] = array();
                                        } else if ($currentReportID == 'DR_D1' || $currentReportID == 'DR_D2' || $currentReportID == 'DR') {
                                            $headerFixedValue = array_keys(($ReportBody[0]));
                                            $newHeader = array_values($headerFixedValue);
                                            $headerFixedValueAll = array_combine($newHeader, $headerFixedValue);
                                            $dataValue1[] = $headerFixedValueAll;
                                            
                                            $bodyvalue = $ReportBody;
                                            
                                            foreach ($bodyvalue as $InnerValue) {
                                                $NewSingleRow = array();
                                                // for all inner value
                                                foreach ($InnerValue as $KeyOfRow => $RowValue) {
                                                    $finalString = '';
                                                    if (is_array($RowValue)) {
                                                        if ($KeyOfRow == 'Publisher_ID') {
                                                            $finalString = $RowValue[0]['Type'] . ':' . $RowValue[0]['Value'];
                                                        } else if ($KeyOfRow == 'Performance') {
                                                            //$finalString = $RowValue[0]['Period']['Begin_Date'] . '-' . $RowValue[0]['Period']['End_Date'] . ';' . $RowValue[0]['Instance'][0]['Metric_Type'] . '-' . $RowValue[0]['Instance'][0]['Count'];
                                                            $PerformanceValue = $RowValue[0]??'';
                                                            $finalString = serialize($PerformanceValue);
                                                        }
                                                        $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $finalString;
                                                    } else {
                                                        $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $RowValue;
                                                    }
                                                }
                                                $dataValue1[] = $NewSingleRow;
                                            }
                                        } else if ($currentReportID == 'PR_P1' || $currentReportID == 'PR') {
                                            
                                            $headerFixedValue = array_keys($ReportBody[0]);
                                            $newHeader = array_values($headerFixedValue);
                                            $headerFixedValueAll = array_combine($newHeader, $headerFixedValue);
                                            $dataValue1[] = $headerFixedValueAll;
                                            
                                            $bodyvalue = $ReportBody;
                                            
                                            foreach ($bodyvalue as $InnerValue) {
                                                $NewSingleRow = array();
                                                // for all inner value
                                                foreach ($InnerValue as $KeyOfRow => $RowValue) {
                                                    $finalString = '';
                                                    if (is_array($RowValue)) {
                                                        if ($KeyOfRow == 'Performance') {
                                                            //$finalString = $RowValue[0]['Period']['Begin_Date'] . '&' . $RowValue[0]['Period']['End_Date'] . ';' . $RowValue[0]['Instance'][0]['Metric_Type'] . '-' . $RowValue[0]['Instance'][0]['Count'] . ';' . $RowValue[0]['Instance'][1]['Metric_Type'] . '-' . $RowValue[0]['Instance'][1]['Count'] . ';' . $RowValue[0]['Instance'][2]['Metric_Type'] . '-' . $RowValue[0]['Instance'][2]['Count'];
                                                            $PerformanceValue = $RowValue[0]??'';
                                                            $finalString = serialize($PerformanceValue);
                                                        }
                                                        $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $finalString;
                                                    } else {
                                                        $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $RowValue;
                                                    }
                                                }
                                                $dataValue1[] = $NewSingleRow;
                                            }
                                        } else if ($currentReportID == 'IR_A1' || $currentReportID == 'IR_M1' || $currentReportID == 'IR') {
                                            
                                            $headerFixedValue = array_keys($ReportBody[0]);
                                            $newHeader = array_values($headerFixedValue);
                                            $headerFixedValueAll = array_combine($newHeader, $headerFixedValue);
                                            $dataValue1[] = $headerFixedValueAll;
                                            
                                            $bodyvalue = $ReportBody;
                                            
                                            foreach ($bodyvalue as $InnerValue) {
                                                $NewSingleRow = array();
                                                // for all inner value
                                                foreach ($InnerValue as $KeyOfRow => $RowValue) {
                                                    $finalString = '';
                                                    $CopyFlag = 0;
                                                    if (is_array($RowValue)) {
                                                        if ($KeyOfRow == 'Performance') {
                                                            $PerformanceValue = $RowValue[0]??'';
                                                            $finalString = serialize($PerformanceValue);
                                                        }
                                                        if(isset($headerFixedValueAll[$KeyOfRow])){
                                                            $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $finalString;
                                                        } else {
                                                           $NewSingleRow['N/A'] = ''; 
                                                        }
                                                    } else {
                                                        $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $RowValue;
                                                    }
                                                }
                                                $dataValue1[] = $NewSingleRow;
                                            }
                                        
                                            
                                        } else {
                                            //TR all Reports
                                            $headerFixedValue = array_keys($ReportBody[0]);
                                            $newHeader = array_values($headerFixedValue);
                                            $headerFixedValueAll = array_combine($newHeader, $headerFixedValue);
                                            $dataValue1[] = $headerFixedValueAll;
                                            
                                            // echo "<pre>";print_r($dataValue1);die;
                                            
                                            $bodyvalue = $ReportBody;
                                            //echo "<pre>MaiN";print_r($bodyvalue);die;
                                            foreach ($bodyvalue as $InnerValue) {
                                                $NewSingleRow = array();
                                                // for all inner value
                                                foreach ($InnerValue as $KeyOfRow => $RowValue) {
                                                    $finalString = '';
                                                    $CopyFlag = 0;
                                                    if (is_array($RowValue)) {
                                                        if ($KeyOfRow == 'Item_ID') {
                                                            $finalString = serialize($RowValue);
                                                        } else if ($KeyOfRow == 'Publisher_ID') {
                                                            $finalString = $RowValue[0]['Type']??'' . ':' . $RowValue[0]['Value']??'';
                                                        } else if ($KeyOfRow == 'Performance') {
                                                            $PerformanceValue = $RowValue[0]??'';
                                                            $finalString = serialize($PerformanceValue);
                                                        }
                                                        if(isset($headerFixedValueAll[$KeyOfRow])){
                                                            $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $finalString;
                                                        } else {
                                                            $NewSingleRow['N/A'] = ''; 
                                                        }
                                                    } else {
                                                        if(isset($headerFixedValueAll[$KeyOfRow]))
                                                            $NewSingleRow[$headerFixedValueAll[$KeyOfRow]] = $RowValue;
                                                        else
                                                            $NewSingleRow['N/A'] = $RowValue;
                                                    }
                                                }
                                                $dataValue1[] = $NewSingleRow;
                                            }
                                            
                                          }
                                          
                                        } else {
                                            $dataValue1=array();
                                        }
                                        //echo "<pre>Serialiaz";print_r($dataValue1);die;
                                        if($selectedFormat==='XLSX'){
                                            
                                            $CorrectHeaderSequence = array(
                                                'Report_Name',
                                                'Report_ID',
                                                'Release',
                                                'Institution_Name',
                                                'Institution_ID',
                                                'Metric_Types',
                                                'Report_Filters',
                                                'Report_Attributes',
                                                'Exceptions',
                                                'Reporting_Period',
                                                'Created',
                                                'Created_By'
                                            );
                                            
                                            $Rid = $dataValue1[3][1];
                                            
                                            // getting array for header from JSON Data
                                            $JsonHeaderValues = array();
                                            for($i=0;$i<11;$i++){
                                                if(isset($dataValue1[$i]) && array_key_exists(1,$dataValue1[$i]))
                                                    $JsonHeaderValues[$dataValue1[$i][0]] = $dataValue1[$i][1]??'';
                                            }
                                            
                                            $rname = str_replace('"', '', $JsonHeaderValues['Report_Name']);
                                            // echo "<pre>";print_r($rname);die;
                                            $orderDataValue = array();
                                            
                                            foreach($CorrectHeaderSequence as $HeaderHeading){
                                                if($HeaderHeading=='Reporting_Period'){
                                                    //echo "<pre>";print_r($JsonHeaderValues);die;
                                                        if(isset($JsonHeaderValues['Report_Filters']) && !empty($JsonHeaderValues['Report_Filters'])){
                                                        $makecompatibleDate = explode(";",$JsonHeaderValues['Report_Filters']);
                                                        $BeginDate = explode("=",$makecompatibleDate[0]);
                                                        $EndDate = explode("=",$makecompatibleDate[1]);

                                                        $start_date = $BeginDate[1];
                                                        $end_date = $EndDate[1];
                                                        $BeginDate[1] = date("Y-m-t", strtotime($start_date));
                                                        $EndDate[1] = date("Y-m-t", strtotime($end_date));
                                                        $FilterValueDate = implode('=',$BeginDate)."; ".implode('=',$EndDate);
                                                    }else{
                                                       $FilterValueDate = ''; 
                                                    }
                                                    //$orderDataValue[] =  array($HeaderHeading,$JsonHeaderValues['Report_Filters']??'');
                                                    $orderDataValue[] =  array($HeaderHeading,$FilterValueDate??'');
                                                    
                                                    
                                                } else {
                                                    if($HeaderHeading=='Report_Filters'){
                                                        $orderDataValue[] =  array($HeaderHeading,'');
                                                    } else if($HeaderHeading=='Report_Name') {
                                                        $orderDataValue[] =  array($HeaderHeading,$rname);
                                                    }else{
                                                        $orderDataValue[] =  array($HeaderHeading,$JsonHeaderValues[$HeaderHeading]??'');
                                                    }
                                                }
                                            }
                                             
                                            $FileNameForSize = $filename;
                                            
                                            if($Rid === 'TR' || $Rid === 'TR_J1' || $Rid === 'TR_J2' || $Rid === 'TR_J3' || $Rid === 'TR_J4' || $Rid === 'TR_B1' || $Rid === 'TR_B2' || $Rid === 'TR_B3'){
                                             
                                               $orderDataValue[] = array();
                                               $BodyReportHeading = array(
                                                   'Title',
                                                   'Publisher', 
                                                   'Publisher_ID', 
                                                   'Platform', 
                                                   'DOI', 
                                                   'Online_ISSN',
                                                   'Print_ISSN', 
                                                   'Proprietary_ID',
                                                   'ISBN',
                                                   'Data_Type',
                                                   'Section_Type',
                                                   'YOP', 
                                                   'Access_Type', 
                                                   'Access_Method',
                                                   'Metric_Type',  
                                                   'Reporting_Period_Total'
                                                   );
                                                
                                               $orderDataValue[] = $BodyReportHeading;
                                               
                                               $LastIndexOfJsonReport = count($dataValue1);
                                              
                                               $ReportingPeriodFlag = 0;
                                               for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                               {
                                                   $matricValueFlage = 0;
                                                   
                                                   $SingleColumn = array();
                                                   $CurrentRowValues = $dataValue1[$iCount];
                                                   foreach($BodyReportHeading as $BodyHeader){
                                                      if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>ALL";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if(isset($instanceArray))
                                                                unset($instanceArray);
                                                            $instanceArray = array();
                                                            foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                              $instanceArray[] = $valueofInstance;
                                                              
                                                            }  
                                                        }
                                                        
                                                        
                                                        $matric_type = array_column($instanceArray, 'Metric_Type');
                                                        
                                                        if($ReportingPeriodFlag === 0){
                                                            $d1 = new DateTime($start_date);
                                                            $d2 = new DateTime($end_date);
                                                            $interval = $d2->diff($d1);
                                                            $Differences = $interval->format('%m');
                                                            //echo "<pre>"; print_r($Differences);die;
                                                            for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                $nextMonth = date('Y-m', strtotime("+".$Irp." months", strtotime($start_date)));
                                                                $orderDataValue[13][] = $nextMonth;
                                                                //$d1->add(new DateInterval('P30D'));
                                                            }
                                                            
                                                            $orderDataValue[5][1] = implode(";",$matric_type);
                                                            $ReportingPeriodFlag = 1;
                                                        }
                                                        
                                                        
                                                        
                                                        
                                                      }
                                                      else if($BodyHeader==='DOI'){
                                                          $updateFlage = 0;
                                                          $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                          foreach($ItemIdValue as $dataValueOfColumn){
                                                              if($dataValueOfColumn['Type']=='DOI'){
                                                                $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                $updateFlage = 1;
                                                              }
                                                              
                                                          }
                                                          if($updateFlage==0){
                                                              $SingleColumn['DOI'] = '';
                                                          }
                                                          
                                                      }
                                                      
                                                      else if($BodyHeader==='Online_ISSN'){
                                                          $updateFlage = 0;
                                                          $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                          foreach($ItemIdValue as $dataValueOfColumn){
                                                              if($dataValueOfColumn['Type']=='Online_ISSN'){
                                                                  $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                  $updateFlage = 1;
                                                              }
                                                              
                                                          }
                                                          if($updateFlage==0){
                                                              $SingleColumn['Online_ISSN'] = '';
                                                          }
                                                          
                                                      }
                                                      
                                                      else if($BodyHeader==='Print_ISSN'){
                                                          $updateFlage = 0;
                                                          $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                          foreach($ItemIdValue as $dataValueOfColumn){
                                                              if($dataValueOfColumn['Type']=='Print_ISSN'){
                                                                  $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                  $updateFlage = 1;
                                                              }
                                                              
                                                          }
                                                          if($updateFlage==0){
                                                              $SingleColumn['Print_ISSN'] = '';
                                                          }
                                                          
                                                      }
                                                      
                                                      else{
                                                          $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                      }
                                                   }
                                                   
                                                   //making duplicate rows for Metric_Type
                                                   if(isset($matricValueFlage) && $matricValueFlage==1){
                                                       $lenghtOfColumn = count($orderDataValue[13]);
                                                       foreach($instanceArray as $valueofInstanceValue){
                                                         $SingleColumnCopy = $SingleColumn;
                                                         $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                         $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                         $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                         $orderDataValue[] = $SingleColumnCopy;
                                                       }
                                                   }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                   }
                                               }
                                               
                                               try {
                                            //echo "<pre>ALL".$matricValueFlage;print_r($orderDataValue);die;
                                            // xlsx creation
                                            Excel::create($filename, function ($excel) use ($orderDataValue) {
                                                // Set the spreadsheet title, creator, and description
                                                $excel->setTitle('Error Report');
                                                $excel->setCreator('Laravel')->setCompany('Counter Project');
                                                $excel->setDescription('xlsx report file');
                                                
                                                // Build the spreadsheet, passing in the $dataValue
                                                $excel->sheet('sheet1', function ($sheet) use ($orderDataValue) {
                                                    $sheet->fromArray($orderDataValue, null, 'A1', false, false);
                                                });
                                            })->store('xlsx', $destinationPath);
                                               } catch (Exception $exception) {
                                                   report($exception);
                                                   
                                                   return parent::render($request, $exception);
                                               }
                                               
                                            unlink($destinationPath . $file);
                                            
                                            } else if($Rid === 'DR_D1' || $Rid === 'DR_D2' || $Rid === 'DR') {
                                                
                                                $orderDataValue[] = array();
                                                $BodyReportHeading = array(
                                                    'Database',
                                                    'Publisher',
                                                    'Publisher_ID',
                                                    'Platform',
                                                    'Proprietary_ID',
                                                    'Data_Type',
                                                    'YOP',
                                                    'Access_Type',
                                                    'Access_Method',
                                                    'Metric_Type',
                                                    'Reporting_Period_Total'
                                                );
                                                
                                                $orderDataValue[] = $BodyReportHeading;
                                                $LastIndexOfJsonReport = count($dataValue1);
                                                
                                                $ReportingPeriodFlag = 0;
                                                for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                                {
                                                    $matricValueFlage = 0;
                                                    $SingleColumn = array();
                                                    $CurrentRowValues = $dataValue1[$iCount];
                                                    foreach($BodyReportHeading as $BodyHeader){
                                                        if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                            $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                            // echo "<pre>";print_r($AllMatricTypeValue);die;
                                                            
                                                            $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                            
                                                            $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                            
                                                            if(is_array($ReportingInstance)){
                                                                $matricValueFlage = 1;
                                                                if(isset($instanceArray))
                                                                    unset($instanceArray);
                                                                    $instanceArray = array();
                                                                    foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                        $instanceArray[] = $valueofInstance;
                                                                        
                                                                    }
                                                            }
                                                            $matric_type = array_column($instanceArray, 'Metric_Type');
                                                            
                                                            if($ReportingPeriodFlag === 0){
                                                                $d1 = new DateTime($start_date);
                                                                $d2 = new DateTime($end_date);
                                                                $interval = $d2->diff($d1);
                                                                $Differences = $interval->format('%m');
                                                                //echo "<pre>"; print_r($Differences);die;
                                                                for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                    $nextMonth = date('Y-m', strtotime("+".$Irp." months", strtotime($start_date)));
                                                                    $orderDataValue[13][] = $nextMonth;
                                                                    //$d1->add(new DateInterval('P30D'));
                                                                }
                                                                
                                                                $orderDataValue[5][1] = implode(";",$matric_type);
                                                                $ReportingPeriodFlag = 1;
                                                            }
                                                        }
                                                        
                                                        
                                                        else{
                                                            $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                        }
                                                    }
                                                    
                                                    if(isset($matricValueFlage) && $matricValueFlage==1){
                                                        $lenghtOfColumn = count($orderDataValue[13]);
                                                        foreach($instanceArray as $valueofInstanceValue){
                                                            $SingleColumnCopy = $SingleColumn;
                                                            $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                            $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                            $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                            $orderDataValue[] = $SingleColumnCopy;
                                                        }
                                                    }else{
                                                        $orderDataValue[] = $SingleColumn;
                                                    }
                                                }
                                                try{
                                                    Excel::create($filename, function ($excel) use ($orderDataValue) {
                                                        // Set the spreadsheet title, creator, and description
                                                        $excel->setTitle('Error Report');
                                                        $excel->setCreator('Laravel')->setCompany('Counter Project');
                                                        $excel->setDescription('xlsx report file');
                                                        
                                                        // Build the spreadsheet, passing in the $dataValue
                                                        $excel->sheet('sheet1', function ($sheet) use ($orderDataValue) {
                                                            $sheet->fromArray($orderDataValue, null, 'A1', false, false);
                                                        });
                                                    })->store('xlsx', $destinationPath);
                                                    
                                                } catch (Exception $exception) {
                                                    report($exception);
                                                    return parent::render($request, $exception);
                                                }
                                                unlink($destinationPath . $file);
                                                
                                            } else if($Rid === 'PR' || $Rid === 'PR_P1') {
                                                
                                                $orderDataValue[] = array();
                                                $BodyReportHeading = array(
                                                    'Platform',
                                                    'YOP',
                                                    'Data_Type',
                                                    'Access_Type',
                                                    'Access_Method',
                                                    'Metric_Type',
                                                    'Reporting_Period_Total'
                                                );
                                                $orderDataValue[] = $BodyReportHeading;
                                                $LastIndexOfJsonReport = count($dataValue1);
                                                
                                                $ReportingPeriodFlag = 0;
                                                for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                                {
                                                    $matricValueFlage = 0;
                                                    $SingleColumn = array();
                                                    $CurrentRowValues = $dataValue1[$iCount];
                                                    foreach($BodyReportHeading as $BodyHeader){
                                                        
                                                        if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                            $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                            $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                            $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                            
                                                            if(is_array($ReportingInstance)){
                                                                $matricValueFlage = 1;
                                                                if(isset($instanceArray))
                                                                    unset($instanceArray);
                                                                    $instanceArray = array();
                                                                    foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                        $instanceArray[] = $valueofInstance;
                                                                    }
                                                            }
                                                            $matric_type = array_column($instanceArray, 'Metric_Type');
                                                            
                                                            if($ReportingPeriodFlag === 0){
                                                                $d1 = new DateTime($start_date);
                                                                $d2 = new DateTime($end_date);
                                                                $interval = $d2->diff($d1);
                                                                $Differences = $interval->format('%m');
                                                                for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                    $nextMonth = date('Y-m', strtotime("+".$Irp." months", strtotime($start_date)));
                                                                    $orderDataValue[13][] = $nextMonth;
                                                                }
                                                                $orderDataValue[5][1] = implode(";",$matric_type);
                                                                $ReportingPeriodFlag = 1;
                                                            }
                                                        }
                                                        else{
                                                            $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                        }
                                                    }
                                                    if(isset($matricValueFlage) && $matricValueFlage==1){
                                                        $lenghtOfColumn = count($orderDataValue[13]);
                                                        foreach($instanceArray as $valueofInstanceValue){
                                                            $SingleColumnCopy = $SingleColumn;
                                                            $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                            $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                            $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                            $orderDataValue[] = $SingleColumnCopy;
                                                        }
                                                    }else{
                                                        $orderDataValue[] = $SingleColumn;
                                                    }
                                                }
                                                try{
                                                    Excel::create($filename, function ($excel) use ($orderDataValue) {
                                                    // Set the spreadsheet title, creator, and description
                                                    $excel->setTitle('Error Report');
                                                    $excel->setCreator('Laravel')->setCompany('Counter Project');
                                                    $excel->setDescription('xlsx report file');

                                                    // Build the spreadsheet, passing in the $dataValue
                                                    $excel->sheet('sheet1', function ($sheet) use ($orderDataValue) {
                                                        $sheet->fromArray($orderDataValue, null, 'A1', false, false);
                                                    });
                                                    })->store('xlsx', $destinationPath);
                                                    
                                                } catch (Exception $exception) {
                                                    report($exception);
                                                    return parent::render($request, $exception);
                                                }
                                                unlink($destinationPath . $file);
                                                
                                            } else if($Rid === 'IR_A1' || $Rid === 'IR_M1' || $Rid === 'IR'){
                                                
                                                $orderDataValue[] = array();
                                                $BodyReportHeading = array(
                                                    'Item',
                                                    'Publisher',
                                                    'Publisher_ID',
                                                    'Platform',
                                                    'Authors',
                                                    'Publication_Date',
                                                    'Article_Version',
                                                    'DOI',
                                                    'Proprietary_ID',
                                                    'ISBN',
                                                    'Print_ISSN',
                                                    'Online_ISSN',
                                                    'URI',
                                                    'Parent_Title',
                                                    'Parent_Data_Type',
                                                    'Parent_DOI',
                                                    'Parent_Proprietary_ID',
                                                    'Parent_ISBN',
                                                    'Parent_Print_ISSN',
                                                    'Parent_Online_ISSN',
                                                    'Parent_URI',
                                                    'Component_Title',
                                                    'Component_Data_Type',
                                                    'Component_DOI',
                                                    'Component_Proprietary_ID',
                                                    'Component_ISBN',
                                                    'Component_Print_ISSN',
                                                    'Component_Online_ISSN',
                                                    'Component_URI',
                                                    'Data_Type',
                                                    'Section_Type',
                                                    'YOP',
                                                    'Access_Type',
                                                    'Access_Method',
                                                    'Metric_Type',
                                                    'Reporting_Period_Total');
                                                
                                                $orderDataValue[] = $BodyReportHeading;
                                                
                                                $LastIndexOfJsonReport = count($dataValue1);
                                                
                                                $ReportingPeriodFlag = 0;
                                                for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                                {
                                                    $matricValueFlage = 0;
                                                    
                                                    $SingleColumn = array();
                                                    $CurrentRowValues = $dataValue1[$iCount];
                                                    foreach($BodyReportHeading as $BodyHeader){
                                                        if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                            $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                            // echo "<pre>ALL";print_r($AllMatricTypeValue);die;
                                                            
                                                            $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                            
                                                            $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                            //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                            if(is_array($ReportingInstance)){
                                                                $matricValueFlage = 1;
                                                                if(isset($instanceArray))
                                                                    unset($instanceArray);
                                                                    $instanceArray = array();
                                                                    foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                        $instanceArray[] = $valueofInstance;
                                                                        
                                                                    }
                                                            }
                                                            
                                                            
                                                            $matric_type = array_column($instanceArray, 'Metric_Type');
                                                            
                                                            if($ReportingPeriodFlag === 0){
                                                                $d1 = new DateTime($start_date);
                                                                $d2 = new DateTime($end_date);
                                                                $interval = $d2->diff($d1);
                                                                $Differences = $interval->format('%m');
                                                                //echo "<pre>"; print_r($Differences);die;
                                                                for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                    $nextMonth = date('Y-m', strtotime("+".$Irp." months", strtotime($start_date)));
                                                                    $orderDataValue[13][] = $nextMonth;
                                                                    //$d1->add(new DateInterval('P30D'));
                                                                }
                                                                
                                                                $orderDataValue[5][1] = implode(";",$matric_type);
                                                                $ReportingPeriodFlag = 1;
                                                            }
                                                            
                                                            
                                                            
                                                            
                                                        }
                                                        else if($BodyHeader==='DOI'){
                                                            $updateFlage = 0;
                                                            $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                            foreach($ItemIdValue as $dataValueOfColumn){
                                                                if($dataValueOfColumn['Type']=='DOI'){
                                                                    $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                    $updateFlage = 1;
                                                                }
                                                                
                                                            }
                                                            if($updateFlage==0){
                                                                $SingleColumn['DOI'] = '';
                                                            }
                                                            
                                                        }
                                                        
                                                        else if($BodyHeader==='Online_ISSN'){
                                                            $updateFlage = 0;
                                                            $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                            foreach($ItemIdValue as $dataValueOfColumn){
                                                                if($dataValueOfColumn['Type']=='Online_ISSN'){
                                                                    $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                    $updateFlage = 1;
                                                                }
                                                                
                                                            }
                                                            if($updateFlage==0){
                                                                $SingleColumn['Online_ISSN'] = '';
                                                            }
                                                        }
                                                        
                                                        else if($BodyHeader==='Print_ISSN'){
                                                            $updateFlage = 0;
                                                            $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                            foreach($ItemIdValue as $dataValueOfColumn){
                                                                if($dataValueOfColumn['Type']=='Print_ISSN'){
                                                                    $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                    $updateFlage = 1;
                                                                }
                                                                
                                                            }
                                                            if($updateFlage==0){
                                                                $SingleColumn['Print_ISSN'] = '';
                                                            }
                                                            
                                                        }
                                                        
                                                        else{
                                                            $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                        }
                                                    }
                                                    
                                                    //making duplicate rows for Metric_Type
                                                    if(isset($matricValueFlage) && $matricValueFlage==1){
                                                        $lenghtOfColumn = count($orderDataValue[13]);
                                                        foreach($instanceArray as $valueofInstanceValue){
                                                            $SingleColumnCopy = $SingleColumn;
                                                            $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                            $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                            $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                            $orderDataValue[] = $SingleColumnCopy;
                                                        }
                                                    }else{
                                                        $orderDataValue[] = $SingleColumn;
                                                    }
                                                }
                                                
                                                try {
                                                    //echo "<pre>ALL".$matricValueFlage;print_r($orderDataValue);die;
                                                    // xlsx creation
                                                    Excel::create($filename, function ($excel) use ($orderDataValue) {
                                                        // Set the spreadsheet title, creator, and description
                                                        $excel->setTitle('Error Report');
                                                        $excel->setCreator('Laravel')->setCompany('Counter Project');
                                                        $excel->setDescription('xlsx report file');
                                                        
                                                        // Build the spreadsheet, passing in the $dataValue
                                                        $excel->sheet('sheet1', function ($sheet) use ($orderDataValue) {
                                                            $sheet->fromArray($orderDataValue, null, 'A1', false, false);
                                                        });
                                                    })->store('xlsx', $destinationPath);
                                                } catch (Exception $exception) {
                                                    report($exception);
                                                    
                                                    return parent::render($request, $exception);
                                                }
                                                
                                                unlink($destinationPath . $file);
                                                
                                            } else {
                                                
                                                $dataValue1=array();
                                                
                                            }
                                            
                                        //} else if($selectedFormat==='TSV' || $selectedFormat==='ConsolidatedTSV') {
                                        } else if($selectedFormat==='TSV') {
                                            
                                            $CorrectHeaderSequence = array(
                                                'Report_Name',
                                                'Report_ID',
                                                'Release',
                                                'Institution_Name',
                                                'Institution_ID',
                                                'Metric_Types',
                                                'Report_Filters',
                                                'Report_Attributes',
                                                'Exceptions',
                                                'Reporting_Period',
                                                'Created',
                                                'Created_By'
                                            );  
                                            
                                            $Rid = $dataValue1[3][1];
                                            
                                            // getting array for header from JSON Data
                                            $JsonHeaderValues = array();
                                            for($i=0;$i<11;$i++){
                                                if(isset($dataValue1[$i]) && array_key_exists(1,$dataValue1[$i]))
                                                    $JsonHeaderValues[$dataValue1[$i][0]] = $dataValue1[$i][1]??'';
                                            }
                                            
                                            $orderDataValue = array();
                                            $rname = str_replace('"', '', $JsonHeaderValues['Report_Name']);
                                         
                                            foreach($CorrectHeaderSequence as $HeaderHeading){
                                                if($HeaderHeading=='Reporting_Period'){
                                                    //echo "<pre>";print_r($JsonHeaderValues);die;
                                                    if(isset($JsonHeaderValues['Report_Filters']) && !empty($JsonHeaderValues['Report_Filters'])){
                                                        $makecompatibleDate = explode(";",$JsonHeaderValues['Report_Filters']);
                                                        $BeginDate = explode("=",$makecompatibleDate[0]);
                                                        $EndDate = explode("=",$makecompatibleDate[1]);

                                                        $start_date = $BeginDate[1];
                                                        $end_date = $EndDate[1];
                                                        $BeginDate[1] = date("Y-m-t", strtotime($start_date));
                                                        $EndDate[1] = date("Y-m-t", strtotime($end_date));
                                                        $FilterValueDate = implode('=',$BeginDate)."; ".implode('=',$EndDate);
                                                    }else{
                                                        $FilterValueDate = '';
                                                    }
                                                    //$orderDataValue[] =  array($HeaderHeading,$JsonHeaderValues['Report_Filters']??'');
                                                    $orderDataValue[] =  array($HeaderHeading,$FilterValueDate??'');
                                                    
                                                } else {
                                                    if($HeaderHeading=='Report_Filters'){
                                                        $orderDataValue[] =  array($HeaderHeading,'');
                                                    } else if($HeaderHeading=='Report_Name') {
                                                        $orderDataValue[] =  array($HeaderHeading,$rname);
                                                    }else{
                                                        $orderDataValue[] =  array($HeaderHeading,$JsonHeaderValues[$HeaderHeading]??'');
                                                    }
                                                }
                                            }
                                            
                                            
                                        // tsv creation start
                                        $filenametsv = $provider_name . "_" . $Member['customer_id'] . "_" . $ReportCode['report_code'] . "_5_" . $begin_date . "_" . $end_date . "_"  .$rundate. ".tsv";
                                        $FileNameForSize = $filenametsv; 
                                        
                                        $filenametsv = $destinationPath . $filenametsv;
                                        if($Rid === 'TR' || $Rid === 'TR_J1' || $Rid === 'TR_J2' || $Rid === 'TR_J3' || $Rid === 'TR_J4' || $Rid === 'TR_B1' || $Rid === 'TR_B2' || $Rid === 'TR_B3'){
                                            
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Title',
                                                'Publisher',
                                                'Publisher_ID',
                                                'Platform',
                                                'DOI',
                                                'Online_ISSN',
                                                'Print_ISSN',
                                                'Proprietary_ID',
                                                'ISBN',
                                                'Data_Type',
                                                'Section_Type',
                                                'YOP',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total'
                                            );
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                $matricValueFlage = 0;
                                                
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount]??'';
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    if($BodyHeader==='Metric_Type'  || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>ALL";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if(isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance??'';
                                                                    
                                                                }
                                                        }
                                            
                                                        $matric_type = array_column($instanceArray, 'Metric_Type');
                                                        
                                                        if($ReportingPeriodFlag === 0){
                                                            $d1 = new DateTime($start_date);
                                                            $d2 = new DateTime($end_date);
                                                            $interval = $d2->diff($d1);
                                                            $Differences = $interval->format('%m');
                                                            //echo "<pre>"; print_r($Differences);die;
                                                            for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                $nextMonth = date('Y-m', strtotime("+".$Irp." months", strtotime($start_date)));
                                                                $orderDataValue[13][] = $nextMonth;
                                                                //$d1->add(new DateInterval('P30D'));
                                                            }
                                                            
                                                            $orderDataValue[5][1] = implode(";",$matric_type);
                                                            $ReportingPeriodFlag = 1;
                                                        } 
                                                    }
                                                        else if($BodyHeader==='DOI'){
                                                            $updateFlage = 0;
                                                            $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                            foreach($ItemIdValue as $dataValueOfColumn){
                                                                if($dataValueOfColumn['Type']=='DOI'){
                                                                    $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                    $updateFlage = 1;
                                                                }
                                                                
                                                            }
                                                            if($updateFlage==0){
                                                                $SingleColumn['DOI'] = '';
                                                            }
                                                            
                                                        }
                                                        
                                                        else if($BodyHeader==='Online_ISSN'){
                                                            $updateFlage = 0;
                                                            $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                            foreach($ItemIdValue as $dataValueOfColumn){
                                                                if($dataValueOfColumn['Type']=='Online_ISSN'){
                                                                    $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                    $updateFlage = 1;
                                                                }
                                                                
                                                            }
                                                            if($updateFlage==0){
                                                                $SingleColumn['Online_ISSN'] = '';
                                                            }
                                                            
                                                        }
                                                        
                                                        else if($BodyHeader==='Print_ISSN'){
                                                            $updateFlage = 0;
                                                            $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                            foreach($ItemIdValue as $dataValueOfColumn){
                                                                if($dataValueOfColumn['Type']=='Print_ISSN'){
                                                                    $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                    $updateFlage = 1;
                                                                }
                                                                
                                                            }
                                                            if($updateFlage==0){
                                                                $SingleColumn['Print_ISSN'] = '';
                                                            }
                                                            
                                                        }
                                                        
                                                        else{
                                                            $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                        }
                                                }
                                                
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn??'';
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting??'';
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy??'';
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn??'';
                                                }
                                            }
                                            
                                            array_walk_recursive($orderDataValue, function (&$value) {
                                                $value = '"'. addslashes($value).'"';
                                            });
                                            
                                            // TSV creation
                                            try {
                                            $TsvContentValue = '';
                                            $myfile = fopen($filenametsv, "w+") or die("Unable to open file!");
                                            foreach ($orderDataValue as $ContentOfTSV) {
                                                $TsvContentValue = implode("\t", $ContentOfTSV) . "\n";
                                                fwrite($myfile, $TsvContentValue);
                                            }
                                            
                                            fclose($myfile);
                                            
                                            } catch (Exception $exception) {
                                                report($exception);
                                                
                                                return parent::render($request, $exception);
                                            }
                                            //deletion of json
                                            unlink($destinationPath . $file);
                                        } else if($Rid === 'DR_D1' || $Rid === 'DR_D2' || $Rid === 'DR') {
                                            
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Database',
                                                'Publisher',
                                                'Publisher_ID',
                                                'Platform',
                                                'Proprietary_ID',
                                                'Data_Type',
                                                'YOP',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total');
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                $matricValueFlage = 0;
                                                
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                
                                                
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        
                                                        $d1 = new DateTime($ReportingPeriod['Begin_Date']);
                                                        $d2 = new DateTime($ReportingPeriod['End_Date']);
                                                        
                                                        $interval = $d2->diff($d1);
                                                        
                                                        // echo "<pre>";print_r($interval);die;
                                                        
                                                        $Differences = $interval->format('%m');
                                                        /* if($ReportingPeriodFlag === 0){
                                                         for($Irp=0;$Irp<=$Differences;$Irp++){
                                                         $orderDataValue[13+$Irp][] = $d1->format('Y-m');
                                                         //$d1->add(new DateInterval('P30D'));
                                                         }
                                                         $ReportingPeriodFlag = 1;
                                                         } */
                                                        
                                                        // die('fhfg');
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        
                                                        
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if (isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                }
                                                        }
                                                    } else {
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                    }
                                                }
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            
                                            array_walk_recursive($orderDataValue, function (&$value) {
                                                $value = '"'. addslashes($value).'"';
                                            });
                                            
                                            try {
                                            $TsvContentValue = '';
                                            $myfile = fopen($filenametsv, "w+") or die("Unable to open file!");
                                            
                                            
                                            foreach ($orderDataValue as $ContentOfTSV) {
                                                $TsvContentValue = implode("\t", $ContentOfTSV) . "\n";
                                                fwrite($myfile, $TsvContentValue);
                                            }
                                            
                                            fclose($myfile);
                                            
                                            } catch (Exception $exception) {
                                                report($exception);
                                                
                                                return parent::render($request, $exception);
                                            }
                                            //deletion of json
                                            unlink($destinationPath . $file);
                                            
                                        } else if($Rid === 'PR' || $Rid === 'PR_P1') {
                                            
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Platform',
                                                'YOP',
                                                'Data_Type',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total'
                                            );
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                
                                                $matricValueFlage = 0;
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        
                                                        $d1 = new DateTime($ReportingPeriod['Begin_Date']);
                                                        $d2 = new DateTime($ReportingPeriod['End_Date']);
                                                        
                                                        $interval = $d2->diff($d1);
                                                        
                                                        // echo "<pre>";print_r($interval);die;
                                                        
                                                        $Differences = $interval->format('%m');
                                                         if($ReportingPeriodFlag === 0){
                                                         for($Irp=0;$Irp<=$Differences;$Irp++){
                                                         $orderDataValue[13+$Irp][] = $d1->format('Y-m');
                                                         //$d1->add(new DateInterval('P30D'));
                                                         }
                                                         $ReportingPeriodFlag = 1;
                                                         } 
                                                        
                                                        // die('fhfg');
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        
                                                        
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if (isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                }
                                                        }
                                                    } else {
                                                        
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                    }
                                                }
                                                
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';$reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            
                                            array_walk_recursive($orderDataValue, function (&$value) {
                                                $value = '"'. addslashes($value).'"';
                                            });
                                            
                                            try {
                                            $TsvContentValue = '';
                                            $myfile = fopen($filenametsv, "w+") or die("Unable to open file!");
                                            
                                            
                                            foreach ($orderDataValue as $ContentOfTSV) {
                                                $TsvContentValue = implode("\t", $ContentOfTSV) . "\n";
                                                fwrite($myfile, $TsvContentValue);
                                            }
                                            
                                            fclose($myfile);
                                            } catch (Exception $exception) {
                                            report($exception);
                                            
                                            return parent::render($request, $exception);
                                            }
                                            
                                            unlink($destinationPath . $file);
                                            
                                        } else if($Rid === 'IR_A1' || $Rid === 'IR_M1' || $Rid === 'IR'){
                                            
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Item',
                                                'Publisher',
                                                'Publisher_ID',
                                                'Platform',
                                                'Authors',
                                                'Publication_Date',
                                                'Article_Version',
                                                'DOI',
                                                'Proprietary_ID',
                                                'ISBN',
                                                'Print_ISSN',
                                                'Online_ISSN',
                                                'URI',
                                                'Parent_Title',
                                                'Parent_Data_Type',
                                                'Parent_DOI',
                                                'Parent_Proprietary_ID',
                                                'Parent_ISBN',
                                                'Parent_Print_ISSN',
                                                'Parent_Online_ISSN',
                                                'Parent_URI',
                                                'Component_Title',
                                                'Component_Data_Type',
                                                'Component_DOI',
                                                'Component_Proprietary_ID',
                                                'Component_ISBN',
                                                'Component_Print_ISSN',
                                                'Component_Online_ISSN',
                                                'Component_URI',
                                                'Data_Type',
                                                'Section_Type',
                                                'YOP',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total');
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                
                                                $matricValueFlage = 0;
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>ALL";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        $d1 = new DateTime($ReportingPeriod['Begin_Date']);
                                                        $d2 = new DateTime($ReportingPeriod['End_Date']);
                                                        
                                                        $interval = $d2->diff($d1);
                                                        
                                                        $Differences = $interval->format('%m');
                                                        if($ReportingPeriodFlag === 0){
                                                            for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                $orderDataValue[13+$Irp][] = $d1->format('Y-m');
                                                                //$d1->add(new DateInterval('P30D'));
                                                            }
                                                            $ReportingPeriodFlag = 1;
                                                        }
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if(isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                }
                                                        }
                                                    }
                                                    
                                                    else if($BodyHeader==='DOI' || $BodyHeader==='Online_ISSN' || $BodyHeader==='Print_ISSN'){
                                                        $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                        foreach($ItemIdValue as $dataValueOfColumn){
                                                            $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value'];
                                                        }
                                                    } else {
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                    }
                                                }
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            array_walk_recursive($orderDataValue, function (&$value) {
                                                $value = '"'. addslashes($value).'"';
                                            });
                                            
                                            try {
                                            $TsvContentValue = '';
                                            $myfile = fopen($filenametsv, "w+") or die("Unable to open file!");
                                            
                                            
                                            foreach ($orderDataValue as $ContentOfTSV) {
                                                $TsvContentValue = implode("\t", $ContentOfTSV) . "\n";
                                                fwrite($myfile, $TsvContentValue);
                                            }
                                            
                                            fclose($myfile);
                                        } catch (Exception $exception) {
                                            report($exception);
                                            
                                            return parent::render($request, $exception);
                                        }
                                            
                                            
                                            unlink($destinationPath . $file);
                                            
                                        } else {
                                            
                                            $dataValue1=array();
                                            
                                        }
                                        
                                        }
                                        
                                        else if($selectedFormat==='ConsolidatedTSV')
                                        {
                                            $CorrectHeaderSequence = array(
                                                'Report_Name',
                                                'Report_ID',
                                                'Release',
                                                'Institution_Name',
                                                'Institution_ID',
                                                'Metric_Types',
                                                'Report_Filters',
                                                'Report_Attributes',
                                                'Exceptions',
                                                'Reporting_Period',
                                                'Created',
                                                'Created_By'
                                            );
                                            
                                            $Rid = $dataValue1[3][1];
                                            
                                            // getting array for header from JSON Data
                                            $JsonHeaderValues = array();
                                            for($i=0;$i<11;$i++){
                                                if(isset($dataValue1[$i]) && array_key_exists(1,$dataValue1[$i]))
                                                    $JsonHeaderValues[$dataValue1[$i][0]] = $dataValue1[$i][1]??'';
                                            }
                                            
                                            $orderDataValue = array();
                                            $rname = str_replace('"', '', $JsonHeaderValues['Report_Name']);
                                            
                                            foreach($CorrectHeaderSequence as $HeaderHeading){
                                                if($HeaderHeading=='Reporting_Period'){
                                                        //echo "<pre>";print_r($JsonHeaderValues);die;
                                                        $makecompatibleDate = explode(";",$JsonHeaderValues['Report_Filters']);
                                                        if(isset($JsonHeaderValues['Report_Filters']) && !empty($JsonHeaderValues['Report_Filters'])){
                                                        $BeginDate = explode("=",$makecompatibleDate[0]);
                                                        $EndDate = explode("=",$makecompatibleDate[1]);

                                                        $start_date = $BeginDate[1];
                                                        $end_date = $EndDate[1];
                                                        $BeginDate[1] = date("Y-m-t", strtotime($start_date));
                                                        $EndDate[1] = date("Y-m-t", strtotime($end_date));
                                                        $FilterValueDate = implode('=',$BeginDate)."; ".implode('=',$EndDate);
                                                    }else{
                                                        $FilterValueDate = '';
                                                    }
                                                    //$orderDataValue[] =  array($HeaderHeading,$JsonHeaderValues['Report_Filters']??'');
                                                    $orderDataValue[] =  array($HeaderHeading,$FilterValueDate??'');
                                                    
                                                    
                                                } else {
                                                    if($HeaderHeading=='Report_Filters'){
                                                        $orderDataValue[] =  array($HeaderHeading,'');
                                                    } else if($HeaderHeading=='Report_Name') {
                                                        $orderDataValue[] =  array($HeaderHeading,$rname);
                                                    }else{
                                                        $orderDataValue[] =  array($HeaderHeading,$JsonHeaderValues[$HeaderHeading]??'');
                                                    }
                                                }
                                            }
                                            
                                            for($i=0;$i<11;$i++){
                                                if(isset($dataValue1[$i]) && array_key_exists(1,$dataValue1[$i]))
                                                    $JsonHeaderValues[$dataValue1[$i][0]] = $dataValue1[$i][1];
                                            }
                                            
                                            
                                        // tsv creation start
                                        $filenametsv = $provider_name . "_" . $Member['customer_id'] . "_" . $ReportCode['report_code'] . "_5_" . $begin_date . "_" . $end_date . "_" .$rundate. ".tsv";
                                        $FileNameForSize = $filenametsv;
                                        
                                        $filenametsv = $destinationPath . $filenametsv;
                                        if($Rid === 'TR' || $Rid === 'TR_J1' || $Rid === 'TR_J2' || $Rid === 'TR_J3' || $Rid === 'TR_J4' || $Rid === 'TR_B1' || $Rid === 'TR_B2' || $Rid === 'TR_B3'){
                                            
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Title',
                                                'Publisher',
                                                'Publisher_ID',
                                                'Platform',
                                                'DOI',
                                                'Online_ISSN',
                                                'Print_ISSN',
                                                'Proprietary_ID',
                                                'ISBN',
                                                'Data_Type',
                                                'Section_Type',
                                                'YOP',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total'
                                            );
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                $matricValueFlage = 0;
                                                
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>ALL";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if(isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                    
                                                                }
                                                        }
                                                        
                                                        $matric_type = array_column($instanceArray, 'Metric_Type');
                                                        
                                                        if($ReportingPeriodFlag === 0){
                                                            $d1 = new DateTime($start_date);
                                                            $d2 = new DateTime($end_date);
                                                            $interval = $d2->diff($d1);
                                                            $Differences = $interval->format('%m');
                                                            //echo "<pre>"; print_r($Differences);die;
                                                            for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                $nextMonth = date('Y-m', strtotime("+".$Irp." months", strtotime($start_date)));
                                                                $orderDataValue[13][] = $nextMonth;
                                                                //$d1->add(new DateInterval('P30D'));
                                                            }
                                                            
                                                            $orderDataValue[5][1] = implode(";",$matric_type);
                                                            $ReportingPeriodFlag = 1;
                                                        }
                                                    }
                                                    
                                                    
                                                    else if($BodyHeader==='DOI'){
                                                        $updateFlage = 0;
                                                        
                                                        if(isset($CurrentRowValues['Item_ID'])){
                                                        $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                        foreach($ItemIdValue as $dataValueOfColumn){
                                                            if($dataValueOfColumn['Type']=='DOI'){
                                                                $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                $updateFlage = 1;
                                                            }
                                                          }
                                                        } else {
                                                             $updateFlage = 0;
                                                        }
                                                        if($updateFlage==0){
                                                            $SingleColumn['DOI'] = '';
                                                        }
                                                        
                                                    }
                                                    
                                                    else if($BodyHeader==='Online_ISSN'){
                                                        $updateFlage = 0;
                                                        
                                                        if(isset($CurrentRowValues['Item_ID'])){
                                                        $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                        foreach($ItemIdValue as $dataValueOfColumn){
                                                            if($dataValueOfColumn['Type']=='Online_ISSN'){
                                                                $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                $updateFlage = 1;
                                                            }
                                                          }
                                                        } else {
                                                            $updateFlage = 0;
                                                        }
                                                        if($updateFlage==0){
                                                            $SingleColumn['Online_ISSN'] = '';
                                                        }
                                                        
                                                    }
                                                    
                                                    else if($BodyHeader==='Print_ISSN'){
                                                        $updateFlage = 0;
                                                        
                                                        if(isset($CurrentRowValues['Item_ID'])){
                                                        $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                        foreach($ItemIdValue as $dataValueOfColumn){
                                                            if($dataValueOfColumn['Type']=='Print_ISSN'){
                                                                $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value']??'';
                                                                $updateFlage = 1;
                                                            }
                                                          }
                                                        } else {
                                                             $updateFlage = 0;
                                                        }
                                                        if($updateFlage==0){
                                                            $SingleColumn['Print_ISSN'] = '';
                                                        }
                                                        
                                                    }
                                                    
                                                    else{
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                    }
                                                }
                                                
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            // TSV creation
                                            $TsvContentValue = '';
                                            $AllConsolidatedTSV[$Rid][]=$orderDataValue;
                                            
                                            
                                            //deletion of json
                                            unlink($destinationPath . $file);
                                            
                                        } else if($Rid === 'DR_D1' || $Rid === 'DR_D2' || $Rid === 'DR') {
                                            
                                           
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Database',
                                                'Publisher',
                                                'Publisher_ID',
                                                'Platform',
                                                'Proprietary_ID',
                                                'Data_Type',
                                                'YOP',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total');
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                $matricValueFlage = 0;
                                                
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                
                                                
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        
                                                        $d1 = new DateTime($ReportingPeriod['Begin_Date']);
                                                        $d2 = new DateTime($ReportingPeriod['End_Date']);
                                                        
                                                        $interval = $d2->diff($d1);
                                                        
                                                        // echo "<pre>";print_r($interval);die;
                                                        
                                                        $Differences = $interval->format('%m');
                                                        /* if($ReportingPeriodFlag === 0){
                                                         for($Irp=0;$Irp<=$Differences;$Irp++){
                                                         $orderDataValue[13+$Irp][] = $d1->format('Y-m');
                                                         //$d1->add(new DateInterval('P30D'));
                                                         }
                                                         $ReportingPeriodFlag = 1;
                                                         } */
                                                        
                                                        // die('fhfg');
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        
                                                        
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if (isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                }
                                                        }
                                                    } else {
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                    }
                                                }
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            
                                            try {
                                                
                                            $TsvContentValue = '';
                                            $AllConsolidatedTSV[$Rid][]=$orderDataValue;
                                            
                                            } catch (Exception $exception) {
                                                
                                                report($exception);
                                                
                                                return parent::render($request, $exception);
                                            }
                                            
                                            //deletion of json
                                            unlink($destinationPath . $file);
                                           
                                        } else if($Rid === 'PR' || $Rid === 'PR_P1') {
                                           
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Platform',
                                                'YOP',
                                                'Data_Type',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total'
                                            );
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                
                                                $matricValueFlage = 0;
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        
                                                        $d1 = new DateTime($ReportingPeriod['Begin_Date']);
                                                        $d2 = new DateTime($ReportingPeriod['End_Date']);
                                                        
                                                        $interval = $d2->diff($d1);
                                                        
                                                        // echo "<pre>";print_r($interval);die;
                                                        
                                                        $Differences = $interval->format('%m');
                                                        if($ReportingPeriodFlag === 0){
                                                            for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                $orderDataValue[13+$Irp][] = $d1->format('Y-m');
                                                                //$d1->add(new DateInterval('P30D'));
                                                            }
                                                            $ReportingPeriodFlag = 1;
                                                        }
                                                        
                                                        // die('fhfg');
                                                        
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        
                                                        
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if (isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                }
                                                        }
                                                    } else {
                                                        
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader]??'';
                                                    }
                                                }
                                                
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            try {
                                                
                                                $TsvContentValue = '';
                                                $AllConsolidatedTSV[$Rid][]=$orderDataValue;
                                                
                                            } catch (Exception $exception) {
                                                
                                                report($exception);
                                                
                                                return parent::render($request, $exception);
                                            }
                                            
                                            //deletion of json
                                            unlink($destinationPath . $file);
                                            
                                        } else if($Rid === 'IR_A1' || $Rid === 'IR_M1' || $Rid === 'IR'){
                                            
                                            $orderDataValue[] = array();
                                            $BodyReportHeading = array(
                                                'Item',
                                                'Publisher',
                                                'Publisher_ID',
                                                'Platform',
                                                'Authors',
                                                'Publication_Date',
                                                'Article_Version',
                                                'DOI',
                                                'Proprietary_ID',
                                                'ISBN',
                                                'Print_ISSN',
                                                'Online_ISSN',
                                                'URI',
                                                'Parent_Title',
                                                'Parent_Data_Type',
                                                'Parent_DOI',
                                                'Parent_Proprietary_ID',
                                                'Parent_ISBN',
                                                'Parent_Print_ISSN',
                                                'Parent_Online_ISSN',
                                                'Parent_URI',
                                                'Component_Title',
                                                'Component_Data_Type',
                                                'Component_DOI',
                                                'Component_Proprietary_ID',
                                                'Component_ISBN',
                                                'Component_Print_ISSN',
                                                'Component_Online_ISSN',
                                                'Component_URI',
                                                'Data_Type',
                                                'Section_Type',
                                                'YOP',
                                                'Access_Type',
                                                'Access_Method',
                                                'Metric_Type',
                                                'Reporting_Period_Total');
                                            
                                            $orderDataValue[] = $BodyReportHeading;
                                            $LastIndexOfJsonReport = count($dataValue1);
                                            
                                            
                                            $ReportingPeriodFlag = 0;
                                            for($iCount=12;$iCount<$LastIndexOfJsonReport;$iCount++)
                                            {
                                                
                                                $matricValueFlage = 0;
                                                $SingleColumn = array();
                                                $CurrentRowValues = $dataValue1[$iCount];
                                                
                                                foreach($BodyReportHeading as $BodyHeader){
                                                    
                                                    if($BodyHeader==='Metric_Type' || $BodyHeader==='Reporting_Period_Total'){
                                                        $AllMatricTypeValue = unserialize($CurrentRowValues['Performance']);
                                                        // echo "<pre>ALL";print_r($AllMatricTypeValue);die;
                                                        
                                                        $ReportingPeriod = isset($AllMatricTypeValue['Period'])?$AllMatricTypeValue['Period']:'';
                                                        
                                                        $d1 = new DateTime($ReportingPeriod['Begin_Date']);
                                                        $d2 = new DateTime($ReportingPeriod['End_Date']);
                                                        
                                                        $interval = $d2->diff($d1);
                                                        
                                                        $Differences = $interval->format('%m');
                                                        if($ReportingPeriodFlag === 0){
                                                            for($Irp=0;$Irp<=$Differences;$Irp++){
                                                                $orderDataValue[13+$Irp][] = $d1->format('Y-m');
                                                                //$d1->add(new DateInterval('P30D'));
                                                            }
                                                            $ReportingPeriodFlag = 1;
                                                        }
                                                        $ReportingInstance = isset($AllMatricTypeValue['Instance'])?$AllMatricTypeValue['Instance']:'';
                                                        //if(is_array($ReportingInstance) && (count($ReportingInstance))>1){
                                                        if(is_array($ReportingInstance)){
                                                            $matricValueFlage = 1;
                                                            if(isset($instanceArray))
                                                                unset($instanceArray);
                                                                $instanceArray = array();
                                                                foreach($ReportingInstance as $keyOfInstance=>$valueofInstance){
                                                                    $instanceArray[] = $valueofInstance;
                                                                }
                                                        }
                                                    }
                                                    
                                                    else if($BodyHeader==='DOI' || $BodyHeader==='Online_ISSN' || $BodyHeader === 'Print_ISSN') {
                                                        $ItemIdValue = unserialize($CurrentRowValues['Item_ID']);
                                                        if (is_array($ItemIdValue)) {
                                                            foreach ($ItemIdValue as $dataValueOfColumn) {
                                                                $SingleColumn[$dataValueOfColumn['Type']] = $dataValueOfColumn['Value'];
                                                            }
                                                        } else {
                                                            $SingleColumn[$dataValueOfColumn['Type']] = '';
                                                        }
                                                    } else {
                                                        $SingleColumn[$BodyHeader] = $CurrentRowValues[$BodyHeader] ?? '';
                                                    }
                                                }
                                                //making duplicate rows for Metric_Type
                                                if(isset($matricValueFlage) && $matricValueFlage==1){
                                                    $lenghtOfColumn = count($orderDataValue[13]);
                                                    foreach($instanceArray as $valueofInstanceValue){
                                                        $SingleColumnCopy = $SingleColumn;
                                                        $SingleColumnCopy['Metric_Type'] = $valueofInstanceValue['Metric_Type']??'';
                                                        $SingleColumnCopy['Reporting_Period_Total'] = $valueofInstanceValue['Count']??'';
                                                        $reportingPeriodTotal = 0;
                                                            $countOfCurrentRow = count($SingleColumnCopy);
                                                            if($countOfCurrentRow<$lenghtOfColumn){
                                                                for(;$countOfCurrentRow<$lenghtOfColumn;$countOfCurrentRow++){
                                                                    $currentDate = date('Y-m',strtotime($ReportingPeriod['Begin_Date']));
                                                                    $ValueOfReporting = $orderDataValue[13][$countOfCurrentRow]==$currentDate?$valueofInstanceValue['Count']:'0';
                                                                    $reportingPeriodTotal = $reportingPeriodTotal + (int)$ValueOfReporting;
                                                                    $SingleColumnCopy[]= $ValueOfReporting;
                                                                }
                                                            }
                                                        $orderDataValue[] = $SingleColumnCopy;
                                                    }
                                                }else{
                                                    $orderDataValue[] = $SingleColumn;
                                                }
                                            }
                                            
                                            try {
                                                
                                                $TsvContentValue = '';
                                                $AllConsolidatedTSV[$Rid][]=$orderDataValue;
                                                
                                            } catch (Exception $exception) {
                                                
                                                report($exception);
                                                
                                                return parent::render($request, $exception);
                                            }
                                            
                                            //deletion of json
                                            unlink($destinationPath . $file);
                                            
                                        } else {
                                            
                                            $dataValue1=array();
                                            
                                        }
                                        
                                        }
                                        
                                        
                                        // tsv creation end
                                        
                                        try{
                                            $fileSize = filesize($destinationPath . $FileNameForSize);
                                        }catch(Exception $exception){
                                            $fileSize=0;
                                        }
                                        
                                        // updating for file size and status
                                        $UpdateTransactionInfo = array(
                                            'status' => 2,
                                            'message' => 'success',
                                            'remarks' => $Remarks,
                                            'exception' => '',
                                            'details' => 'Success:',
                                            'file_name' => $file,
                                            'file_size' => $fileSize,
                                            'end_date_time' => $startdatetimstampEnd
                                        );
                                        
                                        DB::beginTransaction();
                                        try {
                                            Transactionmasterdetail::where(array(
                                                'transaction_id' => $TransactionId,
                                                'user_id' => $user->email,
                                                'member_name' => $Name,
                                                'report_id' => $ReportCode['report_code']
                                            ))->update($UpdateTransactionInfo);
                                            DB::commit();
                                        } catch (Exception $exception) {
                                            DB::rollback();
                                        }
                                    
                                    }
                                }
                            }
                        }
                }
            }
            
            // converting Zip File
            // root Directory
            //echo "<pre>Root";print_r($AllConsolidatedTSV);die;
            
            if($selectedFormat==='ConsolidatedTSV'){
                if(isset($AllDataValue))
                    unset($AllDataValue);
                 $AllDataValue = array();
                foreach($AllConsolidatedTSV as $ReportName=>$AllReportValues){
                    $firsttimeheader = 0;
                    if(isset($orderDataValueNew))
                    unset($orderDataValueNew);
                    // $orderDataValueNew[] = array();
                    foreach($AllReportValues as $keyOfIndex=>$ReportValue){
                        //now Report Body
                            
                            if($firsttimeheader==0){
                                for($irepotCount=0;$irepotCount<12;$irepotCount++){
                                   $orderDataValueNew[] = $ReportValue[$irepotCount];
                                }
                                $orderDataValueNew[13] =  $ReportValue[13];
                                $firsttimeheader = 1;
                            }else{
                                $orderDataValueNew[4][1] = $orderDataValueNew[4][1]??''.",".$ReportValue[4][1]??'';
                            }
                            $LastIndexOfJsonReport = count($ReportValue);
                            
                            for($iCount=14;$iCount<$LastIndexOfJsonReport;$iCount++)
                            {
                                $orderDataValueNew[] = $ReportValue[$iCount];
                            }
                            
                    }
                    $AllDataValue[$ReportName] = $orderDataValueNew;
                }
                
                
                //writting file
                foreach($AllDataValue as $keyofReportName=>$valueOfReportFinal){
                $filenametsv = $destinationPath . $keyofReportName.".tsv";
                $TsvContentValue = '';
                try {
                    $myfile = fopen($filenametsv, "w+") or die("Unable to open file!");
                    foreach ($valueOfReportFinal as $ContentOfTSV) {
                        $TsvContentValue = implode("\t", $ContentOfTSV) . "\n";
                        fwrite($myfile, $TsvContentValue);
                    }
                } catch (Exception $exception) {
                    report($exception);
                    return parent::render($request, $exception);
                }

                fclose($myfile);
                }
               
            }
            
            
            $publicpathforzip = public_path() . '/upload/json/' . $TransactionId . "/";
            $publicpathforzipNew = public_path() . '/upload/json/';
            $ZipFileName = $TransactionId . ".zip";
            $resonse = $this->convertZipFile($TransactionId, $publicpathforzip, $publicpathforzipNew . "/" . $ZipFileName);
            $this->removeDirectory($publicpathforzip);
            echo url('/upload/json/' . $ZipFileName);
            die();
            // return response()->download($publicpathforzipNew . "/" . $ZipFileName);
            // return view('harvesting_report', $dataforpassing);
        }
    }

    // }
    // remove directory
    function removeDirectory($dir = '')
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        // $dir = 'samples' . DIRECTORY_SEPARATOR . 'sampledirtree';
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    // ///////////////// creating a zip file for download/////////////////////////
    // Here the magic happens :)
    function zipData($source, $destination, $zip)
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        
        if (extension_loaded('zip')) {
            if (file_exists($source)) {
                
                if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                    $source = realpath($source);
                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                        // $files = scandir($source);
                        foreach ($files as $file) {
                            
                            $file = realpath($file);
                            
                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source . '\\', '', $file . '\\'));
                            } else if (is_file($file) === true) {
                                $zip->addFile($file, str_replace($source . '\\', '', $file));
                            }
                        }
                    } else if (is_file($source) === true) {
                        $zip->addFile($source, basename($source));
                    }
                }
                $zip->close();
                return $zip;
            }
        }
        
        return false;
    }

    // public function zipFileDownload() {
    public function convertZipFile($TransactionId = '', $publicpathforzip = '', $publicpathforzipNew = '', $ZipFileName = '')
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($publicpathforzipNew . $ZipFileName, ZipArchive::CREATE) === TRUE) {
            $dir = preg_replace('/[\/]{2,}/', '/', $publicpathforzip . "/");
            // $dh = opendir($dir);
            $allFiles = scandir($dir);
            $files = array_diff($allFiles, array(
                '..',
                '.'
            ));
            // echo "<pre>";print_r($files);die;
            try{
            foreach ($files as $value) {
                // echo $archive_folder."/".$value."===>".$value.'<hr>';
                $zip->addFile($publicpathforzip . "/" . $value, $TransactionId . '/' . $value);
            }
            // die;
            $zip->close();
            
            } catch (Exception $exception) {
                report($exception);
                
                return parent::render($request, $exception);
            }
            return true;
        } else {
            return false;
        }
    }

    function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    // update consortium configration update
    function editConsortium()
    {
        $user = Session::get('user');
        if (session::has('user')) {
            if ($user['email'] == '') {
                return Redirect::to('/');
            }

            $data = Input::all();
            // echo "<pre>";print_r($data);die;
            $user = Session::get('user');
            $updatearray['report_name'] = $data['report_name'];
            $updatearray['report_code'] = $data['report_code'];
            
            DB::beginTransaction();
            try {
            $Reportdetail = Reportname::where('id', $data['Id'])->update($updatearray);
            DB::commit();
            } catch(Exception $exception) {
                DB::rollback();
            }
            
            $data['utype'] = $user['utype'];
            $data['userDisplayName'] = $user['display_name'];
            $data['report_detail'] = $Reportdetail;
            Session::flash('userupdatemsg', 'Report successfully updated');
            return Redirect::to('reporthistory'); // obviously wrong...
        } else {
            return Redirect::to('login');
        }
    }

    // ///////////////////////// providers details /////////////////////////////
    function providervalidate()
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }

        $Consortiums = Consortium::where(array())->orderBy('', 'asc')->get();

        $data['provider_details'] = $Consortiums;

        // //////////////////////////////////////////////
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['file_detail'] = $Consortiums;
        return view('consortium', $data);
    }

    // ///////////////////////////////
    function addProvider($Configid = 0, $ProviderId = 0) {
     
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        if ($Configid > 0 && $ProviderId > 0) {
            // get information
            $ProviderDetail = Provider::where(array(
                        'id' => $ProviderId
                    ))->get()
                    ->first()
                    ->toArray();
            $dataforpassing['SingleProvder'] = $ProviderDetail;
        }
        $Consortiums = Consortium::where(array(
                    'id' => $Configid
                ))->get()->first();

        $AllProvidersList = Provider::where(array(
                    'configuration_id' => $Configid
                ))->get();
        $dataforpassing['userDisplayName'] = $user['display_name'];
        $dataforpassing['utype'] = $user['utype'];
        $dataforpassing['information'] = $Consortiums;
        $dataforpassing['alllistofprovider'] = $AllProvidersList;
        $dataforpassing['id'] = $Configid;
        // echo "<pre>";print_r($dataforpassing);die;
        return view('provider', $dataforpassing);
    }

    // //////// saveproviders //////////
    function saveProvider() {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $data = Input::all();
        //echo "<pre>";print_r($data);die;
        $rules = array(
            'provider_name' => 'required',
            'provider_url' => 'required|url',
            //'apikey' => 'required',
            'customer_id' => 'required',
            'requestor_id' => 'required'
                // 'remarks' => 'required',
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {

            // If validation fails redirect back to login.

            return Redirect::back()->withInput($data)->withErrors($validator, 'add_provider');
        } else if (isset($data['provider_id']) && $data['provider_id'] > 0) {
            $updatedataArray = array();
            $updatedataArray['provider_name'] = $data['provider_name'];
            $updatedataArray['provider_url'] = $data['provider_url'];
            $updatedataArray['apikey'] = $data['apikey'];
            $updatedataArray['customer_id'] = $data['customer_id'];
            $updatedataArray['requestor_id'] = $data['requestor_id'];
            $updatedataArray['remarks'] = $data['remarks'];
            
            $Reportdetail = Provider::where(array(
                        'id' => $data['provider_id'],
                        'configuration_id' => $data['configuration_id']
                    ))->update($updatedataArray);
        
            Session::flash('colupdatemsg', 'Provider Updated Successfully');
            return Redirect::intended('/add_provider/' . $data['configuration_id']);
        } else {
            
                $newUser = Provider::create($data);
               
            
            $InsertedIDOfProvider = $newUser->id;
// echo "<pre>";print_r($newUser);die;
            if ($newUser) {
                Session::flash('colupdatemsg', 'Provider Added Successfully');
                
                        $mainURL =$data['provider_url'];
                        $fields = array(
                            'apikey' => $data['apikey'],
                            'customer_id' => $data['customer_id'],
                            'requestor_id' => $data['requestor_id']
                        );
                        $fields = array_filter($fields);
                        $url = $mainURL . "/members?" . http_build_query($fields, '', "&");
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
                                Session::flash('reportmsg', 'This URL is not exist.');
                                Session::put('keyurl', 'display');
                                return Redirect::intended('/filelist/');
                                //mark Shushi URL is incorrect and save in Database;
                            } else {
                               
                                $MembersList = json_decode($result);
                                
                                if(empty($MembersList)){
                                    Session::flash('reportmsg', 'This URL has been moved.');
                                    return Redirect::intended('/consortium/');
                                } else {
                                foreach($MembersList as $Member){
                                     $CustomerId = $Member->Customer_ID??'';
                                    if(!empty($Member->Requestor_ID)){
                                        $RequestorId = $Member->Requestor_ID??'';
                                    }else{
                                        $RequestorId = $data['requestor_id']??'';
                                    }
                                     $Name = $Member->Name??'';
                                     $Notes = $Member->Notes??'';
                                     $InstitutionIdType = $Member->Institution_ID[0]->Type??'';
                                     
                                     $InstitutionIdvalue = $Member->Institution_ID[0]->Value??'';
                                     $ProviderId = $InsertedIDOfProvider;
                                     
                                    $MembersValue= array(
                                            'customer_id' => $CustomerId,
                                            'requestor_id' => $RequestorId,
                                            'name' => $Name,
                                            'notes' => $Notes,
                                            'institution_id_type' => $InstitutionIdType,
                                            'institution_id_value' => $InstitutionIdvalue,
                                            'provider_id' => $ProviderId,
                                        );
                                    if(!empty($MembersValue['customer_id']))
                                        DB::beginTransaction();
                                        try {
                                        $SaveMemberNew = Members::create($MembersValue);
                                        DB::commit();
                                        } catch(Exception $exception) {
                                            DB::rollback();
                                        }
                                    // echo "<pre>";print_r($SaveMemberNew);die;
//                                     $data['allmember'] = $SaveMemberNew;

                            }
                            }
                        }
                
                        }
                return Redirect::to('add_provider/' . $data['configuration_id']);
                // return view('provider', $data);
            }
        }
    }
    
    // ////////////////Creating New View page for Import configuration////////////////////////
    public function importConfiguration()
    {
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        
        return view('import_configuration', $data);
    }

    /*
     * function add_provider(Request $request,$id)
     * {
     * if (session::has('user')){
     * $user = Session::get('user');
     *
     * $Provider = Provider::where(array('id'=>$id))->orderBy('id', 'asc')
     * ->get();
     * dd($Provider);
     * $data['consortium_config'] = $Provider;
     *
     * $data['file_detail']=$Provider;
     * //return Redirect::to('provider', $data);
     * }
     * }
     */
    public function readConfigurationFile()
    {
        // die('fgdf');
        // $data = Input::all();
        $user = Session::get('user');
        if ($user['email'] == '') {
            return Redirect::to('/');
        }
        
        if (Input::hasFile('import_file')) {
            
            $extension = Input::File('import_file')->getClientOriginalExtension();
            if ($extension == 'xlsx') {
                
                $path = Input::file('import_file')->getRealPath();
            }
            
            try{
            Excel::load($path, function ($reader) {
                // $error = array();
                $reader->calculate();
                $objExcel = $reader->getExcel();
                $sheet = $objExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $ConfigName = $sheet->rangeToArray('B1' . ':' . 'B1', NULL, TRUE, FALSE);
                $ConfigName = $ConfigName[0][0] ?? '';
                
                $user = Session::get('user');
                $reportdta = new Consortium();
                $ConsortiumDetail = Consortium::where(array(
                    'configuration_name' => $ConfigName,
                    'user_id' => $user['id'] 
                ))->orderBy('id', 'asc')
                    ->get()
                    ->first();
                // echo"<pre>hjg";print_r($ConsortiumDetail->user_id);die;
                $Remarks = $sheet->rangeToArray('D1' . ':' . 'D1', NULL, TRUE, FALSE);
                $Remarks = $Remarks[0][0] ?? '';
                // ->toArray();
                
                if (empty($ConfigName)) {
                    Session::flash('error', 'Invalid Configuration');
                    ?>
                   <script>
                        window.location.href='/consortium';
                        </script>
                 <?php
                } else if (isset($ConsortiumDetail['user_id'])) {
                    
                    
                    Session::flash('error', 'Configuration Already Exist');
                    ?>
                  <script>
                        window.location.href='/consortium';
                        </script>
                    <?php
                } else {
                    // insert case for consortium
                    $user = Session::get('user');
                    $SaveData = array(
                        'configuration_name' => $ConfigName,
                        'remarks' => $Remarks,
                        'created_by' => $user['email'],
                        'user_id' => $user['id'] 
                    );
                    
                    DB::beginTransaction();
                    try {
                    $SaveReort = Consortium::create($SaveData);
                    DB::commit();
                    } catch(Exception $exception) {
                        DB::rollback();
                    }
                    
                    $lastinsertedId = $SaveReort->id;
                    $maxloopcount = $highestRow-1;
                    $startColumn = 'A';
                    $providerMaster = array('configuration_id','provider_name','provider_url','apikey','requestor_id','customer_id');
                    //$highestColumn
                    for($maxloopcount= 3;$maxloopcount<=$highestRow;$maxloopcount++){
                        $providerDetail = array();
                        $i=0;
                       
                        for ($startColumn = 'A';$startColumn<=$highestColumn;$startColumn++){
                            
                            $id = $sheet->rangeToArray($startColumn.$maxloopcount. ':' .$startColumn.$maxloopcount,NULL, TRUE, FALSE);
                            if($i==0)
                                $providerDetail[$providerMaster[$i]] =$lastinsertedId;
                            else
                                $providerDetail[$providerMaster[$i]] =$id[0][0];
                            $i++;
                        }
                        
                        //insert in to provider detail
                        DB::beginTransaction();
                        try {
                       $provider = Provider::create($providerDetail);
                       DB::commit();
                        } catch(Exception $exception) {
                        DB::rollback();
                         }
                       
                       $ProviderDetailID = $provider['id'];
                       
                       $mainURL =$provider['provider_url'];
                       $fields = array(
                           'apikey' => $provider['apikey'],
                           'customer_id' => $provider['customer_id'],
                           'requestor_id' => $provider['requestor_id']  
                       );
                       
                       $fields = array_filter($fields);
                       //echo "<pre>";print_r($fields);die;
                       $url = $mainURL . "/members?" . http_build_query($fields, '', "&");
                       // echo "<pre>";print_r($url);die;
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
                        // echo "<pre>";print_r($result);die;
                       
                       if ($result !== false) {
                           
                           $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                           if ($statusCode == 404) {
                                Session::flash('reportmsg', 'This URL is not exist.');
                                Session::put('keyurl', 'display');
                                return Redirect::intended('/filelist/');
                               //mark Shushi URL is incorrect and save in Database;
                           } else {
                               
                               $MembersList = json_decode($result);
                               if(empty($MembersList)){
                                    Session::flash('reportmsg', 'This URL has been moved.');
                                    return Redirect::intended('/consortium/');
                                } else {
                               
                               foreach($MembersList as $Member){
                                   
                                   $CustomerId = $Member->Customer_ID??'';
                                    if(!empty($Member->Requestor_ID)){
                                        $RequestorId = $Member->Requestor_ID??'';
                                    }else{
                                        $RequestorId = $data['requestor_id']??'';
                                    }
                                   $Name = $Member->Name??'';
                                   $Notes = $Member->Notes??'';
                                   $InstitutionIdType = $Member->Institution_ID[0]->Type??'';
                                   // echo "<pre>";print_r($InstitutionIdType);die;
                                   $InstitutionIdvalue = $Member->Institution_ID[0]->Value??'';
                                   $ProviderId = $ProviderDetailID;
                                 
                                   $MembersValue= array(
                                   'customer_id' => $CustomerId,
                                   'requestor_id' => $RequestorId,
                                   'name' => $Name,
                                   'notes' => $Notes,
                                   'institution_id_type' => $InstitutionIdType,
                                   'institution_id_value' => $InstitutionIdvalue,
                                   'provider_id' => $ProviderId,
                                   
                                   );
                                   if(!empty($MembersValue['customer_id'])){
                                       
                                       DB::beginTransaction();
                                       try {
                                       $SaveMemberNew = Members::create($MembersValue);
                                       DB::commit();
                                       } catch(Exception $exception) {
                                           DB::rollback();
                                       }
                                       
                                   }
                               }
                             }
                           }
                           
                       }
                       
                        Session::flash('colupdatemsg', 'Provider Imported Successfully');
                        ?>
                        <script>
                        window.location.href='/consortium';
                        </script>
                        <?php
                    }
                    }
            }
            );
            } catch (Exception $exception) {
                report($exception);
                
                return parent::render($request, $exception);
            }
}
    }
}
