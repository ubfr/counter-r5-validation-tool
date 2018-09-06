<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>
@extends('layout.master')

@section("content")
<?php
// makeing parent and child relationship
/*
 * $allgroup = array();
 * $i = 0;
 * foreach ($reportsname as $reportDetails) {
 * $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['id'] = $reportDetails->id;
 * $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['report_name'] = $reportDetails->report_name;
 * $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['report_code'] = $reportDetails->report_code;
 * $i ++;
 * }
 */
?>
    <!--========================login form start here======================================-->

<div class="main-content">
	@if (Session::has('error'))
	<div class="alert alert-success" style="color: red">{{
		Session::get('error') }}</div>
	@endif
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
			<ul class="nav nav-tabs ulTabs">
				<li><a data-toggle="tab" href="#menu1">Harvesting</a></li>
				<li class="active"><a data-toggle="tab" href="#file1">Configuration</a></li>
			</ul>
			<div class="tab-content">
				<div id="file1" class="tab-pane fade in active">

					<div class="col-md-12">
						<div class="widget stacked widget-table action-table">
							
							<div>
							<h3>Consortium Configuration</h3>
							<form name="saveconsortium" method="post" class="file-uploader1" action="saveconsortium" enctype="multipart/form-data">
							<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
							<hr class="colorgraph">

                                <!-- Div section for Confoguration information -->
								<div class="form-group col-md-4 noPaddingXS noLeftPadd">
									<input value="" type="text"
										name="configuration_name" id="ConfigurationName"
										class="form-control input-lg" placeholder="Configuration Name*"> <span
										style="color: #ff0000">{{ $errors->consortium_config_add->first('registercon') }}</span>
								</div>
								
								<div class="form-group col-md-8 noPaddingXS noLeftPadd">
        									<input value="" type="text" name="remarks"
        										id="name" class="form-control input-lg"
        										placeholder="Remarks*"> <span style="color: #ff0000">
        										{{ $errors->consortium_config_add->first('registercon') }}</span>
    								</div>
								
								
								<!-- Div section for Provider information -->
								<div class="form-group col-md-12 noPaddingXS noLeftPadd" >
    								<div class="form-group col-md-4 noPaddingXS noLeftPadd">
    									<input value="{{ old('CustomerId') }}" type="text"
    										name="provider_name" id="ProviderName"
    										class="form-control input-lg" placeholder="Provider Name*"> <span
    										style="color: #ff0000">{{ $errors->consortium_config_add->first('consortium_configuration') }}</span>
    								</div>
								
    								<div class="form-group col-md-8 noPaddingXS noLeftPadd">
    									<input value="{{ old('CustomerId') }}" type="text"
    										name="provider_url" id="ProviderUrl"
    										class="form-control input-lg" placeholder="Provider Url*"> <span
    										style="color: #ff0000">{{ $errors->registercon->first('consortium_configuration') }}</span>
    								</div>
								<!-- 
    								<div class="form-group col-md-4 noPaddingXS noLeftPadd">
        								<select class="form-control input-lg">
                                         <option value="">APIKey</option>
                                         <option value="">Requestor ID</option>
                                         <option value="">Customer ID</option>
        								</select>
    								</div>
								
    								<div class="form-group col-md-8 noPaddingXS noLeftPadd">
    									<input value="{{ old('APIkey') }}" type="text" name="apikey"
    										id="name" class="form-control input-lg"
    										placeholder="Value*"> <span style="color: #ff0000"></span>
    								</div>
								-->
								
									<div class="form-group col-md-4 noPaddingXS noLeftPadd">
    									<input value="{{ old('APIkey') }}" type="text" name="apikey"
    										id="name" class="form-control input-lg"
    										placeholder="Api Key*"> <span style="color: #ff0000"></span>
    								</div>
    								
									<div class="form-group col-md-4 noPaddingXS noLeftPadd">
    									<input value="" type="text" name="customer_id"
    										id="name" class="form-control input-lg"
    										placeholder="Customer ID*"> <span style="color: #ff0000"></span>
    								</div>
    								
    								
    								<div class="form-group col-md-4 noPaddingXS noLeftPadd">
    									<input value="" type="text" name="requestor_id"
    										id="name" class="form-control input-lg"
    										placeholder="Requestor ID*"> <span style="color: #ff0000"></span>
    								</div>
    								
    								<div class="form-group col-md-12 noPaddingXS noLeftPadd">
        									<input value="" type="text" name="comments"
        										id="name" class="form-control input-lg"
        										placeholder="Comments*"> <span style="color: #ff0000"></span>
    								</div>
								
								</div>
								
								
					<div class="form-group">
                        <div class="col-xs-12 col-md-4">
                        <input type="submit"  value="Add Configuration" 
                        class="btn btn-primary btn-block btn-lg" tabindex="7">
                        </div>
                    </div>
							</form>
							
							
							</div>
							
							<!-- /widget-header -->
							
							<!-- /widget-content -->
						</div>
						<!-- /widget -->
					</div>
					
					
					<div class="col-md-12">
					<h3>Consortium Configuration List</h3>
					<hr class="colorgraph">
					<div class="widget-content">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Id</th>
											<th>Configuration_name</th>
											<th>Remarks</th>
											
											<th class="td-actions">Edit</th>
										</tr>
									</thead>
									<tbody>
										<?php
        $i = 1;
        if (isset($file_detail)) {
            foreach ($file_detail as $filedetails) {
                ?>
        									<tr>
											<td><?php echo $i++;?></td>
											<td><?php echo $filedetails->configuration_name;?></td>
											<td><?php echo $filedetails->remarks;?></td>
											
											<td class="td-actions"><a
												href="edit_consortium/{{$filedetails->configuration_name}}"><i
													class="fa fa-edit" aria-hidden="true"></i></a></td>
										    </tr>
										<?php
            }
        }
        ?>
									</tbody>
								</table>
							</div>
					
					</div>
					
					
					<div class="clearfix"></div>
				</div>
				<div id="menu1" class="tab-pane fade">
					<div class="col-xs-12 col-sm-12 col-md-12">
						
					</div>

					<div class="clearfix"></div>
				</div>

			</div>

		</div>

	</div>

</div>

@endsection
<!--========================login form END here======================================-->



@section("additionaljs")
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
 $(function() {
  $('.date-picker').datepicker( {
    format: "yyyy-mm-dd",
     //viewMode: "months", 
     //minViewMode: "months"
    });
 });
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
 if(!((ext == 'tsv') || (ext == 'csv') || (ext == 'xlsx') || (ext == 'xls') || (ext == 'json')))
 {
  $("#pop1").html("<span style='color:#ff0000'>The File should be xls,xlsx,csv,tsv or json extensions</span>");
  return false;
 }
 
 
}
</script>

</head>
</html>

<!---=======================javascripts comes in bottom============================-->


