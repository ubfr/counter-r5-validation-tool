<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>
@extends('layout.master')

@section("content")

<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
						<div class="widget stacked widget-table action-table">
							<div class="widget-header">
								<i class="fa fa-tasks" aria-hidden="true"></i>
								<h3>Old Downloads</h3>
							</div>
							<!-- /widget-header -->
							<div class="widget-content">
								<table  id="example" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>File Type</th>
											<th>File Name</th>
											<th class="td-actions">Download</th>
										</tr>
									</thead>
									
									<tbody>
										<?php
        
        if (isset($file_detail)) {
            
            foreach ($file_detail as $filedetails) {
                ?>
        										<tr>
											<td><?php echo $filedetails->file_type;?></td>
											<td><?php echo $filedetails->filename;?></td>
											<td class="td-actions"><a
												href="download/{{$filedetails->id}}/{{$filedetails->filename}}"><i
													class="fa fa-download" aria-hidden="true"></i></a></td>
										</tr>
										<?php
            }
        }
        ?>
									</tbody>
									
									
									<tfooter>
									<th>File Type</th>
        							<th>File Name</th>
        							<th class="td-actions">Download</th>
									</tfooter>
									
								</table>
							</div>
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


	
	$('#example').DataTable( {
		 "searching": false,
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
