@extends("layout.master")

@section('content')

	<div class="main-content">
	@if (Session::has('logoutMsg'))
		<div class="alert alert-success">
            {{ Session::get('logoutMsg') }}
        </div>
@endif
	@if (Session::has('RegisterMsg'))
		<div class="alert alert-success">
            {{ Session::get('RegisterMsg') }}
        </div>
@endif

    <div class="row">
   		<div class="col-md-6">
        <fieldset>
        	 <h3>Register now for <span class="text-success">FREE</span></h3>
             <hr class="colorgraph">
                      <ul class="list-unstyled" style="line-height: 2">
                          <li><span class="fa fa-check text-success"></span> Validate Your COUNTER Report</li>
                          <li><span class="fa fa-check text-success"></span> Validate Your Tabular Report Format</li>
                          <li><span class="fa fa-check text-success"></span> SUSHI Service Validation</li>
                          <li><span class="fa fa-check text-success"></span> Fetch Your Old Test Report</li>
                          <li><span class="fa fa-check text-success"></span> Test Development Enviroment</li>
                                           <li><span class="fa fa-check text-success"></span> Get On-Screen Error Display and Email Report</li>                    </ul>
                       <div class="col-xs-6 col-sm-6 col-md-6 noPadding marTop10">
                            <a  data-toggle="modal" data-target="#myModal" class="btn btn-lg btn-primary btn-block">Register</a>
                        </div>
         </fieldset>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6  col-lg-6 ">
            <form action="login" method="post" name="login">
			<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
                <fieldset>
                    <h3>Please Sign In</h3>
                    <hr class="colorgraph">
                    <div class="form-group">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control input-lg" placeholder="Email Address">
                       <span style="color:#ff0000"> {{ $errors->login->first('email') }}</span> 
				  </div>
					 
                    <div class="form-group">
                        <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password">
						<span style="color:#ff0000">{{ $errors->login->first('password') }}</span> 
                    </div>
                     
                        <span class="checkbox">
                          <label><input type="checkbox" name="remember" value="">Remember Me</label>
                           <a href="forgetpassword" class="btn-link pull-right">Forgot Password?</a>
                        </span>
                         
                       
                       @if (Session::has('error'))
    <span style="color:#ff0000">{{ Session::get('error') }}</span>
@endif
                    <hr class="colorgraph">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
						<input type="submit" value="Sign In" class="btn btn-lg btn-primary btn-block">
                         
                        </div>
					
                       <!-- <div class="col-xs-6 col-sm-6 col-md-6">
                            <a  data-toggle="modal" data-target="#myModal" class="btn btn-lg btn-primary btn-block">Register</a>
                        </div>-->
                    </div>
                </fieldset>
            </form>
        </div>
        
    </div>
    </div>
    <!--========================login form END here======================================-->
    
    <!-- Modal for registration -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
       
      </div>
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 ">
		<form action="register" method="post" onsubmit="return checkForm(this);">
		<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
			<h2>Please Sign Up <small>It's free and always will be.</small></h2>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="form-control input-lg" placeholder="First Name" tabindex="1">
						<span style="color:#ff0000">{{ $errors->register->first('first_name') }}</span>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="text" name="last_name" id="last_name"  value="{{ old('last_name') }}" class="form-control input-lg" placeholder="Last Name" tabindex="2">
						<span style="color:#ff0000">{{ $errors->register->first('last_name') }}</span>
					</div>
				</div>
			</div>
			<div class="form-group">
				<input type="text" name="display_name" id="display_name"  value="{{ old('display_name') }}" class="form-control input-lg" placeholder="Display Name" tabindex="3">
				<span style="color:#ff0000">{{ $errors->register->first('display_name') }}</span>
			</div>
			<div class="form-group">
				<input type="email" name="email" id="email"  value="{{ old('email') }}" class="form-control input-lg" placeholder="Email Address" tabindex="4">
				<span style="color:#ff0000">{{ $errors->register->first('email') }}</span>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="5">
						<span style="color:#ff0000">{{ $errors->register->first('password') }}</span>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="password" name="password_confirmation" id="password_confirmation" class="form-control input-lg" placeholder="Confirm Password" tabindex="6">
						<span style="color:#ff0000">{{ $errors->register->first('password_confirmation') }}</span>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-xs-4 col-sm-3 col-md-3">
                	 <span class="checkbox">
                          <label><input type="checkbox" name="agree" value="1" > I Agree</label>
                            
                        </span>
					 
				</div>
				<div class="col-xs-8 col-sm-9 col-md-9">
					 <p style="margin-top:10px; text-align:right">By clicking <strong class="label label-primary">Register</strong>, you agree to the <a href="https://www.projectcounter.org/terms-and-conditions/" target="_blank">Terms and Conditions</a>.</p>
					
				</div>
				<div id="show_error"></div>
			</div>
			
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-md-6"><input type="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="7"></div>
				<!--<div class="col-xs-12 col-md-6"><a href="#" class="btn btn-success btn-block btn-lg">Sign In</a></div>-->
			</div>
		</form>
	</div>
</div>
      </div>
      
    </div>

  </div>
</div>
@endsection

