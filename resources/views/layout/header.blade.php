
		<meta content="True" name="HandheldFriendly" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=0"/>
		<meta name="viewport" content="width=device-width" />

		<meta name="copyright" content="Copyright 2016 Project Counter. All rights reserved." />
		<meta name="resource-type" content="DOCUMENT" />
		<meta name="distribution" content="GLOBAL" />
		<meta name="rating" content="GENERAL" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
        
<link rel="stylesheet" href="{{URL::asset('assets/css/bootstrap.min.css')}}"  />
<link rel="stylesheet" href="{{URL::asset('assets/css/bootstrap-theme.min.css')}}" />
<link rel="stylesheet" href="{{URL::asset('assets/css/style.css')}}" />
<link rel="stylesheet" href="{{URL::asset('assets/css/font-awesome.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('assets/css/bootstrap-datepicker.min.css')}}">
<link rel="stylesheet" href="{{URL::asset('assets/css/responsive.css')}}" />

</head>
<body>




<div class="preloader-wrap">
  <div class="percentage" id="precent"></div>
  <div class="loader">
    <div class="trackbar">
      <div class="loadbar"></div>
    </div>
    <div class="glow"></div>
  </div>
</div>

<div class="container">
    <header>
    	<div class="row">
    	<div class="col-md-3">
        	<h1 class="logoBlock"><a href="https://www.projectcounter.org/"><img title="Project Counter" alt="Project Counter" src="https://www.projectcounter.org/wp-content/themes/project-counter-2016/images/logo.png"></a></h1>
        </div>
        
        <div class="col-md-3 pull-right pt-3" style="padding-top:25px">
       <div class="row">
        <div class="col-md-6" >
        <a href="{{url('/')}}"> <img title="Project Counter" alt="Project Counter" src="{{ url('/assets/image/OCLC_Logo.jpg') }}" style="width: 100%;"></a>
        </div>
        
       <div class="col-md-6" style="padding-top:7px">
     	<a href="{{url('/')}}"><img title="Project Counter" alt="Project Counter" src="{{ url('/assets/image/rel_logo.png') }}" style=" width: 100%;"></a>
        </div> 
  
        
        </div>
        </div>
        </div>
       
		<?php if(isset($utype)){?>
	
    		<?php if($utype=='user'){?>
        		<nav class="navbar navbar-inverse">
        			<div class="container-fluid">
        				<ul class="nav navbar-nav">
        				    <li><a href="{{url('filelist')}}">File Validation</a></li>
        				  <?php if(Config::get('c5tools.enableConsortiumTool')) {?>
        				    <li><a href="{{url('consortium')}}">Consortium Tool</a></li>
        				  <?php }?>
        				     <li><a href="{{url('showreport')}}">User's Reports</a></li>
        				    </ul>
        				<ul class="nav navbar-nav pull-right">
        			     <li><a href="{{url('useredit')}}" ><i class="fa fa-user" aria-hidden="true"></i> <?php  echo "Welcome ".$userDisplayName?></a></li>
        			     <li><a href="#">|</a></li>
        			      <li> <a href="{{url('logout')}}" > <i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a> </li>
        			    </ul>
        			</div>
        		</nav>
    		<?php }?>
    
    
       	 	<?php if($utype=='admin'){?>
        		<nav class="navbar navbar-inverse">
        			<div class="container-fluid">
        				<ul class="nav navbar-nav">
        				    <li><a href="{{url('filelist')}}">File Validation</a></li>
        					<li><a href="{{url('userlist')}}">User Management</a></li>
        				      <!-- disabled, has no effect on ubfr/c5tools
        					<li><a href="{{url('reporthistory')}}">Report Management</a></li>
        					<li><a href="{{url('rulemanagement')}}">Rule Management</a></li>
        				      -->
        					<li><a href="{{url('uploadedreports')}}">Uploaded Reports</a></li>
        				  <?php if(Config::get('c5tools.enableConsortiumTool')) {?>
        					<li><a href="{{url('consortium')}}">Consortium Tool</a></li>
        				  <?php }?>
        				</ul>
        				<ul class="nav navbar-nav pull-right" style="margin-top:15px;">
        			     <li><i class="fa fa-user" aria-hidden="true"></i> <?php  echo "Welcome ".$userDisplayName;?></li>
        			     <li style="margin:0 10px">|</li>
        			      <li> <a href="{{url('logout')}}" style="margin:0;padding:0;"> <i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a> </li>
        			    </ul>
        			</div>
        		</nav>
        	<?php }?>
        	</header>
    	</div>
        <?php } 
        else {?>
       </header>
        </div>
        
    	
        <?php }?>
        <div class="container">
        <!-- <small>Note: If the SUSHI Service you are testing is restricted by IP address, you will need to make sure that IP 82.145.59.15 is authorized for access to use this utility.</small>  -->
		        
	
		
	 

		
		
	
	
