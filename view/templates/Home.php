<div class="page-wrap" id="home-wrap">

<!-- Grunts -->
<div class="widget wleft grunts-cont home-grunts">
	<div class="widget-header"><strong>Team</strong></div>
	<div class="widget-body" id="grunts-cont" style="overflow:hidden;padding:0;padding-top:18px;height:346px;">
		<div class="grunt-cont" id="grunt-cont-model" style="display:none;">
			<span class="glyphicon glyphicon-remove delGruntX a-remove" data-toggle="tooltip" data-placement="bottom" data-original-title="Remove Team Member"></span>
			<div class="grunt-img" style="url('view/images/user-128.jpg') no-repeat center center"></div>
			<ul class="grunt-det-popover">
            	<li style="margin-bottom:5px;"><span class="grunt-name" style="font-weight:bold; font-size:13px;">Grunt 1</span><br/><span class="grunt-lli" id="grunt-lli-0" style="color:#696969;font-size:10px;">No Login Records</span></li>
                <li>Slices:  <span class="grunt-tbv" id="grunt-tbv-0">5000</span></li>
                <li>Well:  <span class="grunt-well" id="grunt-well-0">0</span>%</li>
                <li>Well Balance:  <span class="grunt-wellb" id="grunt-wellb-0">0</span><span class="span-currency"></li>
                <li>Pie:  <span class="grunt-pct" id="grunt-tbv-pct-0">50%</span></li>
                <li style="padding:10px 0 5px;"><!-- Select -->
					<select class="contrib-selector form-control">
						<option value="-1">Add Contribution</option>
						<option value="timeForm">Time</option>
						<option value="expForm">Expenses</option>
						<option value="supForm">Supplies</option>
						<option value="eqForm">Equipment</option>
						<option value="salesForm">Sales</option>
						<option value="royaltyForm">Royalty</option>
						<!-- <option value="ifForm">Finder's Fee Earned</option> -->
						<option value="faciForm">Facilities</option>
						<option value="otherForm">Other</option>
					</select>
				</li>
				<!-- <li class="li-resign"><button class="btn btn-warning btn-sm btn-resign">Resign</button></li> -->
                
				<!-- <li> -->
                <!-- <button style="padding: 5px auto; width: 100%;" class="btn btn-xs button-danger a-remove" href="#">Remove Team Member</button> -->
                <!-- </li> -->
				
			</ul>
		</div>
	</div>
	<div style="width:100%;text-align:center;" id="grunt-showMore"><span class="glyphicon glyphicon-chevron-down" style="position:relative;font-size:18px;"></span></div>
	<script type="text/javascript">
		var ctrMod = 0;
		$("#grunt-showMore").click(function() {
			var sep = Math.round((Pie.grunts.length - inactiveGruntsLength) / 3);
			sep = ((Pie.grunts.length - inactiveGruntsLength) / 3) > sep ? sep+1 : sep;
			if(ctrMod < sep-1) {
				ctrMod++;
				$("#grunts-cont").animate({height:"+=330px"}, 250);
				if(ctrMod==(sep)-1)
					 $(this).fadeToggle();
			} else $(this).fadeToggle();
		});
	</script>
</div>

<div class="widget wright" style="width:450px;position:inherit;right:0px;" id="pie-chart">
	<div class="widget-header"><strong>Fund Summary</strong></div>
	<div class="widget-body">
		<canvas class="wleft" id="canvas" height="300" width="300" style="width: 300px; height: 300px; margin-bottom:15px;"></canvas>
		
		<div class="wright" id="chart-legend" style="width:100px;">
			<div id="colorBox-model" class="hide">
				<div class="grunt-colorBox"></div>
				<span class="grunt-name colorBox-gname">x</span>
			</div>
		</div>
	</div>
</div>

</div> <!-- page-wrap -->

<script type="text/javascript">
$(window).scroll(function() {    
	    var scroll2 = $(window).scrollTop();

	    var px = scroll2 + 70;

	    if (scroll2 >= 100) {
	    	$("#pie-chart").css("position", "absolute");
	        $("#pie-chart").stop().animate({"top":px+"px"}, 250);
	    } else {
	    	$("#pie-chart").css("position", "absolute");
	        $("#pie-chart").stop().animate({"top":"170px"}, 250);
	    }
	});
$(document).ready(function() {
	$(".delGruntX").tooltip({animation:true});
});

<?php
	$cookie_name = "ps_chrome_browser";
	$cookie_value = true;
		
	if(!isset($_COOKIE[$cookie_name])) {
		setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
	}

 ?>
</script>