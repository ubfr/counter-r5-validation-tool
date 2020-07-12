
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>

@extends('layout.master')

@section("content")

<!--========================login form start here======================================-->
<div class="main-content">
	@if (Session::has('error'))
		<div class="alert alert-success" style="color:red">
			{{ Session::get('error') }}
		</div>
	@endif
	<?php foreach($user_detail as $user_details){?>
	<form action="{{url('edit_user')}}/{{$user_details->id}}" method="post" name="edit_user">
	<input type="hidden" name="_token" value="{{csrf_token()}}">
	
	<div class="form-group row">
		<label for="example-text-input" class="col-xs-2 col-form-label">First Name</label>
		<div class="col-xs-10">
			<input class="form-control" name="first_name" type="text" value="{{$user_details->first_name}}" id="example-text-input" placeholder="First Name">
		</div>
	</div>
	
	<div class="form-group row" >
		<label for="example-text-input" class="col-xs-2 col-form-label">Last Name</label>
		<div class="col-xs-10">
			<input class="form-control" type="text" name="last_name" value="{{$user_details->last_name}}" id="example-text-input" placeholder="Last Name">
		</div>
	</div>
	
	<div class="form-group row">
		<label for="example-text-input" class="col-xs-2 col-form-label">Display Name</label>
		<div class="col-xs-10">
			<input class="form-control" type="text" name="display_name" value="{{$user_details->display_name}}" id="example-text-input" placeholder="Dislay Name">
		</div>
	</div>
	
	<div class="form-group row">
		<label for="example-text-input" class="col-xs-2 col-form-label">Email</label>
		<div class="col-xs-10">
		<input class="form-control" type="email" readonly="readonly" value="{{$user_details->email}}" id="example-text-input" placeholder="test@testmail.com">
		</div>
	</div>
	
	
	<div class="form-group row">
		<label for="example-text-input" class="col-xs-2 col-form-label">Password</label>
		<div class="col-xs-10">
			<input class="form-control" name="password" type="password"  value="" id="example-text-input" placeholder="Enter password">
		</div>
	</div>
	
	<div class="form-group row">
		<div class="offset-sm-2 col-sm-10" align="center">
			<a href="{{url('userlist')}}" class="btn btn-primary btnBlockxs ">Back</a>
			<button type="submit" class="btn btn-primary">Save</button>
		</div>
    </div>
	</form>
	<?php }?>
</div>
    <!--========================login form END here======================================-->
@endsection
<!---=======================javascripts comes in bottom============================-->


@section("addtionaljs")
<!-- Latest compiled and minified JavaScript -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap-datepicker.min.js')}}"></script>
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
        'fax', 'wmp', 'ico', 'txt', 'cs', 'rtf', 'xls', 'xlsx']
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

//init 
$(document).ready(function(){
  $('.fileUploader').uploader({
    MessageAreaText: "No files selected. Please select a file."
  });
 
});
//  $('#sandbox-container .input-daterange').datepicker({
//	 changeMonth: true,
//        changeYear: true,
//        showButtonPanel: true,
//        dateFormat: 'MM yy',
//        onClose: function(dateText, inst) { 
//            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
//        }    
//    });   
$(function() {
    $('.date-picker').datepicker( {
    format: "mm-yyyy",
    viewMode: "months", 
    minViewMode: "months"
    });
});
</script>
 <script>
 function check()
{
 var aa = $("#file").val();
 
 var ext = aa.split('.').pop();
 //alert(ext);
 if(!((ext == 'tsv') || (ext == 'csv') || (ext == 'xlsx') || (ext == 'xls')))
 {
  $("#pop1").html("<span style='color:#ff0000'>The File should be xls,xlsx,csv or tsv extensions</span>");
  return false;
 }
 
 
}
</script>

@if(count($errors->welcome)>0) 
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif
 @if (Session::has('error'))
		<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
	@endif
	
	@endsection
 
</body>
</html>
