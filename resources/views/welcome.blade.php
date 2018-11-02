<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>
@extends('layout.master')

@section("content")
<link href="{{URL::asset('assets/css/multiselect.css')}}" rel="stylesheet">
<?php
// makeing parent and child relationship
/*$allgroup = array();
$i = 0;
foreach ($reportsname as $reportDetails) {
    $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['id'] = $reportDetails->id;
    $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['report_name'] = $reportDetails->report_name;
    $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['report_code'] = $reportDetails->report_code;
    $i ++;
}*/
?>
    <!--========================login form start here======================================-->


	@if (Session::has('error'))
	<div class="alert alert-success" style="color: red">{{
		Session::get('error') }}</div>
	@endif
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
			<ul class="nav nav-tabs ulTabs">
				<li class="active"><a data-toggle="tab" href="#file1">File Validate
				</a></li>
				<li><a data-toggle="tab" href="#menu1">SUSHI Validate</a></li>
			</ul>
			<div class="tab-content">
				<div id="file1" class="tab-pane fade in active">
					<div class="col-md-6">
						@if (Session::has('emailMsg'))
						<div class="alert alert-success">{{ Session::get('emailMsg') }}</div>
						@endif
						<form name="file_valid" method="post" class="file-uploader"
							action="fileValidate" enctype="multipart/form-data">
							<input type="hidden" name="_token"
								value="<?php echo csrf_token() ?>">
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
					<div class="col-md-6">
						<div class="widget stacked widget-table action-table">
							<div class="widget-header">
								<i class="fa fa-tasks" aria-hidden="true"></i>
								<h3>Old Downloads</h3>
							</div>
							<!-- /widget-header -->
							<div class="widget-content">
								<table class="table table-striped table-bordered">
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
								</table>
							</div>
							<!-- /widget-content -->
						</div>
						<!-- /widget -->
					</div>
					<div class="clearfix"></div>
				</div>
				<div id="menu1" class="tab-pane fade">
					<div class="col-xs-12 col-sm-12 col-md-12">
						<form id='frmshushivalidate' name="sushi_validation" method="post" class="file-uploader1" action="/sushiValidate" enctype="multipart/form-data">
							<fieldset>
                                                            <h3>Requestor</h3>
                                                            <div class="col-xs-3 col-sm-3 col-md-3 pull-right">
                                                                <a href="/sushirequest" class="btn btn-primary btn-block">View Requests</a>
                                                            </div>
								<hr class="colorgraph">

								<div class="form-group">
									<input value="{{ old('Requestorurl') }}" type="text"
										name="Requestorurl" id="Requestorurl"
										class="form-control input-lg" placeholder="COUNTER_SUSHI URL*"> <span
										style="color: #ff0000">{{
										$errors->welcome->first('Requestorurl') }}</span>
								</div>
								
                                                                
                                                                <!-- tab section Start here -->
								<div class="form-group">
<!--                                                                    <div class="col-md-12">
                                                                        <fieldset>
                                                                        <div class="col-md-12" >
                                                                        <input class="selection" type="radio" checked="checked" name="typeofrequest" value="customerandrequester"> Combination of customer ID and requestor ID
                                                                        <input class="selection" type="radio" name="typeofrequest" value="ipaddress"> IP Address of the COUNTER_SUSHI client
                                                                        <input class="selection" type="radio" name="typeofrequest" value="apikey"> APIKey
                                                                        </div>
                                                                        </fieldset>
                                                                        <hr>
                                                                    </div>-->
                                                                            
                                                                            <div id="apikeyCompbination" class="tab-pane">
                                                                                <span id='CustomerId'>
                                                                                <input value="{{ old('CustomerId') }}" type="text"
                                                                                    name="CustomerId" id="CustomerIdInner"
                                                                                    class="form-control input-lg" placeholder="Customer ID*"> <span
                                                                                    style="color: #ff0000">{{
                                                                                    $errors->welcome->first('CustomerId') }}</span>
                                                                                <br/>
                                                                                </span>
                                                                                <span id='RequestorId'>
                                                                                <input value="{{ old('RequestorId') }}" type="text"
                                                                                    name="RequestorId" id="RequestorIdInner"
                                                                                    class="form-control input-lg" placeholder="Requestor ID"> <span
                                                                                    style="color: #ff0000">{{
                                                                                    $errors->welcome->first('RequestorId') }}</span>
                                                                                <br/>
                                                                                </span>
                                                                                <span id='APIkey'>
                                                                                <input value="{{ old('APIkey') }}" type="text" name="APIkey" id="APIkeyInner"
                                                                                    class="form-control input-lg"
                                                                                    placeholder="API Key"> <span style="color: #ff0000">{{
                                                                                    $errors->welcome->first('APIkey')}}</span>
                                                                                <br/>
                                                                                </span>
                                                                                <span id='platform'>
                                                                                <input value="{{ old('platform') }}" type="text"
                                                                                     name="platform" id="platformInner"
                                                                                    class="form-control input-lg" placeholder="Platform"> <span
                                                                                    style="color: #ff0000">{{
                                                                                    $errors->welcome->first('Platform') }}</span>
                                                                                </span>
                                                                            </div>
                                                                <!-- tab section Start end -->
                                                                </div>
                                                                
                                                                
								
								
								</fieldset>
                                                                </div>
								<div class="col-xs-12 col-sm-12 col-md-12">
                                                                        <div class="col-xs-6 col-sm-4 col-md-2">
                                                                            <a class="btn btn-primary getShushiValue" rel="getverify" href="#getverify">Verify Credential</a>
									</div>
                                                                        <div class="col-xs-6 col-sm-4 col-md-2">
                                                                            <a class="btn btn-primary getShushiValue" rel="getstatus" href="#getstatus">Get Status</a>
									</div>
                                                                        
                                                                        <div class="col-xs-6 col-sm-4 col-md-2">
                                                                            <a class="btn btn-primary getShushiValue" rel="getmembers" href="#getmembers">Get Members</a>
									</div>
                                                                        <div class="col-xs-6 col-sm-4 col-md-3">
                                                                            <a class="btn btn-primary getShushiValue" rel="getsupportedreports" href="#getsupportedreports">Get Supported Reports List</a>
									</div>
                                                                        <div class="col-xs-6 col-sm-4 col-md-3">
                                                                            <button title="Get Report" rel="" type="button" class="btn btn-success openBtn" value="Get Report">Get Report</button>
									</div>
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
									<input type="hidden" id="requestButton" name="requestButton" value="">
								</div>
								<hr class="colorgraph">
							</fieldset>
						</form>
					</div>
					<div class="clearfix"></div>
				</div>

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
                <h4 class="modal-title">Run configuration parameter</h4>
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
    
    
        $("form input:radio").change(function () {
            if ($(this).val() == "ipaddress") {
            // Disable your roomnumber element here
                $('#RequestorId').hide();
                $('#RequestorId').val('');
                $('#APIkey').hide();
                $('#APIkey').val('');
                $('#RequestorId').hide();
            } else if($(this).val() == "apikey") {
                
            // Re-enable here I guess
                $('#APIkey').show();
                $('#platform').show();
                $('#RequestorId').hide();
            }else{
                
                $('#APIkey').val('');
                $('#platform').show();
                $('#RequestorId').show();
                $('#APIkey').hide();
            }
        });
    
    
        $('.openBtn').on('click',function(){
        var Requestorurl = $('#Requestorurl').val();
        var name = $('#APIkeyInner').val();
        if(name==''){
            name=0;
        }
        var checkedValue = $("input[name='typeofrequest']:checked").val();
        var CustomerId = $('#CustomerIdInner').val();
        var platform = $('#platformInner').val();
        var RequestorIdInner = $('#RequestorIdInner').val();
        if(platform==''){
            platform=0;
        }
        //var RequestorId = $('#RequestorId').val());
        if (!Requestorurl) {
            alert("Please enter COUNTER SUSHI URL");
            return false;
        } else if ((!name) && (checkedValue=='apikey')) {
            alert("Please enter API Key");
            return false;
        } else if (!CustomerId) {
            alert("Please enter Customer Id");
            return false;
        }
        
        if((!RequestorIdInner) && (checkedValue=='customerandrequester')){
            alert("Please enter Requester Id");
            return false;
        }
        Requestorurl = Requestorurl.replace(new RegExp('/', 'g'), '_');
        $('.modal-body').load('showshushiparameter/'+Requestorurl+'/'+name+'/'+CustomerId+'/'+platform,function(){
        $('#myModal').modal({show:true});
        });
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

$(document).ready(function(){
  $('.fileUploader').uploader({
    MessageAreaText: "Upload File Here"
  });
  
  
  $('.getShushiValue').click(function(){
      
      
        var Requestorurl = $('#Requestorurl').val();
        var name = $('#APIkeyInner').val();
        if(name==''){
            name=0;
        }
        var checkedValue = $("input[name='typeofrequest']:checked").val();
        var CustomerId = $('#CustomerIdInner').val();
        var platform = $('#platform').val();
        var RequestorIdInner = $('#RequestorIdInner').val();
        if(platform==''){
            platform=0;
        }
        //var RequestorId = $('#RequestorId').val());
        if (!Requestorurl) {
            alert("Please enter COUNTER SUSHI URL");
            return false;
        } else if ((!name) && (checkedValue=='apikey')) {
            alert("Please enter API Key");
            return false;
        } else if (!CustomerId) {
            alert("Please enter Customer Id");
            return false;
        }
        
        if((!RequestorIdInner) && (checkedValue=='customerandrequester')){
            alert("Please enter Requester Id");
            return false;
        }
      
      
      
      $("#requestButton").val($(this).attr('rel'));
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

@if(count($errors->welcome)>0)
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif @if (Session::has('error'))
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


