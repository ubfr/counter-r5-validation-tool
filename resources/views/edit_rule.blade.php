
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>

@extends('layout.master')

@section("content")


span {
    width: 100%;
    display: block;
}
<div class="main-content">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
			<?php if(isset($rulesArray))foreach($rulesArray as $var){
				print_r($var);
			} ?>
			<div>
				<span>Select Report :</span>
				<form name="test">
					<select name="reportname" id="reportname">
						<?php foreach($reportsname as $reportDetails){ ?>
							<option value="<?php echo $reportDetails->id;?>"><?php echo $reportDetails->report_name."vishal";?></option>
						<?php }?>
					</select>
				</form>
				<div class="loadview">
					<ul class="nav nav-stacked" id="accordion1">
						<li class="panel"> <a data-toggle="collapse" data-parent="#accordion1" href="#firstLink">Test12</a>
							<ul id="firstLink" class="collapse">
								<li>
									<div id="example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
										<div class="row">
											<div class="col-sm-12">
												<table id="example" class="table table-striped table-bordered dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="example_info" style="width: 100%;">
													<thead>
														<tr role="row">
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Name: activate to sort column ascending" style="width: 148px;">ColName</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">RowNo</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">RuleType</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">Value</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Required</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">ReportNo</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">IsRange</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">StartColumn</th>
															<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">MatchColumn</th>
														</tr>
													</thead>
													<tfoot>
														<tr>
															<th rowspan="1" colspan="1">ColName</th>
															<th rowspan="1" colspan="1">RowNo</th>
															<th rowspan="1" colspan="1">RuleType</th>
															<th rowspan="1" colspan="1">Value</th>
															<th rowspan="1" colspan="1">Required</th>
															<th rowspan="1" colspan="1">ReportNo</th>
															<th rowspan="1" colspan="1">IsRange</th>
															<th rowspan="1" colspan="1">StartColumn</th>
															<th rowspan="1" colspan="1">MatchColumn</th>
														</tr>
													</tfoot>
													<tbody> 
														<?php for ($i=0;$i<20;$i++){?>
															<table>
																<tbody>
																	<tr>
																		<th></th>
																		<th>A</th>
																		<th>B</th>
																		<th>C</th>
																	</tr>
																	<tr>
																		<th>1</th>
																		<td><span id="A1" contenteditable>#####</span></td>
																		<td><span id="B1" contenteditable></span></td>
																		<td><span id="C1" contenteditable></span></td>
																	</tr>
																	<tr>
																		<th>2</th>
																		<td><span id="A2" contenteditable></span></td>
																		<td><span id="B2" contenteditable></span></td>
																		<td><span id="C2" contenteditable></span></td>
																	</tr>
																</tbody>
															</table>
														<?php }?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-7">
											<div class="dataTables_paginate paging_simple_numbers" id="example_paginate">
											</div>
										</div>
									</div>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section("additonaljs")
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" class="init">
$(document).ready(function() {
	$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
	$('#example').DataTable( {
		 "searching": true,
        "language": {
			
            "lengthMenu": "Show entry _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
	       
        }
		
    } );
	
 $.post('ajaxCall_v/'+$("#reportname").val(), function(response){
		  
            var data=JSON.parse(response);
			var test =  [];
			
			for(var i=0;i<data.length;i++){
				
				test.push("<div class='saved' ><a href="+window.location.href+"?id="+data[i].row+">Rows: "+data[i].row+"</a></div>");

				
			}
	$(".loadview").html(test);
 });
    $("#reportname").change(function(){
        
			 $.post('ajaxCall_v/'+$(this).val(), function(response){
				 alert(response);
		  
           
			
            });
    });
	
} );
	</script>
	@endsection
	
	</head>
	</html>
	
