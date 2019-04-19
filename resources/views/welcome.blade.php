<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>
@extends('layout.master')

@section("content")
<link href="{{URL::asset('assets/css/multiselect.css')}}" rel="stylesheet">

    <!--========================login form start here======================================-->


	@if (Session::has('error'))
	<div class="alert alert-danger" style="color: red;">{{Session::get('error')}}</div>
	@endif
	@if (Session::has('userupdatemsg'))
		<div class="alert alert-success" style="color: green;">{{Session::get('userupdatemsg')}}</div>
	@endif
	@if (Session::has('reportmsg'))
		<div class="alert alert-danger" style="color: red;">{{Session::get('reportmsg')}}</div>
	@endif
	
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
			<ul class="nav nav-tabs ulTabs">
				<li id="first" class="active"><a data-toggle="tab" href="#file1">File Validate
				</a></li>
				<li id="second"><a data-toggle="tab" href="#menu1">SUSHI Validate</a></li>
			</ul>
			<div class="tab-content">
				<div id="file1" class="tab-pane fade in active">
                    @if (Session::has('file_ok'))
                    <div id="file_ok" class="alert alert-success" style="color: green;">{{Session::get('file_ok')}}</div>
                    @endif
                    @if (Session::has('file_error'))
                    <div id="file_error" class="alert alert-danger" style="color: red;">{{Session::get('file_error')}}</div>
                    @endif
					<div class="col-md-2"></div>
					<div class="col-md-8">
						@if (Session::has('emailMsg'))
						<div class="alert alert-success">{{ Session::get('emailMsg') }}</div>
						@endif
						<form name="file_valid" method="post" class="file-uploader"
							action="fileValidate" enctype="multipart/form-data">
							<input type="hidden" name="_token"
								value="{{csrf_token()}}">
							<div class="file-uploader__message-area">
								<p>Upload File Here</p>
							</div>


							<div class="file-chooser">
								<input type="file" id="file" name="import_file">
							</div>
							<div id="pop1"></div>
							<input class="file-uploader__submit-button" type="submit"
								value="Validate" onclick="return check();">
							<div id="pop1"></div>


						</form>
					</div>
					<div class="col-md-12">
					    <hr class="colorgraph" />
						<div class="widget stacked widget-table action-table">
							<div class="widget-header">
								<i class="fa fa-tasks" aria-hidden="true"></i>
								<h3>Recently Uploaded Reports</h3>
							</div>
							<!-- /widget-header -->
							<div class="widget-content">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Uploaded</th>
											<th>Filename</th>
											<th>Report</th>
											<th>Validation Result</th>
											<th>#Errors</th>
											<th>#Warnings</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach($fileReports as $fileReport) {
										    $reportfile = $fileReport->reportfile;
										    // if something goes wrong while storing the report or check result,
										    // reportfile or checkresult might be missing, so just in case...
										    if($reportfile === null) {
										        continue;
										    }
										    $checkresult = $reportfile->checkresult;
										    if($checkresult === null) {
										        continue;
										    }
                                        ?>
        								<tr>
											<td>{{$fileReport->created_at}}</td>
											<td><a href="download/{{$fileReport->id}}">{{$fileReport->filename}}</a></td>
											<td>{{$reportfile->reportid}}</td>
											<td>{{$checkresult->getResult()}}</td>
											<td>{{$checkresult->getNumberOfErrors()}}</td>
											<td>{{$checkresult->getNumberOfWarnings()}}</td>
											<td class="td-actions">
												<a href="download/{{$checkresult->resultfile->id}}" title="Download Validation Result"><i class="fa fa-download" aria-hidden="true"></i></a>
												&nbsp;
												<a href="email/{{$reportfile->id}}?context=file" title="Email Validation Result"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>
												&nbsp;
												<a onclick="confirm_delete_reportfile({{$reportfile->id}});" title="Delete Uploaded File and Validation Result"><i class="fa fa-trash-o trashIcon" aria-hidden="true"></i></a>
											</td>
										</tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
								</table>
							</div> <!-- /widget-content -->
						</div> <!-- /widget -->
						<p style="padding-top: 1ex;">Please see the <a href="{{url('filehistory')}}">Report History</a> for a list of all reports validated within the past {{Config::get('c5tools.clearAfter')}}.</p>
					</div>
					<div class="clearfix"></div>
				</div>
				<div id="menu1" class="tab-pane fade">
                    @if (Session::has('sushi_ok'))
                    <div id="sushi_ok" class="alert alert-success" style="color: green;">{{Session::get('sushi_ok')}}</div>
                    @endif
                    @if (Session::has('sushi_error'))
                    <div id="sushi_error" class="alert alert-danger" style="color: red;">{{Session::get('sushi_error')}}</div>
                    @endif
					<div class="col-xs-12 col-sm-12 col-md-12">
						<form id='frmshushivalidate' name="sushi_validation" method="post" class="file-uploader1" action="{{ url('/sushiValidate') }}" enctype="multipart/form-data">
							<fieldset>
                                <h3>SUSHI Server and Credentials</h3>
					            <hr class="colorgraph" />
                                <div class="form-group row">
								    <div class="col-md-8">
                                        <input value="{{Session::get('sushi_validate.api_url', '')}}" type="text" name="api_url" id="api_url"
									        class="form-control input-lg" placeholder="COUNTER_SUSHI API URL*">
                                        <span style="color: #ff0000">{{$errors->welcome->first('api_url')}}</span>
								    </div>
                                    <div class="col-md-4">
                                        <input value="{{Session::get('sushi_validate.platform', '')}}" type="text" name="platform" id="platform"
                                            class="input-lg form-control" placeholder="Platform">
                                        <span style="color: #ff0000">{{$errors->welcome->first('platform')}}</span>
                                    </div>
                                </div>
								<div class="form-group row">
                                    <div class="col-md-4">
                                        <input value="{{Session::get('sushi_validate.customer_id', '')}}" type="text" name="customer_id" id="customer_id"
                                            class="form-control input-lg" placeholder="Customer ID*">
                                        <span style="color: #ff0000">{{$errors->welcome->first('customer_id')}}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <input value="{{Session::get('sushi_validate.requestor_id', '')}}" type="text" name="requestor_id" id="requestor_id"
                                            class="form-control input-lg" placeholder="Requestor ID">
                                        <span style="color: #ff0000">{{$errors->welcome->first('requestor_id')}}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <input value="{{Session::get('sushi_validate.api_key', '')}}" type="text" name="api_key" id="api_key"
                                            class="form-control input-lg" placeholder="API Key">
                                        <span style="color: #ff0000">{{$errors->welcome->first('api_key')}}</span>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="col-md-1"></div>
                                <div class="col-xs-6 col-sm-4 col-md-2">
                                    <a class="btn btn-primary getShushiValue" rel="getstatus" href="#getstatus">Get Status</a>
								</div>
                                <div class="col-xs-6 col-sm-4 col-md-2">
                                    <a class="btn btn-primary getShushiValue" rel="getmembers" href="#getmembers">Get Members</a>
								</div>
                                <div class="col-xs-6 col-sm-4 col-md-4">
                                    <a class="btn btn-primary getShushiValue" rel="getreports" href="#getreports">Get Supported Reports</a>
								</div>
                                <div class="col-xs-6 col-sm-4 col-md-2">
                                    <button title="Get Report" rel="" type="button" class="btn btn-success openBtn" value="Get Report">Get Report</button>
								</div>
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
								<input type="hidden" id="method" name="method" value="">
							</fieldset>
						</form>
		                <p style="padding-top: 1.5ex;">Note that the responses for "Get Status", "Get Members" and "Get Supported Reports" are not yet validated, instead the JSON is returned.</p>
					</div>
					<div class="col-md-12">
			            <hr class="colorgraph" />
						<div class="widget stacked widget-table action-table">
							<div class="widget-header">
								<i class="fa fa-tasks" aria-hidden="true"></i>
								<h3>Recently Requested Reports</h3>
							</div>
							<!-- /widget-header -->
							<div class="widget-content">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Requested</th>
											<th>Filename</th>
											<th>Report</th>
											<th>Validation Result</th>
											<th>#Errors</th>
											<th>#Warnings</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach($sushiReports as $sushiReport) {
										    $reportfile = $sushiReport->reportfile;
										    // if something goes wrong while storing the report or check result,
										    // reportfile or checkresult might be missing, so just in case...
										    if($reportfile === null) {
										        continue;
										    }
										    $checkresult = $reportfile->checkresult;
										    if($checkresult === null) {
										        continue;
										    }
                                        ?>
        								<tr>
											<td>{{$sushiReport->created_at}}</td>
											<td><a href="download/{{$sushiReport->id}}">{{$sushiReport->filename}}</a></td>
											<td>{{$reportfile->reportid}}</td>
											<td>{{$checkresult->getResult()}}</td>
											<td>{{$checkresult->getNumberOfErrors()}}</td>
											<td>{{$checkresult->getNumberOfWarnings()}}</td>
											<td class="td-actions">
												<a href="download/{{$checkresult->resultfile->id}}" title="Download Validation Result"><i class="fa fa-download" aria-hidden="true"></i></a>
												&nbsp;
												<a href="email/{{$reportfile->id}}?context=sushi" title="Email Validation Result"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>
												&nbsp;
												<a onclick="confirm_delete_reportfile({{$reportfile->id}});" title="Delete Uploaded File and Validation Result"><i class="fa fa-trash-o trashIcon" aria-hidden="true"></i></a>
											</td>
										</tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
								</table>
							</div> <!-- /widget-content -->
						</div> <!-- /widget -->
						<p style="padding-top: 1ex;">Please see the <a href="{{url('filehistory')}}">Report History</a> for a list of all reports validated and the <a href="{{url('sushirequest')}}">SUSHI History</a> for the SUSHI requests made within the past {{Config::get('c5tools.clearAfter')}}.</p>
					</div>
				    <div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>

@endsection
<!--========================login form END here======================================-->




<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Report Parameter</h4>
            </div>
            <div class="modal-body">
            </div>
        </div>
     </div>
  </div>





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
    $('.openBtn').on('click', function() {
        var api_url = $('#api_url').val().trim();
        if (api_url == '') {
            alert("Please enter a COUNTER SUSHI API URL");
            return false;
        }
        var platform = $('#platform').val().trim();
        var customer_id = $('#customer_id').val().trim();
        if (customer_id == '') {
            alert("Please enter a Customer Id");
            return false;
        }
        var requestor_id = $('#requestor_id').val().trim();
        var api_key = $('#api_key').val().trim();
        var token = $('meta[name="csrf-token"]').attr('content');
        
        $('.modal-body').load('showshushiparameter', {
                "_token": token,
                "api_url": api_url,
                "platform": platform,
                "customer_id": customer_id,
                "requestor_id": requestor_id,
                "api_key": api_key
            }, function() {
                $('#myModal').modal({show:true});
            }
        );
    });
</script>
<script>
	//jQuery plugin
(function( $ ) {
   
   $.fn.uploader = function( options ) {
     var settings = $.extend({
       MessageAreaText: "No files selected.",
       MessageAreaTextWithFiles: "File List:",
       DefaultErrorMessage: "Unable to open this file.",
       BadTypeErrorMessage: "We cannot accept this file type at this time.",
       acceptedFileTypes: ['pdf', 'jpg', 'gif', 'jpeg', 'bmp', 'tif', 'tiff', 'png', 'xps', 'doc', 'docx',
        'fax', 'wmp', 'ico', 'txt', 'cs', 'rtf', 'xls', 'xlsx', 'json']
     }, options );
  
     var uploadId = 1;
     //update the messaging 
      $('.file-uploader__message-area p').text(options.MessageAreaText || settings.MessageAreaText);
     
     //create and add the file list and the hidden input list
     var fileList = $('<ul class="file-list"></ul>');
     var hiddenInputs = $('<div class="hidden-inputs hidden"></div>');
     $('.file-uploader__message-area').after(fileList);
     $('.file-list').after(hiddenInputs);
     
    //when choosing a file, add the name to the list and copy the file input into the hidden inputs
     $('.file-chooser__input').on('change', function(){
        var file = $('.file-chooser__input').val();
        var fileName = (file.match(/([^\\\/]+)$/)[0]);

       //clear any error condition
       $('.file-chooser').removeClass('error');
       $('.error-message').remove();
       
       //validate the file
       var check = checkFile(fileName);
       if(check === "valid") {
         
         // move the 'real' one to hidden list 
         $('.hidden-inputs').append($('.file-chooser__input')); 
       
         //insert a clone after the hiddens (copy the event handlers too)
         $('.file-chooser').append($('.file-chooser__input').clone({ withDataAndEvents: true})); 
       
         //add the name and a remove button to the file-list
         $('.file-list').append('<li style="display: none;"><span class="file-list__name">' + fileName + '</span><button class="removal-button" data-uploadid="'+ uploadId +'"></button></li>');
         $('.file-list').find("li:last").show(800);
        
         //removal button handler
         $('.removal-button').on('click', function(e){
           e.preventDefault();
         
           //remove the corresponding hidden input
           $('.hidden-inputs input[data-uploadid="'+ $(this).data('uploadid') +'"]').remove(); 
         
           //remove the name from file-list that corresponds to the button clicked
           $(this).parent().hide("puff").delay(10).queue(function(){$(this).remove();});
           
           //if the list is now empty, change the text back 
           if($('.file-list li').length === 0) {
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
         
         if(check === "badFileName") {
           errorText = options.BadTypeErrorMessage || settings.BadTypeErrorMessage;
         }
         
         $('.file-chooser__input').after('<p class="error-message">'+ errorText +'</p>');
       }
     });
     
     var checkFile = function(fileName) {
       var accepted          = "invalid",
           acceptedFileTypes = this.acceptedFileTypes || settings.acceptedFileTypes,
           regex;

       for ( var i = 0; i < acceptedFileTypes.length; i++ ) {
         regex = new RegExp("\\." + acceptedFileTypes[i] + "$", "i");

         if ( regex.test(fileName) ) {
           accepted = "valid";
           break;
         } else {
           accepted = "badFileName";
         }
       }

       return accepted;
    };
  }; 
}( jQuery ));

/////init 

$(document).ready(function() {
    $('.fileUploader').uploader({
        MessageAreaText: "Upload Your File Here"
    });

    setTimeout(function() {
        $('#file_ok').fadeOut('fast');
        $('#file_error').fadeOut('fast');
        $('#sushi_ok').fadeOut('fast');
        $('#sushi_error').fadeOut('fast');
    }, 15000);
    
    $('.getShushiValue').click(function() {
        var api_url = $('#api_url').val().trim();
        if (api_url == '') {
            alert("Please enter a COUNTER SUSHI API URL");
            return false;
        }
        var customer_id = $('#customer_id').val().trim();
        if (customer_id == '') {
            alert("Please enter a Customer Id");
            return false;
        }

        $("#method").val($(this).attr('rel'));
        $('#frmshushivalidate').submit();
  });
 
});
</script>

<script>
$(function () {
    $('.date-picker').datepicker({
        format: "yyyy-mm-dd",
    });
    
    
    $('#start_month').on('changeDate', function(){
        $(this).datepicker('hide');
    });
    $('#end_month').on('changeDate', function(){
        $(this).datepicker('hide');
    });
})
</script>

@if (count($errors->welcome)>0)
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif
@if ($context === 'sushi')
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif 
@if (session::has('sushi_ok') || Session::has('sushi_error'))
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif
@endsection

<script>
function check()
{
 var aa = $("#file").val();
 
 var ext = aa.split('.').pop();
 //alert(ext);
 if(!((ext == 'tsv') || (ext == 'csv') || (ext == 'xlsx') || (ext == 'xls') || (ext == 'json')))
 {
  $("#pop1").html("<span style='color:#ff0000'>The File should be xls,xlsx,csv,tsv or json extensions</span>");
  return false;
 }
}
</script>




<!---=======================javascripts comes in bottom============================-->

<script>
  function confirm_delete_reportfile(id){
    if(confirm('Do you really want to delete the uploaded report and the validation result?')) {
      window.location.href = "delete_reportfile/"+id;
    }
  }
</script>
