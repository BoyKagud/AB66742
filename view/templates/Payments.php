<html>
<head>
	<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/bootstrap.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/spin.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/layout.css">
</head>
<body>
<?php //print_r($args); ?>
<div id="wait-wrap" style="display:none;background:url('view/images/lightboxbg.png');position:absolute;width:100%;height:100%;z-index:5000;text-align:center;color:white;font-size:18px;">
	<div style="margin:auto;width:300px;height:300px;" id="spinner"></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	var opts = {
	  lines: 11, // The number of lines to draw
	  length: 40, // The length of each line
	  width: 7, // The line thickness
	  radius: 60, // The radius of the inner circle
	  corners: 1, // Corner roundness (0..1)
	  rotate: 41, // The rotation offset
	  direction: 1, // 1: clockwise, -1: counterclockwise
	  color: '#fff', // #rgb or #rrggbb or array of colors
	  speed: 2.2, // Rounds per second
	  trail: 60, // Afterglow percentage
	  shadow: false, // Whether to render a shadow
	  hwaccel: false, // Whether to use hardware acceleration
	  className: 'spinner', // The CSS class to assign to the spinner
	  zIndex: 2e9, // The z-index (defaults to 2000000000)
	  top: '50%', // Top position relative to parent
	  left: '50%' // Left position relative to parent
	};
	var target = document.getElementById('spinner');
	var spinner = new Spinner(opts).spin(target);
});

</script>

	<div class="body-wrap">
		<div class="header">
	        <div class="logo-top"><img src="view/images/logo.png" /></div>
	        <form action="index.php" method="post" class="btn-head-main">
	            <input type="hidden" name="logout" value="1" />
	            <input class="btn btn-danger btn-sm" type="submit" value="Log Out" />
	        </form>
	        <ul class="nav-top">
                <li role="navigation"><a href="index.php" role="header-menu-item">HOME</a></li>
            </ul>
			<div style="width:100%; height:1px; clear:both;"></div>
		</div>

		<div class="widget" style="position:relative;top:100px;margin:auto;width:50%;" >
			<div class="widget-header"><strong>Renew Subscription</strong></div>
			<div class="widget-body" style="border-bottom-left-radius: 7px;border-bottom-right-radius: 7px;border: 1px solid #e0e0e0;">
				
				<form id="checkout" method="post" action="Payments">
					<input type="hidden" id="inpt-subs" name="subscription" value="monthly"/>
					<input type="hidden" id="pie" name="Pie" value='<?php echo json_encode($args) ?>' />
				  	<div id="dropin"></div>

					<div style="width:100%; height:10px; clear:both;"></div>
				  	<input type="submit" class="btn btn-primary" style="margin-bottom:-18px;" value="Confirm Transaction ($100)">
				  	<a class="wright" id="btn-readOnly" class="/pieslicer">or Continue with Read-Only</a>
				</form>

				<script type="text/javascript">
					$(document).ready(function(){
						braintree.setup("eyJ2ZXJzaW9uIjoxLCJhdXRob3JpemF0aW9uRmluZ2VycHJpbnQiOiIyYmFjMzMyMTIwNTc2ZjY1ZDJhNjg0YmE5NjgwMDI4NjRhMGEyYTQ0YjE1MDgwNzgzMDk2OWZjMjgwZjk2NTU1fGNyZWF0ZWRfYXQ9MjAxNC0wOC0xOVQxNTowOToyNi41NTUzNTk0MDIrMDAwMFx1MDAyNm1lcmNoYW50X2lkPWRjcHNweTJicndkanIzcW5cdTAwMjZwdWJsaWNfa2V5PTl3d3J6cWszdnIzdDRuYzgiLCJjaGFsbGVuZ2VzIjpbImN2diJdLCJjbGllbnRBcGlVcmwiOiJodHRwczovL2FwaS5zYW5kYm94LmJyYWludHJlZWdhdGV3YXkuY29tOjQ0My9tZXJjaGFudHMvZGNwc3B5MmJyd2RqcjNxbi9jbGllbnRfYXBpIiwiYXNzZXRzVXJsIjoiaHR0cHM6Ly9hc3NldHMuYnJhaW50cmVlZ2F0ZXdheS5jb20iLCJhdXRoVXJsIjoiaHR0cHM6Ly9hdXRoLnZlbm1vLnNhbmRib3guYnJhaW50cmVlZ2F0ZXdheS5jb20iLCJwYXltZW50QXBwcyI6W10sInRocmVlRFNlY3VyZUVuYWJsZWQiOmZhbHNlLCJwYXlwYWxFbmFibGVkIjp0cnVlLCJwYXlwYWwiOnsiZGlzcGxheU5hbWUiOiJBY21lIFdpZGdldHMsIEx0ZC4gKFNhbmRib3gpIiwiY2xpZW50SWQiOm51bGwsInByaXZhY3lVcmwiOiJodHRwOi8vZXhhbXBsZS5jb20vcHAiLCJ1c2VyQWdyZWVtZW50VXJsIjoiaHR0cDovL2V4YW1wbGUuY29tL3RvcyIsImJhc2VVcmwiOiJodHRwczovL2Fzc2V0cy5icmFpbnRyZWVnYXRld2F5LmNvbSIsImFzc2V0c1VybCI6Imh0dHBzOi8vY2hlY2tvdXQucGF5cGFsLmNvbSIsImRpcmVjdEJhc2VVcmwiOm51bGwsImFsbG93SHR0cCI6dHJ1ZSwiZW52aXJvbm1lbnROb05ldHdvcmsiOnRydWUsImVudmlyb25tZW50Ijoib2ZmbGluZSJ9LCJhbmFseXRpY3MiOnsidXJsIjoiaHR0cHM6Ly9hcGkuc2FuZGJveC5icmFpbnRyZWVnYXRld2F5LmNvbTo0NDMvbWVyY2hhbnRzL2RjcHNweTJicndkanIzcW4vY2xpZW50X2FwaS9hbmFseXRpY3MifX0=", 'dropin', {
						  container: 'dropin',
						  paypal: {
						    singleUse: true
						  },
						  paymentMethodNonceReceived: function (event, nonce) {
						  	$("#wait-wrap").fadeToggle(100);
						    // console.log(nonce);
						    $.post("Payments", {subscription:$("#inpt-subs").val(),payment_method_nonce:nonce,Pie:$("#pie").val()}, function(data) {
						    	// console.log(data);
						    	$.post("system/functions.php", {action:"updateTransaction",args:JSON.parse(data)},function(res){
						    		location.reload();
						    	});
						    });
						  }
						});

						$("#btn-readOnly").click(function(e) {
							e.preventDefault();
							var form = document.createElement("form");
							form.setAttribute("method", "post");
							form.setAttribute("action", "/pieslicer/index.php");

							var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							input.setAttribute("name", "readOnly");
							input.setAttribute("value", "true");

							form.appendChild(input);
							form.submit();
						});
					});
				</script>
			</div>
		</div>
	</div>

</body>
</html>