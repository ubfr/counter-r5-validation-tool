<!DOCTYPE HTML>
<?php 
//print_r($error_report);

?>
<html>
<head>
<meta charset="utf-8">
<title>Validation Report - Counter</title>
@extends('layout.master')

@section("content")

    <!--========================login form start here======================================-->
    <div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
		<div class="pull-left">
             <h3>Validation Results</h3></div><div class="pull-right"><h3><a href="{{url('welcome')}}" class="btn btn-primary btnBlockxs ">Validate New File</a></h3></div> <div class="clearfix"></div>
            <div class="validationReport">
               
               <div class="col-md-6">
                <?php if($success=='1'){?>
				 
					 <p>Your file successfully validated.</p>
                                        <div class="panel panel-body">
                                        <ul>
                                            <h5><a href="<?php  echo "upload/json/".$uploaded_file; ?>" download="{{$uploaded_file}}">Click here for download the report</a></h5>
                                            
                                        </ul>
                                        </div>
				 
				<?php }else{?>
				 
				 <p>Please review the errors below:</p>
				
				 
                
                <?php }?>
			   </div>
			   <div class="clearfix"></div>
             
              <div class="clearfix"></div>
			   <?php if($success=='0'){?>
              <div class="alert alert-danger">
              	<p><strong>Request Details</strong> </p>
				
				<div class="panel panel-body">
                    <ul>
                        <?php
                        //echo "<pre>";print_r($error_report);die;
						
                        foreach($inputvalues as $key=>$inputvalue){
							if($key=='_token')
								continue;
							$valueoffield = empty($inputvalue)?'N/A':$inputvalue;
                            ?>
                        <li><?php echo $key." : ".$valueoffield; ?></li>
                            <?php
                        }
                        ?>
                    </ul>
				</div>
                
                
				<p><strong>Errors</strong> </p>
                <div class="panel panel-body">
                    <ul>
                        <?php
                        //echo "<pre>";print_r($error_report);die;
						
                        foreach($error_report as $error){
                            ?>
                        <li><?php echo $error; ?></li>
                            <?php
                        }
                        ?>
                    </ul>
				</div>
                
              </div>
			   <?php }?>
            </div>
             
			 
        </div>
         
    </div>
    </div>
    <!--========================login form END here======================================-->
   
</div>

@endsection

<!---=======================javascripts comes in bottom============================-->
<!-- Latest compiled and minified JavaScript -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>

 
</body>
</html>
