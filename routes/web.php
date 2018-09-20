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
Route::post('/sushiValidate', 'FilevalidateController@sushiValiate');
// /////////////////////////////////////////forget password link///////////////////////////////////////////////////////////

Route::get('forgetpassword', array(
    'as' => 'forgetpassword',
    'uses' => 'Auth\ForgotPasswordController@getEmail'
));
Route::post('forgetpassword', array(
    'as' => 'forgetpassword',
    'uses' => 'Auth\UserPasswordController@postEmail'
));

// ///////////////////////////////Login user information//////////////////////////////////////////////////////
Route::group([
    'middleware' => [
        'auth',
        'normalusersauth'
    ]
], function () {

    // now sushiValidate
    Route::post('/sushiValidate', 'FilevalidateController@sushiValiate');

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
    Route::post('/sushiValidate', 'FilevalidateController@sushiValiate');
    Route::get('uploadedreports','ShowController@uploaded_report');
    Route::get('delete_upload_report/{id}','ShowController@delete_upload_report');
    Route::get('consortium/{id}','ShowController@harvetsingvalidate');
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
    
    Route::get('runconsortium/{id}/{transactionId}/{begin_date}/{end_date}', 'ShowController@runConsortium');
    Route::get('showprogress/{id}/{begin_date}/{end_date}', 'ShowController@showConsortiumProgress');
    Route::get('showprogressrecord/{id}/{transactionId}/{begin_date}/{end_date}', 'ShowController@showConsortiumProgressForRecord');
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