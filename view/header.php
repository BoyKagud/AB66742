<html>
<head>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/Chart.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/tagmanager.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/bootstrap.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.slimscroll.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/spin.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.imgareaselect.min.js"></script>
	<script src="https://js.braintreegateway.com/v2/braintree.js"></script>


	<link rel="stylesheet" href="<?php echo SCRIPTS_DIRECTORY; ?>/datepicker/css/datepicker.css">
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/datepicker/js/bootstrap-datepicker.js"></script>
	<!-- <script src="<?php // echo SCRIPTS_DIRECTORY; ?>/datepicker/jquery.datepick.js"></script> -->
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/tagmanager.css">
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/layout.css">
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

	<!-- test css -->
	<style type="text/css">
		.popup {
			background:url('<?php echo IMAGES_DIRECTORY; ?>/lightboxbg.png');
			position:absolute;
			width:100%;
			min-height:100%;
			z-index:5000;
			display:none;
			padding:auto;
			text-align: center;
		}

		li.dropdown:hover > ul.dropdown-menu {
		    display: block;    
		}
		
	</style>
	<?php 
	$pie = json_encode($args);
	$_SESSION["gleadID"] = $args["grunt_leader"];
	// print_r($pie);
	?>
	<script>
		var readOnly = false;
		<?php
		if( isset($_SESSION["readOnly"]) )
			echo "readOnly = true;";
		?>
		var sessGrunt;
		var Pie = <?php echo $pie; ?> ;
		var royalty_rate = parseFloat(Pie.settings.fund.royalty_rate);

		var commission_rate = parseFloat(Pie.settings.fund.commission_rate);
		var salary_multiplier = parseInt(Pie.settings.fund.salary_multiplier);

		var noncashx = parseInt(Pie.settings.fund.noncashx);
		var cashx = parseFloat(Pie.settings.fund.cashx);

		for(s in Pie.grunts) {
			var fair_market_salary = parseInt(Pie.grunts[s].share.fair_market_salary);
			Pie.grunts[s].share.hourlyRate = parseFloat(fair_market_salary/2000);
			Pie.grunts[s].share.GHRR = Pie.grunts[s].share.hourlyRate * noncashx;

			if(Pie.grunts[s].gid == <?php echo $_SESSION["user_id"]; ?>) {
				sessGrunt = Pie.grunts[s];
			}

		}

		// inverstor finders vlues
		var Apct = parseFloat(Pie.settings.fund.Apct);
		var Bpct = parseFloat(Pie.settings.fund.Bpct);
		var A = Pie.settings.fund.A;

		var contributions = [];
	</script> 
	<?php

	require_once("controller/Payments_Controller.php");
	$pc = new Payments_Controller();
	$clToken = $pc->clientToken;

	// HELP TEXTS
	$createFundHelp = "Each Pie is a separate subscription.<br/><br/> You may have as many contributors as you want for each Pie.<br/><br/> You may cancel your subscription by deleting the Pie.";
	$timeHelp = "Time is the most common contribution people make to startup companies. The Pie Slicer records time<br/> 
				contributions in hours and performs the following calculation:<br/><br/>
				<strong>Slices = (Hours x ((Market Salary / 2,000) x Non-Cash Multiplier)) - Cash Payment Received</strong><br/><br/>
				Hours and Cash Payment Received are entered on this dialog box. Market Salary and the Non-Cash <br/>
				Multiplier can edited by the Pie administrator under the 'Settings' tab above. 2,000 is roughly the <br/>
				number of working hours in a year.<br/>
				It's useful to allocate time to specific projects and include a description of the work performed. Projects <br/>
				can be edited by the Pie administrator under the 'Settings' tab above.";
	$expensesHelp = "Your company may require you to complete an expense report to receive reimbursement. Only enter <br/>
					expenses after you have checked with your Pie administrator to see if and when you will be reimbursed.<br/><br/>
					Expenses are tracked in the Pie Slicer as cash contributions using the following calculation:<br/><br/>
					<strong>Slices = (Amount - Reimbursement) x Cash Multiplier</strong><br/><br/>
					The Cash Multiplier can be edited by the Pie administrator under the 'Settings' tab above.<br/>
					Be sure to include a description of the expense and save your receipt for tax purposes.";
	$suppliesHelp = "Use this screen if you are contributing supplies to the company. If you bought supplies specifically <br/>
					for the company you can use this dialog box or the Expenses dialog box. The Slicing Pie method <br/>
					differentiates between supplies bought specifically for the company and pre-owned supplies brought <br/>
					from home or purchased for a previous venture.<br/><br/>
					The Pie Slicer uses the following calculations for supplies bought specifically for the company:<br/>
					<strong>Slices = (Amount Paid - Reimbursement) x Cash Multiplier</strong><br/><br/>
					The Pie Slicer uses the following calculations for previously owned supplies less than a year old:<br/>
					<strong>Slices = (Amount Paid - Reimbursement) </strong><br/><br/>
					The Pie Slicer uses the following calculations for previously owned supplies more than a year old:<br/>
					<strong>Slices = (Fair Market Value - Reimbursement</strong>)<br/><br/>
					The Cash Multiplier can be edited by the Pie administrator under the “Settings” tab above.<br/>
					Fair Market Value refers to the cost of the item if it were going to be acquired today. Ebay is a good
					place to start.";
	$equipmentHelp = "Use this screen if you are contributing equipment to the company. If you bought the equipment<br/>
					specifically for the company you can use this dialog box or the Expenses dialog box. The Slicing Pie <br/>
					method differentiates between equipment bought specifically for the company and pre-owned <br/>
					equipment brought from home or purchased for a previous venture.<br/><br/>
					The Pie Slicer uses the following calculations for equipment bought specifically for the company:<br/>
					<strong>Slices = (Amount Paid - Reimbursement) x Cash Multiplier</strong><br/><br/>
					The Pie Slicer uses the following calculations for previously owned equipment less than a year old:<br/>
					<strong>Slices = (Amount Paid - Reimbursement) </strong><br/><br/>
					The Pie Slicer uses the following calculations for previously owned equipment more than a year old:<br/>
					<strong>Slices = (Fair Market Value - Reimbursement) </strong><br/><br/>
					The Cash Multiplier can be edited by the Pie administrator under the 'Settings' tab above.
					Fair Market Value refers to the cost of the item if it were going to be acquired today. Ebay is a good
					place to start.";
	$salesHelp = "When revenue is generated the salesperson responsible for making the sale may be entitled to a <br/>
					commission on the sale. Commissioned salespeople generally have a lower Market Salary than other <br/>
					members of the firm so check with your manager to see if you are entitled to a sales commission. If so, <br/>
					use this dialog box to record the amount of the sale and any cash payments you received. <br/>
					The Pie Slicer tracks sales commissions using the following calculation:<br/><br/>
					<strong>Slices = ((Sales Revenue x Commission Rate) - Cash Payment) x Non-Cash Multiplier</strong><br/><br/>
					The Non-Cash Multiplier and the Commission Rate can be edited by the Pie administrator under the 
					'Settings' tab above.";
	$royaltyHelp = "In some cases the inventor or owner of significant intellectual property is entitled to a royalty on <br/>
					revenue generated by sales related to that intellectual property. Check with your manager to see if you <br/>
					are entitled to a royalty. If so, use this dialog box to record revenue generated for products that use <br/>
					your intellectual property.<br/><br/>
					The Pie Slicer tracks royalties using the following calculation:<br/><br/>
					<strong>Slices = ((Revenue x Royalty Rate) - Cash Payment) x Non-Cash Multiplier</strong><br/><br/>
					The Non-Cash Multiplier and the Royalty Rate can be edited by the Pie administrator under the <br/>
					'Settings' tab above.";
	$facilitiesHelp = "Office or warehouse space can be entered using the Facilities dialog box. Slices are granted to the owner<br/>
					of the space. Enter the fair market value of the facility for the period. The fair market value refers to <br/>
					the going rate for space used by the company. If you provide a 30,000 square foot warehouse and the <br/>
					startup only uses 1,000 square feet they should only be expected to provide slices in exchange for what <br/>
					they actually used.<br/>
					The Pie Slicer tracks facilities using the following calculation:<br/><br/>
					<strong>Slices = Fair Market Rate of Facilities – Cash Payment Received</strong>";
	$otherHelp = "Use this dialog box for other contributions not captured by the others. A one-time bonus payment for a<br/>
					job well done might be an example. You probably won’t use this feature much, but we threw it in here <br/>
					just in case.<br/><br/>
					The Pie Slicer tracks “Other” contribution using the following calculation:<br/><br/>
					<strong>Slices = Amount x Cash or Non-Cash Multiplier as specified</strong><br/><br/>
					The Cash or Non-Cash Multipliers can be edited by the Pie administrator under the “Settings” tab above.";
	$addwellHelp = "The Well represents money kept in a company savings account that has not been spent. Slices are not<br/>
					tracked in the Pie until the money has been transferred to the company checking account to pay bills. <br/>
					The Pie Slicer keeps track of how much money is in the account and who owns the money. When the <br/>
					money is withdrawn Slices will be given to the participants in proportion to how much of the Well they <br/>
					own at that time.<br/><br/>
					For instance, say that Joe contributes $9,000 and Sally contributes $1,000 to the Well. Joe would own <br/>
					90% of the well and Sally would own 10%. When a withdrawal is made 90% of the slices would go to Joe <br/>
					and 10% would go to Sally.<br/><br/>
					Only the Pie administrator can add funds from the well.";
	$drawwellHelp = "When money is drawn from the company savings account and put into the active checking account to<br/>
					pay bills Slices of the Pie are given to the Well owners in proportion to their ownership of the well. <br/><br/>
					The Pie Slicer tracks withdrawals from the Well using the following calculations for each participant in <br/>
					the Well:<br/><br/>
					<strong>Slices = (Amount Withdrawn x Personal Ownership %) x Cash Multiplier</strong><br/><br/>
					The Cash Multiplier can be edited by the Pie administrator under the 'Settings' tab above.<br/>
					The Personal Ownership % depends on how much of the Well the individual owns at the time of <br/>
					withdrawal. <br/>
					Only the Pie administrator can draw funds from the well.";
	$addgruntHelp = "Only the Pie administrator can add team members.<br/><br/>
					Add a team member by entering their name, email and the salary that was negotiated with them when <br/>
					they joined your team. <br/><br/>
					If they are an advisor, check the 'Advisor' box. This will prevent them from being removed from the Pie <br/>
					as advisors are generally not removed from Pies.<br/><br/>
					When a team member is added they will get an email inviting them to register on the Pie Slicer. When <br/>
					they do they can upload their picture. They will not be able to access settings or any details of other <br/>
					participants. They will only see their information as it relates to the whole.";
	$remgruntHelp = "When you remove a team member the Pie Slicer will calculate a buyout price depending on the nature <br/>
					of the termination. The buyout option will appear on the 'Summary' tab. If the company has the funds <br/>
					to buyout the employee their slices will be removed from the pie.";
	$terminategoodHelp = "Terminating for Good Reason implies that the employee acted in a way that was not consistent with the <br/>
					goals of the company. If you choose this option the individual’s slices will be recalculated as follows:<br/><br/>
					<strong>1. Slices for non-cash contributions = 0</strong><br/>
					<strong>2. Slices for cash contributions = Slices / 4 (thus removing the multiplier)</strong><br/><br/>
					The company will have the option to buy back the slices for an amount equal to the number of <br/>
					outstanding slices.<br/><br/>
					By imposing penalties on employees in this way encourages them to consider the impact of their <br/>
					behavior on the company.";
	$resignnogoodHelp = "Resignation for No Good Reason implies that the employee is leaving the company for their own <br/>
					personal reasons and, as a result, the company is losing a valuable resource. If you choose this option <br/><br/>
					the individual’s slices will be recalculated as follows:<br/><br/>
					<strong>1. Slices for non-cash contributions = 0</strong><br/>
					<strong>2. Slices for cash contributions = Slices / 4 (thus removing the multiplier)</strong><br/><br/>
					The company will have the option to buy back the slices for an amount equal to the number of <br/>
					outstanding slices.<br/><br/>
					By imposing penalties on employees in this way encourages them to consider the impact of their choices <br/>
					on the company. In some cases the employee can keep their slices if they agree to stay on as a part-time <br/>
					participant.";
	$terminatenogoodHelp = "When a company choses to terminate an employee that is performing their job as expected, the<br/>
					company will experience the consequences of the decision. If you choose this option the employee gets <br/>
					to keep all of their slices as calculated under the model. The company can offer to buy back the slices at <br/>
					an amount equal to the number of outstanding slices, but the employee should not be obligated to sell.<br/><br/>
					This encourages the company to think twice before they terminate employees for no good reason.";
	$resigngoodHelp = "When a company changes the working conditions or employment terms in a way that adversely <br/>
					affects an employee in a disproportionate way vs. other employees at the same level the company will <br/>
					experience the consequences of the decision. If you choose this option the employee gets to keep all of <br/>
					their slices as calculated under the model. The company can offer to buy back the slices at an amount <br/>
					equal to the number of outstanding slices, but the employee should not be obligated to sell.<br/><br/>
					This protects employees from arbitrary management decisions that change their understanding of the <br/>
					job.";
	?>
</head>
<body>

<div id="spinner-overlay" style="background:url('view/images/lightboxbg.png');width:100%;height:100%;position:absolute;z-index:6000;display:none;">
	<div id="spinner-canvas"></div>
	<div id="spinner-message" style="color:#f5f5f5;position:absolute;top:70%;left:48%;text-align:center;">Please Wait...</div>
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

<!-- contrib tab context menu -->
<div class="popover" id="contrib-contextMenu" style="height:62px;width:100px;z-index:5000;">
	<ul style="padding:5px;">
		<li class="table-record" id="context-edit" style="list-style:none;width:100%;margin-bottom:5px;cursor:pointer;"><span style="margin-right:5px;" class="glyphicon glyphicon-edit"></span>Edit</li>
		<li class="table-record" id="context-del" style="list-style:none;width:100%;margin-bottom:5px;cursor:pointer;"><span style="margin-right:5px;" class="glyphicon glyphicon-trash"></span>Delete</li>
	</ul>
</div>

<!-- CONTRIBUTION FORM -->
	<div class="popup" id="popup" style="position:fixed;">

		<!-- Credits -->
		<div class="contribution-form" id="credits-wrap" style="margin:auto;margin-top:100px;width:700px;background:#fff;text-align:left;border-radius:5px;">
			<div class="widget-header">Credits</div>
			<div class="widget-body" style="padding:20px 20px;">
			<img src="view/images/logo.png">
			<div style="padding:20px;" id="cred-scroll">
			<div><p>I would like to thank the following Grunts who provided feedback and ideas during the development of the Pie Slicer:</p></div>
			<div>
				<table style="width:80%;margin:auto;margin-bottom:20px;text-align:center;">
					<tr>
						<td>Emong Bentiez</td>
						<td>Development</td>
					</tr>
					<tr>					
						<td>Kaku Benitez</td>
						<td>Design</td>
					</tr>
					<tr>
						<td>Detrick DeBurr</td>
						<td>Beta tester</td>
					</tr>
					<tr>
						<td>Maxine Chow</td>
						<td>Beta tester</td>
					</tr>
					<tr>
						<td>Guido Hornig</td>
						<td>Beta tester</td>
					</tr>
					<tr>
						<td>Dennis Lloyd</td>
						<td> Jr. Beta tester</td>
					</tr>
					<tr>
						<td>Kartikeya Singh</td>
						<td>Beta tester</td>
					</tr>
					<tr>
						<td>Danette Wallace</td>
						<td>Super beta tester!</td>
					</tr>
					<tr>
						<td>Jeff Winchell</td>
						<td>Super beta tester!</td>
					</tr>
				</table>	
			</div>
			<div>
				<p>If you would like your name added to this list, please provide input to help make the Pie Slicer better such as ideas for new features, feedback on existing features and, of course, bugs, edits and mistakes! I always appreciate the feedback.</p>
			</div>
			<div>
				<p>Sincerely,</p>
				<img src="view/images/mike_signature.jpg">
				<p>Mike Moyer</p>
			</div>
			</div>
			</div>
			<script type="text/javascript">
			$('#cred-scroll').slimScroll({
		        height: 300+'px'
		    });
			</script>
		</div>

		<div class="contribution-form-wrap">

			<!-- Delete Fund Confirmation -->
			<div class="contribution-form" id="delFund-wrap" style="overflow-y:visible;">
				<div class="contribution-form-header widget-header">Confirm Delete Pie<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.1"></span></div></div>
				<div class="widget-body">
					<form action="index.php" method="post">
						<div class="form-group">
							<label>Are you sure you want to delete this Pie?</label>
							<input id="input-delfund-upass" class="form-control" type="password" name="delfund" placeholder="Enter Password" />
							<span id="delFund-err" style="color:red;font-size:12px;"></span>
						</div>
						<button class="btn btn-sm btn-danger wleft" id="btn-confDelFund">Yes, I want to delete this Pie</button>
						<button class="btn btn-sm btn-danger wright btn-reset">No thanks</button>
					</form>
				</div>
			</div>

			<!-- Delete Grunt no Contrib Confirmation -->
			<div class="contribution-form" id="delGruntConf-wrap" style="overflow-y:visible;">
				<div class="contribution-form-header widget-header">Confirm Delete Member<div style="color:#000;" class="wright"></div></div>
				<div class="widget-body">
						<div class="form-group">
							<label>Are you sure you want to remove <span id="span-remGconf"></span>?</label>
						</div>
						<div class="form-group" style="padding-bottom:10px;">
							<button class="btn btn-sm btn-danger wleft" id="btn-confDelGrunt">Yes, I want to remove this member</button>
							<button class="btn btn-sm btn-danger wright btn-reset">Cancel</button>
						</div>
				</div>
			</div>

			<!-- Reset Fund Confirmation -->
			<div class="contribution-form" id="rFund-wrap" style="overflow-y:visible;">
				<div class="contribution-form-header widget-header">Confirm Reset Fund<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.1"></span></div></div>
				<div class="widget-body">
					<form action="index.php" method="post">
						<div class="form-group">
							<label>Are you sure you want to reset this Fund?</label>
							<input id="input-rfund-upass" class="form-control" type="password" name="rfund" placeholder="Enter Password" /><br/>
							<span id="rFund-err" style="color:red;font-size:12px;"></span><br/>
							<input type="checkbox" name="delGrunts" id="rfund-delg" /> Delete all team members except me
						</div>
						<button class="btn btn-sm btn-danger wleft" id="btn-confRFund">Yes, I want to reset this fund</button>
						<button class="btn btn-sm btn-danger wright btn-reset">No thanks</button>
					</form>
				</div>
			</div>

			<!-- Edit Contributions -->
			<div class="contribution-form" id="editContrib-wrap">
				<div class="contribution-form-header widget-header">Edit Contribution</div>
				<div>
					<form>
						<div class="form-group">
							<label>Project</label>
							<select class="select-project" name="project" id="select-project-dd">
								<option value="-1">For time contributions only...</option>
								<?php
									// print_r($args["projects"]);
									foreach($args["projects"] as $project) {
										?>
											<option value="<?php echo $project; ?>"><?php echo $project; ?></option>
										<?php
									}
								?>
							</select>
						</div>
						<div class="form-group">
							<label>Date</label>
							<input class="edit-input-date" id="edit-input-date" type="date" />
						</div>
						<div class="form-group">
							<label>Description</label>
							<textarea class="edit-input-desc" id="edit-input-desc"></textarea>
						</div>
						<div class="panel-group" id="details">
						  <div class="panel panel-primary">
						    <div class="panel-heading">
						      <h4 class="panel-title">
						        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
						          Show Details
						        </a>
						      </h4>
						    </div>
						    <div id="collapseOne" class="panel-collapse collapse">
						      <div class="panel-body">
						      	<ul id="ul-contrib-details">

						      	</ul>
						      </div>
						    </div>
						  </div>
						</div>

						<div class="alert alert-warning">
							<span><strong>Notice: </strong>If you prefer editing the value of hours or amount of contribution, we recommend to delete this contribution and create a new one.</span>
						</div>

						<button class="btn btn-sm btn-danger" style="margin-top:12px;" id="btn-submitEditCont">Submit</button>
						<button class="btn btn-sm btn-danger btn-reset" style="margin-top:12px;">Cancel</button>
					</form>
				</div>
			</div>

			<!-- Remove Grunt Options -->
			<div class="contribution-form" id="remGrunt-wrap">
				<div class="contribution-form-header widget-header">Remove Team Member<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $remgruntHelp; ?>"></span></div></div>
				<form style="height:163px;">
					<div class="remTerminate" style="margin-bottom:5px;"><input class="wleft" type="radio" name="remType" value="1" checked/> <div class="wleft margleft">Terminate for Good Reason</div><div style="color:#696969;" class="wleft"><span class="glyphicon margleft glyphicon-info-sign wright infoglyphrem" data-content="<?php echo $terminategoodHelp; ?>"></span></div><br /></div>
					<div class="remTerminate" style="margin-bottom:5px;"><input class="wleft" type="radio" name="remType" value="2" /> <div class="wleft margleft">Terminate for No Good Reason</div><div style="color:#696969;" class="wleft"><span class="glyphicon margleft glyphicon-info-sign wright infoglyphrem" data-content="<?php echo $terminatenogoodHelp; ?>"></span></div><br /></div>
					<div style="margin-bottom:5px;"><input class="wleft" type="radio" name="remType" value="3" /> <div class="wleft margleft"> Resign for Good Reason</div><div style="color:#696969;" class="wleft"><span class="glyphicon margleft glyphicon-info-sign wright infoglyphrem" data-content="<?php echo $resigngoodHelp; ?>"></span></div><br /></div>
					<div><input class="wleft" type="radio" name="remType" value="4" /> <div class="wleft margleft">Resign for No Good Reason</div><div style="color:#696969;" class="wleft"><span class="glyphicon margleft glyphicon-info-sign wright infoglyphrem" data-content="<?php echo $resignnogoodHelp; ?>"></span></div><br /></div>
					<div><button class="btn btn-sm btn-danger wleft pop btn-remGExec" data-content="<span style='color:red;font-weight:800;'>Warning: </span>This cannot be undone" style="margin-top:12px;">Remove Team Member</button></div>
					<div><button class="btn btn-sm btn-danger btn-reset wright" style="margin-top:12px;">Cancel</button></div>
				</form>
			</div>

			<!-- Add new Pie -->
			<div class="contribution-form" id="newFund-wrap">
				<!-- <div onclick="reset();" style="width:100%;height:100%;position:absolute;background:url('view/images/lightboxbg.png');">
					<div style="text-align:center;margin:auto;margin-top:20%;color:#f5f5f5;">
						<strong>This feature is currently under maintenance</strong></br>
						<strong>Please try again later...</strong></br>
						<strong>We're sorry for the inconvenience</strong>
					</div>
				</div> -->
				<div class="contribution-form-header widget-header">Create New Pie<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $createFundHelp; ?>"></span></div></div>
				<form>
					<input type="hidden" name="leader" value="<?php echo $_SESSION['user_id']; ?>" />
					<div class="form-group">
						<label>Pie Name</label>
						<input type="text" id="cfname" class="form-control" name="fundName" />
					</div>
					<div class="form-group">
						<label>Subscription</label>
						<select class="form-control" name="subscription">
							<option value="monthly">Monthly ($5)</option>
							<option value="annual">Annual ($50)</option>
							<!-- <option value="forever">Forever ($99)</option> -->
							<option value="triannual">3-Year Plan ($99)</option>
						</select>
					</div>
					<div id="cfpass-wrap" class="form-group">
						<label>Password</label>
						<input class="form-control" name="cfpass" type="password" id="cf-passconf" placeholder="Enter Password" />
					</div>					

					<button class="btn btn-danger" style="margin-top:12px;" id="btn-cfund">Create Pie</button>
					<button class="btn btn-danger btn-reset" style="margin-top:12px;">Cancel</button>
				</form>
			</div>

			<!-- Upgrade Pie -->
			<div class="contribution-form" id="pUpgrade-wrap">
				<div class="contribution-form-header widget-header">Upgrade Pie</div>
				<form id="form-upgrade">
					<div class="form-group">
						<label>Subscription</label>
						<select id="pupgrade-subs" class="form-control" name="subscription">
							<option value="monthly">Monthly ($5)</option>
							<option value="annual">Annual ($50)</option>
							<!-- <option value="forever">Forever ($99)</option> -->
							<option value="triannual">3-Year Plan ($99)</option>
						</select>
					</div>
					<div id="dropin"></div>
					
					<button class="btn btn-danger" style="margin-top:12px;" id="btn-pupgrade">Upgrade</button>
					<button class="btn btn-danger btn-reset" style="margin-top:12px;">Cancel</button>
				</form>
			</div>

			<!-- Update Card -->
			<div class="contribution-form" id="cUpdate-wrap">
				<div class="contribution-form-header widget-header">Update Existing Card</div>
				<form>
					<div id="dropin-update"></div>
					
					<button class="btn btn-danger" style="margin-top:12px;" id="btn-cupdate">Update</button>
					<button class="btn btn-danger btn-reset" style="margin-top:12px;">Cancel</button>
				</form>
			</div>

			<!-- Draw from well -->
			<div class="contribution-form" id="drawWell-wrap">
				<div class="contribution-form-header widget-header">Draw Funds from Well<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $drawwellHelp; ?>"></span></div></div>
				<form id="drawWellForm">
					<div class="form-group">
						<label>Amount</label>
						<input class="form-control" type="text" name="hours" value="0"/><br/>
					</div>
					<input type="button" class="btn btn-danger btn-sm" id="btn-calculateDrawWell" value="Draw Funds" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Investor Finder -->
			<div class="contribution-form" id="invForm-wrap">
				<div class="contribution-form-header widget-header">Add Funds to Well<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $addwellHelp; ?>"></span></div></div>
				<div>
					<form id="invForm" class="">
						<input type="hidden" name="contribType" value="6" />
						<div class="form-group">
							<label>Amount Invested</label>
							<input class="form-control" type="text" name="amount" value="0" /><br/>
						</div>
						<div class="form-group">
							<label>Investor</label>
							<select class="form-control" id="select-investor" name="investor">
								<option value="-1">Choose a Team Member</option>
							</select>
						</div>
						<div style="display:none; margin:10px;">
							<h4>Grunt Profile (for new grunts)</h4>
							<div class="form-group">
								<label>Grunt Name</label>
								<input class="form-control" type="text" name="gruntName" /><br />
							</div>
							<div class="form-group">
								<label>Email</label>
								<input class="form-control" type="text" name="gruntEmail" /><br />
							</div>
							<div class="form-group">
								<label>Fair Market Salary</label>
								<input class="form-control" type="text" name="gruntFMS" /><br />
							</div>
						</div>
						<div class="form-group">
							<label><div class="wleft">Finder's Fee Recipient (optional)</div> <div class="infoglyph margleft wleft" data-content="Who found this investor"></div></label>
							<select id="select-invFinder" name="invFinder">
								<option value="-1">Choose a Team Member</option>
							</select>
						</div>
						<br />

						<input type="button" class="btn btn-danger btn-sm calcBtn" id="addInv" value="Add Funds" />

						<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
					</form>
				</div>
			</div>

			<!-- Add Grunt -->
			<div class="contribution-form" id="addgForm-wrap">
			<div class="contribution-form-header widget-header">Add Team Member<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $addgruntHelp; ?>"></span></div></span></div>
				<form id="addgForm">
					<h4>Team Member Profile</h4>
					<label>Team Member Name</label>
					<input type="text" name="gruntName" /><br />
					<label>Email</label>
					<input type="text" name="gruntEmail" /><br />
					<label>Fair Market Salary Less Cash Compensation</label>
					<span id="gruntFMS-error" style="color:red;"></span>
					<input type="text" name="gruntFMS" /><br />
					<input type="checkbox" class="wleft" name="advisor" /> <div class="margleft wleft">Advisor</div><div class="margleft infoglyph wleft" data-content="<span style='color:red;font-weight:800;'>Warning:</span> Advisors cannot be removed from the fund."></div><br /><br />
					<input type="button" class="btn btn-danger btn-sm calcBtn" id="addG" value="Add Team Member" data-container="#addgForm" data-toggle="popover" data-trigger="manual" data-placement="top" data-content='<div>Oops, that email is already being used for a contributor in another pie! Do you want to continue?</div> <div><button style="margin:10px;" class="btn btn-danger btn-cont-addg btn-sm wleft">Continue</button><button style="margin:10px;" class="btn btn-danger btn-sm btn-reset wright" onclick="event.preventDefault;reset();">Cancel</button></div>' />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- time -->
			<div class="contribution-form" id="timeForm-wrap">
				<div class="contribution-form-header widget-header">Time<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $timeHelp; ?>"></span></div></div>
				<div>
					<form id="timeForm" class="scroll">
						<input type="hidden" name="contribType" value="1" />
						<label>Date</label>
						<input type="date" name="date" /><br />
						<label>Project</label>
						<select name="project" id="select-project-dd">
							<?php
								// print_r($args["projects"]);
								foreach($args["projects"] as $project) {
									?>
										<option value="<?php echo $project; ?>"><?php echo $project; ?></option>
									<?php
								}
							?>
						</select><br/>
						<label>Description</label>
						<textarea name="desc"></textarea>
						<label>Hours Spent (hh:mm)</label>
						<input type="text" name="hours" value="0" placeholder="hh:mm"/><br/>
						<label>Cash Payment Received (if any)</label>
						<input type="text" name="amount" value="0"/>
						<br />
						<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
						<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
					</form>
				</div>
			</div>

			<!-- Expenses -->
			<div class="contribution-form" id="expForm-wrap">
				<div class="contribution-form-header widget-header">Expenses<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $expensesHelp; ?>"></span></div></div>
				<form id="expForm">
					<input type="hidden" name="contribType" value="2" />
					<label>Date</label>
					<input type="date" class="expDate" name="date" /><br />
					<label>Description</label>
					<textarea name="desc" class="expDesc"></textarea>
					<label>Amount</label>
					<input type="text" class="expAmount" name="amount" value="0"/><br/>
					<label>Reimbursed (optional)</label>
					<input type="text" name="reimbursed" value="0"/>
					<br />
					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Supplies -->
			<div class="contribution-form" id="supForm-wrap">
				<div class="contribution-form-header widget-header">Supplies<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $suppliesHelp; ?>"></span></div></div>
				<div>
					<form id="supForm" class="scroll">
						<input type="hidden" name="contribType" value="3" />
						<label>Date</label>
						<input type="date" name="date" /><br />
						<label>Description</label>
						<textarea name="desc"></textarea>
						<label>Amount Paid or Fair Market Value (if older than one year)</label>
						<input type="text" name="amount" value="0" /><br />
						<label>Reimbursed (optional)</label>
						<input type="text" name="reimbursed" value="0"/>
						<br/>
						<input type="radio" name="age" value="0"/>
						<label>Purchased for the Company</label><br/>
						
						<input type="radio" name="age" value="1"/>
						<label>Less Than a Year Old</label><br/>

						<input type="radio" name="age" value="-1"/>
						<label>Older Than a Year</label><br/>
						<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
						<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
					</form>
				</div>
			</div>

			<!-- Equipment -->
			<div class="contribution-form" id="eqForm-wrap">
				<div class="contribution-form-header widget-header">Equipment<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $equipmentHelp; ?>"></span></div></div>
				<div>
					<form id="eqForm" class="scroll">
						<input type="hidden" name="contribType" value="3" />
						<label>Date</label>
						<input type="date" name="date" /><br />
						<label>Description</label>
						<textarea name="desc"></textarea>
						<label>Amount Paid or Fair Market Value (if older than one year)</label>
						<input type="text" name="amount" value="0" /><br />
						<label>Reimbursed (optional)</label>
						<input type="text" name="reimbursed" value="0"/>
						<br/>
						<input type="radio" name="age" value="0"/>
						<label>Purchased for the Company</label><br/>
						
						<input type="radio" name="age" value="1"/>
						<label>Less Than a Year Old</label><br/>

						<input type="radio" name="age" value="-1"/>
						<label>Older Than a Year</label><br/>

						<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
						<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
					</form>
				</div>
			</div>

			<!-- Sales -->
			<div class="contribution-form" id="salesForm-wrap">
				<div class="contribution-form-header widget-header">Sales<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $salesHelp; ?>"></span></div></div>
				<form id="salesForm">
					<input type="hidden" name="contribType" value="4" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Sale Amount</label>
					<input type="text" name="amount" value="0" /><br/>
					<label>Cash Payment Received (if any)</label>
					<input type="text" name="cashP" value="0" /><br />

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Royalty -->
			<div class="contribution-form" id="royaltyForm-wrap">
				<div class="contribution-form-header widget-header">Royalty<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $royaltyHelp; ?>"></span></div></div>
				<form id="royaltyForm">
					<input type="hidden" name="contribType" value="5" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Gross Sales of Product Since Last Entry</label>
					<input type="text" name="sales" value="0" /> <br />
					<label>Cash Payment Received (if any)</label>
					<input type="text" name="cashP" value="0" /><br/>

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Facilities -->
			<div class="contribution-form" id="faciForm-wrap">
				<div class="contribution-form-header widget-header">Facilities<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $facilitiesHelp; ?>"></span></div></div>
				<form id="faciForm">
					<input type="hidden" name="contribType" value="7" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Fair Market Value of Facility</label>
					<input type="text" name="value" value="0" /> <br />
					<label>Cash Payment Received (if any)</label>
					<input type="text" name="cashP" value="0" /> <br />

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Other -->
			<div class="contribution-form" id="otherForm-wrap">
				<div class="contribution-form-header widget-header">Other<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="<?php echo $otherHelp; ?>"></span></div></div>
				<form id="otherForm">
					<input type="hidden" name="contribType" value="8" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Amount</label>
					<input type="text" name="amount" value="0" /><br/>

					<label>
						<input type="radio" name="x" value="0"/>Cash
					</label>
					<label>
						<input type="radio" name="x" value="1"/>Non-Cash
					</label>
					<br/>

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Slices" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>
		</div>
	</div>

	<div class="body-wrap">
		<div class="header">
            <div class="logo-top"><img src="view/images/logo.png" /></div>
            <form action="index.php" method="post" class="btn-head-main">
                <input type="hidden" name="logout" value="1" />
                <input class="btn btn-danger btn-sm" id="btn-logout" type="submit" value="Log Out" />
            </form>
            <ul class="nav-top">
                <li role="navigation"><a id="navitem-home" role="header-menu-item" style="cursor:pointer;">HOME</a></li>
                <!-- <li role="navigation"><a id="navitem-summary" role="header-menu-item" style="cursor:pointer;">SUMMARY</a><div id="sum-indicator" class="wright"></div></li> -->
                <!-- <li role="navigation"><a id="navitem-contribs" role="header-menu-item" style="cursor:pointer;">CONTRIBUTIONS</a></li> -->
                <li role="navigation" class="dropdown">
                	<a style="cursor:pointer;">REPORTS</a>
                	<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel" style="width:100%;">
					    <li role="presentation"><a id="navitem-reports" role="menuitem" tabindex="-1" href="#">Analytics</a></li>
					    <li role="presentation"><a id="navitem-summary" role="menuitem" tabindex="-1" href="#">Summary</a></li>
					    <li role="presentation"><a id="navitem-contribs" role="menuitem" tabindex="-1" href="#">Contributions</a></li>
					  </ul>
                </li>
                <li role="navigation" class="dropdown">
                	<a id="navitem-settings" role="header-menu-item" style="cursor:pointer;" tag="settings">SETTINGS</a>
                	<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel" style="width:100%;">
					    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Personal Settings</a></li>
					    <li role="presentation" id="nav-settings-pie" ><a role="menuitem" tabindex="-1" href="#">Pie Settings</a></li>
					    <li role="presentation" id="nav-settings-payment" ><a role="menuitem" tabindex="-1" href="#">Payment Settings</a></li>
					    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Team Members</a></li>
					    <li role="presentation" id="nav-settings-cancel" ><a role="menuitem" tabindex="-1" href="#">Cancel Subscription</a></li>
					    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Alerts</a></li>
					  </ul>
            	</li>
                <li role="navigation" class="dropdown">
				  <a data-toggle="dropdown" data-target="#" style="cursor:pointer;">HELP</a>
				  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel" style="width:100%;">
				    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Edit Contributions</a></li>
				    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Delete Contributions</a></li>
				    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Edit Salaries</a></li>
				    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Remove Members</a></li>
				  </ul>
            	</li>
            </ul>
			<div style="width:100%; height:1px; clear:both;"></div>
		</div>

<div class="fund-header">
	<div class="wleft">
		<div id="head-fundName" class="dropdown" >
			<h1 data-toggle="dropdown" style="cursor:pointer;"><span class="pieName"></span><span class="caret"></span></h1>
			<ul class="dropdown-menu" role="menu" aria-labelledby="btn-pSwitch">
				<form id="form-fundSwitch" method="post">
					<input type="hidden" name="fund_id" value="0" />
						<div>
							<li role="presentation">
								<div class="dd-links" id="head-createNewFund" role="menuitem" href="#">Create a New Pie</div>
							</li>
							<?php
							foreach($args["funds"] as $fund) {
							?>
							<li role="presentation">
								<a class="dd-links" role="menuitem" href="#" tag="<?php echo $fund['id']; ?>"><?php echo $fund['name']; ?></a>
							</li>
							<?php
							}
							?>
						</div>
				</form>
			</ul>
		</div>
		<input class="btn btn-danger btn-sm" type="button" id="btn-addGrunt" value="Add Team Member" />
		<input class="btn btn-danger btn-sm" type="button" id="btn-addInvestment" value="Add Funds to Well" />
		<input class="btn btn-danger btn-sm" type="button" id="btn-drawFromWell" value="Draw Funds from Well" />
	</div>
	<div class="points-wrap">
		<div class="wleft">
			<div class="head" style="border-radius: 10px 0 0;">Pie Slices</div>
			<div class="dat" id='tbv-wrap' data-content="We have detected a fault in calculation. Please <a target='_blank' href='http://form.jotformpro.com/form/41825700042949'>report the bug</a>. <br /><br /><button class='wright btn btn-sm btn-danger' onclick='dismissResetPop();'>Dismiss</button>"><span id="fund-tbv">0</span><span style="font-size:12px;">Slices</span></div>
		</div>
		<div class="wleft">
			<div class="head" style="border-radius: 0 10px 0 0;">Well Value</div>
			<div class="dat"><span id="fund-well">0</span><span class="span-currency"></span></div>
		</div>
	</div>
</div>

<!-- BRAINTREE INITIALIZATION -->

<script type="text/javascript">
$(document).ready(function() {

	$("body").on("click", "#dhead", function() {
		$("#head-fundName").popover("hide");
	});
	$("body").on("click", "#dupgrade", function() {
		$("#pUpgrade-wrap").css("display", "block");
		togglePopup();
	});

	if(Pie.subscriptionID == 0 || Pie.subscriptionID == "0") {
	$("#head-fundName").popover({
			content:"This pie is in beta. Upgrade your pie! </br><button id='dhead' class='wleft btn btn-danger btn-sm' style='margin:10px;'>Dismiss</button><button id='dupgrade' class='wright btn btn-danger btn-sm' style='margin:10px;'>Upgrade</button>"
			,html:true
			,placement:"bottom"
	});
	$("#head-fundName").popover("show");
	braintree.setup('<?php echo $clToken; ?>', 'dropin', {
	  container: 'dropin',
	  paymentMethodNonceReceived: function (event, nonce) {
	  	loadSpinner("Baking your Pie...");
	    console.log(nonce);
	    var env = gruntFull;
	    env.nonce = nonce;
	    env.subscription = $("#pupgrade-subs").val();

		var info = {action:"upgradePie", args:env};
		$.ajaxSetup({async: true});
		$.post(ajaxUrl, info, function(fid) {
			console.log(fid);
			Pie.subscriptionID = fid;
			saveDOM();
			event.preventDefault();
			hideSpinner();
			var c = 5;
			setInterval(function() {
				$("#form-upgrade").html("Will reload in "+c);
				c--;
				if(c == 0) location.reload();
			}, 1000);
		});
	  }
	});
	} else {
		$("#btn-cupdate").click(function(e) {
		  	loadSpinner("Baking your Pie...");
		});
		  braintree.setup('<?php echo $clToken; ?>', 'dropin', {
		  container: 'dropin-update',
		  paymentMethodNonceReceived: function (event, nonce) {

			var info = {action:"updateCard", args:{BT_customerID:Pie.glead_object.BT_cutomerID, nce:nonce}};
			$.ajaxSetup({async: true});
			$.post(ajaxUrl, info, function(fid) {
				event.preventDefault();
				console.log(fid);

				$("#cUpdate-wrap form").css("width", "100%");
				$("#cUpdate-wrap form").css("text-align", "center");
				$("#cUpdate-wrap form").html("Success!");
				hideSpinner();

				$("#cUpdate-wrap").css("display", "block");
				togglePopup();

				setTimeout(function() {
					reset();
					$("#btn-cupdate").remove();
				}, 1000);
			});
		  }
		});
	}
	
});
	</script>