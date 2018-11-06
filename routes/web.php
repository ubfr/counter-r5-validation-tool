<?php

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register web routes for your application. These
 * | routes are loaded by the RouteServiceProvider within a group which
 * | contains the "web" middleware group. Now create something great!
 * |
 */
Route::get('/', function () {
    return view('index');
});
//Route::post('login', 'UsersController@postLogin');
Route::get('welcome', 'ShowController@checkview');
Route::get('home', 'UserlistController@userlist');
Route::post('login', [ 'as' => 'login', 'uses' => 'UsersController@postLogin']);
Route::get('login', 'UsersController@register');
Route::post('/registeradmin', array(
    'as' => 'registeradmin',
    'uses' => 'UserlistController@registeradmin'
));

Route::get('filelist', 'ShowController@showvalidate');
Route::post('register', 'UsersController@register');

Route::post('/fileValidate', 'FilevalidateController@filevalidate');
Route::get('logout', 'UsersController@logout');
Route::post('sushiValidate', 'FilevalidateController@sushiValiate');
// /////////////////////////////////////////forget password link///////////////////////////////////////////////////////////

Route::get('forgetpassword', array(
    'as' => 'forgetpassword',
    'uses' => 'Auth\ForgotPasswordController@getEmail'
));
Route::post('forgetpassword', array(
    'as' => 'forgetpassword',
    'uses' => 'Auth\UserPasswordController@postEmail'
));

Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('reset/password/{token}', array('as' => 'reset', 'uses' => 'Auth\UserPasswordController@postReset'));
Route::post('/password/reset/', array('as' => 'reset', 'uses' => 'Auth\UserPasswordController@postReset'));



Route::get('consortium/{id}','ShowController@harvetsingvalidate');
Route::get('delete_transaction/{id}', 'ShowController@delete_transaction');
Route::get('consortium','ShowController@harvetsingvalidate');
Route::post('saveconsortium', 'ShowController@saveConsortiumConfig');
//Route::post('edit_consortium', 'ShowController@edit_consortium');
Route::post('update_consortium', 'ShowController@update_consortium');


// providers
//Route::get('add_provider/{id}', 'ShowController@add_provider');
Route::get('add_provider/{id}', 'ShowController@addProvider');
Route::get('edit_consortium/{id}', 'ShowController@editConsortium');
Route::get('delete_consortium/{id}', 'ShowController@deleteConsortium');
Route::get('delete_provider/{id}/{configid}', 'ShowController@deleteProvider');
Route::get('edit_provider/{id}/{configid}', 'ShowController@addProvider');
Route::post('save_provider', 'ShowController@saveProvider');

Route::get('runconsortium/{id}/{transactionId}/{begin_date}/{end_date}/{selectedreports}/{selectedproviders}/{selectedmembers}/{selectedformat}', 'ShowController@runConsortium');
Route::post('showprogress/', 'ShowController@showConsortiumProgress');
Route::get('showprogressnew/{configurationId}', 'ShowController@showConsortiumProgressNew');
Route::get('showprogressrecord/{id}/{transactionId}/{begin_date}/{end_date}/{selectedreports}', 'ShowController@showConsortiumProgressForRecord');
Route::get('downloadconfiguration/{id}','ShowController@downloadExcelConfig');
Route::get('importconfiguration', 'ShowController@importConfiguration');
Route::post('consortiumimport', 'ShowController@readConfigurationFile');
Route::get('member/{provider_id}', 'ShowController@memberListing');
Route::get('delete_members/{id}/{provider_id}', 'ShowController@deleteMembers');
Route::get('refresh_members/{provider_id}', 'ShowController@refreshMembers');
Route::get('/downloadfront/{file_id}/{filename}',  'CommonController@downloadfileFront');
Route::get('sushirequest', 'FilevalidateController@sushiRequest');
Route::get('delete_sushi_request/{id}', 'FilevalidateController@delete_sushi_request');

Route::get('showshushiparameter/{Requestorurl}/{apikey}/{CustomerId}/{PlateformID}', 'FilevalidateController@sushiRequestParameter');
Route::post('getsushireport', 'FilevalidateController@getSushiReport');
Route::get('sushirequest/{id}', 'ShowController@sushiReportRequest');
// ///////////////////////////////Login user information//////////////////////////////////////////////////////
Route::group([
    'middleware' => [
        'auth',
        'normalusersauth'
    ]
], function () {
   
    // now sushiValidate
    //Route::post('/sushiValidate', 'FilevalidateController@sushiValiate');

    // Route::get('/download','FilevalidateController@downloadfile');
    Route::get('/download/{file_id}/{filename}', [
        'as' => 'admin.invoices.downloadfile',
        'uses' => 'FilevalidateController@downloadfile'
    ]);
    Route::get('/email/{file_id}', [
        'as' => 'admin.invoices.emailfile',
        'uses' => 'FilevalidateController@emailfile'
    ]);
});
// Route::group(['middleware' => ['auth','admin']], function () {

// /////////////////////////////Show admin Rule Management////////////////////////////////////////////////////
Route::group([
    'middleware' => [
        'auth',
        'administrator'
    ]
], function () {
    Route::get('/userlist', 'UserlistController@userlist');
    Route::get('reporthistory', 'ShowController@showreport');
    Route::get('rulemanagement', 'RulemanagementController@rulemanagement');
    Route::post('ajaxCall/{id}', 'RulemanagementController@ajaxCall');
    Route::get('edit_column/{id}', 'RulemanagementController@edit_cloumn_view');
    Route::get('loadvalue/{column_id}', 'RulemanagementController@getColumnData');
    Route::post('edit_update/{id}', 'RulemanagementController@updatecolumn');
    Route::get('createvalue/{row_id}/{report_id}', 'RulemanagementController@addColumnView');
    Route::post('add_update/{id}', 'RulemanagementController@addnewcolumn');
    Route::get('deleterow/{rowid}/{reportid}', 'RulemanagementController@deleterowFuntion');
    Route::post('lastcolajax', 'RulemanagementController@is_lastcolumn');
    Route::get('addrow/{rowid}', 'RulemanagementController@addRowFuntion');
    Route::get('rule_manage', 'ShowController@show_rule_manage');
    Route::get('edit_report/{id}', 'ShowController@edit_report');
    Route::get('deletefile/{file_id}', 'ShowController@delete_report');
    Route::post('report_update', 'ShowController@update_report');
    Route::get('/user_status/{id}/{status}', 'UserlistController@user_status');
    Route::post('delete_user', 'UserlistController@delete_user');
    Route::post('edit_user/{user_id}', 'UserlistController@edit_user');
    Route::get('edit_report/{id}', 'ShowController@edit_report');
    Route::get('edituser/{user_id}', 'UserlistController@edit_user_display');
    // now sushiValidate
    //Route::post('/sushiValidate', 'FilevalidateController@sushiValiate');
    Route::get('uploadedreports','ShowController@uploaded_report');
    Route::get('delete_upload_report/{id}','ShowController@delete_upload_report');
    
   
    // Route::get('/download','FilevalidateController@downloadfile');
    Route::get('/download/{file_id}/{filename}', [
        'as' => 'admin.invoices.downloadfile',
        'uses' => 'FilevalidateController@downloadfile'
    ]);
    Route::get('/email/{file_id}', [
        'as' => 'admin.invoices.emailfile',
        'uses' => 'FilevalidateController@emailfile'
    ]);
});
        
        
        
        
        
        
      // });