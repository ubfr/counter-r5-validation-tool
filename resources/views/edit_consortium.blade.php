<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>

@extends("layout.master")

@section('content')

<!--========================login form start here======================================-->
<div class="main-content">
	@if (Session::has('error'))
		<div class="alert alert-success" style="color:red">
			{{ Session::get('error') }}
		</div>
	@endif
	
	<form action="{{url('/update_consortium')}}" method="post" name="edit_consortium">
	<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
	<div class="form-group row">
		<label for="example-text-input" class="col-xs-2 col-form-label">Id</label>
		<div class="col-xs-10">
			<input class="form-control" name="Id" type="text" value="<?php echo $file_detail[0]->id;?>" id="example-text-input" placeholder="Id" readonly>
		</div>
	</div>
	<div class="form-group row" >
		<label for="example-text-input" class="col-xs-2 col-form-label">Configuration Name</label>
		<div class="col-xs-10">
			<input class="form-control" type="text" name="configuration_name" value="<?php echo $file_detail[0]->configuration_name;?>" id="example-text-input" placeholder="configuration_name">
		</div>
	</div>
	
	<div class="form-group row" >
		<label for="example-text-input" class="col-xs-2 col-form-label">Remarks</label>
		<div class="col-xs-10">
			<input class="form-control" type="text" name="remarks" value="<?php echo $file_detail[0]->remarks;?>" id="example-text-input" placeholder="remarks">
		</div>
	</div>
	
	<div class="form-group row">
		<div class="offset-sm-2 col-sm-10" align='center'>
			<a href="{{url('consortium')}}" class="btn btn-primary btnBlockxs ">Back</a>
			<button type="submit" class="btn btn-primary">Save</button>
		</div>
    </div>
	</form>
	
</div>	
@endsection
</head>
</html>
																																