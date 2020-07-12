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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::post('user_edit/{id}', 'UserlistController@user_edit');
Route::get('useredit', 'UserlistController@useredit');
Route::get('welcome', 'ShowController@checkview');
Route::post('login', [ 'as' => 'login', 'uses' => 'UsersController@postLogin']);

Route::get('login', 'ShowController@checkview');
if(Config::get('c5tools.enableRegistration')) {
    Route::post('register', 'UsersController@register');
}
Route::get('logout', 'UsersController@logout');

Route::post('fileValidate', 'FilevalidateController@filevalidate');
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


// ///////////////////////////////Login user information//////////////////////////////////////////////////////
Route::group([
    'middleware' => [
        'auth',
        'normalusersauth'
    ]
],
    
function () {
});

// /////////////////////////////Show admin Rule Management////////////////////////////////////////////////////
Route::group([
    'middleware' => [
        'auth',
        'administrator'
    ]
], 
function () {
Route::get('/userlist', 'UserlistController@userlist');
Route::get('/user_status/{id}/{status}', 'UserlistController@user_status');
Route::post('delete_user', 'UserlistController@delete_user');
Route::post('edit_user/{user_id}', 'UserlistController@edit_user');
Route::get('edituser/{user_id}', 'UserlistController@edit_user_display');
Route::get('home', 'UserlistController@userlist');
Route::post('/registeradmin', array(
    'as' => 'registeradmin',
    'uses' => 'UserlistController@registeradmin'
));

Route::get('/download/{file_id}', 'FilevalidateController@downloadfile'); 
Route::get('/email/{file_id}', 'FilevalidateController@emailfile');
});



Route::group([
    'middleware' => [
        'auth',
        'subscriber'
    ]
],
    function () {
        Route::get('/download/{file_id}', 'FilevalidateController@downloadfile');
        Route::get('/email/{file_id}', 'FilevalidateController@emailfile');

Route::get('delete_reportfile/{id}', 'CommonController@deleteReportfile');

Route::get('sushirequest', 'FilevalidateController@sushiRequest');
Route::get('delete_sushi_request/{id}', 'FilevalidateController@delete_sushi_request');

Route::post('showshushiparameter', 'FilevalidateController@sushiRequestParameter');
Route::post('getsushireport', 'FilevalidateController@getSushiReport');
Route::get('filelist', 'ShowController@showvalidate');
Route::get('filehistory', 'ShowController@fileHistory');

        
    });
