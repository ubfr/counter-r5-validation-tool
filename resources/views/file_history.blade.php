<!DOCTYPE HTML>
<html>
<head>

<meta charset="utf-8">
<title>Validation - Counter</title>
@extends('layout.master')

@section("content")

    @if (Session::has('userdelmsg'))
        <div class="alert alert-success" style="color:green">
            {{ Session::get('userdelmsg') }}
        </div>
    @endif
    
    @if (Session::has('useralertmsg'))
        <div class="alert alert-danger" style="color:red">
            {{ Session::get('useralertmsg') }}
        </div>
    @endif
    
    @if (Session::has('userupdatemsg'))
        <div class="alert alert-success" style="color:green">
            {{ Session::get('userupdatemsg') }}
        </div>
    @endif

	@if (Session::has('emailMsg'))
		<div class="alert alert-success">
		    {{ Session::get('emailMsg') }}
		</div>
	@endif

    <div class="row">
        <div class="col-sm-12">
            <h3>Report History for the past {{Config::get('c5tools.clearAfter')}}</h3>
            <hr class="colorgraph" />
        </div>
        <div class="col-sm-12">
            <div id="filehistory_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-12">
                        <table id="filehistory" class="table table-striped table-bordered" cellspacing="0" width="100%" role="grid" style="width: 100%;">
                            <thead>
                                <tr role="row">
                                    <th class="sorting" aria-label="Time: activate to sort column ascending">Time</th>
                                    <?php if($utype === 'admin') { ?>
                                    <th class="sorting" aria-label="Email address: activate to sort column ascending">Email Address</th>
                                    <?php } ?>
                                    <th class="sorting" aria-label="Source: activate to sort column">Source</th>
                                    <th class="sorting" aria-label="Filename: activate to sort column">Filename</th>
                                    <th class="sorting" aria-label="Report: activate to sort column">Report</th>
                                    <th class="sorting" aria-label="Validation Result: activate to sort column">Validation Result</th>
                                    <th class="sorting" aria-label="Number of Errors: activate to sort column">#Errors</th>
                                    <th class="sorting" aria-label="Number of Warnings: activate to sort column">#Warnings</th>
                                    <th style="width: 96px;">Actions</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Time</th>
                                    <?php if($utype === 'admin') { ?>
                                    <th>Email Address</th>
                                    <?php } ?>
                                    <th>Source</th> 
                                    <th>Filename</th> 
                                    <th>Report</th>
                                    <th>Validation Result</th>
                                    <th>#Errors</th>
                                    <th>#Warnings</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                            <tbody> 
                                <?php
                                foreach($filehistory as $file) {
                                    $reportfile = $file->reportfile;
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
                                <tr role="row">
                                    <td>{{$file->created_at}}</td>
                                    <?php if($utype === 'admin') { ?>
                                    <td>{{$file->user->email}}</td>
                                    <?php } ?>
                                    <td>{{$file->getSourceName()}}</td>
                                    <td><a href="download/{{$file->id}}">{{$file->filename}}</a></td>
                                    <td>{{$reportfile->reportid}}</td>
                                    <td>{{$checkresult->getResult()}}</td>
                                    <td>{{$checkresult->getNumberOfErrors()}}</td>
                                    <td>{{$checkresult->getNumberOfWarnings()}}</td>
                                    <td>
                                        <a href="download/{{$checkresult->resultfile->id}}" title="Download Validation Result"><i class="fa fa-download" aria-hidden="true"></i></a>
                                        &nbsp;
                                        <a href="email/{{$reportfile->id}}" title="Email Validation Result"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>
                                        &nbsp;
                                        <a onclick="confirm_delete_reportfile({{$reportfile->id}});" title="Delete Uploaded File and Validation Result"><i class="fa fa-trash-o trashIcon" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-7">
            <div class="dataTables_paginate paging_simple_numbers" id="filehistory_paginate">
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
    
    $('#filehistory').DataTable( {
        "searching": true,
        "language": {
            "lengthMenu": "Show entries: _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
        },
        "order": [ [ 0, "desc" ] ]
    } );
     
} );
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
//     changeMonth: true,
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
  function confirm_delete_reportfile(id){
    if(confirm('Do you really want to delete the uploaded report and the validation result?')) {
      window.location.href = "delete_reportfile/"+id;
    }
  }
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

