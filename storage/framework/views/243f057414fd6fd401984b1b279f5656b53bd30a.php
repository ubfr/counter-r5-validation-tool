<!DOCTYPE HTML>
<html>
<head>

<meta charset="utf-8">
<title>Validation - Counter</title>



<?php $__env->startSection("content"); ?>

<!--========================login form start here======================================-->
<div class="container">   
	<?php if(Session::has('userdelmsg')): ?>
		<div class="alert alert-success" style="color:green">
			<?php echo e(Session::get('userdelmsg')); ?>

		</div>
	<?php endif; ?>
	
	
	<div class="container">   
	<?php if(Session::has('userdelmsgwrong')): ?>
		<div class="alert alert-success" style="color:red">
			<?php echo e(Session::get('userdelmsgwrong')); ?>

		</div>
	<?php endif; ?>
	
	<?php if(Session::has('userupdatemsg')): ?>
		<div class="alert alert-success" style="color:green">
			<?php echo e(Session::get('userupdatemsg')); ?>

		</div>
	<?php endif; ?>
	<?php if(Session::has('RegisterMsg')): ?>
		<div class="alert alert-success">
            <?php echo e(Session::get('RegisterMsg')); ?>

        </div>
<?php endif; ?>
	
	<div class="row">
        <div class="col-sm-12">
			<div id="example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
				<div class="row">
					<div class="col-xs-3 col-sm-3 col-md-3 noPadding marTop10 pull-right">
                            <a  data-toggle="modal" data-target="#myModal" class="btn btn-primary btn-block">Add New Admin</a>
                        </div>
					<div class="col-sm-12">
						<table id="example" class="table table-striped table-bordered dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="example_info" style="width: 100%;">
							<thead>
								<tr role="row">
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Name: activate to sort column ascending" style="width: 148px;">Email ID</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">First Name</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 233px;">Display Name</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">Last Name</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 108px;">User Type</th>
									<th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Action</th>
								    <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Delete</th>
								    <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Status</th>
								    <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-label="Start date: activate to sort column ascending" style="width: 99px;">Gender</th>
								    
								</tr>
							
							</thead>
							<tfoot>
								<tr>
									<th rowspan="1" colspan="1">Email ID</th>
									<th rowspan="1" colspan="1">First Name</th>
									<th rowspan="1" colspan="1">Display Name</th>
									<th rowspan="1" colspan="1">Last Name</th>
									<th rowspan="1" colspan="1">User Type</th>
									<th rowspan="1" colspan="1">Action</th>
									<th rowspan="1" colspan="1">Delete</th>
									<th rowspan="1" colspan="1">Status</th>
									<th rowspan="1" colspan="1">Gender</th>
								</tr>
							</tfoot>
							<tbody> 
								<?php 
								//echo'<pre>'; print_r($user_detail);  die();
								foreach($user_detail as $user_details){?>
								<tr role="row" class="even">
									<td class=""><?php echo $user_details->email;?></td>
									<td class=""><?php echo $user_details->first_name;?></td>
									<td class=""><?php echo $user_details->display_name;?></td>
									<td class=""><?php echo $user_details->last_name;?></td>
									<td class=""><?php echo $user_details->utype;?></td>
									<td class="" ><a href='edituser/<?php echo $user_details->id;?>'><i class="fa fa-pencil-square-o" style="font-size: 19px;padding-right: 10px;"></i></a></td>
							        <td class="" data-toggle="modal" data-target="#newModal1">
							        	<a onclick='show_confirm(<?php echo $user_details->id;?>);'>
							        		<i class="fa fa-trash-o trashIcon" style="font-size: 15px;padding-right: 10px;"></i>
							        	</a>
						        	</td>
                                    <td class=""><input type="checkbox" class="activecheckbox" id="checkbox1" name="Active" <?php echo $user_details->status=='1'?'checked':'';?> value="<?php echo $user_details->id;?>"></td>
                                    <td class=""><?php echo $user_details->gender;?></td> 
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
                </div>
    <!-- Modal Admin for registration -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
       
      </div>
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 ">
		<form action="registeradmin" method="post">
		<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
			<h2>Please Add Admin</h2>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
                        <input type="text" name="first_name" id="first_name" value="<?php echo e(old('first_name')); ?>" class="form-control input-lg" placeholder="First Name" tabindex="1">
						<span style="color:#ff0000"><?php echo e($errors->registeradmin->first('first_name')); ?></span>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="text" name="last_name" id="last_name"  value="<?php echo e(old('last_name')); ?>" class="form-control input-lg" placeholder="Last Name" tabindex="2">
						<span style="color:#ff0000"><?php echo e($errors->registeradmin->first('last_name')); ?></span>
					</div>
				</div>
			</div>
			<div class="form-group">
				<input type="text" name="display_name" id="display_name"  value="<?php echo e(old('display_name')); ?>" class="form-control input-lg" placeholder="Display Name" tabindex="3">
				<span style="color:#ff0000"><?php echo e($errors->registeradmin->first('display_name')); ?></span>
			</div>
			<div class="form-group">
				<input type="email" name="email" id="email"  value="<?php echo e(old('email')); ?>" class="form-control input-lg" placeholder="Email Address" tabindex="4">
				<span style="color:#ff0000"><?php echo e($errors->registeradmin->first('email')); ?></span>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="5">
						<span style="color:#ff0000"><?php echo e($errors->registeradmin->first('password')); ?></span>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="password" name="password_confirmation" id="password_confirmation" class="form-control input-lg" placeholder="Confirm Password" tabindex="6">
						<span style="color:#ff0000"><?php echo e($errors->registeradmin->first('password_confirmation')); ?></span>
					</div>
				</div>
			</div>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-md-6"><input type="submit" value="Register Admin" class="btn btn-primary btn-block btn-lg" tabindex="7"></div>
				<!--<div class="col-xs-12 col-md-6"><a href="#" class="btn btn-success btn-block btn-lg">Sign In</a></div>-->
			</div>
		</form>
	</div>
</div>
      </div>
      
    </div>

  </div>
</div>
</div>






    <!-- Modal For deleting the user -->
<div id="newModal1" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
       
      </div>
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 ">
		<form id="delet_form" action="delete_user" method="post">
		<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
			<h2>Please Enter the Password</h2>
			<hr class="colorgraph">
			<div class="row"> 
				<div class="col-xs-12 col-sm-6 col-md-6">
				
					<div class="form-group">
						<input type="password" name="pass" id="pass" class="form-control input-lg" placeholder="Password" tabindex="5" required>
						<input type="hidden" id="deleteuseriddiv" name="deleteuseriddiv">
						<span style="color:#ff0000"><?php echo e($errors->delete_user->first('password')); ?></span>
						
						<hr class="colorgraph">
			            <div class="row">
				        <div class="col-xs-12 col-md-6"><input type="submit" value="Delete User" class="btn btn-primary btn-block btn-lg" tabindex="7"></div>
						
					</div>
				</div>
             </div>
             </div>
            </form>
           </div>
          </div>
        </div>
     </div>
  </div>
  </div>
 </head>
</html>

  
  




    <!--========================login form END here======================================-->
<?php $__env->stopSection(); ?>
<!---=======================javascripts comes in bottom============================-->

<?php $__env->startSection("additionaljs"); ?>
<!-- Latest compiled and minified JavaScript -->

<script src="<?php echo e(URL::asset('assets/js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/bootstrap-datepicker.min.js')); ?>"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js">
	</script>
	
	
	<script type="text/javascript">
<?php if(count($errors->registeradmin) > 0): ?>
    $('#myModal').modal('show');
<?php endif; ?>
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

<?php if(count($errors->welcome)>0): ?> 
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
<?php endif; ?>
 <?php if(Session::has('error')): ?>
		<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
	<?php endif; ?>
 
 
 <?php $__env->stopSection(); ?>
</body>
</html>

<?php echo $__env->make('layout.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>