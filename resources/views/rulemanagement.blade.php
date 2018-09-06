
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Validation - Counter</title>

@extends("layout.master")

@section('content')

	@if (Session::has('colupdatemsg'))
		<div class="alert alert-success" style="color:green">
			{{ Session::get('colupdatemsg') }}
		</div>
	@endif
	<?php 
	//makeing parent and child relationship
/*	$allgroup = array();
	$i=0;
	foreach($reportsname as $reportDetails){ 
	    $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['id']=$reportDetails->id;
	    $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['report_name']=$reportDetails->report_name;
	    $allgroup[$parentInfo[$reportDetails->parent_id]][$i]['report_code']=$reportDetails->report_code;
	    $i++;
	}*/
	//echo "<pre>sdfdf";print_r($allgroup);die;
	?>
	<div class="main-content">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
				<div>
					<span style="float:left;margin-right: 10px;font-weight: bold;">Select Report :</span>
					<span style="float:left;"> 
						<form name="test">
							<select name="reportname"  id="reportname" onchange="setReportName(this.value)" style="padding: 4px;">
								<option value='0'>---Please Select---</option>
							<?php foreach($reportsname as $reportDetails){ ?>
							    <option value="<?php echo $reportDetails->id;?>"><?php echo $reportDetails->report_name;?>&nbsp;(<?php echo $reportDetails['report_code'];?>)</option>
								<?php }?>
								<?php 
								/*foreach($allgroup as $parentName=>$reportsname){
								    ?>
								    <option label="<?php echo $parentName;?>">
								    <?php 
								    foreach($reportsname as $reportDetails){ 
								    //echo "<pre>";print_r($reportDetails);die;
								    ?>
									
							    <option value="<?php echo $reportDetails['id'];?>"><?php echo $reportDetails['report_name'];?>&nbsp;(<?php echo $reportDetails['report_code'];?>)</option>
								<?php }
								?></option><?php 
								}*/?>
							</select>
						</form>
					</span>	
					<div style="clear:both; height:10px;"></div>
					<div class="loadview">
					</div>
				</div>
			</div>
		</div>
	</div>
	
	
	    <!-- Modal for registration -->
<div id="myModal" class="modal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
       
      </div>
      <div class="modal-body">
       		 <div class="row">
    <div class="col-xs-12 col-sm-121 col-md-12 ">
		<form action="UpdateColumn" method="post" onsubmit="return checkForm(this);">
		<input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
		<input type="text" name="column_id" id="column_id" value="">
			<hr class="colorgraph">
			<div class="row">
				<div class="col-xs-12 col-md-6"><input type="submit" value="Edit Save" class="btn btn-primary btn-block btn-lg" tabindex="7"></div>
				<!--<div class="col-xs-12 col-md-6"><a href="#" class="btn btn-success btn-block btn-lg">Sign In</a></div>-->
			</div>
		</form>
	</div>
</div>
      </div>
      
    </div>

  </div>
</div>
<?php
$currentReport  = Session::get('report_no'); 
if(isset($currentReport)){
	$currentReport=$currentReport;
}else{
	$currentReport=0;
}
?>
@endsection

@section("additionaljs")
<!-- <script src="{{URL::asset('assets/js/jquery.min.js')}}"></script> -->
<!-- <script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script> -->
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript" language="javascript" src="{{URL::asset('assets/js/itf_popup.min.js')}}"></script>
<script type="text/javascript" language="javascript" src="{{URL::asset('assets/js/itf_popup.js')}}"></script>

 <link rel="stylesheet" href="{{URL::asset('assets/css/itf_popup.css')}}">
<script type="text/javascript" class="init">
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
function setReportName(thisvalue){
	//alert(thisvalue)
	if(thisvalue !="0"){
		 var test=[];
		$.ajax({
			url: 'ajaxCall/'+thisvalue,
			data: {},
			method: "POST",
			dataType: "json",
		error: function() {
			//$('#info').html('<p>An error has occurred</p>');
		},
		
		success: function(response) {
			
			var arr = Object.keys(response).map(function(k) { return response[k] });
			test='';
			
			test="<ul class='nav nav-stacked' id='#accordion1'><div style='text-align: right;font-size: 17px;font-weight: bold;margin-right: 10px;'><a onclick='show_confirm_addrow();'>Add Row</a></div>";
			var row=1;
			
		  for(var i=0;i<arr.length;i++){
			 test+="<li class='panel' ><div style='position: absolute;right: 36px;top: 30px;'><a  style='color:#fff;'class='travel-pop' href='createvalue/"+row+"/"+arr[i][0][9]+"'>Add Column</a></div><h3 style='background:#a1b9c1;font-size:20px;color:#fff;font-weight:bold;padding:10px;'>Row:"+row+" </h3>";
			test+="<ul id='firstLink"+i+"' ><li style='list-style-type: none;'><div id='example_wrapper' class='dataTables_wrapper form-inline dt-bootstrap'><div class='row'><div class='col-sm-12' style='margin-top: 5px;'><table id='example' class='table table-striped table-bordered dataTable' cellspacing='0' width='100%' role='grid' aria-describedby='example_info' style='width: 100%;'><thead><tr role='row'><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>ColName</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>RowNo</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>RuleType</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>Value</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>Required</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>IsRange</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>StartColumn</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>MatchColumn</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>Action</th></tr></thead><tfoot><tr><th rowspan='1' colspan='1'>ColName</th><th rowspan='1' colspan='1'>RowNo</th><th rowspan='1' colspan='1'>RuleType</th><th rowspan='1' colspan='1'>Value</th><th rowspan='1' colspan='1'>Required</th><th rowspan='1' colspan='1'>IsRange</th><th rowspan='1' colspan='1'>StartColumn</th><th rowspan='1' colspan='1'>MatchColumn</th><th rowspan='1' colspan='1'>Action</th></tr></tfoot><tbody>";
				for(var l=0;l<arr[i].length;l++){
					test+="<tr role='row' class='even'>";
				for(var j=0;j<arr[i][l].length-1;j++){
					test+="<td class=''>"+arr[i][l][j]+"</td>";
				}
				test+="</tr>";
				}
			 
			  test+="</tbody></table></div></div></div><div class='row'><div class='col-sm-7'><div class='dataTables_paginate paging_simple_numbers' id='example_paginate'></div></div></div></li></ul></li>";
			  row++;
		  }
			test+="</ul>";
		   $(".loadview").html(test);
		}
		});
	}
	else{
		test='';
		 $(".loadview").html(test);
	}
}
$(document).ready(function() {
	
	
	
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
    /*$("#reportname1").change(function(e){
		if($(this).val() !="0"){
             var test=[];
			 $.post('ajaxCall/'+$(this).val(), function(response){
			    var obj = JSON.parse(response);
				var arr = Object.keys(obj).map(function(k) { return obj[k] });
				test='';
				test="<ul class='nav nav-stacked' id='#accordion1'>";
				var row=1;
              for(var i=0;i<arr.length;i++){
				 test+="<li class='panel' ><h3 style='background:#a1b9c1;font-size:20px;color:#fff;font-weight:bold;padding:10px;'>Click Row:"+row+" </h3>";
				test+="<ul id='firstLink"+i+"' ><li style='list-style-type: none;'><div id='example_wrapper' class='dataTables_wrapper form-inline dt-bootstrap'><div class='row'><div class='col-sm-12' style='margin-top: 5px;'><table id='example' class='table table-striped table-bordered dataTable' cellspacing='0' width='100%' role='grid' aria-describedby='example_info' style='width: 100%;'><thead><tr role='row'><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>ColName</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>RowNo</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>RuleType</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>Value</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>Required</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>IsRange</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>StartColumn</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>MatchColumn</th><th class='sorting' tabindex='0' aria-controls='example' rowspan='1' colspan='1' aria-label='Name: activate to sort column ascending' style='width: 148px;'>Action</th></tr></thead><tfoot><tr><th rowspan='1' colspan='1'>ColName</th><th rowspan='1' colspan='1'>RowNo</th><th rowspan='1' colspan='1'>RuleType</th><th rowspan='1' colspan='1'>Value</th><th rowspan='1' colspan='1'>Required</th><th rowspan='1' colspan='1'>IsRange</th><th rowspan='1' colspan='1'>StartColumn</th><th rowspan='1' colspan='1'>MatchColumn</th><th rowspan='1' colspan='1'>Action</th></tr></tfoot><tbody>";
					for(var l=0;l<arr[i].length;l++){
						test+="<tr role='row' class='even'>";
					for(var j=0;j<arr[i][l].length;j++){
						test+="<td class=''>"+arr[i][l][j]+"</td>";
						
						
					}
					test+="</tr>";
					}
				 
				  test+="</tbody></table></div></div></div><div class='row'><div class='col-sm-7'><div class='dataTables_paginate paging_simple_numbers' id='example_paginate'></div></div></div></li></ul></li>";
				  row++;
			  }
				test+="</ul>";
			   $(".loadview").html(test);
            });
			
		}
		else{
			test='';
			 $(".loadview").html(test);
		}
    });*/
	/* $("#show a").click(function(){
		$("#myModal").css("display","block");
	}); */
	$(".close").click(function(){
		
		$("#myModal").css("display","none");
	});
	
} );
 function getvalue(id){
	
	document.getElementById("myModal").style.display="block";
	//document.getElementById("column_id").value=id;
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
		var res = $.parseJSON(this.responseText);
		alert(res);
		//alert(typeof(this.responseText));
		//var datanew=this.responseText;
		//var obj = JSON.parse(datanew);
	   //var arr = Object.keys(obj).map(function(k) { return obj[k] });
	 //  alert((arr.length));
     document.getElementById("column_id").value = this.responseText['id'];
    }
  };
  xhttp.open("GET", "loadvalue/"+id, true);
  xhttp.send();
	
} 



/* $('.check').change(function(){
  var data= $(this).val();
  alert(data);            
}); */

$('#reportname')
    .val('<?php echo $currentReport;?>')
	//.val('2')
    .trigger('change');
	 function show_confirm(id,rowid){
	  if(confirm('Are you sure want to delete?'))
	  {
	   window.location.href = "deleterow/"+id+"/"+rowid;
	  }
 }
  function show_confirm_addrow(){
	    if(confirm('Are you sure want to add row?'))
	   {
		 var reportno = document.getElementById("reportname").value; 
		 
	     window.location.href = "addrow/"+reportno;
	   }
   }
</script>

@endsection
</head>
</html>

