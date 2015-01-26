// window.onerror = function(){  return true;} 
Date.prototype.toDateInputValue = (function() {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    return local.toJSON().slice(0,10);
});
Date.prototype.thisDateInputValue = (function(date) {
	var st = date;
	var pattern = /(\d{4})\-(\d{2})\-(\d{2})/;
	var local = new Date(st.replace(pattern,'$1-$2-$3'));

    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    try {
    	return local.toJSON().slice(0,10);
    } catch(err) {
    	return new Date().toDateInputValue();
    }
});

var pendingAlerts = [];

// Equity profile / Initialization Script
var ajaxUrl = "system/functions.php";

// pie colors
var piecol = ["#bf0000", "#e46c0a", "#17375e", "#77933c", "#604a7b", 
				"#ffc000", "#4a452a", "#31859c", "#ff0000", "#ffff00"];
// var piecol2 = ["#F7464A", "#46BFBD", "#FDB45C", "#727AC8", "#C184C9", 
// 				"#FFF180", "#C8F09C", "#FFBAA6", "#6C8FA2", "#A5D2BA", 
// 				"#EBB9CB", "#FFD6C9", "#FFE7C9", "#D3F144", "#40CA39"];
var piecol2 = piecol;

//constants
var CONTRIB_TIME = 1;
var CONTRIB_EXPENSES = 2;
var CONTRIB_SUPPLIES = 3;
var CONTRIB_EQUIPMENT = 3;
var CONTRIB_SALES = 4;
var CONTRIB_ROYALTY = 5;
var CONTRIB_INVESTMENT = 6;
var CONTRIB_FACILITIES = 7;
var CONTRIB_OTHER = 8;

var fundValue = Pie.TBV;

var activeContribTag = 0;
var activeSelect;
var activeContribForm=-1;
var pieGraph;
var inactiveGruntsLength=0;

function parseVal(val) {
	var asString = ""+val;
	asString = asString.replace("$","").replace(",","");
	return parseFloat(parseFloat(asString).toFixed(0));
}

var isSpinnerOn = false
function loadSpinner(message) {
	if(isSpinnerOn)
		return;
	var spmsg = $("#spinner-message");

	setTimeout(function(){
		spmsg.html(message);
		var msgwidth = $("#spinner-message").width();
		var bodywidth = $("body").width();
		var center = (bodywidth/2) - (msgwidth/2);
		spmsg.css("left", center);
	}, 200);

	$("#spinner-overlay").fadeToggle(100);
	isSpinnerOn = true;
}

function hideSpinner() {
	if(!isSpinnerOn)
		return;
	$("#spinner-overlay").fadeToggle(100);
	isSpinnerOn = false;
}

function init(firstInit) {
	// clean up if employee
	if(sessGrunt.grunt_type == 3) {
		Pie.grunts = [sessGrunt];
	}
	//=========//


	var tmpNow = new Date();
	$("#fund-tbv").html(parseInt(Pie.TBV).toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
	$("#fund-well").html(parseInt(Pie.well.amount).toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
	var pieData = [];
	
	$.ajaxSetup({async: false});
	fetchContributions();
	//summaries
	initSum();

	//contrib page
	initContribTab();

	// append grunt data to necessary elements
	if(firstInit) {
		if(isLead())
			fixGTypes();
		pieGraph = new Chart(document.getElementById("canvas").getContext("2d")).Pie(null, {animationSteps : 1});

		// billing info
		initBillInfo();

		if(sessGrunt.grunt_type == 2)
			toggleExecutiveView();

		var model = document.getElementById("grunt-cont-model");
		var invFinder = document.getElementById("select-invFinder");
		var investor = document.getElementById("select-investor");
		var total = 0;
		for(var k=0 ; k<Pie.grunts.length ; k++) {
			var grunt = Pie.grunts[k];

			// break down status object
			if(typeof grunt.status === "object") {
				grunt.buyoutpaid = grunt.status.paid;
				grunt.status = grunt.status.buyout;
			}
			// end break down

			grunt.color = (k>10) ? piecol[k%10] : piecol[k];
			var gruntpct = (grunt.share.tbv > 0 ? (Math.round(((grunt.share.tbv/Pie.TBV)*100) * 100) / 100) : 0 );
			grunt.pct = gruntpct;
			if(k == Pie.grunts.length-1 && Pie.grunts[Pie.grunts.length-1].share.tbv > 0 && isLead()) { 
				gruntpct = (100-total).toFixed(2);
			}
			else total += gruntpct;
			// update pie
			var gPie = {value: parseFloat(gruntpct), color:grunt.color, label:grunt.name};
			var gPie2 = {value: parseFloat(gruntpct), color:piecol2[k], label:grunt.name};
			console.log(gPie);
			pieGraph.addData(gPie);
			var node = model.cloneNode(true);
			node.setAttribute("id", "grunt-cont-"+grunt.gid);
			node.setAttribute("style", "");
			node.setAttribute("tag", grunt.gid);
			node.setAttribute("class", "grunt-cont "+grunt.status.toLowerCase());
			node.getElementsByClassName("grunt-well")[0].setAttribute("id", "grunt-well-"+grunt.gid);
			node.getElementsByClassName("grunt-wellb")[0].setAttribute("id", "grunt-wellb-"+grunt.gid);

			var tstr = "";
			var tmplli = new Date(grunt.last_logged_in.replace(/-/g, "/"));
			var tmpdiff = tmpNow - tmplli;
			tmpdiff = tmpdiff / 1000; // to seconds
			tmpdiff = parseInt(tmpdiff / 60); // to minutes
			if(tmpdiff < 1 && grunt.last_logged_in != "0000-00-00 00:00:00")
				tstr = "Last active seconds ago";
			if(tmpdiff < 60 && tmpdiff >= 1 && grunt.last_logged_in != "0000-00-00 00:00:00") {
				tstr = "Last active "+tmpdiff+" minutes ago";
				if(tmpdiff == 1)
					tstr = "Last active "+tmpdiff+" minute ago";
			}
			if(tmpdiff > 60 && grunt.last_logged_in != "0000-00-00 00:00:00") {
				tmpdiff = parseInt(tmpdiff / 60); // to hours
				tstr = "Last active "+tmpdiff+" hours ago";
				if(tmpdiff == 1)
					tstr = "Last active "+tmpdiff+" hour ago";
			}
			if(tmpdiff > 60 && grunt.last_logged_in != "0000-00-00 00:00:00") {
				tmpdiff = parseInt(tmpdiff / 24); // to days
				tstr = "Active "+tmpdiff+" days ago";
				if(tmpdiff == 1)
					tstr = "Last active "+tmpdiff+" day ago";
				if(tmpdiff > 4) {
					tstr = "Last Active "+tmplli.toLocaleDateString();
				}
			}
			if(tstr != "")
				node.getElementsByClassName("grunt-lli")[0].innerHTML = tstr;
			if(sessGrunt.grunt_type == 3) {
				$(node).find(".grunt-lli").remove();
			}
			node.getElementsByClassName("grunt-img")[0].setAttribute("style", "background:url('"+grunt.image+"') no-repeat center center;");

			if(parseInt(grunt.status) > 0)  {
				try {
					node.getElementsByClassName("btn-resign")[0].setAttribute("disabled", "disabled");
				} catch(s) {}
				inactiveGruntsLength++;
				$("a[aria-controls='div-inactive-grunts']").css("margin-right", "5px");
				$("#grunts-inactive-badge").show();
				$("#grunts-inactive-badge").html(inactiveGruntsLength);
				$("#nogrunts-prompt").remove();
				$(node.getElementsByClassName("contrib-selector")[0]).remove();
				if(!isLead()) {
					document.getElementById("grunts-cont").appendChild(node);
					$(".btn-buyout").attr("disabled", "disabled");
				}

				// gruntpct = (parseInt(grunt.status) > 0 ? (Math.round(((parseInt(grunt.status)/Pie.TBV)*100) * 100) / 100) : 0 );
				// var lbl = grunt.name+" (see Summary tab)"
				// var gPie = {value:gruntpct, color:grunt.color, label:lbl};
				// pieGraph.addData(gPie);
				// continue;
				var prnt = node.getElementsByClassName("grunt-det-popover");
				prnt = prnt[0];
				prnt.innerHTML = "<button class='btn btn-danger btn-buyout pop' data-toggle='popover' data-placement='left' data-content='<span style=\"color:red;font-weight:800;\">Warning:</span> this action cannot be undone'>Buyout "+grunt.name+" for <br />"+Pie.settings.fund.currency+" "+parseInt(grunt.status).toLocaleString()+"</button>";
				document.getElementById("div-inactive-grunts").appendChild(node);
			} else {
				node.getElementsByClassName("grunt-name")[0].innerHTML = grunt.name;
				node.getElementsByClassName("grunt-tbv")[0].innerHTML = parseInt(grunt.share.tbv).toLocaleString();
				node.getElementsByClassName("grunt-pct")[0].innerHTML = (gruntpct) ? gruntpct + "%" : "0%";
				if(isLead() || sessGrunt.grunt_id != grunt.gid) {
					$(node.getElementsByClassName("li-resign")[0]).remove();
				}
				document.getElementById("grunts-cont").appendChild(node);
			}

			// append to investor list
			var option = document.createElement("option");
			option.setAttribute("value", grunt.gid);
			option.innerHTML = grunt.name;
			invFinder.appendChild(option);
			investor.appendChild(option.cloneNode(true));

			// disable widget if terminated
			// if(parseInt(grunt.status) > 0) disableGruntForBuyout(grunt);

		}
		// workaround, append last new child
		var option = document.createElement("option");
		option.setAttribute("value", 0);
		option.innerHTML = "Add New Grunt";
		// investor.appendChild(option);

		// display pie name
		$(".pieName").html(Pie.name);

		// set home view vissible
		$("#home-wrap").fadeToggle(100);
		document.getElementById("home-wrap").setAttribute("active","true");

		// SETTINGS		
		$("#st-currency").val(Pie.settings.fund.currency);
		$("#st-ncx").val(Pie.settings.fund.noncashx);
		$("#st-cx").val(Pie.settings.fund.cashx);
		$("#st-comrate").val(Pie.settings.fund.commission_rate*100);
		$("#st-royaltyRate").val(Pie.settings.fund.royalty_rate*100);
		$("#st-fms").val(Pie.settings.fund.fair_market_salary);

		$("#st-inv-apct").val(Pie.settings.fund.Apct*100);
		$("#st-inv-bpct").val(Pie.settings.fund.Bpct*100);

		$("#st-inv-aval").val(Pie.settings.fund.A);
		$("#st-inv-aval-duplicate").html(parseInt($("#st-inv-aval").val()).toLocaleString());
		$("#st-inv-aval").change(function(){ $("#st-inv-aval-duplicate").html(parseInt($("#st-inv-aval").val()).toLocaleString()); });

		$(".span-currency").html(Pie.settings.fund.currency);


		// tag manager
		jQuery(".tm-input").tagsManager();
		for(z in Pie.projects) {
			jQuery(".tm-input").tagsManager('pushTag',Pie.projects[z]);
		}

		// contribs wrap
		var contribHtml = "";
		for(w in contributions) {
			contribHtml += contributions[w].stringify;
 		}
		$("#contributions-wrap").html();
		initLegendColorBox();

		document.getElementById("admin-img").setAttribute("style", "background:url('"+sessGrunt.image+"') no-repeat center center;");
		initAlertsSettings();

		initTMStab();

		pieData.push(gPie);
	} else {
		saveDOM();
		pieGraph.segments.length = 0;
		for(var k=0; k<Pie.grunts.length ; k++) {
			var grunt = Pie.grunts[k];
			grunt.color = piecol[k];
			var gruntpct = (Math.round(((grunt.share.tbv/Pie.TBV)*100) * 100) / 100);
			try {
				var node = document.getElementById("grunt-cont-"+Pie.grunts[k].gid);
				console.log(node);
				node.getElementsByClassName("grunt-name")[0].innerHTML = Pie.grunts[k].name;
				node.getElementsByClassName("grunt-tbv")[0].innerHTML = parseInt(Pie.grunts[k].share.tbv).toLocaleString();
				node.getElementsByClassName("grunt-pct")[0].innerHTML = (gruntpct) ? gruntpct + "%" : "0%";;
			} catch(err) {}
			
			// update pie
			var gPie = {value:gruntpct, color:grunt.color, label:grunt.name};
			var gPie2 = {value:gruntpct, color:piecol2[k], label:grunt.name};
			pieData.push(gPie);
			pieGraph.addData(gPie);
		}
	}

	// IF GRUNT IS NOT LEADER
	// console.log(pieData);
	// if(Pie.grunts.length == 1 && Pie.grunts[0].share.tbv < Pie.TBV) {
	if(!isLead() && sessGrunt.grunt_type != 2) {
		pieGraph.addData({value:100-pieData[0].value, color:"#e0e0e0", label:"Others"});
		$("#btn-addGrunt").attr("disabled", "true");
		$("#btn-addInvestment").attr("disabled", "true");
		$("#btn-drawFromWell").attr("disabled", "true");
		$("#navitem-reports").css("display", "none");
		$($(".remTerminate")[0]).parent().css("height", "120px");
		$(".remTerminate").remove();
	}

	// update well share displays
	for(var l=0 ; l<Pie.well.grunts.length ; l++) {
		Pie.well.grunts[l].gid = parseInt(Pie.well.grunts[l].gid);
		Pie.well.grunts[l].pct = parseFloat(Pie.well.grunts[l].pct);
		try{
			document.getElementById("grunt-well-"+Pie.well.grunts[l].gid).innerHTML = (Pie.well.grunts[l].pct*100).toFixed(2);
			var wbal = Math.round(((Pie.well.grunts[l].pct * Pie.well.amount)*100) / 100).toFixed(0);
			if(wbal > 999999) 
				wbal = ((wbal/1000000).toFixed(2)).toLocaleString()+"M";
			else if(wbal > 100000)
				wbal = ((wbal/1000).toFixed(2)).toLocaleString()+"K";
			else
				wbal = wbal.toLocaleString();
			document.getElementById("grunt-wellb-"+Pie.well.grunts[l].gid).innerHTML = wbal;
		} catch(a){}
	}
	// pieGraph = new Chart(document.getElementById("canvas").getContext("2d")).Pie(pieData);

	if(inactiveGruntsLength > 0)
		document.getElementById("sum-indicator").setAttribute("style", "background:#BF0000;width:8px;height:8px;border-radius:100%;position:relative;display:block;");
}

$(document).ready(function() {
setTimeout(function() {initRemVissibility()}, 1);
});
function initRemVissibility() {
	if(isLead()) $("#grunt-cont-"+sessGrunt.grunt_id+" .delGruntX").remove();
	for(var b in Pie.grunts) {
		var grunt = Pie.grunts[b];
		if(grunt.grunt_type < 3 && sessGrunt.grunt_type > 1) {
			$("#grunt-cont-"+grunt.grunt_id+" .delGruntX").remove();
		}
	}

}

function pctToDec() {
	
}

function decToPct(dec) {
	return (Math.round(((grunt.share.tbv/Pie.TBV)*100) * 100) / 100);
}

function isLead() {
	if(parseInt(sessGrunt.grunt_id) == parseInt(Pie.grunt_leader))
		return true;
	return false;
}

function initLegendColorBox() {
	for(d in Pie.grunts) {
		var grunt = Pie.grunts[d];
		var model = document.getElementById("colorBox-model");
		var node = model.cloneNode(true);

		if(grunt.status > 0) {
			if(!isLead()) continue;
			node.setAttribute("class", "colorBox popoverBuyout");
			$(node.getElementsByClassName("grunt-colorBox")[0]).addClass("infoglyph");
			node.getElementsByClassName("grunt-colorBox")[0].setAttribute("data-content", "You can buyout "+grunt.name+" in the summary tab");
			node.getElementsByClassName("grunt-colorBox")[0].setAttribute("data-placement", "left");
			node.getElementsByClassName("grunt-colorBox")[0].setAttribute("style", "float:left;margin-right:5px;margin-top:2px;");
		}
		else {
			node.setAttribute("class", "colorBox");
			node.getElementsByClassName("grunt-colorBox")[0].setAttribute("style", "background:"+grunt.color+";height:10px;width:10px;float:left;margin:5px;");
		}
		node.setAttribute("id", "");
		node.setAttribute("style", "float:left;");

		node.getElementsByClassName("colorBox-gname")[0].innerHTML = (grunt.name.length>8 ? grunt.name.substring(0,8)+".." : grunt.name);

		document.getElementById("chart-legend").appendChild(node);
	}
	$(".popoverBuyout").popover({animation:true, trigger:'hover'});
}

function reset() {
	$(".infoglyph2").popover('hide');
	remGruntTag = -1;
	$(".contribution-form-wrap input[type='text']").val("");
	$(".btn[data-toggle='popover']").popover('hide');
	if(activeSelect != null) {
		activeSelect.val('-1');
		// activeSelect.fadeToggle(100);
		activeSelect = null;
	}
	$(".contribution-form").css("display", "none");
	togglePopup();
}

function addNewGrunt(gname, gemail, fms, flag, accT) { //need AJAX
	// send ajax request to create new grunt and return id
	fms = parseVal(fms);
	if(gemail.indexOf("@") == -1 || fms <= 0) {
		return;
	}
	var f = 0;
	if(flag==true) f=1;
	var info = {share:{GHRR:0, tbv:0, fair_market_salary:fms}, name:gname, email:gemail, fid:Pie.id, fexec:f, accType:accT, lead:sessGrunt.name, pname:Pie.name};
	var grunt = null;
	var ret = 0;
	$.ajaxSetup({async: false});
	$.post(ajaxUrl, {action:"addGrunt", args:info}, function(data) {
		console.log(data);
		grunt = JSON.parse(data);

		if(grunt.aff_exists) ret=1;
		else {
			if(flag == false) {
				if(grunt.grunt_exists==true)
					ret = 0;
				else {
					executeAdd(grunt);
					ret = grunt;
				}
			} else {
				executeAdd(grunt);
				ret = grunt;
			}
		}
	});

	return ret;
}

function executeAdd(grunt) {
	Pie.grunts.push(grunt);

	var model = document.getElementById("grunt-cont-model");
	var node = model.cloneNode(true);
	node.setAttribute("id", "grunt-cont-"+grunt.gid);
	node.setAttribute("style", "url('view/images/user-128.jpg') no-repeat center center");
	node.setAttribute("tag", grunt.gid);
	node.getElementsByClassName("grunt-well")[0].setAttribute("id", "grunt-well-"+grunt.gid);
	node.getElementsByClassName("grunt-img")[0].setAttribute("style", "background:url('view/images/user-5.jpg') no-repeat center center;");
	node.getElementsByClassName("grunt-name")[0].innerHTML = grunt.name;
	node.getElementsByClassName("grunt-tbv")[0].innerHTML = grunt.share.tbv;
	node.getElementsByClassName("grunt-pct")[0].innerHTML = (Math.round(((grunt.share.tbv/Pie.TBV)*100) * 100) / 100) + "%";
	document.getElementById("grunts-cont").appendChild(node);
}

// options for grunt removal
var TERMINATE_GOOD = 1;
var TERMINATE_NOGOOD = 2;
var RESIGN_GOOD = 3;
var RESIGN_NOGOOD = 4;

// draft function, waiting for DB integration
function removeGrunt(gid, option) {
	var grunt = -1;
	for(k in Pie.grunts) {
		if(Pie.grunts[k].gid == gid)
			grunt = Pie.grunts[k];
	}
	if(grunt < 0) return;
	
	// ajax get all grunt's contribution
	var contribs; // for now

	$.post(ajaxUrl, {action:"getContributions", args:{gid:grunt.gid, fid:Pie.id}}, function(data) {
		
		// TODO: DELETE CONTRIBS

		// alert(data);
		contribs = JSON.parse(data);

		var noncashContribs = []; // MUST REMOVE FROM DB
		var nonCashTotalPoints = 0;
		var cashContribs = [];
		var cashTotalAmount = 0;

		console.log(contribs);
		for(var k=0 ; k<contribs.length ; k++) {
			if(contribs[k].details.type.toLowerCase() == "noncash") {
				noncashContribs.push(contribs[k]);
				nonCashTotalPoints += parseInt(contribs[k].details.tv);
			}
			else {
				cashContribs.push(contribs[k]);
				cashTotalAmount += parseFloat(contribs[k].details.amount);
			}
		}

		var totalDel = 0;
		switch(option) {
			case TERMINATE_GOOD:
			case RESIGN_NOGOOD:
				grunt.share.tbv = cashTotalAmount;
				grunt.status = cashTotalAmount;
				totalDel = nonCashTotalPoints+(cashTotalAmount*(cashx-1));
				break;
			case TERMINATE_NOGOOD:
			case RESIGN_GOOD:
				grunt.status = grunt.share.tbv;
				break;
			default: return;
		}
		Pie.TBV -= totalDel;
		// get well balance
		updateWellOnGruntDelete(grunt);

		disableGruntForBuyout(grunt);
		saveDOM();

		$.post(ajaxUrl, {action:"delContribs", args:{gid:grunt.gid, fid:Pie.id}}, function(data){
			if(grunt.status == 0) {
				$.post(ajaxUrl, {action:"removeGrunt", args:{id:grunt.affid}}, function(data) {
					init(false);
				});
			}
			location.reload();
		});
	});
}

function updateWellOnGruntDelete(grunt) {
	var gbal = 0;
	for(k in Pie.well.grunts) {
		if(Pie.well.grunts[k].gid == grunt.grunt_id) {
			var wbal = parseInt(Math.round(((Pie.well.grunts[k].pct * Pie.well.amount)*100) / 100).toFixed(0));
			grunt.status = parseInt(grunt.status)+wbal;
			Pie.well.grunts.splice(k,1);
			gbal = wbal;
			break;
		}
	}
	var finalWell = Pie.well.amount-gbal;
	for(k in Pie.well.grunts) {
		mem = Pie.well.grunts[k];
		var wbal = parseInt(Math.round(((mem.pct * Pie.well.amount)*100) / 100).toFixed(0));
		var pctNew = wbal/finalWell;
		mem.pct = pctNew;
	}
	Pie.well.amount = finalWell;
}

function disableGruntForBuyout(grunt) {
	var parentDiv = document.getElementById("grunt-cont-"+grunt.gid);
	$(parentDiv).fadeToggle(100);
	$(parentDiv).remove();
}

$("body").on("click", ".btn-buyout",function() {

	var container = $(this).parent().parent();
	var tag = container.attr("tag");
	var grunt = null;
	for(k in Pie.grunts) {
		if(Pie.grunts[k].gid == tag) {
			grunt = Pie.grunts[k];
			Pie.grunts.splice(k, 1);
		}
	}
	if(grunt == null) return;

	$(".boGrunt-name").html(grunt.name);
	$("#boGrunt-amount").html(grunt.status);

	$("#boGrunt-wrap").modal();
	return;

	var container = $(this).parent().parent();
	var tag = container.attr("tag");
	var grunt = null;
	for(k in Pie.grunts) {
		if(Pie.grunts[k].gid == tag) {
			grunt = Pie.grunts[k];
			Pie.grunts.splice(k, 1);
		}
	}
	if(grunt == null) return;
	$.post(ajaxUrl, {action:"removeGrunt", args:{id:grunt.affid}}, function(data) {
		Pie.TBV -= grunt.share.tbv;
		inactiveGruntsLength--;
	});
	container.animate({opacity:0},150,function(){container.remove();location.reload();});
	// if(inactiveGruntsLength < 1)
		// $("#sum-indicator").fadeToggle();
	saveDOM();
});

function saveDOM() {
	// TODO: ADD PIE.PENDINGALERTS and output alerts
	// $.ajaxSetup({async: true});

	for(h in Pie.grunts) {
		if(Pie.grunts[h].alerts.length) {
			Pie.grunts[h].alerts = {alerts:Pie.grunts[h].alerts};
		} else {
			if(Pie.grunts[h].alerts.alerts.length) {
				Pie.grunts[h].alerts = {alerts:Pie.grunts[h].alerts.alerts};
			} else {
				Pie.grunts[h].alerts = {alerts:["null"]};
			}
		}
	}

	$.post(ajaxUrl, {action:"save", args:Pie}, function(data){
		console.log("TV earned here"+data);
		$.post(ajaxUrl, {action:"sendNotifs", args:{alerts:pendingAlerts}}, function(g) {
			console.log(g);
			console.log(pendingAlerts);
			pendingAlerts = [];
		});
	});

}

// navigation
$(".header ul a[role='header-menu-item'], #navitem-reports, #navitem-summary, #navitem-contribs").click(function(){
	var link = $(this).html().toLowerCase();
	if(link == "help") return;
	var div = $("#"+link+"-wrap");
	console.log(div);
	try {
		$(".page-wrap[active='true']").stop().fadeToggle(100, function(){
			$(this).attr("active", "false");
			div.stop().fadeToggle(100);
			div.attr("active", "true");
		});
	} catch(err) {}

});

function fetchContributions() {
	$.ajaxSetup({async: false});
	$.post(ajaxUrl, {action:"getContributions", args:{gid:0, fid:Pie.id}}, function(data) {
		console.log(data);
		contributions = JSON.parse(data);
	});

	if(sessGrunt.grunt_type == 3) {
		var nContrib = [];
		for(d in contributions) {
			if(sessGrunt.grunt_id == contributions[d].grunt_id)
				nContrib.push(contributions[d]);
		}
		contributions = nContrib;
	}
}

$(".btn-saveSettings").click(function(){
	var btn = $(this);
	btn.button('loading');
	saveSettings();
	location.reload();
});
function saveSettings() {
	var form = $("#settings-form").serializeArray();
	var alertsForm = $("#alertsForm").serializeArray();
	var projects = $(".tm-input").tagsManager('tags');

	Pie.name = form[0].value;

	var fundSettings = Pie.settings.fund;
	fundSettings.currency = form[1].value;
	fundSettings.noncashx = form[2].value;
	fundSettings.cashx = form[3].value;
	fundSettings.commission_rate = parseFloat(form[4].value)/100;
	fundSettings.royalty_rate = parseFloat(form[5].value)/100;
	fundSettings.fair_market_salary = parseInt(form[6].value);
	fundSettings.Apct = parseFloat(form[7].value)/100;
	fundSettings.A = form[8].value;
	fundSettings.Bpct = parseFloat(form[9].value)/100;

	for(h in Pie.grunts) {
		Pie.grunts[h].alerts = (Pie.grunts[h].alerts.length) ? {alerts:Pie.grunts[h].alerts} : {alerts:["null"]};
	}

	var sess = getSessgrunt();
	var arr = [];
	if(sess.grunt_id) {
		for(f in alertsForm) {
			arr.push(alertsForm[f].name);
		}
		sess.alerts = (arr.length) ? {alerts:arr} : {alerts:["null"]};
	}


	Pie.projects = projects;
	saveDOM();
	location.reload();
}

function initAlertsSettings() {
	for(l in sessGrunt.alerts) {
		try {
			$("input[name='"+sessGrunt.alerts[l]+"']")[0].checked=true;
		} catch(err) {};
	}
}

function initTblFMS() {
	var tbl = document.getElementById("tbl-fms");
	var thmodel = document.getElementById("th-fms-model");
	var thclone = thmodel.cloneNode(true);
	tbl.appendChild(thclone);
	for(l in Pie.grunts) {
		var trmodel = document.getElementById("tr-fms-row-model");
		var trclone = trmodel.cloneNode(true);
			trclone.setAttribute("class", "");
		var trname = trclone.getElementsByClassName("tr-fms-row-name")[0];
			trname.setAttribute("tag", Pie.grunts[l].grunt_id);
			trname.innerHTML = Pie.grunts[l].name;

		var trdata = trclone.getElementsByClassName("tr-fms-row-fmsdata")[0];
			trdata.setAttribute("tag", Pie.grunts[l].share.fair_market_salary);
			trdata.getElementsByClassName("tr-span-fms")[0].innerHTML = parseInt(Pie.grunts[l].share.fair_market_salary).toLocaleString();

		tbl.appendChild(trclone);
	}
}

function getGruntTypeString(id) {
	var tmp = ["CEO", "Executive", "Adviser", "Employee"];
	return tmp[id];
}

function initTMStab() {
	var model = document.getElementById("settings-grunt-cont-model");
	var container = document.getElementById("settings-gruntswrap-tms");
	for(var i in Pie.grunts) {
		var grunt = Pie.grunts[i];

		var node = model.cloneNode(true);
		node.setAttribute("style", "");
		$(node.getElementsByClassName("grunt-img")[0]).css("background", "url('"+grunt.image+"')");
		node.getElementsByClassName("grunt-name")[0].innerHTML = grunt.name;
		node.getElementsByClassName("grunt-jobtitle")[0].innerHTML = grunt.jobtitle;

		var gfms = node.getElementsByClassName("grunt-fms")[0];
		$(gfms).parent().attr("tag", parseInt(grunt.share.fair_market_salary));
		gfms.innerHTML = parseInt(grunt.share.fair_market_salary).toLocaleString();

		if(parseInt(Pie.grunt_leader) == grunt.grunt_id) {
			$(node).find(".a-remove").remove();
			$(node).find(".li-set-accType").remove();
		} else {
			$($(node).find(".li-set-accType select")[0]).val(grunt.grunt_type);
			$($(node).find(".li-set-accType span")[0]).html(getGruntTypeString(grunt.grunt_type));
			if(!isLead())
				$(node).find(".li-set-accType").remove();
		}
		node.getElementsByClassName("actions-wrap")[0].setAttribute("tag", grunt.grunt_id);

		container.appendChild(node);
	}	
}

function getGruntById(id) {
	for(o in Pie.grunts) {
		if(Pie.grunts[o].grunt_id == id) 
			return Pie.grunts[o];
	}
	return 0;
}


function getSessgrunt() {
	for(i in Pie.grunts) {
		if(sessGrunt.grunt_id == Pie.grunts[i].grunt_id) return Pie.grunts[i];
	}
	return -1;
}

function togglePopup() {
	$(".popup").fadeToggle(150);
}

function initContribTab() {
	document.getElementById("contrib-list-table").innerHTML = "";
	var thmodel = document.getElementById("contribTab-tr-th");
	var th = thmodel.cloneNode(true);
	document.getElementById("contrib-list-table").appendChild(th);
	for(k in contributions) {
		if(contributions[k].name) {
			var gname = contributions[k].name.toLowerCase();
			delete contributions[k].name;
			contributions[k].details.name = gname;
		}
		var model = document.getElementById("contribTab-tr-model");
		var node = model.cloneNode(true);

		node.setAttribute("tag", contributions[k].id);
		node.setAttribute("flag", parseInt(contributions[k].flag));

		var desc = (contributions[k].details.desc ? contributions[k].details.desc :	"");
		var descclass = "";
		if(desc.length > 40) {
			descclass = "pop";
			desc = desc.substring(0,40)+"...";
		}
		if(desc.indexOf("<br/>") > 0) {
			descclass = "pop";
			desc = desc.substring(0,desc.indexOf("<br/>"))+"...";
		}

		node.getElementsByClassName("contribTab-grunt-name")[0].innerHTML = contributions[k].details.name;
		node.getElementsByClassName("contribTab-contri")[0].innerHTML = getContribName(parseInt(contributions[k].details.contrib));
		node.getElementsByClassName("contribTab-proj")[0].innerHTML = "<span class='editable editable-project'>"+(contributions[k].details.project == "ZZZ" ? "" : (contributions[k].details.project ? contributions[k].details.project : ""))+"</span>";

		var valstring = parseFloat(contributions[k].details.amount);
		if(valstring % 1 != 0)
			valstring.toFixed(2);
		node.getElementsByClassName("contribTab-value")[0].innerHTML = "<span class='editable editable-value'>"+valstring+"</span>";

		node.getElementsByClassName("contribTab-date")[0].innerHTML = "<span class='editable editable-date'>"+(contributions[k].details.date ? contributions[k].details.date : "")+"</span>";
		node.getElementsByClassName("contribTab-desc")[0].innerHTML = "<span class='editable editable-text "+descclass+"' data-placement='top' data-content='"+contributions[k].details.desc+"'>"+desc+"</span>";

		var tvstring = parseFloat(contributions[k].details.tv);
		if(tvstring % 1 != 0)
			tvstring.toFixed(2);
		node.getElementsByClassName("contribTab-points")[0].innerHTML = tvstring.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");

		if(parseInt(contributions[k].flag) == 1) {
			$(node).css("display", "none");
			$(node).addClass("deleted");
			$(node).addClass("danger");
		}

		document.getElementById("contrib-list-table").appendChild(node);

		if(!contributions[k].details.project)
			contributions[k].details.project = "ZZZ";
	}	

	// $("#contributions-wrap").html(JSON.stringify(contributions));
	$(".pop").popover({animation:true,trigger:'hover',html:true});
}

function getContribName(id) {
	switch(id) {
		case CONTRIB_TIME: return "Time"; break;
		case CONTRIB_EXPENSES: return "Cash"; break;
		case CONTRIB_SUPPLIES: return "Equipment and Supplies"; break;
		case CONTRIB_EQUIPMENT: return "Equipment and Supplies"; break;
		case CONTRIB_SALES: return "Sales"; break;
		case CONTRIB_ROYALTY: return "Royalties"; break;
		case CONTRIB_FACILITIES: return "Facilities"; break;
		case CONTRIB_OTHER : return "Other"; break;
		default: id;
	}
}

var gruntFull;
function initBillInfo() {
	$.ajaxSetup({async: true});

	$.post(ajaxUrl, {action:"getBillingInfo", args:{grunt:sessGrunt.grunt_id}}, function(data) {
		console.log(data);
		var bi = JSON.parse(data);
		gruntFull = bi;

		$("#pset-fname").val(bi.first_name);
		$("#pset-lname").val(bi.last_name);
		$("#pset-ad1").val(bi.address_1);
		$("#pset-ad2").val(bi.address_2);
		$("#pset-city").val(bi.city);
		$("#pset-zip").val(bi.zip);
		$("#pset-tel").val(bi.phone);

	});
	$.ajaxSetup({async: false});
}

$("#btn-saveBilling").click(function() {
	saveBilling();
});

function saveBilling() {
	var grunt = getGruntById(sessGrunt.grunt_id);
	grunt.first_name = $("#pset-fname").val();
	grunt.last_name = $("#pset-lname").val();
	grunt.address_1 = $("#pset-ad1").val();
	grunt.address_2 = $("#pset-ad2").val();
	grunt.city = $("#pset-city").val();
	grunt.zip = $("#pset-zip").val();
	grunt.phone = $("#pset-tel").val();

	// console.log(grunt);

	saveDOM();
	location.reload();
}

function initSum() {

	var table = document.getElementById("contrib-sum-tb");
	$("#contrib-sum-tb .table-record").remove();
	// table.setAttribute("class", "table");
	// table.setAttribute("style", "width:100%;text-align:center;");

	// var modelth = document.getElementById("contrib-tr-th");
	// var cloneth = modelth.cloneNode(true);
	// cloneth.setAttribute("id", "");
	// table.appendChild(cloneth);
	for(k in Pie.grunts) {
		// add grunt to table

		var model = document.getElementById("contrib-tr-model");
		var node = model.cloneNode(true);

		var timeTotal = 0;
		var cashTotal = 0;
		var ensTotal = 0;
		var commTotal = 0;
		var intelTotal = 0;
		var faciTotal = 0;

		for(l in contributions) {
			var con = contributions[l];
			if(parseInt(con.flag) == 1) continue;
			var conId = parseInt(con.details.contrib);
			if( Pie.grunts[k].gid == parseInt(contributions[l].grunt_id) ) {
				var ctv = parseInt(con.details.tv);
				// alert(ctv);
				switch(conId) {
					case CONTRIB_TIME: 
										timeTotal += ctv;
										break;
					case CONTRIB_EXPENSES: 
										cashTotal += ctv;
										break;
					case CONTRIB_SUPPLIES: 
										ensTotal += ctv;
										break;
					case CONTRIB_EQUIPMENT: 
										ensTotal += ctv;
										break;
					case CONTRIB_SALES: 
										commTotal += ctv;
										break;
					case CONTRIB_ROYALTY: 
										intelTotal += ctv;
										break;
					case CONTRIB_FACILITIES: 
										faciTotal += ctv;
										break;
					case CONTRIB_OTHER: 
										timeTotal += ctv;
										break;
				}
			}
		}

		node.setAttribute("id", "");
		node.setAttribute("tag", Pie.grunts[k].grunt_id);
		node.setAttribute("class", "table-record");

		var nodeG = node.getElementsByClassName("contrib-grunt")[0];
		nodeG.getElementsByClassName("contrib-grunt-name")[0].innerHTML = Pie.grunts[k].name;

		// node.getElementsByClassName("grunt-img")[0].setAttribute("style", "background:url('"+Pie.grunts[k].image+"') no-repeat center center;");

		if(parseInt(Pie.grunts[k].status) > 0) {
			node.setAttribute("class", "table-record danger");
			var btn_content = "<button class='btn btn-danger btn-buyout pop' data-toggle='popover' data-placement='left' data-content='<span style=\"color:red;font-weight:800;\">Warning:</span> this action cannot be undone'>Buyout "+Pie.grunts[k].name+" for <br />"+Pie.settings.fund.currency+" "+parseInt(Pie.grunts[k].status).toLocaleString()+"</button>";
			node.getElementsByClassName("contrib-slice")[0].innerHTML = btn_content;
		} else {
			node.getElementsByClassName("contrib-time")[0].innerHTML = timeTotal.toLocaleString();
			node.getElementsByClassName("contrib-cash")[0].innerHTML = cashTotal.toLocaleString();
			node.getElementsByClassName("contrib-ens")[0].innerHTML = ensTotal.toLocaleString();
			node.getElementsByClassName("contrib-faci")[0].innerHTML = faciTotal.toLocaleString();
			node.getElementsByClassName("contrib-intel")[0].innerHTML = intelTotal.toLocaleString();
			node.getElementsByClassName("contrib-comm")[0].innerHTML = commTotal.toLocaleString();

			var contribTotal = parseInt(timeTotal+cashTotal+ensTotal+commTotal+intelTotal+faciTotal);
			node.getElementsByClassName("contrib-total")[0].innerHTML = contribTotal.toLocaleString();
			node.getElementsByClassName("contrib-slice")[0].innerHTML = (Pie.grunts[k].share.tbv > 0 ? (Math.round(((Pie.grunts[k].share.tbv/Pie.TBV)*100) * 100) / 100) : 0 )+"%";
		}
		table.appendChild(node);
	}
	table.setAttribute("class", "table");
	// document.getElementById("sum-table").appendChild(table);
	$(".pop").popover({animation:true, html:true, trigger:'hover'});
}

var sc_contType = 0;
var sc_tv = 1;
var sc_grunt = 2;
var sc_proj = 3;
var sc_month = 4;

function sortContrib(sortType) {

	var comp = null;

	// month
	if(sortType == 4) {
	  var length = contributions.length - 1;
	  do {
	    var swapped = false;
	    for(var i = 0; i < length; ++i) {
		  if(contributions[i].details.date == undefined || contributions[i+1].details.date == undefined) continue;
	      if (contributions[i].details.date.split("-")[1] > contributions[i+1].details.date.split("-")[1]) {
	        var temp = contributions[i];
	        contributions[i] = contributions[i+1];
	        contributions[i+1] = temp;
	        swapped = true;
	      }
	    }
	  }
	  while(swapped == true)
	}
	

	switch(sortType) {
		case sc_contType: comp="contrib"; break;
		case sc_tv: comp="tv"; break;
		case sc_grunt: comp="name"; break;
		case sc_proj: comp="project"; break;
		default: return;
	}

	for(var i=0 ; i<contributions.length-1 ; i++) {
		for(var p=0 ; p<contributions.length-1 ; p++) {
			if(!parseInt(contributions[p].details[comp])) {
				if(contributions[p].details[comp] > contributions[p+1].details[comp]) {
					var temp = contributions[p];
					contributions[p] = contributions[p+1];
					contributions[p+1] = temp;
				}
			} else {
				if(parseInt(contributions[p].details[comp]) > parseInt(contributions[p+1].details[comp])) {
					var temp = contributions[p];
					contributions[p] = contributions[p+1];
					contributions[p+1] = temp;
				}
			}
		}
	}

}
function delContrib() {
	$($(".cbox-contribs:checked")[i]).parent().parent().fadeToggle(300);
	var len = $(".cbox-contribs:checked").length;

	var cids = [];
	for(var i=0; i<len ; i++) {
		conid = parseInt($($(".cbox-contribs:checked")[i]).parent().parent().attr("tag"));
		if(!conid) continue;
		cids.push(conid);
		var con = getContribByID(conid);
		var grunt = getGruntById(parseInt(con.grunt_id));
		grunt.share.tbv = parseInt(grunt.share.tbv) - parseInt(con.details.tv);
		Pie.TBV = parseInt(Pie.TBV) - parseInt(con.details.tv);
	}

	$.ajaxSetup({async: false});
	$.post(ajaxUrl, {action:"delContribs", args:{cid:cids,fid:Pie.id}}, function(data) {
		// alert(data);
	});

	$(".cbox-contribs:checked").parent().parent().fadeToggle(100);
	setTimeout(function() {
		init(false);
	},120);

	saveDOM();
}

function editContrib() {
	var con = getContribByID(contextTrigger);

	var form = document.getElementById("editContrib-wrap");
	if(con.details.project != "ZZZ") {
		$(form.getElementsByClassName("select-project")[0]).parent().css("display", "block");
		$(form.getElementsByClassName("select-project")[0]).val(con.details.project);
	} else {
		$(form.getElementsByClassName("select-project")[0]).parent().css("display", "none");
	}
	$("#ul-contrib-details").html("");
	for( var g in con.details ) {
		var det = con.details[g];
		var li = document.createElement("li");
		li.setAttribute("style", "list-style-type:none;");

		var name = null;
		var val = null;
		switch(g) {
			case "contrib": name = "Contribution Type";
							val = getContribName(parseInt(det));
							break;
			case "amount": name = (parseInt(con.details.contrib)==1) ? "Value (Hours)" : "Value";
							val = det;
							break;
			case "reim": name = "Cash Reimbursement";
							val = det;
							break;
		}

		if(name && val)
			li.innerHTML = name+" : "+val;
		$("#ul-contrib-details").append(li);
	}

	$(form.getElementsByClassName("edit-input-date")[0]).val(new Date().thisDateInputValue(con.details.date));
	$(form.getElementsByClassName("edit-input-desc")).html(con.details.desc);

	$("#editContrib-wrap").css("display", "block");
	togglePopup();
}

$("#btn-submitEditCont").click(function() {
	var con = getContribByID(contextTrigger);
	console.log(con);
	var proj = $("#select-project-dd").val();
	var date = $("#edit-input-date").val();
	var desc = $("#edit-input-desc").val();
	if(proj != "-1")
		con.details.project = proj;
	con.details.date = date;
	con.details.desc = desc;
	$.ajaxSetup({async: false});
	$.post(ajaxUrl, {action:"updateContrib", args:{contrib:con}}, function(data){
		console.log(data);
	});	
	$("#contrib-list-table").html("");
	fetchContributions();
	initContribTab();
	reset();
});

function getContribByID(id) {
	for(k in contributions) {
		if(parseInt(contributions[k].id) == id)
			return contributions[k];
	}
}

$("#context-del").click(function() {
	delContrib();
	var element = $("#contrib-contextMenu");	
    element.fadeToggle(50);
	contribcontextshow = false;
});

$("#context-edit").click(function() {
	if($(this).hasClass("inactive")) return;
	editContrib();
	var element = $("#contrib-contextMenu");	
    element.fadeToggle(50);
	contribcontextshow = false;
});

$("#btn-confDelFund").click(function(e) {
	var delfundcdown = 3;
	e.preventDefault();
	var form = $(this).parent();
	$("#delFund-err").html("");
	var pass = $("#input-delfund-upass").val();
	$.ajaxSetup({async: true});
	var md;
	loadSpinner("We'll be right back...");
	$.post(ajaxUrl, {action:"getMD5", args:{string:pass}}, function(data) {
		console.log(data);
		md = data;
		console.log("gruntFull: "+gruntFull.password);

		var feedback = $("#fb-delete").val();
		if(md == gruntFull.password){
			$.post(ajaxUrl, {action:"delFund", args:{pid:Pie.id,sid:Pie.subscriptionID,gid:Pie.glead_object.id, pname:Pie.name, msg:feedback, email:Pie.glead_object.email}}, function(data2) {
				console.log(data2);
				var nf = document.createElement("form");
				nf.setAttribute("method", "post");
				var inp = document.createElement("input");
				inp.setAttribute("type", "hidden");
				inp.setAttribute("name", "delfund");
				inp.setAttribute("value", "true");
				$("body")[0].appendChild(nf);
				nf.appendChild(inp);
				hideSpinner();
				$(form).css("text-align", "center");
				form.html("Your Pie was successfully deleted.<br/>You will now be redirected in <span id='span-cd-delfund'>3</span>");
				setInterval(function() {
					$("body #span-cd-delfund").html(delfundcdown);
					delfundcdown--;
					if(delfundcdown <= 0)
						nf.submit();
				}, 1000);
			});
		}
		else
			$("#delFund-err").html("Invalid Password");
		hideSpinner();
	});
});

$("#btn-confRFund").click(function(e) {
	e.preventDefault();
	var form = $(this).parent();
	$("#rFund-err").html("");
	var pass = $("#input-rfund-upass").val();
	$.ajaxSetup({async: false});
	var md;
	$.post(ajaxUrl, {action:"getMD5", args:{string:pass}}, function(data) {
		console.log(data);
		md = data;
	});
	if(md == gruntFull.password){

		for(p in Pie.grunts) {
			Pie.grunts[p].share.tbv = 0;
		}

		Pie.TBV = 0;
		Pie.well.amount = 0;
		Pie.well.grunts.length = 0;
		saveDOM();

		var r = {pid:Pie.id,lead:sessGrunt.grunt_id};
		if($("#rfund-delg:checked").length > 0) r.remG = 1;
		$.post(ajaxUrl, {action:"resetFund", args:r}, function(data) {
			form.submit();
		});
	}
	else
		$("#rFund-err").html("Invalid Password");
});

var contribcontextshow = false;
var contextTrigger;
function contribContextMenu(e) {
	e.preventDefault();
	var element = $("#contrib-contextMenu");	
	// if(isLead()) {
		if(!contribcontextshow) {
			contextTrigger = ($(e.target).parent().attr("tag") > -1 ? $(e.target).parent().attr("tag") : $(e.target).parent().parent().attr("tag"));
		    
			var con = getContribByID(contextTrigger);
			if(parseInt(con.flag) == 1) return;

		    element.fadeToggle(50);
		    element.css("top", e.pageY);
		    element.css("left", e.pageX);
		    element.trigger("focus");
		    contribcontextshow = true;

		    var a = $(e.target).parent().find(".cbox-contribs");
		    var b = a.length;
		    if(b < 1) a = $(e.target).parent().parent().find(".cbox-contribs");
		   	a.prop('checked', true);
		   	a.change();
		} else {
		    element.fadeToggle(50);
			contribcontextshow = false;
			contextTrigger = -1;
		}
	// }
}

$(document).mouseup(function(e) {
	// alert(e.pageY+"-"+parseInt($("#contrib-contextMenu").css("top")));
	if(e.pageY > parseInt($("#contrib-contextMenu").css("top")) && e.pageY < parseInt($("#contrib-contextMenu").css("top"))+62) {
		// alert(1);
		if(e.pageX > parseInt($("#contrib-contextMenu").css("left")) && e.pageX < parseInt($("#contrib-contextMenu").css("left"))+100) {
			// alert(2);
			return;
		}
	}
	if(e.which == 1 && contribcontextshow)
		contribContextMenu(e);
});

$("body").on("click", ".table-record", function() {
	var cbox = $(this).find(".cbox-contribs");
	if(cbox.length > 0) { 
		if(cbox.is(":checked")) cbox.prop('checked', false); 
		else cbox.prop('checked', true); 
		cbox.change(); 
	}
});

$("#contribSort-selector").change(function() {
	sortContrib(parseInt($(this).val()));
	initContribTab();
});

$(".grunts-cont").on("mouseenter", ".grunt-img", function() {
	$(this).find(".grunt-det-popover").stop().css("display", "block").animate({opacity:1}, 200);
});

$(".grunts-cont").on("mouseleave", ".grunt-img", function() {
	if(!$(this).find("select").is(":focus"))
		$(this).find(".grunt-det-popover").stop().animate({opacity:0}, 200).css("display", "none");
});

$(".grunts-cont").on("focusout", "select", function() {
	// $(this).parent().stop().animate({opacity:0}, 200).css("display", "none");
});

$(".popup").click(function(event) {
	// reset();
	if(event) {
		if(event.target.id == "popup" ||event.target.id == "credits-wrap")
			reset();
	}
});

$("#head-fundName a").click(function() {
	var fundID = $(this).attr("tag");
	$("#form-fundSwitch input[type='hidden']").val(fundID);
	$("#form-fundSwitch").submit();
});

$("#head-createNewFund").click(function() {
	$("#newFund-wrap").css("display", "block");
	togglePopup();
});

$("#btn-cfund").click(function(e) {
	loadSpinner("Please wait...");
	if(Pie.glead_object.BT_cutomerID == "0") return;
	var dat = $($("#newFund-wrap form")[0]).serializeArray();
	var info = {action:"nFund", args:{glead:dat[0].value, fundName:dat[1].value,subscription:dat[2].value, extra:Pie.glead_object}};

	var md;
	$.post(ajaxUrl, {action:"getMD5", args:{string:dat[3].value}}, function(data) {
		console.log(data);
		var dat = $($("#newFund-wrap form")[0]).serializeArray();
		var info = {action:"nFund", args:{glead:dat[0].value, fundName:dat[1].value,subscription:dat[2].value, extra:Pie.glead_object}};

		md = data;
		if(info.args.glead == "") {
			$("#cfname").addClass("has-error");
			e.preventDefault();
			hideSpinner();
			return;
		}

		if(md != Pie.glead_object.password) {
			$("#cfpass-wrap").addClass("has-error");
			$("#cf-passconf").focus();
			e.preventDefault();	
			hideSpinner();
			return;
		}

		e.preventDefault();	
		loadSpinner("Baking your Pie...");
		if(gruntFull.BT_cutomerID != "0") {
			var dat = $($("#newFund-wrap form")[0]).serializeArray();
			var info = {action:"nFund", args:{glead:dat[0].value, fundName:dat[1].value,subscription:dat[2].value, extra:gruntFull}};
			$.post(ajaxUrl, info, function(fid) {
				console.log(fid);
				var nform = document.createElement("form");
				nform.setAttribute("method", "post");
				var inpt = document.createElement("input");
				inpt.setAttribute("name", "fund_id");
				inpt.setAttribute("type", "hidden");
				inpt.setAttribute("value", parseInt(fid));
				$("body")[0].appendChild(nform);
				nform.appendChild(inpt);
				nform.submit();
			});
		} else 
			hideSpinner();
	});


});

$(".glyphicon").css("cursor", "pointer");
$("input[type='text']").addClass("form-control");
$("input[type='password']").addClass("form-control");
$("input[type='date']").addClass("form-control");
$("select").addClass("form-control");
$("textarea").addClass("form-control");

$(function(){
	var h = parseInt($(window).height() * 0.7);
	$(".scroll").each(function() {
		if($(this).parent().parent().parent().hasClass("contribution-form"))
			$(this).parent().parent().parent().css("height", h+'px');	
	});

    $('.scroll').slimScroll({
        height: h+'px'
    });
});


// Context Menus
// if (document.addEventListener) {
//     document.addEventListener('contextmenu', function(e) {
//         e.preventDefault();
//     }, false);
// } else {
//     document.attachEvent('oncontextmenu', function(e) {
//         window.event.returnValue = false;
//     });
// }

if ($("#contributions-wrap").addEventListener) {
    $("#contributions-wrap").addEventListener('contextmenu', function(e) {
        contribContextMenu(e);
        $($("#contrib-list-table tr")[1]).popover('hide');
        if($(".cbox-contribs:checked").length > 1)
        	$("#context-edit").addClass("inactive");
        else 
        	$("#context-edit").removeClass("inactive");
        e.preventDefault();
    }, false);
} else {
    $('body').on('contextmenu', '#contributions-wrap td', function(e) {
        contribContextMenu(e);
        $($("#contrib-list-table tr")[1]).popover('hide');
        if($(".cbox-contribs:checked").length > 1)
        	$("#context-edit").addClass("inactive");
        else 
        	$("#context-edit").removeClass("inactive");
        window.event.returnValue = false;
        return false;
    });
}

$(document).ready(function() {
	$(".pop").popover({animation:true, html:true, trigger:'hover'});
	$('.infoglyph').popover({
	  trigger: 'hover',
	  animation: true,
	  html:true
	})
});

// HELP

$("a[role='menuitem']").click(function() {
	switch($(this).html()) {
		case "Edit Contributions" :
			$("#navitem-contribs").click();
			setTimeout(function(){
				$($("#contrib-list-table tr[flag=0]")[0]).popover(
										{content:'Right-click on a contribution<br/><br/><a class="clickable" onclick="$($(\'#contrib-list-table tr[flag=0]\')[0]).popover(\'hide\');" style="color:red;"><span class="glyphicon glyphicon-remove-sign"></span> Dismiss</a>',
										placement:'bottom',
										trigger: 'manual',
										title:'Helper',
										html:true});
				$($("#contrib-list-table tr[flag=0]")[0]).popover('show');
			}, 150);
			break;
		case "Delete Contributions" :
			$("#navitem-contribs").click();
			setTimeout(function(){
				$($("#contrib-list-table tr[flag=0]")[0]).popover(
										{content:'Right-click on a contribution<br/><br/><a class="clickable" onclick="$($(\'#contrib-list-table tr[flag=0]\')[0]).popover(\'hide\');" style="color:red;"><span class="glyphicon glyphicon-remove-sign"></span> Dismiss</a>',
										placement:'bottom',
										trigger: 'manual',
										title:'Helper',
										html:true});
				$($("#contrib-list-table tr[flag=0]")[0]).popover('show');
			}, 150);
			break;
		case "Edit Salaries" :
			$("#navitem-settings").click();
			setTimeout(function(){
				$("#settings-tms").popover(
											{content:'Go to Team Members Settings<br/><br/><a class="clickable" onclick="$($(\'#settings-tms\')).popover(\'hide\');" style="color:red;"><span class="glyphicon glyphicon-remove-sign"></span> Dismiss</a>',
											placement:'bottom',
											trigger: 'manual',
											title:'Helper',
											html:true});
				$("#settings-tms").popover('show');
				$("#settings-tms").click(function() {
					$("#settings-tms").popover('hide');
					setTimeout(function() {
						$($(".btn-editFMS")[1]).popover(
											{content:'Click on the edit icon<br/><br/><a class="clickable" onclick="$($(\'.btn-editFMS\')[1]).popover(\'hide\');" style="color:red;"><span class="glyphicon glyphicon-remove-sign"></span> Dismiss</a>',
											placement:'right',
											trigger: 'manual',
											title:'Helper',
											html:true});
						$($(".btn-editFMS")[1]).popover('show');
					},150);
				});
			}, 150);
			break;
		case "Remove Members" :
			$("#navitem-home").click();
			setTimeout(function() {
				$($(".a-remove:visible")[0]).popover(
								{content:'This icon removes this member. <br/><br/><a class="clickable" onclick="$($(\'.a-remove:visible\')[0]).popover(\'hide\');" style="color:red;"><span class="glyphicon glyphicon-remove-sign"></span> Dismiss</a>',
								placement:'top',
								trigger: 'manual',
								title:'Helper',
								html:true});
				$($(".a-remove:visible")[0]).popover('show');
			},150);
			break;
		case "Personal Settings":
			$("#navitem-settings").click();
			$("#settings-personal strong").click();
			break;
		case "Pie Settings":
			$("#navitem-settings").click();
			$("#settings-pie strong").click();
			break;
		case "Payment Settings":
			$("#navitem-settings").click();
			$("#settings-payment strong").click();
			break;
		case "Team Members":
			$("#navitem-settings").click();
			$("#settings-tms strong").click();
			break;
		case "Cancel Subscription":
			$("#navitem-settings").click();
			$("#settings-cancel strong").click();
			break;
		case "Alerts":
			$("#navitem-settings").click();
			$("#settings-alerts strong").click();
			break;
	}	
});

// clean up blank infos
$(".glyphicon-info-sign[data-content^='info.']").hide();

$(".infoglyph2").popover({html:true, trigger:'manual', placement:'bottom', title:'Helper', container:'.popup'});
$("body").on("click", ".infoglyph2", function() {
	if ($('.popover:visible').length){
		$(".infoglyph2").popover('hide');
	} else {
		$(this).popover('show');
		$(".popover-content").slimScroll({
	        height: '350px'
	    });
	}
});
$(".infoglyphrem").popover({html:true, trigger:'manual', placement:'bottom', title:'Helper', container:'.popup'});
$("body").on("click", ".infoglyphrem", function() {
	if ($('.popover:visible').length){
		$(".infoglyphrem").popover('hide');
	} else {
		$(this).popover('show');
		$(".popover-content").slimScroll({
	        height: '182px'
	    });
	}
});

function dismissResetPop() {
	$('#tbv-wrap').popover('hide');
}

function goToReset() {
	dismissResetPop();
	$("#settings-pie strong").click();
	$("#navitem-settings").click();
	setTimeout(function() {
		setTimeout(function() {
			var $target = $('html,body'); 
			$target.animate({scrollTop: $target.height()}, 1000);
		}, 150);
	}, 200);
}

$(document).ready(function() {
	if(Pie.TBV < 0) {
		$('#tbv-wrap').popover({html:true,placement:'left'});
		$('#tbv-wrap').popover('show');
		return;
	}
	var tot = 0;
	for(u in Pie.grunts) {
		tot += Pie.grunts[u].share.tbv;
	}
	if((Pie.TBV > tot+3 || Pie.TBV < tot-3) && isLead()) {
		$('#tbv-wrap').popover({html:true,placement:'left'});
		// $('#tbv-wrap').popover('show');
		return;
	}
});

$("body").on("click", "#btn-cupdate", function() {
	$("#cUpdate-wrap").css("display", "block");
	togglePopup();
});

// last logged in
if(Pie) {
	var fid = Pie.id;
	var gid = sessGrunt.grunt_id;
	var t = new Date();
	var tstring = t.getFullYear()+"-"+(t.getMonth()+1)+"-"+t.getDate()+" "+t.getHours()+":"+t.getMinutes()+":"+t.getSeconds();
	jQuery.post(ajaxUrl, {action:"addLogDate", args:{ldate:tstring, grunt:gid, fund:fid}}, function(data) {
	    console.log(data);
	});
}

$("#btn-dl-contribCsv").click(function() {
	loadSpinner("Preparing your file...");
	setTimeout(function() {
		downloadContribCSV();
	}, 500);
});
function downloadContribCSV() {
	var cTmp = contributions;
	var env = [];
	for(var h in cTmp) {
		var tmp = {};
		var con = cTmp[h];
		var ctbutor = getGruntById(con.grunt_id);
		tmp.id = con.id;
		tmp.grunt = ctbutor.name;
		tmp.contrib = getContribName(parseInt(con.details.contrib));
		tmp.amount = con.details.amount;
		tmp.reim = con.details.reim;
		tmp.date = con.details.date;
		tmp.project = con.details.project == "ZZZ" ? "" : con.details.project;
		tmp.desc = con.details.desc;
		tmp.tv = con.details.tv;
		tmp.flag = con.flag == 0 ? "" : "deleted";

		env.push(tmp);
	}

	$.ajaxSetup({async:true});
    $.post(ajaxUrl, {action:"createCSV", args:{fname:sessGrunt.grunt_id, args:env}}, function(data) {
    	// alert(data);

	    var hiddenIFrameID = 'hiddenDownloader',
	        iframe = document.getElementById(hiddenIFrameID);
	    if (iframe === null) {
	        iframe = document.createElement('iframe');
	        iframe.id = hiddenIFrameID;
	        iframe.style.display = 'none';
	        document.body.appendChild(iframe);
	    }
	    iframe.src = data.substr(3);
    	deleteTMP(data);
		hideSpinner();
    });
}

function deleteTMP(fn) {
	$.post(ajaxUrl, {action:"deleteTmp", args:{fname:fn}}, function(data2){
		console.log(data2);
		alert(data2);
	});
}

function checkSubs() { // this function is to clean up subscriptions
	$.post(ajaxUrl, {action:"checkSubs", args:{}}, function(data) {
		console.log(data);
	});
}


/*
** CROSS BROWSER FUNCTIONS
**
*/
function checkBrowser(){
    c=navigator.userAgent.search("Chrome");
    f=navigator.userAgent.search("Firefox");
    m8=navigator.userAgent.search("MSIE 8.0");
    m9=navigator.userAgent.search("MSIE 9.0");
    if (c>-1){
        brwsr = "Chrome";
    }
    else if(f>-1){
        brwsr = "Firefox";
    }else if (m9>-1){
        brwsr ="MSIE 9.0";
    }else if (m8>-1){
        brwsr ="MSIE 8.0";
    }
    return brwsr;
}
var browser = checkBrowser();
if(browser == "Firefox") {
	$("input[type='date']").datepicker();
	$("input[type='date']").val(new Date().toLocaleDateString());
}
else if (browser == "Chrome")
	$("input[type='date']").val(new Date().toDateInputValue());


/*
** GRUNT TYPE VIEWS
**
*/
function readOnly() {
	$(".btn").each(function() {
		if($(this).attr("id") == "btn-delFund" || $(this).attr("id") == "btn-logout" || $(this).hasClass("btn-reset"))
			console.log("null");
		else
		$(this).attr("disabled", "disabled");
	});
	$(".btn-editFMS").remove();
	$("#contrib-contextMenu").remove();
}
if(readOnly == true) readOnly();

function toggleExecutiveView() {
	$("#settings-payment").remove();
	$("#settings-cancel").remove();

	$("#nav-settings-payment").remove();
	$("#nav-settings-cancel").remove();

	$(".widget-settings-fund[tag='pie'] input").attr("disabled", "disabled");
	$(".widget-settings-fund[tag='pie'] select").attr("disabled", "disabled");
	$(".tm-input").parent().css("display","none");
	$(".widget-settings-fund[tag='payment']").remove();
	$(".widget-settings-fund[tag='cancel']").remove();
}

/*
** FIX GRUNT_TYPES
**
*/
fixGTypes();
function fixGTypes() {
	var iswrong = false;
	for(var y in Pie.grunts) {
		var grunt = Pie.grunts[y];

		if(grunt.grunt_type == 0 && Pie.grunt_leader != grunt.grunt_id) {
			grunt.grunt_type = 3; // set to employee
			iswrong = true;
		}
	}
	if(iswrong) {
		saveDOM();
		location.reload();
	}
}