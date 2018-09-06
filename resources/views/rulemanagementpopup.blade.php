<div>
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 "><form action="register" method="post" onsubmit="return checkForm(this);">
		<input type="hidden" name="_token" value="'.csrf_token().'">
			<h2>Edit <small>Rule Management</small></h2>
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
                        <input type="text" name="col_name" id="col_name" value="" class="form-control input-lg" placeholder="Column Name" tabindex="1" disabled>
						
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="form-group">
						<input type="text" name="row_no" id="row_no"  value="" class="form-control input-lg" placeholder="Row Name" tabindex="2" disabled>
					</div>
				</div>
			</div>
			
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-md-6"><input type="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="7"></div>
				<!--<div class="col-xs-12 col-md-6"><a href="#" class="btn btn-success btn-block btn-lg">Sign In</a></div>-->
			</div>
		</form></div>
</div>
      </div>
      
    </div>

  </div>
</div>
