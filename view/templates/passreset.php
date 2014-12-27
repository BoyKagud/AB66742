<?php

	// print_r($_GET);

require_once("config.php");
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/choose-fund.css">
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">
    <script src="<?php echo SCRIPTS_DIRECTORY; ?>/spin.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/init.js"></script>
    <script src="<?php echo SCRIPTS_DIRECTORY; ?>/spin.min.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
</head>
<body>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>

<div id="spinner-overlay" style="background:url('view/images/lightboxbg.png');width:100%;height:100%;position:absolute;top:0;z-index:6000;display:none;">
    <div id="spinner-canvas"></div>
    <div id="spinner-message" style="color:#f5f5f5;position:absolute;top:70%;left:48%;">Please Wait...</div>
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
      color: '#f5f5f5', // #rgb or #rrggbb or array of colors
      speed: 2.2, // Rounds per second
      trail: 60, // Afterglow percentage
      shadow: false, // Whether to render a shadow
      hwaccel: false, // Whether to use hardware acceleration
      className: 'spinner', // The CSS class to assign to the spinner
      zIndex: 2e9, // The z-index (defaults to 2000000000)
      top: '50%', // Top position relative to parent
      left: '50%' // Left position relative to parent
    };
    var target = document.getElementById('spinner-overlay');
    var spinner = new Spinner(opts).spin(target);
});
</script>

	<div class="wrap-choose-fund" style="background:url('https://slicingpie.com/pieslicer/view/images/logo-main.png') top center no-repeat;">
    
		<span id="login-err" style="color:red;font-size:12px;"></span><br/>
        <form id="login-form" action="#" style="width:324px;margin:auto;">
        	<input type="hidden" name="gid" value="<?php echo $_GET['gid']; ?>" />
        	<input type="hidden" name="token" value="<?php echo $_GET['token']; ?>" />
        	<input type="hidden" name="token_id" value="<?php echo $_GET['token_id']; ?>" />
			<div class="form-group">
				<input id="p1" class="form-control" placeholder="Enter New Password" name="pass" type="password" style="margin-bottom:8px;"/>
			</div>
			<div class="form-group">
				<input id="p2" class="form-control" placeholder="Confirm Password" name="pass2" type="password" style="margin-bottom:8px;"/>
			</div>
			<button style="float:left;" class="btn btn-danger btn-sm" id="btn-changePass">Submit</button>
		</form>
        <br />
        <div style="width:100%; height:1px; clear:both;"></div>
    </div>
<script type="text/javascript">
var cdown = 3;
$("#btn-changePass").click(function(e) {
	e.preventDefault();
	$("#login-err").html("");
	if($("#p1").val() != $("#p2").val()) {
		$("#login-err").html("Passwords do not match");
		return;
	}
	loadSpinner("Processing...");
	$.post("system/functions.php", {action:"changePass", args:$("#login-form").serializeArray()}, function(data) {
		console.log(data);
		$("#login-form input").remove();
		hideSpinner();
		setInterval(function() {
			$("#login-err").html("Congratulations! You've got a new password.<br/><br/>This page will redirect in "+cdown);
			cdown--;
			if(cdown==-1) window.location.assign("https://www.slicingpie.com/pieslicer");
		},800);
	});
});
</script>
</body>
</html>