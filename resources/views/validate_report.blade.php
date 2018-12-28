<!DOCTYPE HTML>
<?php 
//print_r($data_error);die;

$tot_errors=isset($error_details[0])?count($error_details):0;
//die("iii".$tot_errors);
$tot_warning=count($warning_details);
$tot_str_error=$tot_errors;
$tot_data_error=isset($data_error)?$data_error:'';
$tot_str_warning=$structure_warning;
$tot_data_warning=$tot_warning;
?>
<html>
<head>
<meta charset="utf-8">
<title>Validation Report - Counter</title>

@extends('layout.master')
@section("content")

    <!--========================login form start here======================================-->
    <div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
		<div class="pull-left">
             <h3>Validation Results</h3></div><div class="pull-right"><h3><a href="welcome" class="btn btn-primary btnBlockxs ">Validate New File</a></h3></div> <div class="clearfix"></div>
            <div class="validationReport">
               
               <div class="col-md-6">
           		 <h5><a href="download/{{$file_id}}/{{$uploaded_file}}" download="{{$uploaded_file}}">{{$uploaded_file}}</a></h5>
                 @if($tot_errors=='0')
				 
					 <p>Your file successfully validated.</p>
				 
				 @else
				 
				<p>Sorry, your File did not pass validation. Please review the errors and warnings below:</p>
				
				 
                <p class="marTop20">
					<a  href="email/{{$file_id}}" class="btn btn-primary btnBlockxs"><i class="fa fa-envelope" ></i> E-mail Report  </a>&nbsp;
					<a href="downloadfront/{{$file_id}}/{{$uploaded_file}}" class="btn btn-primary btnBlockxs"><i class="fa fa-cloud-download"></i> Download Error Report</a>
				</p>
                @endif
			   </div>
               <div class="col-md-6">
               			<div class="table-responsive">
               			<table>
                                <tr class="row my_naglowki">
                                   <th class="col-md-6 my_important"></th>
                                   <th class="col-md-3 my_planFeature my_pusty"><?php echo $tot_errors;?><br> Errors</th>
                                   <th class="col-md-3 my_planFeature my_pusty"><?php echo $tot_warning;?><br> Warnings</th>
                                   
                                </tr>
                                
                                <tr class="row my_featureRow" >
                                    <td class="col-md-6 my_feature">
                                       Structure
                                    </td>
                                    <td class="col-md-3 my_planFeature my_szary">
                                       <?php echo $tot_str_error;?>
                                    </td>
                                    <td class="col-md-3 my_planFeature my_niebieski">
                                       <?php echo $tot_str_warning;?>
                                    </td>
                                </tr>
                                <tr class="row my_featureRow" >
                                    <td class="col-md-6 my_feature">
                                       Data
                                    </td>
                                   <td class="col-md-3 my_planFeature my_szary">
                                       <?php echo $tot_data_error;?>
                                    </td>
                                    <td class="col-md-3 my_planFeature my_niebieski">
                                        <?php echo $tot_data_warning;?>
                                    </td>
                                </tr>
                                
                                
                                </table>
                        </div>
               </div>
              
            </div>
             <div class="clearfix"></div>
             
              <div class="clearfix"></div>
              <h2><?php echo $tot_errors;?> Errors, <?php echo $tot_warning;?> Warnings</h2>
			  <?php for($i=0;$i<$tot_errors;$i++){?>
              <div class="alert alert-danger">
              	<p><strong>Errors</strong> </p>
				
                <div class="panel panel-body">
					<?php echo $error_details[$i]["data"]??'';?>
				</div>
                <p style="color:#000">
					<?php echo $error_details[$i]["error"]??'';?>
				</p>
              </div><?php }?>
			  <?php if($tot_warning>0){
				  for($j=0;$j<$tot_warning;$j++){?>
              <div class="alert alert-warning">
              	<p><strong>Warning</strong></p>
                <div class="panel panel-body">
				<?php echo $warning_details[$j]["data"]??'';?>
				</div>
                <p style="color:#000">
					<?php echo $warning_details[$j]["error"]??'';?>
				</p>
              </div>
				  <?php }} if($tot_str_warning>0){?>
              <div class="alert alert-info">
              	<p>Structural problem: <strong>Missing Columns on row 5</strong> </p>
                <div class="panel panel-body">test</div>
                <p style="color:#000">Row 5 contains a different number of columns to the first row in the CSV file.<br>
This may indicate a problem with the data, e.g. an incorrectly escaped value, or that you are mixing together different tables of information.</p>
              </div>
			  <?php }?>
        </div>
         
    </div>
    </div>
    @endsection
    <!--========================login form END here======================================-->
   
</div>


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


