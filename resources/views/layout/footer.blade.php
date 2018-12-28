</div>
<div class="footer">

		<div class="container text-center">
			
			<a class="footer-logo" href="https://www.projectcounter.org/">
				<img title="Project Counter" alt="Project Counter" src="https://www.projectcounter.org/wp-content/themes/project-counter-2016/images/logo.png">
			</a>

			<div class="footer-menu">
				<div class="menu-main-container"><ul class="menu" id="menu-main-2"><li class="menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home menu-item-160"><a href="https://www.projectcounter.org/">Home</a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-535"><a href="#">About</a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-536"><a href="#">Code of Practice</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-499"><a href="https://www.projectcounter.org/about/register/">Registries of Compliance</a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-537"><a href="#">Members' Section</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-164"><a href="https://www.projectcounter.org/contact-counter/">Contact</a></li>
</ul></div>			</div>

				
				<a href="https://twitter.com/ProjectCounter" target="_blank" class="footer-twitter">
					<img src="{{asset('assets/image/twitter-new.png')}}">
				</a>
						
			<div class="clearer">&nbsp;</div>
			
			 <a href="mailto:lorraine.estelle@counterusage.org " class="footer-email">lorraine.estelle@counterusage.org </a> 
		</div>
		
</div>
<div class="footer-bottom">
	
	<div class="container">
		
		<div class="bf-left">
			&copy;<?php echo date("Y"); ?> Counter | <a href="https://www.projectcounter.org/terms-and-conditions/" target="_blank">Terms and Conditions</a> | <a href="https://www.projectcounter.org/privacy/">Privacy Policy</a>
		</div>
		
		<div class="bf-right" style="display:none">
			Web design by <a title="MPS" href="http://www.adi-mps.com" target="_blank" style="color:#ff0000">MPS Ltd</a>
		</div>
	
	</div>
</div>
<!-- Latest compiled and minified JavaScripts -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>

<?php
if($_SERVER['REQUEST_URI']==='/consortium' || $_SERVER['REQUEST_URI']==='/filelist'){
?>
<script type="text/javascript">
var jQuery_1_3_2 = $.noConflict(true);
</script>
<?php 
}
?>
<?php
if($_SERVER['REQUEST_URI']==='/'){
?>
 <script src="{{URL::asset('assets/js/popper.min.js')}}"></script>  
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<?php } ?>
<script type="text/javascript">
@if (count($errors->register) > 0)
    $('#myModal').modal('show');
@endif
</script>
<script type=text/javascript>
function checkForm(form)
    {
        if(!form.agree.checked) {
			document.getElementById("show_error").innerHTML="<span style='color:#ff0000'>Please accept the Terms and Conditions</span>";
            form.agree.focus();
            return false;
        }
    return true;
    }
	
</script>
@yield("additionaljs")
<script>
var width = 100,
perfData = window.performance.timing, // The PerformanceTiming interface represents timing-related performance information for the given page.
EstimatedTime = -(perfData.loadEventEnd - perfData.navigationStart),
time = parseInt((EstimatedTime/1000)%60)*20;

//Loadbar Animation
jQuery(".loadbar").animate({
width: width + "%"
}, time);

//Loadbar Glow Animation
jQuery(".glow").animate({
width: width + "%"
}, time);

//Percentage Increment Animation
var PercentageID = $("#precent"),
    start = 0,
    end = 100,
    durataion = time;
    animateValue(PercentageID, start, end, durataion);
    
function animateValue(id, start, end, duration) {

var range = end - start,
  current = start,
  increment = end > start? 1 : -1,
  stepTime = Math.abs(Math.floor(duration / range)),
  obj = jQuery(id);

var timer = setInterval(function() {
    current += increment;
    jQuery(obj).text(current + "%");
  //obj.innerHTML = current;
    if (current == end) {
        clearInterval(timer);
    }
}, stepTime);
}

//Fading Out Loadbar on Finised
setTimeout(function(){
	jQuery('.preloader-wrap').fadeOut(100);
	jQuery('.preloader-wrap').hide();
	
}, time);
</script>
</body>
</html>