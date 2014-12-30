/* Calculator Script */

$.ajaxSetup({async: false});

function addContrib(form) {
	// contrib in json form
	var contrib = form.serializeArray();
	console.log("Serial Array = == = = = ");console.log(contrib[4].value);
	var tv = 0;
	var finder=0;

	var det = {};
	det.contrib = parseInt(contrib[0].value);
	var info = {gid:0, fid:Pie.id};

	var grunt = null;

	for(g in Pie.grunts) {
		if(Pie.grunts[g].gid==activeContribTag) {
			grunt = Pie.grunts[g];
		}
	}

	// TODO: review multipliers and excess

	switch(det.contrib) {
		case CONTRIB_TIME: 
			var hours = parseTime(contrib[4].value) ? parseTime(contrib[4].value) : 0;
			var cash = parseVal(contrib[5].value) ? parseVal(contrib[5].value) : 0;
			var due = (hours*grunt.share.hourlyRate)-cash;
			tv = (due > 0 ? due*noncashx : due*noncashx/salary_multiplier);
			// alert(due+" "+hours+" "+grunt.share.hourlyRate+" "+cash);
			console.log(contrib);
			det.amount = hours;
			det.reim = cash;
			det.type = "noncash";
			det.date = contrib[1].value;
			det.project = contrib[2].value;
			det.desc = contrib[3].value;
			break;
		case CONTRIB_EXPENSES:
			var amount = parseVal(contrib[3].value) ? parseVal(contrib[3].value) : 0;
			var reim = parseVal(contrib[4].value) ? parseVal(contrib[4].value) : 0;
			var due = amount-reim;
			tv = (due > 0 ? due*cashx : due*cashx/salary_multiplier);
			det.type = "cash";
			det.amount = amount;
			det.reim = reim;
			det.date = contrib[1].value;
			det.desc = contrib[2].value;
			break;
		case CONTRIB_SUPPLIES:
			var amount = parseVal(contrib[3].value) ? parseVal(contrib[3].value) : 0;
			var reim = parseVal(contrib[4].value) ? parseVal(contrib[4].value) : 0;
			var age = parseVal(contrib[5].value) ? parseVal(contrib[5].value) : 0;
			tv = (age == 0 ? (amount-reim)*cashx : (amount-reim)*noncashx );
			det.type = "cash";
			det.date = contrib[1].value;
			det.desc = (age != 0) ? "(Used) "+contrib[2].value : contrib[2].value;
			det.reim = reim;
			det.amount = amount;
			break;
		case CONTRIB_SALES:
			var amount = parseVal(contrib[3].value) ? parseVal(contrib[3].value) : 0;
			var cashP = parseVal(contrib[4].value) ? parseVal(contrib[4].value) : 0;
			var due = (amount * commission_rate)-cashP;
			tv = (due > 0 ? due*salary_multiplier : due*noncashx/salary_multiplier);
			det.type = "noncash";
			det.date = contrib[1].value;
			det.desc = contrib[2].value;
			det.amount = amount;
			det.reim = cashP;
			break;
		case CONTRIB_ROYALTY:
			var sales = parseVal(contrib[3].value) ? parseVal(contrib[3].value) : 0;
			var cashP = parseVal(contrib[4].value) ? parseVal(contrib[4].value) : 0;
			var due = (sales * royalty_rate)-cashP;
			tv = (due > 0 ? due*salary_multiplier : due*cashx/salary_multiplier);
			det.type = "cash";
			det.date = contrib[1].value;
			det.desc = contrib[2].value;
			det.amount = sales;
			det.reim = cashP;
			break;
		case CONTRIB_INVESTMENT: 
			var amount = parseVal(contrib[1].value) ? parseVal(contrib[1].value) : 0;
			var inv = 0;

			if(amount <= A)
				inv = (amount * Apct) * noncashx;
			else
				inv = (A*Apct*noncashx) + (((amount-A) * Bpct) * noncashx);
			var investor = parseVal(contrib[2].value) ? parseVal(contrib[2].value) : 0;
			finder = parseVal(contrib[6].value) ? parseVal(contrib[6].value) : 0;
			addWell(amount, investor, contrib[3].value, contrib[4].value);
			if(finder>0) {
				tv = inv;
				activeContribTag = finder;
				for(g in Pie.grunts) {
					if(Pie.grunts[g].gid==finder) {
						grunt = Pie.grunts[g];
					}
				}

			}
			det.type = "noncash";
			det.amount = amount;
			det.desc = "Investor Finder";
			det.date = new Date().toDateInputValue();

			break;
		case CONTRIB_FACILITIES:
			var val = parseVal(contrib[3].value) ? parseVal(contrib[3].value) : 0;
			var cashP = parseVal(contrib[4].value) ? parseVal(contrib[4].value) : 0;
			var due = val-cashP;
			tv = (due > 0 ? due*noncashx : due*noncashx/salary_multiplier);
			det.type = "noncash";
			det.desc = contrib[2].value;
			det.reim = cashP;
			break;
		case CONTRIB_OTHER:
			var amount = parseVal(contrib[3].value) ? parseVal(contrib[3].value) : 0;
			var x = parseVal(contrib[4].value) ? parseVal(contrib[4].value) : 0;
			tv = (x > 0 ? amount*noncashx : amount*cashx);
			if(x > 0)
				det.type = "noncash";
			else {
				det.type = "cash";
				det.amount = amount
			}
			det.desc = contrib[2].value;
			det.reim = x;
			break;
	}
		console.log("TV OF CONTRIBUTION = "+tv);
		if(det.contrib == CONTRIB_INVESTMENT) {
			det.contrib = CONTRIB_OTHER;
			var reload = 1;
			if(grunt==0 || grunt == null) {
				init(false);
				location.reload();	
				return;
			}
		}
		det.desc.replace(/[\r\n]/g, " ");
		det.tv = tv;	
		info.gid = activeContribTag;
		info.details = det;
		

		// alert subscribers
		pendingAlerts.push({name:"newContributions", contributor:grunt, type:getContribName(det.contrib), contrib:det, fid:Pie.id, pieTBV:Pie.TBV, pname:Pie.name, plead:Pie.grunt_leader});

		// console.log(info);return;
		reset();
		$.ajaxSetup({async: false});
		$.post(ajaxUrl, {action:"addContrib", args:info}, function(data) {
			console.log("asdfasdf ===== "+data);
			if(data < 0)
				alert("error");
			else {
				Pie.TBV = Pie.TBV + tv;
				console.log(grunt);
				grunt.share.tbv = grunt.share.tbv + tv;
				init(false);
			}
		});

		if(reload)
			location.reload();

}

function checkVals() {
	// asynchronous
    setTimeout(function() {
		var sumContrib = 0;
		for(var e in Pie.grunts) {
			var gr = Pie.grunts[e];
			var sumgrunt = 0;
			for(var w in contributions) {
				var con = contributions[w];
				if(parseInt(con.flag) == 1) continue;
				if(parseInt(con.grunt_id) == parseInt(gr.gid))
					sumgrunt += parseInt(con.details.tv);
			}
			if(sumgrunt != gr.share.tbv) {
				fixCalc();
				return;
			}
			sumContrib += sumgrunt;
		}

		if(sumContrib != Pie.TBV) {
			fixCalc();
			return;
		}
    }, 0);
}
if(isLead()) {
	checkVals();
}

function fixCalc() {
	var msg = "Please wait as we fix your pie for you.";
	loadSpinner(msg);
	setTimeout(function() {
		var sumContrib = 0;

		for(var e in Pie.grunts) {
			var gr = Pie.grunts[e];
			var sumgrunt = 0;
			for(var w in contributions) {
				var con = contributions[w];
				if(parseInt(con.flag) == 1) continue;
				if(parseInt(con.grunt_id) == parseInt(gr.gid))
					sumgrunt += parseInt(con.details.tv);
			}
			gr.share.tbv = sumgrunt;
			sumContrib += sumgrunt;
		}

		Pie.TBV = sumContrib;
		setTimeout(function() {
			saveDOM();
			init(false); 
		},0);
		hideSpinner();
	},1000);
}

function parseTime(time) {
	time = time.split(":");
	time[0] = parseInt(time[0]);
	if(time[1] < 60) {
		time[1] = time[1]/60;
		time[0] += parseFloat(time[1]);
	}
	return parseFloat(time[0]);
}

$("body").on("click", ".calcBtn", function(event){
	addContrib($(this).parent());
});

$("body").on("click", ".btn-reset", function() {
	reset();
});

$("body").on("change", ".contrib-selector", function() {
	activeContribTag = $(this).parent().parent().parent().attr("tag");
	activeSelect = $(this);
	$(this).find(":selected").each(function() {
		// activeSelect.fadeToggle();
		activeContribForm = $("#"+$(this).val()+"-wrap");
		activeContribForm.css("display", "block");
		activeSelect.blur();
		activeContribForm.focus();
	});
	togglePopup();
});

function addWell(amount, gid, newGruntName, newGruntEmail) {
	amount = parseVal(amount);
	if(gid < 0) return;
	var Well = Pie.well;
	var oldAmount = Well.amount;
	Well.amount = oldAmount + amount;	
	// add new grunt

	var isGruntExistInWell = false;
	var grunt;
	grunt = {gid:gid, pct:(amount/Well.amount).toFixed(5)};
	if(gid == 0 ) {
		nGrunt = addNewGrunt(newGruntName, newGruntEmail);
		gid = nGrunt.gid;
		grunt = {gid:gid, pct:amount/Well.amount};
		Well.grunts.push(grunt);
	}

	// Update PCTs
	if(oldAmount > 0) {
		for(var k=0 ; k<Well.grunts.length ; k++) {
			var newPct=0;
			if(Well.grunts[k].gid==gid) {
				var curSplit = Well.grunts[k].pct*oldAmount;
				var newSplit = curSplit + amount;
				newPct = newSplit/Well.amount;
				isGruntExistInWell = true;
			} else {
				var oldVal = Well.grunts[k].pct * oldAmount;
				newPct = oldVal/Well.amount;
			}
			Well.grunts[k].pct = newPct;
		}
	} else {
		grunt = {gid:gid, pct:1};
	}

	if(!isGruntExistInWell) {
		Pie.well.grunts.push(grunt);
	}
	// save to DB via AJAX
}

function drawFromWell(amount) {
	var Well = Pie.well;
	if(amount < 0 || amount > Well.amount) { // catch
		alert("invalid amount");
		return;
	}
	// update grunt points
	for(var k=0 ; k<Well.grunts.length ; k++) {
		// get pct
		var share = Well.grunts[k].pct * amount;
		// alert(share);continue;
		var points = share * cashx;
		// Pie.TBV = Pie.TBV + points;

		for(l in Pie.grunts) {
			if(Pie.grunts[l].gid == Well.grunts[k].gid) {
				// Pie.grunts[l].share.tbv = Pie.grunts[l].share.tbv + points;
				activeContribTag = Pie.grunts[l].gid;

				var model = document.getElementById("expForm");
				var form = model.cloneNode(true);
				form.getElementsByClassName("expAmount")[0].value = share;
				form.getElementsByClassName("expDate")[0].value = new Date().toDateInputValue();
				form.getElementsByClassName("expDesc")[0].value = "Drawn From Well";

				addContrib($(form));
			}
		}
		// update grunt points
	}
	if(Well.amount == amount) {
		for(var l=0 ; l<Pie.well.grunts.length ; l++) {
			document.getElementById("grunt-well-"+Pie.well.grunts[l].gid).innerHTML = 0;
		}
		Well.amount = 0;
		Well.grunts.length = 0;

		$(document.getElementsByClassName("grunt-well")).html("0");
		$(document.getElementsByClassName("grunt-wellb")).html("0");
	}
	else
		Well.amount = Well.amount-amount;

	init(false);
	// Update DOMSAVE VIA AJAX
}

$("body #btn-addInvestment").on("click", function() {
	togglePopup();
	activeContribForm =-1;
	$("#invForm-wrap").fadeToggle(100);
});
$("body #addInv").on("click", function() {
	$(this).parent().parent().fadeToggle();
});
$("body").on("click", "#btn-drawFromWell", function(event) {
	togglePopup();
	activeContribForm =-1;
	$("#drawWell-wrap").fadeToggle(100);
});
$("#btn-calculateDrawWell").click(function(e) {
	e.preventDefault();
	var contrib = $("#drawWellForm").serializeArray();
	var val = parseVal(contrib[0].value) ? parseVal(contrib[0].value) : 0;
	drawFromWell( val );
	$(this).parent().parent().fadeToggle(200);
	location.reload();
});
$("#btn-addGrunt").on("click", function() {
	togglePopup();
	activeContribForm =-1;
	$("#addgForm-wrap").fadeToggle(100);
});
$("#addG").click(function(){
	var agf = $("#addgForm").serializeArray();
	$("#gruntFMS-error").html("");
	$("#gruntMail-error").html("");
	agf[2].value = agf[2].value.replace(/,/g, '');
	if(agf[2].value == "" || /^([0-9]*)$/.test(agf[2].value) == false ) {
		$("#gruntFMS-error").html("Invalid Fair Market Salary");
		return;
	}
	if(agf[1].value == "" ) {
		$("#gruntMail-error").html("Please provide the team member's email");
		return;
	}
	if(agf[1].value.indexOf("@") == -1) {
		$("#gruntMail-error").html("Please provide a valid email address");
		return;
	}

	var advisor = false;
	if(agf[3])
		advisor = true;
	var k = addNewGrunt(agf[0].value, agf[1].value, parseVal(agf[2].value), false, advisor);
	console.log(k);
	if(k==0 || k=="0") {
		$(".btn[data-toggle='popover']").popover('show');
	}
	else
		location.reload();
});

$(".btn-cont-addg").click(function() {
	var advisor = false;
	if(agf[3]) 
		advisor = true;
	addNewGrunt(agf[0].value, agf[1].value, agf[2].value, true, advisor);
	location.reload();
	$("#addgForm-wrap").fadeToggle(100);
});

$(".btn[data-toggle='popover']").popover({html:'true'});

// dummy
var remGruntTag = -1;
$(".a-remove").click(function() {
	$($(".a-remove")[1]).popover('hide');
	remGruntTag = $(this).parent().attr("tag");

	var hasContribs = false;
	for(var h in contributions) {
		if(parseInt(contributions[h].grunt_id) == remGruntTag) {
			hasContribs = true;
			break;
		}
	}
	if(hasContribs)
		$("#remGrunt-wrap").css("display", "block");
	else {
		$("#delGruntConf-wrap").css("display", "block");
		var g = getGruntById(remGruntTag);
		$("body #span-remGconf").html(g.name);
	}
	togglePopup();
});

$("body").on("click", ".btn-resign", function(e) {
	remGruntTag = sessGrunt.grunt_id;
	$("#remGrunt-wrap").css("display", "block");
	togglePopup();
});

$("body").on("click", "#btn-confDelGrunt", function(){
	if(remGruntTag < 0) return;
	removeGrunt(parseInt(remGruntTag), 1);
});

$("body").on("click", ".btn-remGExec", function(e) {
	e.preventDefault();
	console.log(remGruntTag);
	if(remGruntTag < 0) return;
	var serial = $(this).parent().parent().serializeArray();
	removeGrunt(parseInt(remGruntTag), parseInt(serial[0].value));
	// reset();
});