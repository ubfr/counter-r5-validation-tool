<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
div {
    margin-bottom: ;
    padding: ;
}
.info {
    background-color: #e7f3fe;
    border-left: 6px solid #2196F3;
}

 border-left: 6px solid #ffeb3b;
}
</style>
<title>Validation - Counter</title> @extends('layout.master')

@section("content")

<!--<link href="https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet">-->
<link href="{{URL::asset('assets/css/multiselect.css')}}" rel="stylesheet">
<!--<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>-->


<!--========================login form start here======================================-->

@if (Session::has('error'))
	<div class="alert alert-danger" >
		{{ Session::get('error') }}
	</div>
@endif

@if (Session::has('colupdatemsg'))
<div class="alert alert-success" style="color: green">{{
	Session::get('colupdatemsg') }}</div>
@endif



<div class="info">
  <p> The COUNTER Consortium Tool is made available through funding from OCLC and RedLink.</p>
</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg -12 col-md-12">
			<ul class="nav nav-tabs ulTabs">
				<li id="first"><a data-toggle="tab" href="#file1">Harvesting</a></li>
				<li id="second" class="active"><a data-toggle="tab" href="#menu1">Configuration</a></li>
				<li id="third"><a data-toggle="tab" href="#menu2">Transaction</a></li>
			</ul>
			<div class="tab-content" style="width: 100%;float: left;">

				<div id="file1" class="tab-pane fade ">

				
					<div class="col-xs-12 col-sm-12 col-md-12">
					
                                            
						<h3>Harvesting Consortium Configuration List</h3>
                                               
						<hr class="colorgraph"><hr>

						<div class="widget-content">
							<table id="Harvesting_list"
								class="table table-striped table-bordered" style="width: 100%;float: left;">
								<thead>
									<tr>
										<th>Id</th>
										<th>Configuration_name</th>
										<th>Providers</th>
										<th>Remarks</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
                                    <?php
                                    $i = 1;
                                    if (count($file_detail) > 0) {
                                        foreach ($file_detail as $filedetails) {
                                            // echo "<pre>";print_r($filedetails);die;
                                            ?>
                                        <tr>
										<td><?php echo $i++; ?></td>
										<td><?php echo $filedetails['configuration_name']; ?></td>
										<td><?php echo $filedetails['providers']==''?'&nbsp;<a class="btn btn-primary" href="add_provider/'.$filedetails['id'].'">Add Provider</a>':$filedetails['providers'].'&nbsp;<a class="btn btn-primary" href="add_provider/'.$filedetails['id'].'">Add/View Provider </a>';?></td>
										<td><?php echo $filedetails['remarks']; ?></td>

										<td class="td-actions">
                                        <button title="Run Configuration" rel="<?php echo $filedetails['id']; ?>" type="button" class="btn btn-success openBtn"><i class="fa fa-play" aria-hidden="true"></i></button>
                                        </td>
									    </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                            <tr>
										<td colspan="5">No Record found!</td>
									</tr> 
                                       <?php
                                    }
                                    ?>
                                </tbody>
							</table>
						</div>
					</div>
				</div>
				
				
				<div id="menu1" class="tab-pane fade in active">

					<div class="col-md-12">
						<div class="widget stacked widget-table action-table">

							<div>
								<h3>Consortium Configuration</h3>
								<form name="saveconsortium" method="post" class="file-uploader1"
									action="/saveconsortium" enctype="multipart/form-data">
									<input type="hidden" name="_token"
										value="<?php echo csrf_token() ?>">
                                        <?php
                                        if (isset($singleDetail['id'])) {
                                            ?>
                                            <input type="hidden"
										name="currentId" value="<?php echo $singleDetail['id']; ?>">
                                            <?php
                                        }
                                        ?>
                                        
                                        <hr class="colorgraph">

									<!-- Div section for Confoguration information -->
									<div class="form-group col-md-4 noPaddingXS noLeftPadd">
										<input type="text"
											value="{{ old('configuration_name') }}<?php echo $singleDetail['configuration_name']??''; ?>"
											name="configuration_name" id="ConfigurationName"
											class="form-control input-lg"
											placeholder="Configuration Name*"> <span
											style="color: #ff0000">{{
											$errors->consortium->first('configuration_name') }}</span>
									</div>

									<div class="form-group col-md-8 noPaddingXS noLeftPadd">
										<input 
											value="{{ old('remarks') }}<?php echo $singleDetail['remarks']??''; ?>"
											type="text" name="remarks" id="name"
											class="form-control input-lg" placeholder="Remarks"> <span
											style="color: #ff0000">{{
											$errors->consortium->first('remarks') }}</span>
									</div>
							
							</div>


							<div class="form-group">
								<div class="col-xs-12 col-md-4">
									<input type="submit"
										value="<?php echo isset($singleDetail['id'])?'Update Configurataion':'Add Configuration';?>"
										class="btn btn-primary btn-block btn-lg" tabindex="7">
								</div>
							</div>
							
							
							<div class="form-group">
								<div class="col-xs-12 col-md-4">
									<input type="button" onClick="location.href='importconfiguration'" value='Import Configuration' class="btn btn-primary btn-block btn-lg" tabindex="7">
									
									
								</div>
							</div>
							</form>
						</div>
					</div>

					<div class="col-md-12">
						<h3>Consortium Configuration List</h3>
						<hr class="colorgraph">
						<div class="widget-content">
							<table id="Consortium_list"
								class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Id</th>
										<th>Configuration_name</th>
										<th>Providers</th>
										<th>Remarks</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
                                    <?php
                                    $i = 1;
                                    if (count($file_detail) > 0) {
                                        foreach ($file_detail as $filedetails) {
                                            // echo "<pre>";print_r($filedetails);die;
                                            ?>
                                            <tr>
										<td><?php echo $i++; ?></td>
										<td><?php echo $filedetails['configuration_name']; ?></td>
										<td><?php echo $filedetails['providers']==''?'&nbsp;<a class="btn btn-primary" href="add_provider/'.$filedetails['id'].'">Add Provider</a>':$filedetails['providers'].'&nbsp;<a class="btn btn-primary" href="add_provider/'.$filedetails['id'].'"> Add More/View</a>';?></td>
										<td><?php echo $filedetails['remarks']; ?></td>
										<td class="td-actions"><a
											href="/consortium/<?php echo $filedetails['id']; ?>"><i
												class="fa fa-edit" aria-hidden="true"></i></a>&nbsp;&nbsp; <a
											onclick="return confirm('Are you sure for delete this configuration?')"
											href="delete_consortium/<?php echo $filedetails['id']; ?>"><i
												class="fa fa-trash" aria-hidden="true"></i></a>&nbsp;&nbsp;<a
											href="/downloadconfiguration/<?php echo $filedetails['id']; ?>"><i
												class="fa fa-download" aria-hidden="true"></i></a></td>
									</tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                            <tr>
										<td colspan="5">No Record found!</td>
									</tr> 
                                       <?php
                                    }
                                    ?>
                                </tbody>
							</table>
						</div>

					</div>

				</div>
				
				<div id="menu2" class="tab-pane fade">

					

					<div class="col-xs-12 col-sm-12 col-md-12">
						<h3>Harvesting Consortium Transaction Lists</h3>
						<hr class="colorgraph">

						

						<div class="widget-content">
                                                    <?php if (count($alltransaction) > 0) { ?>
						<table id="Transcation_list"
								class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Id</th>
										<th>Transcation_Id</th>
										<th>Configuration_name</th>
										<th>Start_Date</th>
										<th>End_Date</th>
										<th>No.of Record Processed</th>
										<th>No.of Record Completed</th>
										<th>Message</th>
										<th>Download Zip File</th>
										<th>Delete</th>
									</tr>
								</thead>
								<tbody>
								<?php
                                    $i = 1;
                                    
                                        foreach ($alltransaction as  $TransactionSingle) {                                            
                                            ?>
                                            
                                        <tr>
										<td><?php echo $i++; ?></td>
										<td><?php echo $TransactionSingle['transaction_id']; ?></td>
										<td><?php echo $TransactionSingle['config_name']; ?></td>
										<td><?php echo $TransactionSingle['begin_date']; ?></td>
										<td><?php echo $TransactionSingle['end_date']; ?></td>
										<td><?php echo $TransactionSingle['count']; ?></td>
										<td><?php echo $TransactionSingle['count']; ?></td>
										<td><?php echo $TransactionSingle['message']; ?></td>
										
										<td><a href="/upload/json/<?php echo $TransactionSingle['transaction_id']; ?>.zip"><i class="fa fa-download"
												aria-hidden="true"></i>	</a></td>
												
												
										<td class=""><a onclick="return confirm('Are you sure to delete this Transaction?')" 
										href="delete_transaction/<?php echo $TransactionSingle['transaction_id'];?>"><i class="fa fa-trash-o trashIcon" style="font-size: 15px;padding-right: 10px;"></i></a></td>		
							</tr>
							
							
                                            <?php
                                        }
                                    } else {
                                        ?>
                                                                        <tr>
										<td colspan="9">No Record found!</td>
									</tr> 
                                       <?php
                                    }
                                    ?>
							</tbody>		
							</table>
							
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
@endsection

                <div class="modal fade" id="myModal" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Run configuration parameter</h4>
                            </div>
                            <div class="modal-body">
                            </div>
                        </div>
                     </div>
                  </div>
                
                
                
                
                
		<!--========================login form END here======================================-->
		
		@section("additionaljs")
		<!-- Latest compiled and minified JavaScript -->
		
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.1/jquery.min.js"></script>
                
                <script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
		<script src="{{URL::asset('assets/js/bootstrap-datepicker.min.js')}}"></script>
		<script src="{{URL::asset('assets/js/jquery.multi-select.min.js')}}"></script>
		<script type="text/javascript" language="javascript"
			src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js">
	</script>
		<script type="text/javascript" language="javascript"
			src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js">
	</script>
        <script>
        $('.openBtn').on('click',function(){
        var CurrentConsortiumId = $(this).attr('rel');
        $('.modal-body').load('\/showprogressnew/'+CurrentConsortiumId,function(){
        $('#myModal').modal({show:true});
        });
        });
        </script>
		<script type="text/javascript" class="init">
	

function validateDate(url){
	var date1 = $("#datepicker-12").val();
	var date2 = $("#datepicker-10").val();
	if(date1 && date2){
		var NewConsotiumURL = url+'/'+date1+'/'+date2;
		//alert(NewConsotiumURL);
		//return false;
		window.location.href = url+'/'+date1+'/'+date2; 	
	}else{
		alert("Please enter date first");
		return false;
	}
		
}
$(document).ready(function() {
	
	$('#Consortium_list').DataTable( {
		 "searching": true,
        "language": {
			
            "lengthMenu": "Show entry _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
	       
        }
		
    } );
	 
} );
$(document).ready(function() {
	
	$('#Harvesting_list').DataTable( {
		 "searching": true,
        "language": {
			
            "lengthMenu": "Show entry _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
	       
        }
		
    } );
	 
} );

$(document).ready(function() {
	
	$('#Transcation_list').DataTable( {
		 "searching": true,
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

		<!-- Javascript for date-picker -->
		<script>
                    
            $(function() {
                $('.date-picker').datepicker( {
                    format: "mm-yyyy",
                    viewMode: "months", 
                    minViewMode: "months"
                });
                
            });
                    
                    
                    
                    
</script>

	<script>
        jQuery(function(){
            var flag = '<?php echo Session::get('keyconsortium');?>'
               if(flag=='delete'){
                	$("#third a").trigger("click");
                }
                 <?php Session::put('keyconsortium', ''); ?>   
        //jQuery('#reports').multiSelect();
        /*jQuery('#line-wrap-example').multiSelect({
            selectAllValue: 'multiselect-all',
            positionMenuWithin: $('.position-menu-within')
        });*/
        
    }); 
    //jQuery plugin
    

/////init 

    /*$('#sandbox-container .input-daterange').datepicker({
     changeMonth: true,
     changeYear: true,
     showButtonPanel: true,
     dateFormat: 'MM yy',
     onClose: function(dateText, inst) { 
     $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
     }    
     });  */
</script>

		@if(count($errors->welcome)>0)
		<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
		@endif @if (Session::has('error'))
		<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
		@endif @endsection

		<script>
    function check()
    {
        var aa = $("#file").val();

        var ext = aa.split('.').pop();
        //alert(ext);
        if (!((ext == 'tsv') || (ext == 'csv') || (ext == 'xlsx') || (ext == 'xls') || (ext == 'json')))
        {
            $("#pop1").html("<span style='color:#ff0000'>The File should be xls,xlsx,csv,tsv or json extensions</span>");
            return false;
        }


    }
    
    
    
   
    
    
    
</script>

</head>
</html>



