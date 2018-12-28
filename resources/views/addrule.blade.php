
<div>
<script>
$(document).ready(function(){
	var selectedvalue = $('#reportname').val();
	//alert(selectedvalue);
	$('#report_no').val(selectedvalue);
});
</script>
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 ">
	
	<form action="add_update/<?php echo $newarr["id"]?>" method="post">
		<input type="hidden" name="_token" value="<?php echo csrf_token();?>">
		<input type="hidden" name="row_no" value="<?php echo $newarr["id"]; ?>">
		<input type="hidden" name="report_no" value="" id="report_no">
		<input type="hidden" name="previous_col" value="<?php echo $newarr["col_name"]?>" id="previous_col">		
			<h2>Add <small>Rule Management</small></h2>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<label>Column Name</label>
                        <input type="text" name="col_name" id="col_name" value="<?php echo ++$newarr["col_name"]?>" class="form-control input-lg" placeholder="Column Name" tabindex="1" >
						
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<label>Row No</label>
						<input type="text" name="row_no" id="row_no"  value="<?php echo $newarr["id"]?>" class="form-control input-lg" placeholder="Row Name" tabindex="2" >
					</div>
				</div>
			</div>
			<div class="form-group" tabindex="3">
				<label>Rule Type</label>
				<select class="form-control" name="rule_type"  id="rule_type">
					<option value="">--Select Value--</option>
					<option value="integer">Integer</option>
					<option value="text" >text</option>
					<option value="date_format" >date_format</option>
					<option value="issn" >issn</option>
					<option value="sum" >sum</option>
					<option value="row sum" >row sum</option>
					<option value="doi" >doi</option>
					<option value="uri" >uri</option>
					<option value="isbn" >isbn</option>
					<option value="sumif" >sumif</option>
					<option value="sum-row-column" >sum-row-column</option>
					<option value="string" >string</option>
					<option value="stringcheck">stringcheck</option>
				</select>
			</div>
			<div class="form-group">
				<label>Column Value</label>
				<input type="text" name="col_value" id="col_value"  value="" class="form-control input-lg" placeholder="Column Value" tabindex="4">
			</div>
			<div id="show_col_value" style="color:#ff0000"></div>
			<div class="form-group" tabindex="5">
				<label>Required</label>
				<select class="form-control" name="col_req" id="col_req" required="required">
				   <option value="">--Select Value--</option>
				   <option value="1">YES</option>
				   <option value="0">NO</option>
				</select>
			</div>
			<div id="show_col_req" style="color:#ff0000"></div>
			<div class="form-group" tabindex="6">
				<label>Range</label>
				<select class="form-control" name="col_range" id="col_range" required="required">
				   <option value="">--Select Value--</option>
				   <option value="1" >YES</option>
				   <option value="0">NO</option>
				</select>
			</div>
			<div id="show_col_range" style="color:#ff0000"></div>
			<div class="form-group">
				<label>Start Column</label>
				<input type="text" name="start_column" id="start_column"  value="" class="form-control input-lg" placeholder="Start Column" tabindex="7">
			</div>
			<div id="show_start_column" style="color:#ff0000"></div>
			<div class="form-group">
				<label>Match Column</label>
				<input type="text" name="match_column" id="match_column"  value="" class="form-control input-lg" placeholder="Match Column" tabindex="8">
			</div>
			<div id="show_match_column" style="color:#ff0000"></div>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-md-6"><input type="submit" value="Add Column" class="btn btn-primary btn-block btn-lg" tabindex="9"></div>
				
			</div>
		</form></div>
</div>
      </div>
      
    </div>

  </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
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