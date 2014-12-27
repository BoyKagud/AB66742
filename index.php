<?php
$request = explode('/', $_SERVER['REQUEST_URI']);
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
header("Access-Control-Allow-Origin: *");
if($request[2] == "auth") {
	if(isset($_POST["email"]) && isset($_POST["password"])) {
		require_once("system/functions.php");
		$id = authLogin($_POST);
		die($id);
	} else {
		loginUI();
		exit;
	}
}


if($request[2] == "fpass") {
	if(isset($_POST["email"])) {
		require_once("system/functions.php");
		$id = fpass($_POST["email"]);
		die($id);
	} else {
		loginUI();
		exit;
	}
}
session_start();
if($request[2] == "signup") {
	if(isset($_POST["form"])) {
		require_once("system/functions.php");
		require_once("system/Controller.php");
		$form = array();
		foreach ($_POST["form"] as $key => $value) {
			$form[$value["name"]] = $value["value"];
		}
		$id = signup($form);
		die($id);
	} else {
		signUpUI();
		die();
	}
}

// print_r($_POST);
// print_r($_POST);
// die();
if(isset($_POST["delfund"]))
	unset($_SESSION["fund_id"]);
	
if(isset($_POST["gid"]) && $_POST["gid"] > 0) {
	$_SESSION["user_id"] = $_POST["gid"];
}
if(isset($_POST["fund_id"]) && $_POST["fund_id"] > 0) {
	$_SESSION["fund_id"] = $_POST["fund_id"];
}

if(isset($_POST["logout"]) && $_POST["logout"] == 1) {
	unset($_SESSION);
	session_destroy();
	loginUI();
	exit;
}


if(isset($_SESSION["user_id"])) {
	ini_set('memory_limit', '64M');
	require_once("system/Controller.php");
	require_once("config.php");

	// instead of instantiating directly, must call Controller function get_page()
	Controller::get_page($request[2]); //revert to 2 on live
	// Controller::get_index();
} else {
	$tmp = explode("?", $request[2]);
	$request[2] = $tmp[0];
	if($request[2]=="Passreset") {
		ini_set('memory_limit', '64M');
		require_once("system/Controller.php");
		require_once("config.php");
		Controller::get_page($request[2]); //revert to 2 on live
	}
	else 
		loginUI();
	exit;
}

function loginUI() {
require_once("config.php");
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/choose-fund.css">
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
</head>
<body>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>

	<div class="wrap-choose-fund" style="background:url('https://slicingpie.com/pieslicer/view/images/logo-main.png') top center no-repeat;">
    
		<span id="login-err" style="color:red;font-size:12px;"></span><br/>
        <form id="login-form" action="#" style="width:324px;margin:auto;">
			<div class="form-group">
				<input class="form-control" placeholder="Email" name="email" type="email" style="margin-bottom:8px;"/>
			</div>
			<div class="form-group">
				<input class="form-control" placeholder="Password" name="password" type="password" style="margin-bottom:8px;"/>
			</div>
			<button style="float:left;" class="btn btn-danger btn-sm" id="btn-login">Login</button>
			<a href="#" id="lnk-fpass" style="background:none;">forgot password?</a>
			<button style="float:right;" class="btn btn-danger btn-sm" id="btn-singup">Sign Up</button>
		</form>
        <br />
        <div style="width:100%; height:1px; clear:both;"></div>
    </div>
	<script type="text/javascript">

		$("#btn-singup").click(function(e) {
			e.preventDefault();
			window.location.assign("https://www.slicingpie.com/pieslicer/signup");
		});

    	$("body").on("click", "#btn-login", function(e) {
    		e.preventDefault();
			var info = $(this).parent().serializeArray();
			$.post("https://www.slicingpie.com/pieslicer/auth", {email:info[0].value, password:info[1].value}, function(data) {
				console.log(data);
				var id = data.slice(0, -1);
				id = parseInt(id);

				if(id>0) {
					//redirect
					var url = 'https://www.slicingpie.com/pieslicer/index.php';
					var form = $('<form style="display:none;" action="' + url + '" method="post">' +
					  '<input type="text" name="gid" value="'+id+'" />' +
					  '</form>');
					$('body').append(form);
					$(form).submit();
				} else $("#login-err").html("Invalid email or password");
			});

		});


		var cdown = 3;
		$("#lnk-fpass").click(function() {
			$(".btn").remove();
			$("#login-form input[name='password']").remove();
			if($("#login-form input[name='email']").val() == "") {
				$(this).parent().addClass("has-error");
				$("#login-err").html("Please provide a valid email address");
			} else {
				var form = $(this).parent().serializeArray();
				$(this).fadeToggle();
				$.post("https://www.slicingpie.com/pieslicer/fpass", {email:form[0]}, function(data) {
					console.log(data);
					$("#login-form input[name='email']").remove();
					setInterval(function() {
						$("#login-err").html("A link has been sent to your email.<br/><br/>Will reload in "+cdown);
						cdown--;
						if(cdown==-1) location.reload();
					},800);
					$(this).remove();
				});
			}
		});
    </script>

</body>
</html>
<?php

}

function signUpUI($args = array()) {
require_once("config.php");
require_once("controller/Payments_Controller.php");
$pc = new Payments_Controller();
	?>


<html>
<head>
	<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/choose-fund.css">
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/spin.min.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

</head>
<body>
	<!-- <div id="spinner-overlay" style="background:url('view/images/lightboxbg.png');top:0;text-align:center;width:100%;height:100%;position:absolute;z-index:6000;display:block;">
		<div id="spinner-message" style="font-size:20px;color:#f5f5f5;position:relative;top:45%;margin:auto;text-align:center;">
			<strong>Hmmmmm... something is not quite right. </strong></br>
			<strong>We're working on it. </strong></br>
			<strong>Please try again in a little while.</strong>
		</div>
	</div> -->
	<div id="spinner" style="width:100%;height:70%;position:absolute;z-index:500;display:none;"></div>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>

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
	  color: '#727272', // #rgb or #rrggbb or array of colors
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

	braintree.setup("<?php echo $pc->clientToken; ?>", 'dropin', {
		  container: 'dropin',
		  paymentMethodNonceReceived: function (event, nonce) {
		  	$("#spinner").fadeToggle(100);
		    console.log(nonce);
		    var nonceArr = {name:"nonce", value:nonce};
		    var env = $("#signup-form").serializeArray();
		    env.push(nonceArr);
		    $.post("signup", {form:env}, function(data) {
		    	console.log(data);
		    	if(parseInt(data)) {
					window.location.assign("https://www.slicingpie.com/pieslicer");
		    	}
		    	else {
		    		$("#login-err").html(data);
		    		$("input[type='email']").addClass("has-error");
				  	$("#spinner").fadeToggle(100);
		    	}
		    });
		  }
		});
	});

</script>
	<div class="wrap-choose-fund" style="margin-top:0;background:url('https://slicingpie.com/pieslicer/view/images/logo-main.png') top center no-repeat;width:40%;margin-top:5%;">
    
		<form id="signup-form" style="text-align:left;">
			<div class="form-group has-warning">
				<select class="form-control" name="subscription">
					<option value="monthly">Subscription: Monthly ($5), 14-days FREE</option>
					<option value="annual">Subscription: Annual ($50), 14-days FREE</option>
					<option value="forever">Subscription: Forever ($99), 14-days FREE</option>
				</select>
			</div>
			<div class="form-group has-warning required">
				<input type="text" class="form-control" name="fundname" placeholder="Fund Name"/>
			</div>
			<div style="float:left;width:50%;padding:10px;border-right:1px solid #e0e0e0;text-align:left;">
				<h3>Account Information</h3>
				<div class="form-group has-warning required">
					<input type="text" class="form-control" name="first_name" placeholder="First Name"/>
				</div>
				<div class="form-group has-warning required">
					<input type="text" class="form-control" name="last_name" placeholder="Last Name"/>
				</div>
				<div class="form-group has-warning required">
					<input type="text" class="form-control" name="email" placeholder="Email"/>
				</div>
				<div class="form-group has-warning required">
					<input type="password" class="form-control" name="password" placeholder="Password"/>
				</div>
				<div class="form-group has-warning required">
					<input type="password" class="form-control" name="pword2" placeholder="Re-type Password"/>
				</div>
				<div class="form-group has-warning required">
					<input type="text" class="form-control" name="address" placeholder="Address"/>
				</div>
				<div class="form-group has-warning required">
					<input type="text" class="form-control" name="city" placeholder="City/State"/>
				</div>
				<div class="form-group has-warning required">
					<?php getCountriesSelect(); ?>
				</div>
				<div class="form-group has-warning">
					<input type="text" class="form-control" name="zip" placeholder="Zip Code"/>
				</div>
				<div class="form-group has-warning required">
					<input type="text" class="form-control" name="phone" placeholder="Phone"/>
				</div>
				
			</div>
			<div style="float:left;width:50%;height:100%;padding:10px;position:relative;">
				<h3>Payment Options</h3>
				<div id="dropin"></div>
				<div id="paypal-container"></div>
       			<div style="width:100%; height:1px; clear:both;"></div>
				<span id="login-err" style="color:red;font-size:12px;"></span><br/>
				<button id="btn-submit-signup" class="btn btn-primary" style="float:right;margin-top:10px;">Sign me up!</button>
			</div>
		</form>
		<span id="login-err" style="color:red;font-size:12px;"></span><br/>
        
        <br />
        <div style="width:100%; height:1px; clear:both;"></div>
    </div>
    <script type="text/javascript">
$("#btn-submit-signup").click(function(e) {
			var sf;
				sf = $("#signup-form").serializeArray();
				console.log(sf);
				if(sf[5].value.length < 8) {
					$("#login-err").html("Password must be 8-15 characters long");
					e.preventDefault();
					return;
				} else if(sf[5].value != sf[6].value) {
					$("#login-err").html("Passwords do not match");
					e.preventDefault();
					return;
				}
				// $.post("http://www.slicingpie.com/pieslicer/signup", {form:sf}, function(data) {
				// 	// console.log(data);
				// 	var res = data.split("||");
				// 	if(parseInt(res[0]) > 0) {
				// 		$(this).parent().html("You can now log in to Pie Slicer");
				// 		setTimeout(function(){location.reload();}, 1500);
				// 	} else {
				// 		console.log(res);
				// 		$("#login-err").html(res[0]);
				// 	}
				// });
		});
    </script>

</body>
</html>

	<?php

}

function getCountriesSelect() {
	?>
<select class="form-control" name="Country">
<option value="" selected="selected">Select Country</option>
<option value="United States">United States</option>
<option value="United Kingdom">United Kingdom</option>
<option value="Afghanistan">Afghanistan</option>
<option value="Albania">Albania</option>
<option value="Algeria">Algeria</option>
<option value="American Samoa">American Samoa</option>
<option value="Andorra">Andorra</option>
<option value="Angola">Angola</option>
<option value="Anguilla">Anguilla</option>
<option value="Antarctica">Antarctica</option>
<option value="Antigua and Barbuda">Antigua and Barbuda</option>
<option value="Argentina">Argentina</option>
<option value="Armenia">Armenia</option>
<option value="Aruba">Aruba</option>
<option value="Australia">Australia</option>
<option value="Austria">Austria</option>
<option value="Azerbaijan">Azerbaijan</option>
<option value="Bahamas">Bahamas</option>
<option value="Bahrain">Bahrain</option>
<option value="Bangladesh">Bangladesh</option>
<option value="Barbados">Barbados</option>
<option value="Belarus">Belarus</option>
<option value="Belgium">Belgium</option>
<option value="Belize">Belize</option>
<option value="Benin">Benin</option>
<option value="Bermuda">Bermuda</option>
<option value="Bhutan">Bhutan</option>
<option value="Bolivia">Bolivia</option>
<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
<option value="Botswana">Botswana</option>
<option value="Bouvet Island">Bouvet Island</option>
<option value="Brazil">Brazil</option>
<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
<option value="Brunei Darussalam">Brunei Darussalam</option>
<option value="Bulgaria">Bulgaria</option>
<option value="Burkina Faso">Burkina Faso</option>
<option value="Burundi">Burundi</option>
<option value="Cambodia">Cambodia</option>
<option value="Cameroon">Cameroon</option>
<option value="Canada">Canada</option>
<option value="Cape Verde">Cape Verde</option>
<option value="Cayman Islands">Cayman Islands</option>
<option value="Central African Republic">Central African Republic</option>
<option value="Chad">Chad</option>
<option value="Chile">Chile</option>
<option value="China">China</option>
<option value="Christmas Island">Christmas Island</option>
<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
<option value="Colombia">Colombia</option>
<option value="Comoros">Comoros</option>
<option value="Congo">Congo</option>
<option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
<option value="Cook Islands">Cook Islands</option>
<option value="Costa Rica">Costa Rica</option>
<option value="Cote D'ivoire">Cote D'ivoire</option>
<option value="Croatia">Croatia</option>
<option value="Cuba">Cuba</option>
<option value="Cyprus">Cyprus</option>
<option value="Czech Republic">Czech Republic</option>
<option value="Denmark">Denmark</option>
<option value="Djibouti">Djibouti</option>
<option value="Dominica">Dominica</option>
<option value="Dominican Republic">Dominican Republic</option>
<option value="Ecuador">Ecuador</option>
<option value="Egypt">Egypt</option>
<option value="El Salvador">El Salvador</option>
<option value="Equatorial Guinea">Equatorial Guinea</option>
<option value="Eritrea">Eritrea</option>
<option value="Estonia">Estonia</option>
<option value="Ethiopia">Ethiopia</option>
<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
<option value="Faroe Islands">Faroe Islands</option>
<option value="Fiji">Fiji</option>
<option value="Finland">Finland</option>
<option value="France">France</option>
<option value="French Guiana">French Guiana</option>
<option value="French Polynesia">French Polynesia</option>
<option value="French Southern Territories">French Southern Territories</option>
<option value="Gabon">Gabon</option>
<option value="Gambia">Gambia</option>
<option value="Georgia">Georgia</option>
<option value="Germany">Germany</option>
<option value="Ghana">Ghana</option>
<option value="Gibraltar">Gibraltar</option>
<option value="Greece">Greece</option>
<option value="Greenland">Greenland</option>
<option value="Grenada">Grenada</option>
<option value="Guadeloupe">Guadeloupe</option>
<option value="Guam">Guam</option>
<option value="Guatemala">Guatemala</option>
<option value="Guinea">Guinea</option>
<option value="Guinea-bissau">Guinea-bissau</option>
<option value="Guyana">Guyana</option>
<option value="Haiti">Haiti</option>
<option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
<option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
<option value="Honduras">Honduras</option>
<option value="Hong Kong">Hong Kong</option>
<option value="Hungary">Hungary</option>
<option value="Iceland">Iceland</option>
<option value="India">India</option>
<option value="Indonesia">Indonesia</option>
<option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
<option value="Iraq">Iraq</option>
<option value="Ireland">Ireland</option>
<option value="Israel">Israel</option>
<option value="Italy">Italy</option>
<option value="Jamaica">Jamaica</option>
<option value="Japan">Japan</option>
<option value="Jordan">Jordan</option>
<option value="Kazakhstan">Kazakhstan</option>
<option value="Kenya">Kenya</option>
<option value="Kiribati">Kiribati</option>
<option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
<option value="Korea, Republic of">Korea, Republic of</option>
<option value="Kuwait">Kuwait</option>
<option value="Kyrgyzstan">Kyrgyzstan</option>
<option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
<option value="Latvia">Latvia</option>
<option value="Lebanon">Lebanon</option>
<option value="Lesotho">Lesotho</option>
<option value="Liberia">Liberia</option>
<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
<option value="Liechtenstein">Liechtenstein</option>
<option value="Lithuania">Lithuania</option>
<option value="Luxembourg">Luxembourg</option>
<option value="Macao">Macao</option>
<option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
<option value="Madagascar">Madagascar</option>
<option value="Malawi">Malawi</option>
<option value="Malaysia">Malaysia</option>
<option value="Maldives">Maldives</option>
<option value="Mali">Mali</option>
<option value="Malta">Malta</option>
<option value="Marshall Islands">Marshall Islands</option>
<option value="Martinique">Martinique</option>
<option value="Mauritania">Mauritania</option>
<option value="Mauritius">Mauritius</option>
<option value="Mayotte">Mayotte</option>
<option value="Mexico">Mexico</option>
<option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
<option value="Moldova, Republic of">Moldova, Republic of</option>
<option value="Monaco">Monaco</option>
<option value="Mongolia">Mongolia</option>
<option value="Montserrat">Montserrat</option>
<option value="Morocco">Morocco</option>
<option value="Mozambique">Mozambique</option>
<option value="Myanmar">Myanmar</option>
<option value="Namibia">Namibia</option>
<option value="Nauru">Nauru</option>
<option value="Nepal">Nepal</option>
<option value="Netherlands">Netherlands</option>
<option value="Netherlands Antilles">Netherlands Antilles</option>
<option value="New Caledonia">New Caledonia</option>
<option value="New Zealand">New Zealand</option>
<option value="Nicaragua">Nicaragua</option>
<option value="Niger">Niger</option>
<option value="Nigeria">Nigeria</option>
<option value="Niue">Niue</option>
<option value="Norfolk Island">Norfolk Island</option>
<option value="Northern Mariana Islands">Northern Mariana Islands</option>
<option value="Norway">Norway</option>
<option value="Oman">Oman</option>
<option value="Pakistan">Pakistan</option>
<option value="Palau">Palau</option>
<option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
<option value="Panama">Panama</option>
<option value="Papua New Guinea">Papua New Guinea</option>
<option value="Paraguay">Paraguay</option>
<option value="Peru">Peru</option>
<option value="Philippines">Philippines</option>
<option value="Pitcairn">Pitcairn</option>
<option value="Poland">Poland</option>
<option value="Portugal">Portugal</option>
<option value="Puerto Rico">Puerto Rico</option>
<option value="Qatar">Qatar</option>
<option value="Reunion">Reunion</option>
<option value="Romania">Romania</option>
<option value="Russian Federation">Russian Federation</option>
<option value="Rwanda">Rwanda</option>
<option value="Saint Helena">Saint Helena</option>
<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
<option value="Saint Lucia">Saint Lucia</option>
<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
<option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
<option value="Samoa">Samoa</option>
<option value="San Marino">San Marino</option>
<option value="Sao Tome and Principe">Sao Tome and Principe</option>
<option value="Saudi Arabia">Saudi Arabia</option>
<option value="Senegal">Senegal</option>
<option value="Serbia and Montenegro">Serbia and Montenegro</option>
<option value="Seychelles">Seychelles</option>
<option value="Sierra Leone">Sierra Leone</option>
<option value="Singapore">Singapore</option>
<option value="Slovakia">Slovakia</option>
<option value="Slovenia">Slovenia</option>
<option value="Solomon Islands">Solomon Islands</option>
<option value="Somalia">Somalia</option>
<option value="South Africa">South Africa</option>
<option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
<option value="Spain">Spain</option>
<option value="Sri Lanka">Sri Lanka</option>
<option value="Sudan">Sudan</option>
<option value="Suriname">Suriname</option>
<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
<option value="Swaziland">Swaziland</option>
<option value="Sweden">Sweden</option>
<option value="Switzerland">Switzerland</option>
<option value="Syrian Arab Republic">Syrian Arab Republic</option>
<option value="Taiwan, Province of China">Taiwan, Province of China</option>
<option value="Tajikistan">Tajikistan</option>
<option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
<option value="Thailand">Thailand</option>
<option value="Timor-leste">Timor-leste</option>
<option value="Togo">Togo</option>
<option value="Tokelau">Tokelau</option>
<option value="Tonga">Tonga</option>
<option value="Tunisia">Tunisia</option>
<option value="Turkey">Turkey</option>
<option value="Turkmenistan">Turkmenistan</option>
<option value="Tuvalu">Tuvalu</option>
<option value="Uganda">Uganda</option>
<option value="Ukraine">Ukraine</option>
<option value="United Arab Emirates">United Arab Emirates</option>
<option value="United Kingdom">United Kingdom</option>
<option value="United States">United States</option>
<option value="Uruguay">Uruguay</option>
<option value="Uzbekistan">Uzbekistan</option>
<option value="Vanuatu">Vanuatu</option>
<option value="Venezuela">Venezuela</option>
<option value="Yemen">Yemen</option>
<option value="Zambia">Zambia</option>
<option value="Zimbabwe">Zimbabwe</option>
</select>
	<?php
}