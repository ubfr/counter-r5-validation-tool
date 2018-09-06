<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Auth\PasswordController;
use App\User;
use App\Filename;
use App\Allreportsname;
Use Session;
Use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
Use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserlistController extends Controller
{
    
   
        
        public function __construct(){
            if (Session::has('user')){
                
            }else{
                return Redirect::to('login');
    }
        }

        public function  userlist(){
            if (Session::has('user')){
                $user = Session::get('user');
                $Userdetail=User::select('id','first_name','display_name','last_name','utype','email','status','gender')
                ->orderBy('id','desc')->get();
                $data['utype']=$user['utype'];
                $data['userDisplayName']= $user['display_name'];
                $data['user_detail']=$Userdetail;
                
                return view('admin_welcome',$data);
            }
            else{
                return Redirect::to('login');
            }
        }
        
        
        
        public function registeradmin(){
            $data=Input::all();
            $data['utype']='admin';
            // Applying validation rules.
            $rules = array(
                'first_name' => 'required|min:1',
                'last_name' =>'required|min:1',
                'display_name' =>'required|min:1',
                'gender' => 'required|gender|select',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6',
            );
            
            $validator = Validator::make($data, $rules);
            if ($validator->fails()){
                // If validation fails redirect back to login.
                
                return Redirect::back()->withInput(Input::except('password'))->withErrors($validator,'registeradmin');
            }else{
                $newUser = User::create($data);
                if($newUser){
                    //Auth::login($newUser);
                    Session::flash('RegisterMsg', 'New Admin successfully registered');
                    return Redirect::intended('userlist');
                }
            }
            
        }
        
        
        
        function delete_user(){
            
            if (Session::has('user')){
                $data = Input::all();
                
                $Userdetail=User::where('id',$data['deleteuseriddiv'])->first();
                $user = Session::get('user');
                $password =  $data['pass'];
                
                
                //checking with admin user
                $currentAdminEmail = $user['email'];
                
                
                $userdata = array(
                    'email' => $currentAdminEmail,
                    'password' => $password
                );
                if (Auth::validate($userdata)) {
                    $remember = (Input::has('remember')) ? true : false;
                    if (Auth::attempt($userdata,$remember)) {
                        $loggeddata=User::where('email',$userdata['email'])->first();
                        
                        $Userdetail=User::where('id',$data['deleteuseriddiv'])->delete();
                        Session::flash('userdelmsg', 'User successfully deleted');
                        return Redirect::intended('/userlist');
                    }
                }
                else {
                    Session::flash('userdelmsgwrong', 'Admin password mismatched');
                    return Redirect::intended('/userlist');
                }
                
                
            }
            else{
                return Redirect::to('login');
            }
        }
        
        
        function edit_user_display($id){
            if (Session::has('user')){
                $user = Session::get('user');
                $Userdetail=User::select('id','first_name','display_name','last_name','email','gender')
                ->where('id',$id)->get();
                $data['utype']=$user['utype'];
                $data['userDisplayName']= $user['display_name'];
                //$data['gender']= $user['gender'];
                $data['user_detail']=$Userdetail;
                //echo "<pre>";print_r($data);die;
                return view('admin_edituser',$data);
            }
            else{
                return Redirect::to('login');
            }
        }
        
        
        function edit_user($id){
            if (Session::has('user')){
                $user = Session::get('user');
                $data = Input::all();
                $newpassword = $data['password'];
                $updatearray = array(
                    "first_name" => $data['first_name'],
                    "last_name" => $data['last_name'],
                    "display_name" => $data['display_name'],
                    "gender" => $data['gender'],
                );
                if(!empty($newpassword)){
                    $newpasswordincoded = Hash::make($newpassword);
                    $updatearray['password'] = $newpasswordincoded;
                    
                }
                
                $Userdetail=User::where('id',$id)
                ->update(
                    $updatearray
                    );
                $data['utype']=$user['utype'];
                $data['userDisplayName']= $user['display_name'];
                $data['user_detail']=$Userdetail;
                //Session::flash('userupdatemsg', 'Password successfully updated');
                Session::flash('userupdatemsg', 'Changes successfully updated');
                return Redirect::intended('userlist');
            }
            else{
                return Redirect::to('login');
            }
        }
        
        
        
        function user_status($id,$status='1'){
            
            
            if(session::has('user')){
                $user=session::get('user');
                $data = Input::all();
                
                $Userdetail=User::select('id','first_name','last_name','display_name','utype','email','status','gender')
                ->where('id',$id)->get();
                
                
                
                $updatearray = array(  "status" => $status);
                
                $Userdetails=User::where('id',$id)
                ->update(
                    $updatearray
                    );
                
                Session::flash('useractivemsg', 'User successfully activated');
                
                return Redirect::intended('userlist');
                
                
            }
        }
        
}