
		<meta content="True" name="HandheldFriendly" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=0"/>
		<meta name="viewport" content="width=device-width" />

		<meta name="copyright" content="Copyright 2016 Project Counter. All rights reserved." />
		<meta name="resource-type" content="DOCUMENT" />
		<meta name="distribution" content="GLOBAL" />
		<meta name="rating" content="GENERAL" />
		<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        
<link rel="stylesheet" href="<?php echo e(URL::asset('assets/css/bootstrap.min.css')); ?>"  />
<link rel="stylesheet" href="<?php echo e(URL::asset('assets/css/bootstrap-theme.min.css')); ?>" />
<link rel="stylesheet" href="<?php echo e(URL::asset('assets/css/style.css')); ?>" />
<link rel="stylesheet" href="<?php echo e(URL::asset('assets/css/font-awesome.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(URL::asset('assets/css/bootstrap-datepicker.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(URL::asset('assets/css/responsive.css')); ?>" />

</head>
<body>
<div class="container">
    <header>
    	<div class="row">
    	<div class="col-md-3">
        	<h1 class="logoBlock"><a href="<?php echo e(url('/')); ?>"><img title="Project Counter" alt="Project Counter" src="<?php echo e(asset('assets/image/logo.png')); ?>"></a></h1>
        </div>
        <div class="col-md-3 pull-right">
        	<h1 class="logoBlock text-right"><a href="<?php echo e(url('/')); ?>"><img title="Project Counter" alt="Project Counter" src="<?php echo e(asset('assets/image/logo2.png')); ?>"></a></h1>
        	 
        </div>
        </div>
       
		<?php if(isset($utype)){?>
	
    		<?php if($utype=='user'){?>
        		<nav class="navbar navbar-inverse">
        			<div class="container-fluid">
        				
        				<ul class="nav navbar-nav pull-right" style="margin-top:15px;">
        			     <li><i class="fa fa-user" aria-hidden="true"></i> <?php  echo "Welcome ".$userDisplayName;?></li>
        			     <li style="margin:0 10px">|</li>
        			      <li> <a href="<?php echo e(url('logout')); ?>" style="margin:0;padding:0;"> <i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a> </li>
        			    </ul>
        			</div>
        		</nav>
    		<?php }?>
    
    
       	 	<?php if($utype=='admin'){?>
        		<nav class="navbar navbar-inverse">
        			<div class="container-fluid">
        				<ul class="nav navbar-nav">
        				    <li><a href="<?php echo e(url('filelist')); ?>">File Validation</a></li>
        					<li><a href="<?php echo e(url('userlist')); ?>">User Management</a></li>
        					<li><a href="<?php echo e(url('reporthistory')); ?>">Report Mangement</a></li>
        					<!--<li><a href="<?php echo e(url('rule_manage')); ?>">Rule Mangement</a></li>-->
        					<li><a href="<?php echo e(url('rulemanagement')); ?>">Rule Mangement</a></li>
        				</ul>
        				<ul class="nav navbar-nav pull-right" style="margin-top:15px;">
        			     <li><i class="fa fa-user" aria-hidden="true"></i> <?php  echo "Welcome ".$userDisplayName;?></li>
        			     <li style="margin:0 10px">|</li>
        			      <li> <a href="<?php echo e(url('logout')); ?>" style="margin:0;padding:0;"> <i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a> </li>
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
        <small>Note: If the SUSHI Service you are testing is restricted by IP address, you will need to make sure that IP 82.145.59.15 is authorized for access to use this utility.</small>
		        
	
		
	 

		
		
	
	