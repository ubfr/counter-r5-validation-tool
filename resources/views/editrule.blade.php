<div>
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 "><form id='editruleform' action="edit_update/<?php echo $newarr["id"]?>" method="post" >
	<!-- onsubmit="return checkAddForm(this);" -->
		<input type="hidden" name="_token" value=<?php echo csrf_token();?>>
		<input type="hidden" name="report_no" id="report_no" value=<?php echo $newarr["report_no"];?>>
		<input type="hidden" name="curr_row_id" id="curr_row_id" value=<?php echo $newarr["id"];?>>
			<h2>Edit <small>Rule Management</small></h2>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<label>Column Name</label>
					<div class="form-group">
                        <input type="text" name="col_name" id="col_name" value="<?php echo $newarr["colname"]?>" class="form-control input-lg" placeholder="Column Name" tabindex="1" >
						
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<label>Row No</label>
					<div class="form-group">
						<input type="text" name="row_no" id="row_no"  value="<?php echo $newarr["rowno"]?>" class="form-control input-lg" placeholder="Row Name" tabindex="2" >
					</div>
				</div>
			</div>
			<div class="form-group" tabindex="3">
				<label>Rule Type</label>
				<select class="form-control" name="rule_type"  id="rule_type">
					<option value="">--Select Value--</option>
					<option value="integer" <?php if($newarr["ruletype"]=="integer")echo "Selected";?>>Integer</option>
					<option value="text" <?php if($newarr["ruletype"]=="text")echo "Selected";?>>text</option>
					<option value="date_format" <?php if($newarr["ruletype"]=="date_format")echo "Selected";?>>date_format</option>
					<option value="issn" <?php if($newarr["ruletype"]=="issn")echo "Selected";?>>issn</option>
					<option value="sum" <?php if($newarr["ruletype"]=="sum")echo "Selected";?>>sum</option>
					<option value="row sum" <?php if($newarr["ruletype"]=="row sum")echo "Selected";?>>row sum</option>
					<option value="isbn" <?php if($newarr["ruletype"]=="isbn")echo "Selected";?>>isbn</option>
					<option value="doi" <?php if($newarr["ruletype"]=="doi")echo "Selected";?>>doi</option>
					<option value="uri" <?php if($newarr["ruletype"]=="uri")echo "Selected";?>>uri</option>
					<option value="sumif" <?php if($newarr["ruletype"]=="sumif")echo "Selected";?>>sumif</option>
					<option value="sum-row-column" <?php if($newarr["ruletype"]=="sum-row-column")echo "Selected";?>>sum-row-column</option>
					<option value="string" <?php if($newarr["ruletype"]=="string")echo "Selected";?>>string</option>
					<option value="stringcheck" <?php if($newarr["ruletype"]=="stringcheck")echo "Selected";?>>stringcheck</option>
				</select>
			</div>
			<div class="form-group">
				<label>Column Value</label>
				<input type="text" name="col_value" id="col_value"  value="<?php echo $newarr["value"]?>" class="form-control input-lg" placeholder="Column Value" tabindex="4">
			</div>
			<div id="show_col_value" style="color:#ff0000"></div>
			<div class="form-group" tabindex="5">
				<label>Required</label>
				<select class="form-control" name="col_req" id="col_req" required="required">
				   <option value="">--Select Value--</option>
				   <option value="1" <?php if($newarr["required"]==1)echo "Selected";?>>YES</option>
				   <option value="0" <?php if($newarr["required"]==0)echo "Selected";?>>NO</option>
				</select>
			</div>
			<div id="show_col_req" style="color:#ff0000"></div>
			<div class="form-group" tabindex="6">
				<label>Range</label>
				<select class="form-control" name="col_range" id="col_range" required="required">
				   <option value="">--Select Value--</option>
				   <option value="1" <?php if($newarr["is_range"]==1)echo "Selected";?>>YES</option>
				   <option value="0" <?php if($newarr["is_range"]==0)echo "Selected";?>>NO</option>
				</select>
			</div>
			<div id="show_col_range" style="color:#ff0000"></div>
			<div class="form-group">
				<label>Start Column</label>
				<input type="text" name="start_column" id="start_column"  value="<?php echo $newarr["start_column"]?>" class="form-control input-lg" placeholder="Start Column" tabindex="7">
			</div>
			<div id="show_start_column" style="color:#ff0000"></div>
			<div class="form-group">
				<label>Match Column</label>
				<input type="text" name="match_column" id="match_column"  value="<?php echo $newarr["match_column"]?>" class="form-control input-lg" placeholder="Match Column" tabindex="8">
			</div>
			<div id="show_match_column" style="color:#ff0000"></div>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-md-6">
					<!-- <input type="submit" value="Update Column" class="btn btn-primary btn-block btn-lg" tabindex="9">   -->
					<input type="button" id='submitbutton' value="Update Column" class="btn btn-primary btn-block btn-lg" tabindex="9">
				</div>
				<!--<div class="col-xs-12 col-md-6"><a href="#" class="btn btn-success btn-block btn-lg">Sign In</a></div>-->
			</div>
		</form></div>
</div>
      </div>
      
    </div>

  </div>
  <script type="text/javascript">
function checkAddForm(form)
    {
       
		var flageerror = 0;
		var range=$('#col_range').val();
		//alert(range);
		if(range==1)
		{
			var rowno=$('#row_no').val();
			var rep_id=$('#report_no').val();
			var colname =  $('#col_name').val();
			var datavalue = $('#editruleform').serialize() ;
			
			$.ajax({
			   type: 'POST',
			   url: 'lastcolajax',
			   data: datavalue,
			   dataType: 'text',
			   success: function(data) {
				if(data!=colname)
				 {
					flageerror = 1;
					$('#show_col_range').html('Can not Make this Column as Range As This is Not the Last Column Of This Row');
				 }	
				}
			}).then(function(response) {
					if(flageerror==1){
					//event.preventDefault();
					return false;
					}
					else{
					$('#editruleform').submit();
					return false;
					}
				}); 
			
		}
		
    }
$(document).ready(function(){
	$('#submitbutton').click(function(){
		var flageerror = 0;
		var range=$('#col_range').val();
		var data1;
		var rowno=$('#row_no').val();
		var rep_id=$('#report_no').val();
		var colname =  $('#col_name').val();
		var datavalue = $('#editruleform').serialize() ;
		$.ajax({
			type: 'POST',
			url: 'lastcolajax',
			data: datavalue,
			dataType: 'text',
			success: function(data1){
				if(data1!=colname && range==1){
					$('#show_col_range').html('Can not Make this Column as Range As This is Not the Last Column Of This Row');
				}
				else{
					$('#editruleform').submit();
				}
			}
		}); 
	});
	
$('#rule_type').change(function(){
	if(this.value=='' || this.value=='integer' || this.value=='issn' || this.value=='sum'  || this.value=='isbn')
		{
			$('#col_value').val('');
			$('#col_value').attr('readonly',true);
			$('#start_column').val('');
			$('#start_column').attr('readonly',true);
			$('#match_column').val('');
			$('#match_column').attr('readonly',true);
		}
		else if(this.value=='text')
		{
			$('#col_value').attr('readonly',false);
			$('#start_column').attr('readonly',true);
			$('#match_column').attr('readonly',true);
			
		}
		else if(this.value=='row sum')
		{
			$('#col_value').val('');
			$('#col_value').attr('readonly',true);
			$('#start_column').attr('readonly',false);
			$('#match_column').val('');
			$('#match_column').attr('readonly',true);
		}
		else if(this.value=='sumif')
		{
			$('#col_value').attr('readonly',false);
			$('#start_column').attr('readonly',false);
			$('#match_column').val('');
			$('#match_column').attr('readonly',true);
		}
		else if(this.value=='sum-row-column')
		{
			$('#col_value').attr('readonly',false);
			$('#col_range').val('0');
			$('#start_column').attr('readonly',false);
			$('#match_column').attr('readonly',false);
		}
		else if(this.value=='stringcheck')
		{
			$('#col_value').attr('readonly',false);
			$('#start_column').attr('readonly',true);
			$('#match_column').attr('readonly',true);
			
		}
		else{
			$('#col_value').attr('readonly',false);
			$('#start_column').attr('readonly',false);
			$('#match_column').attr('readonly',false);
		}
			
		//alert(this.value);
	});
	$('#col_range').change(function(){
		var ruletype=$('#rule_type').val();
		if(ruletype=='sum-row-column')
		{
			$('#col_range').val('0');
		}
		else{
			
		}
	});
});
</script>
</div>
