<!DOCTYPE HTML>
<?php
$reportfileName = $reportfile->reportfile->filename;

$crFatal = $checkResult->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_FATAL);
$crErrors = $crFatal + $checkResult->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_CRITICAL) + $checkResult->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_ERROR);
$crWarnings = $checkResult->getNumberOfMessages(\ubfr\c5tools\CheckResult::CR_WARNING);
$crMessages = $checkResult->asArray();
$now = date('Y-m-d H:i:s');
?>
<html>
<head>
<meta charset="utf-8">
<title>Validation Report - Counter</title>

@extends('layout.master')
@section("content")

  <!-- === validation report start === -->
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
        <div class="pull-left">
          <h3>Validation Result</h3>
        </div>
        <div class="pull-right">
          <h3><a href="{{url('filelist')}}?context={{$context}}" class="btn btn-primary btnBlockxs ">Validate New Report</a></h3>
        </div>
        <div class="clearfix"></div>
        <div class="validationReport">
          <div class="col-md-6">
  @if($crFatal !== 0)
            <p>The validation of the report {{$reportfileName}} failed with a fatal error at {{$now}}, please review the errors and warnings.</p>
  @elseif($crErrors + $crWarnings > 0)
            <p>The report {{$reportfileName}} did not pass the validation at {{$now}}, please review the errors and warnings.</p>
  @else
            <p>The report {{$reportfileName}} passed the (not yet complete) validation at {{$now}}.</p>
  @endif
            <p class="marTop20">
              <a href="email/{{$reportfile->id}}?context={{$context}}" class="btn btn-primary btnBlockxs"><i class="fa fa-envelope"></i>Email Validation Result</a>&nbsp;<a href="download/{{$reportfile->checkresult->resultfile->id}}" class="btn btn-primary btnBlockxs"><i class="fa fa-cloud-download"></i>Download Validation Result</a>
            </p>
          </div>
          <div class="col-md-6">
            <div class="table-responsive">
              <table>
                <tr class="row my_naglowki">
                  <th class="col-md-6 my_important"></th>
                  <th class="col-md-3 my_planFeature my_pusty">{{$crErrors}}<br> Errors</th>
                  <th class="col-md-3 my_planFeature my_pusty">{{$crWarnings}}<br> Warnings</th>
                </tr>
              </table>
            </div>
          </div>
        </div>
  @if($crErrors + $crWarnings > 0)
        <div class="clearfix"></div>
        <h2>{{$crErrors}} Errors, {{$crWarnings}} Warnings</h2>
    <?php foreach($crMessages as $crMessage) { ?>
    @if($crMessage['level'] === \ubfr\c5tools\CheckResult::CR_WARNING)
        <div class="alert alert-warning">
    @else
        <div class="alert alert-danger">
    @endif
          <p><strong>{{$crMessage['header']}}</strong></p>
          <div class="panel panel-body">
            {{$crMessage['data']}}
          </div>
          <p style="color:#000">{{$crMessage['message']}}</p>
        </div>
    <?php } ?>
  @else
    <p>&nbsp;</p>
  @endif
      </div>
    </div>
  </div>
  @endsection
  <!-- === validation report end === -->

<!---=======================javascripts comes in bottom============================-->
@section("additionaljs")



<!-- Latest compiled and minified JavaScript -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
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

//init 
$(document).ready(function(){
  $('.fileUploader').uploader({
    MessageAreaText: "No files selected. Please select a file."
  });
});

</script>
 
 @endsection
 
</body>
</html>


