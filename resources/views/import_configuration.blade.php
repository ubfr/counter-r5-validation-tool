<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>
@extends('layout.master')

@section("content")
    <!--========================login form start here======================================-->

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
		
			<ul class="nav nav-tabs ulTabs">
			
				<li class="active"><a data-toggle="tab" href="#file1"><b>Import Configuration</b>
				</a></li>
			</ul>
			
			<div class="tab-content">
			
				<div id="file1" class="tab-pane fade in active">
				
					<div class="col-md-6">
					
					<h3><strong>Import COUNTER Configuration</strong></h3>
						<hr class="colorgraph">
						<form name="importconfiguration" method="post" class="file-uploader"
							action="/consortiumimport" enctype="multipart/form-data">
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
								value="Import" onclick="return check();">
							<div id="pop1"></div>


						</form>
					</div>
					
					<div class="col-md-6">
					<div class="clearfix"></div>
					
						<div class="widget stacked widget-table action-table">
							<div class="widget-header">
							
								<i class="fa fa-tasks" aria-hidden="true"></i>
						
								<h3>Sample file format for import configuration</h3>
							</div>
							<!-- /widget-header -->
							<div class="widget-content">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											
											<th class="td-actions">Download</th>
										</tr>
									</thead>
									<tbody>
										
        										<tr>
        										
											<td><a href="/sample/Template for import configuration.xlsx"><i class="fa fa-download"
												aria-hidden="true"></i>	</a></td>
										</tr>
									
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
					<div class="clearfix"></div>
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
@endif 
@endsection

<script>
 function check()
{
 var aa = $("#file").val();
 
 var ext = aa.split('.').pop();
 //alert(ext);
 if(!  (ext == 'xlsx') )
 {
  $("#pop1").html("<span style='color:#ff0000'>The File should xlsx extension</span>");
  return false;
 }
 
 
}
</script>

</head>
</html>

<!---=======================javascripts comes in bottom============================-->


