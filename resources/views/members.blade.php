
<!DOCTYPE HTML>
<html>
<head>

<meta charset="utf-8">
<title>Validation - Counter</title>

@extends('layout.master')

@section("content")

<!--========================login form start here======================================-->


@if (Session::has('memberdeletemsg'))
<div class="alert alert-success" style="color: green">{{
	Session::get('memberdeletemsg') }}</div>
@endif


@if (Session::has('memberfreshmsg'))
<div class="alert alert-success" style="color: green">{{
	Session::get('memberfreshmsg') }}</div>
@endif
<div class="">   
	
	<div class="row">
        <div class="col-sm-12">
			<div id="example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
				<div class="row">
					
					
					<div class="col-sm-12"> 
				<div class="col-md-12">
				<h3><button><a href="{{url('consortium')}}">Back </a></button>&nbsp;&nbsp; Member Details : {{$SingleProvder->provider_name}}  ||  {{$SingleProvder->remarks}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button><a href="{{url('refresh_members')}}/<?php echo $SingleProvder['id'];?>">Refresh Member </a></button></h3>
				
				<hr class="colorgraph"></div>
					
					
						<table id="example" class="table table-striped table-bordered dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="example_info" style="width: 100%;">
							<thead>
								<tr role="row">
						            <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;"> Name</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">Customer Id</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">Requestor Id</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;"> Notes</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Institution Id Type</th>
								    <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Institution Id Value</th>
								    <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Delete</th>
								    
								</tr>
							
							</thead>
							<tbody> 
								<?php 
								
								foreach($allmember as $member){?>
								<tr role="row" class="even">
									<td class=""><?php echo $member['name'];?></td>
									<td class=""><?php echo $member['customer_id'];?></td>
									<td class=""><?php echo $member['requestor_id'];?></td>
									<td class=""><?php echo $member['notes'];?></td>
									<td class=""><?php echo $member['institution_id_type'];?></td>
									<td class=""><?php echo $member['institution_id_value'];?></td>
							        <td class="" >
							        	<a
											onclick="return confirm('Are you sure for delete this member?')"
											href="/delete_members/<?php echo $member['id']."/".$SingleProvder->id; ?>"><i
												class="fa fa-trash" aria-hidden="true"></i></a>
						        	</td>
                                    
								</tr>
		
								<?php }?>
							</tbody>
						</table> 
					</div>
				</div>
			</div>
        </div>
    </div>
  

    
	 <div class="row">
                  
                  <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers" id="example_paginate">
                      
                    </div>
              </div>
 
 </head>
</html>

  
  




    <!--========================login form END here======================================-->
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
	
	
	<script type="text/javascript">
@if (count($errors->registeradmin) > 0)
    $('#myModal').modal('show');
@endif
</script>

	<script type="text/javascript" class="init">
     $(document).ready(function() {


	//click event of check box
    $('.activecheckbox').change(function() {
    	var id = $(this).val();
        if($(this).is(":checked")) {
            if(confirm("Are you sure to activate this user?")){

                
                window.location.href = "/user_status/"+id+"/1";
            }else{
            	$(this).attr('checked', false);
            }
            
        }else{
        	if(confirm("Are you sure to de-activate this user?")){
        		window.location.href = "/user_status/"+id+"/0";
            }else{
                //alert("asdf");
            	$(this).attr('checked', true);
            }
        	$(this).attr('checked', true);
        }
       
        
    });


	
	$('#example').DataTable( {
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
 function show_confirm(id){

	 // if(confirm('Do you really want to delete selected user'))
	  {
	   //window.location.href = "deleteuser/"+id;
		   $('#deleteuseriddiv').val(id);
	  }
	 }

 
// Delete user detail  



 //$('#delet_form').on('submit',function(){
	 //var user_data= { 'user_id' : $("#deleteuseriddiv").val()};
	//alert(user_id); 
	/* $.ajax({
         type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
         url         : '/deleteuser/, // the url where we want to POST
         data        : formData, // our data object
         dataType    : 'json', // what type of data do we expect back from the server
         encode          : true
     }).done(function(data) {

                // log data to the console so we can see
                console.log(data); 

                // here we will handle errors and validation messages*/
           
         

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
