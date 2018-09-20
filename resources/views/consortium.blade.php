<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title> @extends('layout.master')

@section("content")

<link
	href="https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css"
	rel="stylesheet">
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>


<!--========================login form start here======================================-->


@if (Session::has('error'))
<div class="alert alert-success" style="color: red">
	{{Session::get('error') }}</div>
@endif @if (Session::has('colupdatemsg'))
<div class="alert alert-success" style="color: green">{{
	Session::get('colupdatemsg') }}</div>
@endif
<div class="container">
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg -12 col-md-12">
			<ul class="nav nav-tabs ulTabs">

				<li class="active"><a data-toggle="tab" href="#file1">Configuration</a></li>
				<li><a data-toggle="tab" href="#menu1">Harvesting</a></li>
			</ul>
			<div class="tab-content pull-left">

				<div id="file1" class="tab-pane fade in active">

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
											style="color: #ff0000"> {{
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
							</form>


						</div>

						<!-- /widget-header -->

						<!-- /widget-content -->
					</div>
					<!-- /widget -->

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
										<td>
                                                    
                                                    
                                                    <?php echo $filedetails['providers']==''?'<a href="add_provider/'.$filedetails['id'].'">Add Provider</a>':$filedetails['providers'].'<a href="add_provider/'.$filedetails['id'].'"> Add More/View</a>';?></td>
										<td><?php echo $filedetails['remarks']; ?></td>
										<td class="td-actions"><a
											href="/consortium/<?php echo $filedetails['id']; ?>"><i
												class="fa fa-edit" aria-hidden="true"></i></a>&nbsp;&nbsp; <a
											onclick="return confirm('Are you sure for delete this configuration?')"
											href="delete_consortium/<?php echo $filedetails['id']; ?>"><i
												class="fa fa-trash" aria-hidden="true"></i></a></td>
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
				<div id="menu1" class="tab-pane fade">

					<div class="clearfix"></div>

					<div class="col-xs-12 col-sm-12 col-md-12">
						<h3>Harvesting Consortium Configuration List</h3>
						<hr class="colorgraph">

						<div class="row">
							<div class="col-md-4">
								<h4>Please Provide Date Range:</h4>
							</div>
							<div class="col-md-4">
								<p>
									Begin_Date: <input type="text" id="datepicker-12" class="date-picker">
								</p>
							</div>
							<div class="col-md-4">
								<p>
									End_Date: <input type="text" id="datepicker-10" class="date-picker">
								</p>
							</div>
						</div>

						<div class="widget-content">
							<table id="Harvesting_list"
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
										<td><?php echo $filedetails['providers']==''?'<a href="add_provider/'.$filedetails['id'].'">Add Provider</a>':$filedetails['providers'].'<a href="add_provider/'.$filedetails['id'].'"> </a>';?></td>
										<td><?php echo $filedetails['remarks']; ?></td>

										<td class="td-actions"><a
											onclick="validateDate('/showprogress/<?php echo $filedetails['id']; ?>')"
											href="javascript:void(0)"><i class="fa fa-play"
												aria-hidden="true"></i></a></td>
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
			</div>
		</div>

		@endsection
		<!--========================login form END here======================================-->
		
		@section("additionaljs")
		<!-- Latest compiled and minified JavaScript -->
		<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
		<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
		<script src="{{URL::asset('assets/js/bootstrap-datepicker.min.js')}}"></script>
		<script type="text/javascript" language="javascript"
			src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js">
	</script>
		<script type="text/javascript" language="javascript"
			src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js">
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
                    
                    
                    
                    
         $(function() {
        	//$('input').datepicker({format: 'yyyy-mm-dd'});
            //$( "#datepicker-12" ).datepicker({format: 'yyyy-mm'});
            //$('input').datepicker({format: 'yyyy-mm-dd'});
            //$( "#datepicker-10" ).datepicker({format: 'yyyy-mm'});
           
         });
</script>

		<script>
    //jQuery plugin
    (function ($) {

        $.fn.uploader = function (options) {
            var settings = $.extend({
                MessageAreaText: "No files selected.",
                MessageAreaTextWithFiles: "File List:",
                DefaultErrorMessage: "Unable to open this file.",
                BadTypeErrorMessage: "We cannot accept this file type at this time.",
                acceptedFileTypes: ['pdf', 'jpg', 'gif', 'jpeg', 'bmp', 'tif', 'tiff', 'png', 'xps', 'doc', 'docx',
                    'fax', 'wmp', 'ico', 'txt', 'cs', 'rtf', 'xls', 'xlsx', 'json']
            }, options);

            var uploadId = 1;
            //update the messaging 
            $('.file-uploader__message-area p').text(options.MessageAreaText || settings.MessageAreaText);

            //create and add the file list and the hidden input list
            var fileList = $('<ul class="file-list"></ul>');
            var hiddenInputs = $('<div class="hidden-inputs hidden"></div>');
            $('.file-uploader__message-area').after(fileList);
            $('.file-list').after(hiddenInputs);

            //when choosing a file, add the name to the list and copy the file input into the hidden inputs
            $('.file-chooser__input').on('change', function () {
                var file = $('.file-chooser__input').val();
                var fileName = (file.match(/([^\\\/]+)$/)[0]);

                //clear any error condition
                $('.file-chooser').removeClass('error');
                $('.error-message').remove();

                //validate the file
                var check = checkFile(fileName);
                if (check === "valid") {

                    // move the 'real' one to hidden list 
                    $('.hidden-inputs').append($('.file-chooser__input'));

                    //insert a clone after the hiddens (copy the event handlers too)
                    $('.file-chooser').append($('.file-chooser__input').clone({withDataAndEvents: true}));

                    //add the name and a remove button to the file-list
                    $('.file-list').append('<li style="display: none;"><span class="file-list__name">' + fileName + '</span><button class="removal-button" data-uploadid="' + uploadId + '"></button></li>');
                    $('.file-list').find("li:last").show(800);

                    //removal button handler
                    $('.removal-button').on('click', function (e) {
                        e.preventDefault();

                        //remove the corresponding hidden input
                        $('.hidden-inputs input[data-uploadid="' + $(this).data('uploadid') + '"]').remove();

                        //remove the name from file-list that corresponds to the button clicked
                        $(this).parent().hide("puff").delay(10).queue(function () {
                            $(this).remove();
                        });

                        //if the list is now empty, change the text back 
                        if ($('.file-list li').length === 0) {
                            $('.file-uploader__message-area').text(options.MessageAreaText || settings.MessageAreaText);
                        }
                    });

                    //so the event handler works on the new "real" one
                    $('.hidden-inputs .file-chooser__input').removeClass('file-chooser__input').attr('data-uploadId', uploadId);

                    //update the message area
                    $('.file-uploader__message-area').text(options.MessageAreaTextWithFiles || settings.MessageAreaTextWithFiles);

                    uploadId++;

                } else {
                    //indicate that the file is not ok
                    $('.file-chooser').addClass("error");
                    var errorText = options.DefaultErrorMessage || settings.DefaultErrorMessage;

                    if (check === "badFileName") {
                        errorText = options.BadTypeErrorMessage || settings.BadTypeErrorMessage;
                    }

                    $('.file-chooser__input').after('<p class="error-message">' + errorText + '</p>');
                }
            });

            var checkFile = function (fileName) {
                var accepted = "invalid",
                        acceptedFileTypes = this.acceptedFileTypes || settings.acceptedFileTypes,
                        regex;

                for (var i = 0; i < acceptedFileTypes.length; i++) {
                    regex = new RegExp("\\." + acceptedFileTypes[i] + "$", "i");

                    if (regex.test(fileName)) {
                        accepted = "valid";
                        break;
                    } else {
                        accepted = "badFileName";
                    }
                }

                return accepted;
            };
        };
    }(jQuery));

/////init 

    $(document).ready(function () {
        $('.fileUploader').uploader({
            MessageAreaText: "Upload File Here"
        });

    });
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

<!---=======================javascripts comes in bottom============================-->


