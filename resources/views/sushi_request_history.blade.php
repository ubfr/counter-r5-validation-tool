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
        <div class="col-sm-12">
            <h3>SUSHI History for the past {{Config::get('c5tools.clearAfter')}}</h3>
            <hr class="colorgraph" />
        </div>
        <div class="col-sm-12">
            <div id="sushihistory_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="sushihistory" class="table table-striped table-bordered" cellspacing="0" width="100%" role="grid" style="width: 100%;">
                            <thead>
                                <tr role="row">
                                    <th class="sorting" aria-label="Time: activate to sort column">Time</th>
                                    <?php if ($utype === 'admin') { ?>
                                    <th class="sorting" aria-label="Email Address: activate to sort column">Email Address</th>
                                    <?php } ?>
                                    <th class="sorting" aria-label="Server URL: activate to sort column">Server URL</th>
                                    <th class="sorting" aria-label="Path: activate to sort column ascending">Path</th>
                                    <th class="sorting" aria-label="Platform: activate to sort column ascending">Platform</th>
                                    <!-- Does this make sense? The format always should be JSON...
                                    <th class="sorting" aria-label="Report Format: activate to sort column ascending">Report Format</th>
                                    -->
                                    <th class="sorting" aria-label="HTTP Code: activate to sort column ascending">HTTP Code</th>
                                    <th class="sorting" aria-label="Success: activate to sort column ascending">Success</th>
                                    <!-- must be fixed
                                    <th>Actions</th>
                                    -->
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Time</th>
                                    <?php if ($utype === 'admin') { ?>
                                    <th>Email Address</th>
                                    <?php } ?>
                                    <th>Server URL</th>
                                    <th>Path</th>
                                    <th>Platform</th>
                                    <!-- Does this make sense? The format always should be JSON...
                                    <th>Report Format</th>
                                    -->
                                    <th>HTTP Code</th>
                                    <th>Success</th>
                                    <!-- must be fixed
                                    <th>Actions</th>
                                    -->
                                </tr>
                            </tfoot>
                            <tbody> 
                                <?php 
                                foreach($sushi_detail as $sushi_details) {
                                    $path = '/' . substr($sushi_details->request_name, 3);
                                    if($path === '/report') {
                                        $path = '/reports/' . strtolower(str_replace('_', '', $sushi_details->report_id));
                                    }
                                ?>
                                <tr role="row">
                                    <td>{{$sushi_details->date_time}}</td>
                                    <?php if ($utype === 'admin') { ?>
                                    <td class="">{{$sushi_details->user_email}}</td>
                                    <?php } ?>
                                    <td>{{$sushi_details->sushi_url}}</td>
                                    <td>{{$path}}</td>
                                    <td>{{$sushi_details->platform}}</td>
                                    <!-- Does this make sense? The format always should be JSON...
                                    <td>{{$sushi_details->report_format}}</td>
                                    -->
                                    <td>{{$sushi_details->number_of_errors}}</td>
                                    <td>{{$sushi_details->success === 'Y' ? 'Yes' : 'No'}}</td>
                                    <!-- must be fixed
                                    <td><a onclick="return confirm('Do you really want to delete this entry?')" 
                                            href="delete_sushi_request/{{$sushi_details['id']}}">
                                            <i class="fa fa-trash-o trashIcon" style="font-size: 15px;padding-right: 10px;"></i>
                                        </a>
                                    </td>
                                    -->
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-7">
            <div class="dataTables_paginate paging_simple_numbers" id="sushihistory_paginate">
            </div>
        </div>
    </div>

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


	
	$('#sushihistory').DataTable( {
		"searching": true,
        "language": {
            "lengthMenu": "Show entries: _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
        },
        "order": [ [ 0, "desc" ] ]
    } );
	 
} );
	</script>
 
 
 @endsection
</body>
</html>
