
<?php
if(isset($_FILES["gruntImg"])) {
	// print_r($_FILES);
	if($_POST["picToggle"] > 0 )
		$result = process_image_upload('gruntImg');
	// else {
		?>
		<script type="text/javascript">	
			$(document).ready(function() {
				var toggleReload = false;
				<?php if(isset($_POST["gemail"]) && $_POST["gemail"] != "") { ?>
					sessGrunt.email = "<?php echo $_POST['gemail']; ?>";
					toggleReload = true;
				<?php }
				if(isset($_POST["gname"]) && $_POST["gname"] != "") { ?>
					sessGrunt.name = "<?php echo $_POST['gname']; ?>";
					toggleReload = true;
				<?php }
				if($_POST['pw'] != "**********") { ?>
					sessGrunt.password = "<?php echo md5($_POST['pw']); ?>";
					toggleReload = true;
				<?php
				}
				?>
				if(toggleReload) {
					saveDOM();
					window.location = window.location.href;
				}
			});
		</script>
		<?php
	// }
} 

function process_image_upload($field)
{

	$DESIRED_IMAGE_WIDTH = 128;
	$DESIRED_IMAGE_HEIGHT = 128;

	$UPLOADED_IMAGE_DESTINATION = "uploads/";
	$THUMBNAIL_IMAGE_DESTINATION = "uploads/thumbs/";
    $source_path = $_FILES['gruntImg']['tmp_name'];

	/*
	 * Add file validation code here
	 */

	list($source_width, $source_height, $source_type) = getimagesize($source_path);

	switch ($source_type) {
	    case IMAGETYPE_GIF:
	        $source_gdim = imagecreatefromgif($source_path);
	        break;
	    case IMAGETYPE_JPEG:
	        $source_gdim = imagecreatefromjpeg($source_path);
	        break;
	    case IMAGETYPE_PNG:
	        $source_gdim = imagecreatefrompng($source_path);
	        break;
	}

	$source_aspect_ratio = $source_width / $source_height;
	$desired_aspect_ratio = $DESIRED_IMAGE_WIDTH / $DESIRED_IMAGE_HEIGHT;

	if ($source_aspect_ratio > $desired_aspect_ratio) {
	    /*
	     * Triggered when source image is wider
	     */
	    $temp_height = $DESIRED_IMAGE_HEIGHT;
	    $temp_width = ( int ) ($DESIRED_IMAGE_HEIGHT * $source_aspect_ratio);
	} else {
	    /*
	     * Triggered otherwise (i.e. source image is similar or taller)
	     */
	    $temp_width = $DESIRED_IMAGE_WIDTH;
	    $temp_height = ( int ) ($DESIRED_IMAGE_WIDTH / $source_aspect_ratio);
	}

	/*
	 * Resize the image into a temporary GD image
	 */

	$temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
	imagecopyresampled(
	    $temp_gdim,
	    $source_gdim,
	    0, 0,
	    0, 0,
	    $temp_width, $temp_height,
	    $source_width, $source_height
	);

	/*
	 * Copy cropped region from temporary image into the desired GD image
	 */

	$x0 = ($temp_width - $DESIRED_IMAGE_WIDTH) / 2;
	$y0 = ($temp_height - $DESIRED_IMAGE_HEIGHT) / 2;
	$desired_gdim = imagecreatetruecolor($DESIRED_IMAGE_WIDTH, $DESIRED_IMAGE_HEIGHT);
	imagecopy(
	    $desired_gdim,
	    $temp_gdim,
	    0, 0,
	    $x0, $y0,
	    $DESIRED_IMAGE_WIDTH, $DESIRED_IMAGE_HEIGHT
	);

	$imName = $THUMBNAIL_IMAGE_DESTINATION.time().".jpg";
	imagejpeg($desired_gdim, $imName);
	?>
	<script type="text/javascript">	
			sessGrunt.image = "<?php echo $imName; ?>";
	</script>
	<?php
}
?>

<div class="page-wrap" id="settings-wrap">
	<div class="widget widget-settings-fund wright" tag="personal" style="display:block;" active="true">
		<div class="widget-header"><strong>Personal Settings</strong><div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.16"></span></div></div>
		<div class="widget-body">
			<form enctype="multipart/form-data" method="post">  
				<input id="upIMGinput" class="form-control" name="gruntImg" type="file" style="display:none;" />
				<div style="margin:auto;" onclick="$('#upIMGinput').click();" class="grunt-img" id="admin-img">
					<img id="imgprev" width="128px" height="128px" class="grunt-img" />
					<span class="wright" style="margin-top:-15px; font-size:10px;">(edit)</span>
				</div>	  

				<div style="width:70%;margin:15px auto;">
					<div class="form-group">
						<label>Public Name  <span class="glyphicon glyphicon-pencil" onclick="$('#st-sessGName').attr('disabled', false);"></span></label>
						<input id="st-sessGName" name="gname" type="text" disabled/><script>$("#st-sessGName").val(sessGrunt.name);document.getElementById("st-sessGName").setAttribute("value", sessGrunt.name)</script>
					</div>
					<div class="form-group">
						<label>Email <span class="glyphicon glyphicon-pencil" onclick="$('#st-sessGMail').attr('disabled', false);"></span></label>
						<input type="text" name="gemail" id="st-sessGMail" disabled /><script>$("#st-sessGMail").val(sessGrunt.email);document.getElementById("st-sessGMail").setAttribute("value", sessGrunt.email)</script>
					</div>
					<div class="form-group">
						<label>Password  <span class="glyphicon glyphicon-pencil" onclick="$('#st-sessGPass').attr('disabled', false);"></span></label>
						<input id="st-sessGPass" name="pw" type="password" disabled value="**********" />
						<span id="st-sessGPass-err" style="color:red;"></span>
					</div>
				</div>
				<input id="picToggle" type="hidden" name="picToggle" value="-1" />
				<button class="btn btn-warning wleft btn-resign">Resign</button>
				<button class="btn btn-danger wright" id="btn-personal-submit" name="Upload">Save Changes</button>

			</form> 
		</div>

		<script type="text/javascript">

			$("#btn-personal-submit").click(function(e) {
				e.preventDefault();
				if($("#st-sessGPass").val().length < 8) {
					$("#st-sessGPass-err").html("Password must be more than 8 characters");
				} else $(this).parent().submit();
			});
			
			function readURL(input) {
				if (input.files && input.files[0]) {
				    var reader = new FileReader();

				    reader.onload = function (e) {
				        $('#imgprev').attr('src', e.target.result);
				        $('#picToggle').attr('value', 1);
				    }
				    reader.readAsDataURL(input.files[0]);
				}
			 }

		    $("#upIMGinput").change(function(){
				readURL(this);
				document.getElementById("admin-img").setAttribute("style", "background:url('view/images/user-128.jpg') no-repeat center center;");
		    });
		</script>
	</div>
	<div class="widget widget-settings-fund wright" tag="pie" style="display:none;" active="false">


		<div class="widget-header"><strong>Pie Settings</strong><div style="color:#000;;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.17"></span></div></div>
		<div class="widget-body" style="margin-bottom:28px">
			<form id="settings-form">
				<div>
					<div class="form-group"><label for="">Pie Name</label> <input class="form-control" type="text" name="pname" id="st-pname" /><script>$("#st-pname").attr("value", Pie.name);</script></div>
					<div class="form-group"><label for="">Currency:</label> <select class="form-control" name="currency" id="st-currency">
									<option value="USD">USD</option>
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="AED">AED</option>
									<option value="AUD">AUD</option>
									<option value="CAD">CAD</option>
									<option value="CHF">CHF</option>
									<option value="CNY">CNY</option>
									<option value="COP">COP</option>
									<option value="INR">INR</option>
									<option value="THB">THB</option>
									<option value="MYR">MYR</option> 
								</select>
					</div>

					<div class="form-group"><label for="">Non-Cash Multiplier:</label> <input class="form-control" type="number" name="noncashx" id="st-ncx" /></div>
					<div class="form-group"><label for="">Cash Multiplier:</label> <input class="form-control" type="number" name="cashx" id="st-cx" /></div>
					<div class="form-group"><label for="">Commission Rate:</label> <input class="form-control" type="number" name="comrate" id="st-comrate" /></div>
					<div class="form-group"><label for="">Royalty Rate:</label> <input class="form-control" type="number" name="rorate" id="st-royaltyRate" /></div>
					<div class="form-group" style="display:none;"><label for="">Fair Market Salary:</label> <input class="form-control" type="number" name="fms" id="st-fms" /></div>
					<div class="form-group"><label for="">Investor Finder's Fee:</label> 
						<input class="form-control" type="number" name="Apct" id="st-inv-apct" />% for up to 
						<input class="form-control" type="number" name="A" id="st-inv-aval" /><span class="span-currency"></span> raised; 
						<input class="form-control" type="number" name="Bpct" id="st-inv-bpct"/>% of over 
						<span id="st-inv-aval-duplicate">$A</span><span class="span-currency"></span> raised</div>
					<div class="form-group"><label for="">Projects: </label><br /> <input type="text" name="tags" placeholder="Add Project (Hit enter or comma to add a project)" class="tm-input tm-input-error form-control"/></div>

				</div>
			</form>
			<input class="btn btn-danger wright btn-saveSettings" data-loading-text="Saving..." type="button" id="btn-saveSettings" value="Save Changes" />
			<input class="btn btn-danger wleft" data-loading-text="Please Wait..." type="button" id="btn-rFund" value="Reset Pie" />
		</div>
	</div>

	<div class="widget widget-settings-fund wright" tag="payment" style="display:none;" active="false">
		<div class="widget-header"><strong>Payment Settings</strong><div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.18"></span></div></div>
		<div class="widget-body">
			<h4>Billing Information</h4>

			<form id="form-pset">
				<div class="form-group">
					<label>First Name</label>
					<input id="pset-fname" type="text" />
				</div>
				<div class="form-group">
					<label>Last Name</label>
					<input id="pset-lname" type="text" />
				</div>
				<div class="form-group">
					<label>Address Line 1</label>
					<input id="pset-ad1" type="text" />
				</div>
				<div class="form-group">
					<label>Address Line 2 (Optional)</label>
					<input id="pset-ad2" type="text" />
				</div>
				<div class="form-group">
					<label>City/State</label>
					<input id="pset-city" type="text" />
				</div>
				<div class="form-group">
					<label>Zip Code</label>
					<input id="pset-zip" type="text" />
				</div>
				<div class="form-group">
					<label>Telephone</label>
					<input id="pset-tel" type="text" />
				</div>
			</form>

			<div id="btn-cupdate" class="clickable" style="margin:12px;"><i>Change Payment Method</i> <span class="glyphicon glyphicon-pencil"></span></div>
			<script type="text/javascript">
			$(document).ready(function(){
			if(!isLead()) $("#btn-cupdate").remove();
			});
			</script>

			<input class="btn btn-danger wright" data-loading-text="Saving..." type="button" id="btn-saveBilling" value="Save Changes" />
		</div>
	</div>

	<div class="widget widget-settings-fund wright" tag="team" style="display:none;margin-bottom:24px;" active="false">
		<div class="widget-header">
			<strong>Team Members Settings</strong>
		</div>
		<div class="widget-body" id="settings-gruntswrap-tms">
			<!-- <table class="table hide">
				<tr id="th-fms-model">
					<th>Team Member</th>
					<th>Fair Market Salary Less Cash Compensation</th>
				</tr>
				<tr id="tr-fms-row-model" class="hide">
					<td class="tr-fms-row-name" tag="1">Mongskie</td>
					<td class="tr-fms-row-fmsdata" tag="50000"><span class="tr-span-fms">50,000</span><span class="glyphicon glyphicon-pencil btn-editFMS" style="cursor: pointer;font-size:12px;margin-left:5px;"></span></td>
				</tr>
			</table>
			<table class="table">
				<tbody id="tbl-fms">
				</tbody>
			</table> -->

			<div class="grunt-cont" id="settings-grunt-cont-model" style="display:none;">
				<div class="grunt-img" style="background:url('view/images/user-1.jpg') no-repeat center center; width"></div>
				<ul class="grunt-det-popover">
	            	<li style="margin-bottom:5px;"><span class="grunt-name" style="font-weight:bold; font-size:13px;">Grunt 1</span></li>
	                <li><span class="grunt-jobtitle">Employee</span></li> <!--  JOB TITLE	 -->
	                <li tag="50000" data-trigger="hover" data-toggle="tooltip" data-placement="bottom" title="Fair Market Salary Less Cash Compensation">
	                	<span class="grunt-fms" id="">50,000</span> <span class="span-currency"></span> 
	                </li> <!-- FAIR MARKET SALARY -->
	                <br/>
	                <li class="li-set-accType">
	                	<label>Account Type</label>
	                	<span role="static" style="display:block;"></span>
	                	<select name="grunt_type" style="display:none;" disabled>
	                		<option value="1">Advisor</option>
	                		<option value="2">Executive</option>
	                		<option value="3">Employee</option>
	                	</select>
	                </li>
	                <br />
	                <li class="actions-wrap" tag="0">
	                	Actions: 
            			<span class="glyphicon glyphicon-trash a-remove" style="cursor: pointer;font-size:12px;margin-left:5px;"></span>
            			<span class="glyphicon glyphicon-pencil btn-editFMS" style="cursor: pointer;font-size:12px;margin-left:5px;"></span>
	                </li>
				</ul>
			</div>
		</div>
		<div style="width:100%; height:1px; clear:both;"></div>
		<div id="wrap-btn-save-grunts" style="width:100%;text-align:right;display:none;">
			<input class="btn btn-danger wright" id="btn-save-grunts" data-loading-text="Saving..." type="button" value="Save Changes" />
		</div>
		<script type="text/javascript">

			$("body").on("click", "#btn-save-grunts", function() {
				var gruntcont = $(this).parent().parent().find(".grunt-cont");
				console.log(gruntcont);

				for(var k=1 ; k<gruntcont.length ; k++) {
					var cont = gruntcont[k];
					var tag = parseInt($(cont).find(".actions-wrap").attr("tag"));
					if(gruntSettingChanges.indexOf(tag) > -1) {
						var tmp = getGruntById(tag);
						var grunt = Pie.grunts[Pie.grunts.indexOf(tmp)];
						var inputs = $(cont).find("input[type='text']");
						grunt.jobtitle = inputs[0].value;		
						$(inputs[0]).parent().html('<span class="grunt-jobtitle">'+grunt.jobtitle+'</span>');	
								
						grunt.share.fair_market_salary = parseVal(inputs[1].value);					
						$(inputs[1]).parent().html('<span class="grunt-fms" id="">'+(grunt.share.fair_market_salary.toLocaleString())+'</span> <span class="span-currency">'+Pie.settings.fund.currency+'</span>');	
						$(inputs[1]).parent().attr("tag", grunt.share.fair_market_salary);

						try {
							grunt.grunt_type = parseInt($(cont).find("select")[0].value);	
							$($(cont).find("select")[0]).css("display", "none");
							$($(cont).find("span[role='static']")[0]).html(getGruntTypeString(grunt.grunt_type));
							$($(cont).find("span[role='static']")[0]).css("display", "block");
						} catch (e) {}
					}
				}
				$(".li-set-accType select").attr("disabled", "disabled");
				saveDOM();
			});

			var gruntSettingChanges = [];
			$("body").on("click", ".btn-editFMS", function() {
				$(this).removeClass("btn-editFMS");
				if(!$("#wrap-btn-save-grunts").is(':visible'))
					$("#wrap-btn-save-grunts").fadeToggle();
				gruntSettingChanges.push(parseInt($(this).parent().attr("tag")));
				$(".btn-editFMS").popover('hide');
				var fmswrap = $(this).parent().parent().find(".grunt-fms")[0];
				var fms = $(fmswrap).parent().attr("tag");

				var jtwrap = $(this).parent().parent().find(".grunt-jobtitle")[0];
				jt = jtwrap.innerHTML;

				var liacc = $(this).parent().parent().find(".li-set-accType select")[0];
				$(liacc).removeAttr("disabled");
				$(liacc).css("display", "block");
				$($(this).parent().parent().find(".li-set-accType span")[0]).css("display", "none");

				var div = document.createElement("div");

				var formjt = document.createElement("input");
				formjt.setAttribute("type", "text");
				formjt.setAttribute("class", "form-control wleft");
				formjt.value = jt;
				$(jtwrap).parent().html(formjt);

				var form = document.createElement("input");
				form.setAttribute("type", "text");
				form.setAttribute("class", "form-control wleft");
				form.value = parseInt(fms).toLocaleString();

				$(form).css("margin-bottom", "9px");
				div.appendChild(form);
				// div.appendChild(btn);

				$(fmswrap).parent().html(div);
			});
		</script>

	</div>

	<div class="widget widget-settings-fund wright" tag="cancel" style="display:none;" active="false">
		<div class="widget-header"><strong>Cancel Subscription</strong><div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.20"></span></div></div>
		<div class="widget-body">
			<p>To cancel your account you will have to delete your Pie by clicking the button below.</p>
			<p>We hate to see you go! Would you please take a moment to let us know why you are cancelling so we can improve the product in the future?</p>
			
			<textarea id="fb-delete" style="width:100%;height:40%;margin-bottom:18px;">

			</textarea>
			<input class="btn btn-danger wright margleft" data-loading-text="Please Wait..." type="button" id="btn-delFund" value="Delete Pie" />
		</div>
	</div>

	<div class="widget widget-settings-fund wright" tag="alerts" style="display:none;" active="false">
		<div class="widget-header"><strong>Alerts</strong><div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.20"></span></div></div>
		<div class="widget-body">
			<form id="alertsForm">
				<input name="contributions" type="checkbox" /> Email me for new contributions <br />
				<input name="well" type="checkbox" /> Email me when funds from the well have been used <br />
				<button class="btn btn-danger wright btn-saveSettings" data-loading-text="Saving..." name="Upload" type="submit">Save Changes</button>
			</form>
		</div>
	</div>


	<div class="widget widget-settings wleft ws-static">
		<div class="widget-header"><strong>Settings</strong></div>
		<div class="widget-body" id="settings-list">
			<div id="settings-personal"><strong>Personal Settings</strong></div>
			<div id="settings-pie"><strong>Pie Settings</strong></div>
			<div id="settings-payment"><strong>Payment Settings</strong></div>
			<div id="settings-tms"><strong>Team Members Settings</strong></div>
			<div id="settings-cancel"><strong>Cancel Subscription</strong></div>
			<div id="settings-alerts"><strong>Alerts</strong></div>
		</div>
	</div>
	<script type="text/javascript">

	if(sessGrunt.grunt_type > 2) {
		$("#settings-list>div>strong").each(function(){
		     var nam = $(this).html().split(" ")[0];
		     console.log(nam);
		     if(nam == "Payment" || nam == "Pie" || nam == "Team" || nam == "Cancel")
		        $(this).parent().remove();
		});

		$(".dropdown-menu>li>a").each(function(){
		     var nam = $(this).html().split(" ")[0];
		     console.log(nam);
		     if(nam == "Payment" || nam == "Pie" || nam == "Team" || nam == "Cancel")
		        $(this).parent().remove();
		});
	}

	$(".widget-settings div strong").click(function() {
		var tag = $(this).html();
		tag = tag.split(" ");
		tag = tag[0].toLowerCase();

		console.log(tag);

		$($(".widget-settings-fund[active='true']")[0]).fadeToggle(100, function(){
			$(this).attr("active", "false");
			$(".widget-settings-fund[tag='"+tag+"']").fadeToggle(100, function(){ $(this).attr("active", "true"); });
		});
	});

	$("#btn-delFund").click(function() {
		$("#delFund-wrap").css("display", "block");
		togglePopup();
	});
	$("#btn-rFund").click(function() {
		$("#rFund-wrap").css("display", "block");
		togglePopup();
	});

	$(window).scroll(function() {    
	    var scroll = $(window).scrollTop();
	    var px = scroll + 70;

	    $("#spinner-overlay").css("top", scroll);

	    if (scroll >= 100) {
	    	$(".widget-settings").css("position", "absolute");
	        $(".widget-settings").stop().animate({"top":px+"px"}, 250);
	    } else {
	    	$(".widget-settings").css("position", "absolute");
	        $(".widget-settings").stop().animate({"top":"170px"}, 250);
	    }
	});
	</script>
</div> <!-- page-wrap -->
