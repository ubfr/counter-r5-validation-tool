<?php

namespace App\Http\Controllers;

use App\Allreportsname;
use App\Filename;
use App\User;
use App\Reportname;
use App\Validateerror;
use App\Validationrule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Parentreport;
use App\Filtertype;
use App\Consortium;
use App\Provider;
use Illuminate\Http\Request;
use File;
use App\Transactionmaster;
use App\Transactionmasterdetail;
use Exception;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use DateTime;
use App\Currenttransaction;

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
            return view('login');
        }
    }

    function showvalidate() {

        // ///////////show file upload list/////////////
        $user = Session::get('user');

        $filename = Filename::where('user_id', $user['id'])->orderBy('id', 'desc')
                ->take(10)
                ->get();

        // $i=0;
        // $FilterReports = Filtertype::where(array())->orderBy('id', 'asc')->get()->toArray();
        // foreach( $FilterReports as $filterReport)
        // {
        // $value[$i] ['name']=$filterReport['name'];
        // echo "<pre>";print_r($value); die;
        // $i++;
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

    // ////////////// start for Consortium/////////////////
    function harvetsingvalidate($id = 0) {
        $user = Session::get('user');

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

        $Consortiums = Consortium::where(array())->orderBy('configuration_name', 'asc')
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
                $newProvderList[] = $Providerevalue['provider_name'];
            }
            $providernamecommaseparted = implode(", ", $newProvderList);
            $providerMaster[$key]['providers'] = $providernamecommaseparted;
        }
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['file_detail'] = $providerMaster;
        $data['userDisplayName'] = $user->display_name;
        $data['utype'] = $user->utype;
        //echo "<pre>";print_r($user);die;
        return view('consortium', $data);
    }


    // ///////////////////////////////
    function saveConsortiumConfig() {
        $data = Input::all();
        // echo "<pre>";print_r($data);die;
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
                $newUser = Consortium::create($data);
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
        if (Session::has('user')) {
            $user = Session::get('user');

            $Consortiums = Consortium::select('id', 'configuration_name', 'Remarks')->where('id', $id)->get();

            // $data['utype']=$user['utype'];
            // $data['userDisplayName']= $user['display_name'];
            // $data['gender']= $user['gender'];
            $data['file_detail'] = $Consortiums;
            // echo "<pre>";print_r($data);die;
            return view('consortium', $data);
        } else {
            return Redirect::to('login');
        }
    }

    function update_consortium() {
        if (session::has('user')) {

            $data = Input::all();
            // echo "<pre>";print_r($data);die;
            $user = Session::get('user');
            $updatearray['configuration_name'] = $data['configuration_name'];
            $updatearray['remarks'] = $data['remarks'];

            $Consortiums = Consortium::where('id', $data['Id'])->update($updatearray);

            $data['utype'] = $user['utype'];
            $data['userDisplayName'] = $user['display_name'];
            $data['file_detail'] = $Consortiums;

            Session::flash('userupdatemsg', 'Report successfully updated');
            return Redirect::to('consortium');
        } else {
            return Redirect::to('login');
        }
    }

    // //////////Function For Report History//////////
    function showreport() {
        // ///////////show file upload list/////////////
        $user = Session::get('user');

        $filename = Filename::join('users', 'users.id', '=', 'filenames.user_id')->select('filenames.id', 'filenames.filename', 'filenames.file_type', 'filenames.report_name', 'filenames.report_id', 'filenames.filename', 'users.email')
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
    function uploaded_report() {
        // ///////////show file upload list/////////////
        $user = Session::get('user');

        $filename = Filename::join('users', 'users.id', '=', 'filenames.user_id')->select('filenames.id', 'filenames.filename', 'filenames.file_type', 'filenames.report_name', 'filenames.report_id', 'filenames.filename', 'users.email')
                ->orderBy('id', 'desc')
                ->get();
        // echo "<pre>";print_r($filename);die;
        // $AllSushiReports = Allreportsname::where(array())->orderBy('id','desc')->take(100)->get();
        // $data['sushireports']=$AllSushiReports;
        // echo "<pre>1234";print_r($AllSushiReport);die;
        // //////////////////////////////////////////////
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];
        $data['file_detail'] = $filename;
        return view('Uploaded_report', $data);
    }

    // ///////////show Rule Management Page////////////
    function show_rule_manage() {
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
    function delete_report($id) {
        // echo "1";die;
        if (Session::has('user')) {

            $AllSushiReports = Reportname::where('id', $id)->delete();
            $Filedelete = Validateerror::where('id', $id)->delete();
            Session::flash('userdelmsg', 'Report successfully deleted');
            return Redirect::intended('/reporthistory');
            // echo "<pre>";print_r($id);die;
        }
    }

    // delete providers value
    function deleteConsortium($id) {
        if (Session::has('user')) {

            Consortium::where('id', $id)->delete();
            Provider::where('configuration_id', $id)->delete();
            Session::flash('colupdatemsg', 'Configuation successfully deleted');
            return Redirect::intended('/consortium');
            // echo "<pre>";print_r($id);die;
        }
    }

    // delete providers value
    function deleteProvider($id = 0, $configid = 0) {
        if (Session::has('user')) {

            Provider::where(array(
                'id' => $id,
                'configuration_id' => $configid
            ))->delete();
            Session::flash('colupdatemsg', 'Provider successfully deleted');
            return Redirect::intended('/add_provider/' . $configid);
            // echo "<pre>";print_r($id);die;
        }
    }

    //show progress bar for data download
    function showConsortiumProgress($id = 0, $begin_date = '', $end_date = '') {
        //get configuration Name
        $Configurationname = Consortium::where('id',$id)->get()->first()->toArray();
        
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $user = Session::get('user');
        $data['userDisplayName'] = $user['display_name'];
        $data['utype'] = $user['utype'];

        $data['id'] = $id;
        $data['begin_date'] = $begin_date;
        $data['end_date'] = $end_date;
        $data['success'] = 1;
        $data['uploaded_file'] = "a.zip";
        $data['configuration_name'] = $Configurationname['configuration_name'];
        return view('show_consortium_progress', $data);
    }

    //show current Record progress File
    function showConsortiumProgressForRecord($id = 0,$TransactionId='',$begin_date = '', $end_date = '') {
        //$TransactionId = $this->getTransctionId();
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
        $OutputString = $OutputString."<tr><th>Sl. No.</th><th>Transaction ID</th><th>Provider Name</th><th>Member Name</th><th>Processed Report</th></tr>";
        $i = 1;
        foreach ($TransactionDetail as $keyValue => $TransactionSingle) {
            $OutputString = $OutputString . "<tr>";
            $OutputString = $OutputString . "<td>" . $i++ . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['transaction_id'] . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['provider_name'] . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['member_name'] . "</td>";
            $OutputString = $OutputString . "<td>" . $TransactionSingle['count'] . "</td>";
            $OutputString = $OutputString . "</tr>";
        }
        $OutputString = $OutputString . "</table>";
        die($OutputString);
        //die("Processed  Done for no of records.");
    }

    // /////////Run Consortium///////////////////////////////
    // /////////Run Consortium///////////////////////////////
    function runConsortium($id = 0,$TransactionId='', $begin_date = '', $end_date = '') {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $ConfigurationName = Consortium::where('id', $id)->get()->first()->toArray();
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
            // insert data in table and get ID = 1 folder with id-name
            //generateTranasctionId
            
            //$this->setTransctionId($TransactionId);
            //Session::put('TransactionId',$TransactionId);
            //die("Hello");
            // log for unique Config Run ID
            foreach ($AllProvidersList as $ProviderDetail) {
                //echo "<pre>";print_r($ProviderDetail);die;
                extract($ProviderDetail);
                $ProviderDetailID = $ProviderDetail['id'];
                $mainURL = $provider_url;
                $fields = array(
                    'apikey' => $apikey,
                    'customer_id' => $customer_id
                );
                $url = $mainURL . "/members?" . http_build_query($fields, '', "&");
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
                $file = time() . '_file.json';
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_NOBODY, true);
                $result = curl_exec($curl);
                // echo "<pre>ssdsadd";print_r($result);die;
                if ($result !== false) {
                    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    if ($statusCode == 404) {
                        //mark Shushi URL is incorrect and save in Database;
                    } else {
                        // $data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);
                        $opts = [
                            "http" => [
                                "method" => "GET",
                                "header" => "Accept-language: en\r\n"
                            ]
                        ];
                        $context = stream_context_create($opts);
                        $AllReportCodes = Reportname::select(array(
                                    'report_code'
                                ))->orderBy('id', 'asc')
                                ->get()
                                ->toArray();
                        // Open the file using the HTTP headers set above
                        $data = file_get_contents($url, false, $context);
                        $json = json_decode(($data), true);
                        //echo "<pre>";print_r($json);die;
                        //member detail creating file
                        $providerNameFolder = str_replace(" ", "_", $provider_name);
                        $destinationPath = public_path() . "/upload/json/" . $TransactionId . "/" . $providerNameFolder . "/";
                        $destinationPathCopy = public_path() . "/upload/json/" . $TransactionId . "/" . $providerNameFolder . "/";
                        $file = $providerNameFolder . "_members.json";
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        File::put($destinationPath . $file, $data);
                        //response()->download($destinationPath . $file);
                        // Now Checking for each Report related to Member
                        //try {
                        foreach ($json as $Member) {
                            //echo "<pre>";print_r($Member);die;
                            if (isset($Member['Apikey'])) {
                                // loop for reprt code
                                foreach ($AllReportCodes as $ReportCode) {
                                    // Config run ID, Config Name, Config ID, provider, Member, report , start Date_time stamp
                                    extract($Member);
                                    $Mfields = array(
                                        'apikey' => $Apikey,
                                        'customer_id' => $Customer_ID,
                                        'begin_date' => $begin_date,
                                        'end_date' => $end_date
                                    );
                                    
                                    $startdatetimstamp = date('Y-m-d H:i:s');
                                    $SaveTransaction = array(
                                        'user_id' => $user->email,
                                        'transaction_id' => $TransactionId,
                                        'config_name' => $ConfigurationName,
                                        'client_ip' => $ClientIp,
                                        'provider_name' => $provider_name,
                                        'member_name' => $Name,
                                        'report_id' => $ReportCode['report_code'],
                                        'begin_date' => $begin_date,
                                        'end_date' => $end_date,
                                        'status' => 1,
                                        'message' => 'started',
                                        'remarks' => $Remarks,
                                        'exception' => '',
                                        'details' => 'Started:',
                                        'file_name' => 'Test.json',
                                        'file_size' => 0,
                                        'start_date_time' => $startdatetimstamp
                                    );
                                    $SaveReort = Transactionmasterdetail::create($SaveTransaction);


                                    $Murl = $mainURL . "/reports/" . strtolower($ReportCode['report_code']) . "?" . http_build_query($Mfields, '', "&");
                                    if (!preg_match("~^(?:f|ht)tps?://~i", $Murl)) {
                                        $Murl = "https://" . $Murl;
                                    }

                                    $Mfile = $customer_id . '_file.json';

                                    $Mcurl = curl_init($Murl);
                                    // echo "<pre>";print_r($curl);die;
                                    curl_setopt($Mcurl, CURLOPT_NOBODY, true);

                                    $Mresult = curl_exec($Mcurl);
                                    // echo "<pre>ssdsadd";print_r($result);die;
                                    if ($Mresult !== false) {
                                        $statusCode = curl_getinfo($Mcurl, CURLINFO_HTTP_CODE);
                                        if ($statusCode == 404) {
                                            $Mresult = array(
                                                "Member URL doest not exist"
                                            );
                                        } else {
                                            // $data = json_encode(['Text 1','Text 2','Text 3','Text 4','Text 5']);
                                            $opts = [
                                                "http" => [
                                                    "method" => "GET",
                                                    "header" => "Accept-language: en\r\n"
                                                ]
                                            ];

                                            $context = stream_context_create($opts);

                                            // Open the file using the HTTP headers set above
                                            // $file = file_get_contents('https://c5.mpsinsight.com/insightc5api/services/reports/tr_j1?customer_id=11&begin_date=2018-01-01&end_date=2018-06-30', false, $context);
                                            $data = file_get_contents($Murl, false, $context);
                                            //$Mjson = json_decode(($data), true);
                                            //making directory
                                            $file = $Member['Customer_ID'] . "_" . $ReportCode['report_code'] . ".json";
                                            $destinationPath = $destinationPathCopy . "/" . $Member['Customer_ID'] . "/";
                                            //die($destinationPath);

                                            if (!is_dir($destinationPath)) {
                                                mkdir($destinationPath, 0777, true);
                                            }

                                            File::put($destinationPath . $file, $data);
                                            $fileSize = filesize($destinationPath . $file);

                                            //updating for file size and status
                                            $startdatetimstampEnd = date('Y-m-d H:i:s');
                                            $UpdateTransactionInfo = array(
                                                'status' => 2,
                                                'message' => 'successed',
                                                'remarks' => $Remarks,
                                                'exception' => '',
                                                'details' => 'Success:',
                                                'file_name' => $file,
                                                'file_size' => $fileSize,
                                                'end_date_time' => $startdatetimstampEnd
                                            );

                                            Transactionmasterdetail::where(array(
                                                        'transaction_id' => $TransactionId,
                                                        'user_id' => $user->email,
                                                        'member_name' => $Name,
                                                        'report_id' => $ReportCode['report_code'],
                                                    ))
                                                    ->update(
                                                            $UpdateTransactionInfo
                                            );
                                        }
                                    }
                                    // on SUCCESS operation
                                    // update below with End Date_time_stamp along with rematks and status
                                    // Config run ID, Config Name, Config ID, provider, Member, report , start Date_time_stamp
                                    // on FAIL operation
                                    // update below with End Date_time_stamp along with rematks, exception and status
                                    // Config run ID, Config Name, Config ID, provider, Member, report , start Date_time_stamp
                                } // tomorrow fiday 14 sep
                            }
                        }
                        //} catch (Exception $e) {
                        //capture Wrong Information
                        //}
                    }
                }
                // echo "<pre>";print_r($TransactionId->id);die;
            }

            //converting Zip File
            //root Directory

            $publicpathforzip = public_path() . '/upload/json/' . $TransactionId . "/";
            $publicpathforzipNew = public_path() . '/upload/json';
            //die($publicpathforzip);
            $zip = new ZipArchive();
            $ZipFileName = $TransactionId . ".zip";
            $resonse = $this->zipData($publicpathforzip, $publicpathforzipNew . "/" . $ZipFileName, $zip);
            //$finalZipFileURL = $publicpathforzipNew . "/" . $ZipFileName;
            echo "/upload/json/" . $ZipFileName;die;
            //return response()->download($publicpathforzipNew . "/" . $ZipFileName);
            //return view('harvesting_report', $dataforpassing);
        }
    }

    ///////////////////  creating a zip file for download/////////////////////////
    // Here the magic happens :)
    function zipData($source, $destination, $zip) {

        if (extension_loaded('zip')) {
            if (file_exists($source)) {


                if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                    $source = realpath($source);
                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
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

    public function zipFileDownload() {

        $public_dir = public_path() . '/upload';
        $zipFileName = 'json.zip';
        //root Directory
        $publicpathforzip = $public_dir . "/json/88888888";
        $publicpathforzipNew = $public_dir . "/json/";
        //die($publicpathforzip);
        $zip = new ZipArchive();
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');
        $resonse = $this->zipData($publicpathforzip, $publicpathforzipNew . "/backupnew.zip", $zip);

        //echo 'Finished.';
    }

    function get_client_ip() {
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

    /*
     * function callCURL($url) {
     * $ch = curl_init();
     * curl_setopt($ch, CURLOPT_URL, $url);
     * curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     * $combined = curl_exec ($ch);
     * curl_close ($ch);
     * return $combined;
     * }
     *
     *
     * function getResult($urls) {
     * $return = array();
     *
     * foreach ($urls as $url) {
     * $response = callCURL($url);
     * if (strlen($response) !== 0) {
     * $return[] = $response;
     * break;
     * }
     * }
     * return $return;
     * }
     */

    // /////////delete uploaded report///////////////////////////////
    function delete_upload_report($id) {
        // echo "2";die;
        if (Session::has('user')) {
            // $user = Session::get('user');
            $AlluploadReports = Filename::where('id', $id)->delete();
            $Filedelete = Validateerror::where('id', $id)->delete();
            Session::flash('userupdatemsg', 'File successfully deleted');
            return Redirect::intended('/uploadedreports');
            // echo "<pre>";print_r($id);die;
        }
    }

    // ///////////////////edit report//////////////////////////////////
    function edit_report($id) {

        // echo "1".$id;die;
        if (Session::has('user')) {
            $user = Session::get('user');
            $Reportdetail = Reportname::select('id', 'report_name', 'report_code')->where('id', $id)->get();

            $data['utype'] = $user['utype'];
            $data['report_detail'] = $Reportdetail;
            $data['userDisplayName'] = $user['display_name'];

            // echo "<pre>";print_r($data);die;

            return view('edit_report', $data);
        } else {
            return Redirect::to('login');
        }
    }

    function update_report() {
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

    // update consortium configration update
    function editConsortium() {
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

    // ///////////////////////// providers details /////////////////////////////
    function providervalidate() {
        $user = Session::get('user');

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
        $data = Input::all();
        // echo "<pre>";print_r($data);die;
        $rules = array(
            'provider_name' => 'required',
            'provider_url' => 'required|url',
            'apikey' => 'required',
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
            // echo "<pre>";print_r($newUser);die;
            if ($newUser) {
                Session::flash('colupdatemsg', 'Provider Added Successfully');
                return Redirect::to('add_provider/' . $data['configuration_id']);
                // return view('provider', $data);
            }
        }
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
}
