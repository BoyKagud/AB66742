<html>
<head>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/Chart.min.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/tagmanager.js"></script>
	<script src="<?php echo SCRIPTS_DIRECTORY; ?>/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/tagmanager.css">
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">

	<!-- test css -->
	<style type="text/css">
		body {
			background:#f6f6f6;
		}

		.popup {
			background:url('<?php echo IMAGES_DIRECTORY; ?>/lightboxbg.png');
			position:absolute;
			width:100%;
			height:100%;
			z-index:5000;
			display:none;
			padding:auto;
			text-align: center;
		}

		.contribution-form {
			display: none;
		}

		.contribution-form form {
			padding:15px;
		}

		.contribution-form-header widget-header {
			border-top-right-radius:4px;
			border-top-left-radius:4px;
			background:#e0e0e0;
			padding:5px;
			margin-bottom:5px;
		}

		.contribution-form-wrap {
			position:relative;
			width:400px;
			border-radius:4px;
			background:#fff;
			margin:auto;
			margin-top:150px;
			text-align: left;
		}

		.body-wrap {
			margin:auto;
			width:1020px;
			position:relative;
		}

		.grunt-cont {
			width: 130px;
			height: 130px;
			border-radius: 100%;
			background: #75cee0;
			text-align: center;
			float: left;
			margin: 18px;
		}

		.home-grunts {
			width:55%;
			float:left;
		}

		.points-wrap {
			text-align: right;
			padding:20px;
		}

		.points-wrap .head{
			text-align:left;
			margin-right: 15px;
		}

		.points-wrap .dat{
			text-align:right;
			margin-left: 15px;
			font-size: 28px;
		}

		.points-wrap .wleft {
			margin-left: 10px;
		}

		.grunt-name {
			text-transform: capitalize;
		}

		.inactive {
			width:100%;
			height:100%;
			opacity: 0.5;
		}

		.page-wrap {
			display:none;
		}

		.header {
			width: 100%;
			height: 50px;
		}

		.header ul li {
			float:left;
			margin:0px;
			margin-right:20px;
		}

		.header a {
			color:#5a5a5a;
		}

		.header a:hover {
			text-decoration: none;
		}

		#nav {
			padding-top: 10px;
			float:right;
		}

		.fund-header {
			width:100%;
			height:135px;
		}

		.widget {
			background:#fff;
		}

		.wright {float: right} .wleft{float: left}

		.widget-header {
			width:100%;
			height:42px;
			background:#d2322d;
			color:#fff;
			border-top-left-radius: 5px;
			border-top-right-radius: 5px;
		}

		.widget-body {
			padding:18px;
		}

		.widget-summary {
			width:100%;
		}

		.widget-settings {
			position:fixed;
			width:380px;
		}

		.widget-settings-fund {
			width:60%;
		}

		.span-currency {
			font-size: 12px;
		}

		.btn-reset {
			float:right;
		}
	</style>
	<?php 
	$pie = json_encode($args);
	?>
	<script>
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
		}

		// inverstor finders vlues
		var Apct = parseFloat(Pie.settings.fund.Apct);
		var Bpct = parseFloat(Pie.settings.fund.Bpct);
		var A = Pie.settings.fund.A;

		var contributions = [];
	</script>
	<?php
	?>
</head>
<body>

<!-- CONTRIBUTION FORM -->
	<div class="popup" id="popup">
		<div class="contribution-form-wrap">

			<!-- Draw from well -->
			<div class="contribution-form" id="drawWell-wrap">
				<div class="contribution-form-header widget-header">Draw Funds from Well</div>
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
			<div class="contribution-form-header widget-header">Add Funds to Well</div>
				<form id="invForm">
					<input type="hidden" name="contribType" value="6" />
					<div class="form-group">
						<label>Amount Invested</label>
						<input class="form-control" type="text" name="amount" value="0" /><br/>
					</div>
					<div class="form-group">
						<label>Investor</label>
						<select class="form-control" id="select-investor" name="investor">
							<option value="-1">Choose a Grunt</option>
						</select>
					</div>
					<div style="display:block; margin:10px;">
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
						<label>Finder's Fee Recipient (optional)</label>
						<select id="select-invFinder" name="invFinder">
							<option value="-1">Choose a Grunt</option>
						</select>
					</div>
					<br />

					<input type="button" class="btn btn-danger btn-sm calcBtn" id="addInv" value="Add Funds" />

					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Add Grunt -->
			<div class="contribution-form" id="addgForm-wrap">
			<div class="contribution-form-header widget-header">Add New Grunt</div>
				<form id="addgForm">
					<h4>Grunt Profile</h4>
					<label>Grunt Name</label>
					<input type="text" name="gruntName" /><br />
					<label>Email</label>
					<input type="text" name="gruntEmail" /><br />
					<label>Fair Market Salary</label>
					<input type="text" name="gruntFMS" /><br />
					<input type="button" class="btn btn-danger btn-sm calcBtn" id="addG" value="Add Grunt" data-container="#addgForm" data-toggle="popover" data-trigger="manual" data-placement="top" data-content='<div>Oops, that email is already in use for another Grunt! Do you want to continue?</div> <div><button style="margin:10px;" class="btn btn-danger btn-cont-addg btn-sm wleft">Continue</button><button style="margin:10px;" class="btn btn-danger btn-sm btn-reset wright">Cancel</button></div>' />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- time -->
			<div class="contribution-form" id="timeForm-wrap">
				<div class="contribution-form-header widget-header">Time</div>
				<form id="timeForm">
					<input type="hidden" name="contribType" value="1" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Project</label>
					<select name="project">
						<?php
							foreach($args["projects"] as $project) {
								?>
									<option value="<?php echo $project; ?>"><?php echo $project; ?></option>
								<?php
							}
						?>
					</select><br/>
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Hours Spent</label>
					<input type="text" name="hours" value="0"/><br/>
					<label>Cash Payment (optional)</label>
					<input type="text" name="amount" value="0"/>
					<br />
					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Expenses -->
			<div class="contribution-form" id="expForm-wrap">
				<div class="contribution-form-header widget-header">Expenses</div>
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
					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Supplies -->
			<div class="contribution-form" id="supForm-wrap">
				<div class="contribution-form-header widget-header">Supplies</div>
				<form id="supForm">
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
					<label>New</label><br/>
					
					<input type="radio" name="age" value="1"/>
					<label>Less Than a Year</label><br/>

					<input type="radio" name="age" value="-1"/>
					<label>Older Than a Year</label><br/>
					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Equipment -->
			<div class="contribution-form" id="eqForm-wrap">
				<div class="contribution-form-header widget-header">Equipment</div>
				<form id="eqForm">
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
					<label>New</label><br/>
					
					<input type="radio" name="age" value="1"/>
					<label>Less Than a Year</label><br/>

					<input type="radio" name="age" value="-1"/>
					<label>Older Than a Year</label><br/>

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Sales -->
			<div class="contribution-form" id="salesForm-wrap">
				<div class="contribution-form-header widget-header">Sales</div>
				<form id="salesForm">
					<input type="hidden" name="contribType" value="4" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Sale Amount</label>
					<input type="text" name="amount" value="0" /><br/>
					<label>Cash Payment (optional)</label>
					<input type="text" name="cashP" value="0" /><br />

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Royalty -->
			<div class="contribution-form" id="royaltyForm-wrap">
				<div class="contribution-form-header widget-header">Royalty</div>
				<form id="royaltyForm">
					<input type="hidden" name="contribType" value="5" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Gross Sales of Product Since Last Entry</label>
					<input type="text" name="sales" value="0" /> <br />
					<label>Cash Payment (Optional)</label>
					<input type="text" name="cashP" value="0" /><br/>

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Facilities -->
			<div class="contribution-form" id="faciForm-wrap">
				<div class="contribution-form-header widget-header">Facilities</div>
				<form id="faciForm">
					<input type="hidden" name="contribType" value="7" />
					<label>Date</label>
					<input type="date" name="date" /><br />
					<label>Description</label>
					<textarea name="desc"></textarea>
					<label>Fair Market Value of Facility</label>
					<input type="text" name="value" value="0" /> <br />
					<label>Cash Payment (Optional)</label>
					<input type="text" name="cashP" value="0" /> <br />

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>

			<!-- Other -->
			<div class="contribution-form" id="otherForm-wrap">
				<div class="contribution-form-header widget-header">Other</div>
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

					<input type="button" class="btn btn-danger btn-sm calcBtn" value="Calculate Points" />
					<input class="btn btn-danger btn-sm btn-reset" type="button" value="Cancel" />
				</form>
			</div>
		</div>
	</div>

	<div class="body-wrap">
		<div class="header">

		<form action="index.php" method="post" style="float:right;">
			<input type="hidden" name="logout" value="1" />
			<input class="btn btn-danger btn-sm" type="submit" value="Log Out" />
		</form>
		<ul id="nav" style="list-style-type:none;">
			<li><a href="#">HOME</a></li>
			<li><a href="#">SUMMARY</a></li>
			<li><a href="#">CONTRIBUTIONS</a></li>
			<li><a href="#">SETTINGS</a></li>
		</ul>

</div>

<div class="fund-header">
	<div class="wleft">
		<div style="width:50%;"><h1><span class="pieName"></span></h1></div>
		<input class="btn btn-danger btn-sm" type="button" id="btn-addGrunt" value="Add New Grunt" />
		<input class="btn btn-danger btn-sm" type="button" id="btn-addInvestment" value="Add Funds to Well" />
		<input class="btn btn-danger btn-sm" type="button" id="btn-drawFromWell" value="Draw Funds from Well" />
	</div>
	<div class="wright points-wrap widget">
		<div class="wleft">
			<div class="head">Fund Points</div>
			<div class="dat"><span id="fund-tbv">0</span><span style="font-size:12px;">Points</span></div>
		</div>
		<div class="wleft">
			<div class="head">Well Value</div>
			<div class="dat"><span id="fund-well">0</span><span class="span-currency"></span></div>
		</div>
	</div>
</div>