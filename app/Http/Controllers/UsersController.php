<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\File;
use App\User;
use Illuminate\Support\Facades\Config;
Use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Filename;


class Userscontroller extends Controller
{
    //
    
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        Session::flush();
        return Redirect::to('/');
    }
    
/////////////////////////////////////////////////////Login/////////////////////////////////////////////////////
    
    public function postLogin() {
        // Getting all post data
        
        
        $data = Input::all();
    //  echo "<pre>";print_r($data);die;
        
       
        // Applying validation rules.//
        
        $rules = array(
            'email' => 'required|email',
            'password' => 'required|min:6',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()){
            
            //echo "<pre>";print_r($validator);die;
            
            // If validation fails redirect back to login.
            
            return Redirect::back()->withInput(Input::except('password'))->withErrors($validator,'login');
        } else {
            
            $userdata = array(
                'email' => Input::get('email'),
                'password' => Input::get('password') 
                
            );
          
            //echo "<pre>";print_r($userdata);die;
            
            // doing login.
            
            if (Auth::validate($userdata)) {
                $remember = (Input::has('remember')) ? true : false;
                
                try {
                    
                if (Auth::attempt($userdata,$remember)) {
                    $loggeddata=User::where('email',$userdata['email'])->first();
                    Session::put('user', $loggeddata);
                    //dd('shashi');
                    return Redirect::intended('welcome');
                     }
                     
                } catch (Exception $exception) {
                    
                    report($exception);
                    
                    return parent::render($request, $exception);
                } 
                
            } else {
                // if any error send back with message.
                Session::flash('error', 'Email Address and password do not match');
                return view('index');
            }
        }
    }
    
////////////////////////////////////////////Register///////////////////////////////////////////////////////////
    
    public function register(){
        if(! Config::get('c5tools.enableRegistration')) {
            return view('index');
        }
        
        $data=Input::all();
        $data['utype']='user';
      
        // Applying validation rules.
        $rules = array(
            'first_name' => 'required|min:1',
            'last_name' =>'required|min:1',
            'display_name' =>'required|min:1',
            'email' =>'required|email|unique:users,email',
            'password' =>'required|min:6|confirmed',
            'password_confirmation' =>'required|min:6',
            
        
          // 'g-recaptcha' =>'required|GoogleRecaptcha',
        );
        
        $validator = Validator::make($data, $rules);
        if ($validator->fails()){
            // If validation fails redirect back to login.
            return Redirect('')->withInput(Input::except('password'))->withErrors($validator,'register');
        }else{
            
            $data['newsletter'] = $data['newsletter']??0;
            $data['commercial'] = $data['commercial']??0;
            $newUser = User::create($data);
            
            
            if($newUser){
                //Auth::login($newUser);
                Session::flash('RegisterMsg', 'You have successfully registered');
                return view('index');
            }
        }
    }
    
    
/////////////////////////////////////////////function for logout///////////////////////////////////////////////
    public function logout(){
        
        Auth::logout();
        Session::flush();
        Session::flash('logoutMsg', 'You successfully Logout');
        return Redirect::intended('/');
        
    }
}

    ///////////////////////////////////////////////////////////////////
    
  ?> 