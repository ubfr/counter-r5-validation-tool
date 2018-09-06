<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Forget Password - Counter</title>
		@include('layout.header')
    <!--========================login form start here======================================-->
    <div class="main-content">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6  col-lg-6 ">
            <form action="forgetpassword" method="post" name="login">
			<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
                <fieldset>
                    <h3>Please Provide Registered Email</h3>
                    <hr class="colorgraph">
                    <div class="form-group">
					@if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control input-lg" placeholder="Email Address">
                       <span style="color:#ff0000"> {{ $errors->first('email') }}</span> 
				  </div>
                       @if (Session::has('error'))
    <span style="color:#ff0000">{{ Session::get('error') }}</span>
@endif
                    <hr class="colorgraph">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
						<button type="submit" class="btn btn-lg btn-primary btn-block">
                                    <i class="fa fa-btn fa-envelope"></i> Send Password Reset Link
                        </button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        
    </div>
    </div>
    <!--========================login form END here======================================-->
<!--========================Footer page Include======================================-->
@include('layout.footer')
<!---=======================javascripts comes in bottom============================-->
<!-- Latest compiled and minified JavaScript -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
<script type="text/javascript">
</script>

</body>
</html>
