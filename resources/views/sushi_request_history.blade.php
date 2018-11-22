<!DOCTYPE HTML>
<html>
<head>

<meta charset="utf-8">
<title>Validation - Counter</title>

@extends('layout.master')

@section("content")

<!--========================login form start here======================================-->
<div class="">   

	@if (Session::has('userdelmsg'))
		<div class="alert alert-success" style="color:green">
			{{ Session::get('userdelmsg') }}
		</div>
	@endif
	@if (Session::has('useralertmsg'))
		<div class="alert alert-danger" style="color:red">
			{{ Session::get('useralertmsg') }}
		</div>
	@endif
	
	@if (Session::has('userdelmsgwrong'))
		<div class="alert alert-success" style="color:red">
			{{ Session::get('userdelmsgwrong') }}
		</div>
	@endif
	
	@if (Session::has('userupdatemsg'))
		<div class="alert alert-success" style="color:green">
			{{ Session::get('userupdatemsg') }}
		</div>
	@endif
	@if (Session::has('RegisterMsg'))
		<div class="alert alert-success">
            {{ Session::get('RegisterMsg') }}
        </div>
@endif
	 <div class="row">
		<div class="col-sm-6"  >
			<a href="{{url('filelist')}}" class="btn btn-primary btnBlockxs ">Back</a>
			
		</div>
    
    
	<div class="col-sm-6 text-right"  >
       <a class="btn btn-primary getShushiValue" rel="getall" href="sushirequest/id">Download</a>
	</div>
	</div>
	
	 
				<div class="row">
					<div class="col-sm-12" id="example_wrapper">
					<div style="overflow-x:auto;">
						<table  id="example" class="table table-striped table-bordered  " cellspacing="0" width="100%" role="grid" aria-describedby="example_info" style="width: 100%;">
							<thead>
								<tr role="row">
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Name: activate to sort column ascending" style="width: 148px;">User Email</th>
									<th class="sorting" tabindex="1" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">Name</th>
									<th class="sorting" tabindex="2" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">SUSHI URL</th>
									<th class="sorting" tabindex="3" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">Request Name</th>
									<th class="sorting" tabindex="4" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">Platform</th>
									<th class="sorting" tabindex="5" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">Report ID</th>
									<th class="sorting" tabindex="6" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">Report Format</th>
									<th class="sorting" tabindex="7" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Success</th>
								    <th class="sorting" tabindex="8" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">#Errors</th>
								    <th class="sorting" tabindex="9" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Date Time</th>
								    <th class="sorting" tabindex="10" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Delete</th>
								</tr>
							
							</thead>
							<tfoot>
								<tr>
									<th rowspan="1" colspan="1">User Email</th>
									<th rowspan="1" colspan="1">Name</th>
									<th rowspan="1" colspan="1">SUSHI URL</th>
									<th rowspan="1" colspan="1">Request Name</th>
									<th rowspan="1" colspan="1">Platform</th>
									<th rowspan="1" colspan="1">Report ID</th>
									<th rowspan="1" colspan="1">Report Format</th>
									<th rowspan="1" colspan="1">Success</th>
									<th rowspan="1" colspan="1">#Errors</th>
									<th rowspan="1" colspan="1">Date Time</th>
									<th rowspan="1" colspan="1">Delete</th>
								</tr>
							</tfoot>
							<tbody> 
								<?php 
                                                                $DisplayValueOfMethod = array(
                                                                                            'getverify'=>'Verify Credential',
                                                                                            'getstatus'=>'Get Status',
                                                                                            'getmembers'=>'Get Members',
                                                                                            'getsupportedreports'=>'Get Supported Reports List',
                                                                                            'getall'=>'Get All',
                                                                                            'fail'=>'Get Report/Sushi Verification Failed',
                                                                                            'getreport'=>'Get Report/Sushi Verified',
                                                                                            'getreportrequest'=>'Get Report'
                                                                                            );
								//echo'<pre>'; print_r($sushi_detail);  die();
								foreach($sushi_detail as $sushi_details){?>
								<tr role="row" class="even">
									<td class=""><?php echo $sushi_details->user_email;?></td>
									<td class=""><?php echo $sushi_details->session_id;?></td>
									<td class=""><?php echo $sushi_details->sushi_url;?></td>
									<td class=""><?php echo $DisplayValueOfMethod[$sushi_details->request_name];?></td>
									<td class=""><?php echo $DisplayValueOfMethod[$sushi_details->platform];?></td>
									<td class=""><?php echo $sushi_details->report_id;?></td>
									<td class=""><?php echo $sushi_details->report_format;?></td>
									<td class=""><?php echo $sushi_details->success;?></td>
									<td class=""><?php echo $sushi_details->number_of_errors;?></td>
									<td class=""><?php echo $sushi_details->date_time;?></td>
									<td class=""><a onclick="return confirm('Are you sure for delete?')" 
										href="delete_sushi_request/<?php echo $sushi_details['id'];?>">
										<i class="fa fa-trash-o trashIcon" style="font-size: 15px;padding-right: 10px;"></i></a></td>
								</tr>
		
								<?php }?>
							</tbody>
						</table> 
					</div>
				 
			</div>
        

    
   

</div>
</div>



   


  
  




    <!--========================login form END here======================================-->
@endsection
<!---=======================javascripts comes in bottom============================-->

@section("additionaljs")
<!-- Latest compiled and minified JavaScript -->

<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js">
	</script>
	
	

	<script type="text/javascript" class="init">
     $(document).ready(function() {


	//click event of check box


	
	$('#example').DataTable( {
		 "searching": true,
                 "ordering":  false,
        "language": {
			
            "lengthMenu": "Show entry _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
	       
        }
		
    } );
	 
} );
	</script>
 
 
 @endsection
</body>
</html>
