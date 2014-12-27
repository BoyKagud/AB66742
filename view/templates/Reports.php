<div class="page-wrap" id="analytics-wrap">
	<div class="widget widget-summary wleft">
		<div class="widget-header">
			<strong>Analytics</strong>
		</div>
		<div class="widget-body">
			<div class="wleft" id="" style="padding:10px;padding-top:0;">
                <div style="font-size:15px;"><strong>Fund Evolution</strong></div>
				<canvas class="wleft" id="reports-linecanvas" height="630" width="680"></canvas>
			</div>
			<div class="wright">
                <div style="font-size:15px;"><strong>Fund Summary</strong><br/></div>
                <div><strong>By Project</strong></div>
                <div id="fbm-legend-wrap">
                    <div id="fbm-colorbox-model" class="colorBox" style="float:left;display:none;">
                        <div class="grunt-colorBox" style="background:#31859c;height:10px;width:10px;float:left;margin:5px;"></div>
                        <span class="grunt-name colorBox-gname">Time</span>
                    </div>
                </div>
                <div style="width:100%; height:1px; clear:both;"></div>
			 	<div id="reports-fbm-wrap" style="margin-bottom:18px;border:1px solid #e0e0e0;width:250px;height:250px;">
					<canvas class="wleft" id="reports-piecanvas-fbm" height="220" width="220" style="margin:14px;"></canvas>
				</div>

                <div><strong>By Type of Contribution</strong></div>
                <div id="reports-fbc-wrap" style="width:250px;height:350px;">
				    <div id="reports-fbc-wrap" style="border:1px solid #e0e0e0;width:250px;height:250px;">
					   <canvas class="wleft" id="reports-piecanvas-fbc" height="220" width="220" style="margin:14px;"></canvas>
                    </div>
                    <div style="margin-bottom:88px;">
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#31859c;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Time</span>
                        </div>
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#4a452a;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Expenses</span>
                        </div>
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#ffc000;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Supplies and Equipments</span>
                        </div>
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#604a7b;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Sales</span>
                        </div>
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#77933c;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Royalty</span>
                        </div>
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#17375e;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Facilities</span>
                        </div>
                        <div id="" class="colorBox" style="float:left;">
                            <div class="grunt-colorBox" style="background:#e46c0a;height:10px;width:10px;float:left;margin:5px;"></div>
                            <span class="grunt-name colorBox-gname">Other</span>
                        </div>
                    </div>
                </div>
            </div>
			</div>
		</div>
	</div>

<script type="text/javascript">

function getMonthString(num) {
    var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    return months[num-1];
}

function initFBM() {
    // FBM
    // get projects
    sortContrib(3);
    var wproj = [];
    var projTotal = 0;
    for(var b in contributions) {
        if(contributions[b].details.project != "ZZZ") {
            wproj.push(contributions[b]);
            projTotal++;
        }
    }
    for(var w in wproj) {
        var attr = "projDat."+wproj[w].details.project;
        eval(attr+" == undefined ? "+attr+"=1 : "+attr+"+=1;");
    }
    var i = piecol.length-5;
    for (var prop in projDat) {
        if (projDat.hasOwnProperty(prop)) {
            pieCanvasFBM.addData({
                value: eval("parseFloat(((projDat."+prop+")/projTotal).toFixed(2))"),
                color: piecol[i%10],
                label: prop
            });
            addFBMLegend(prop, piecol[i%10]);
            i--;
        }
    }
}

function addFBMLegend(desc, color) {
    var model = document.getElementById("fbm-colorbox-model");
    var clone = model.cloneNode(true);

    clone.setAttribute("id", "");
    var colorBox = clone.getElementsByClassName("grunt-colorBox");
    $(colorBox[0]).css("background", color);
    var pname = $(clone).find("span");
    $(pname).html(desc);
    $(clone).css("display", "block");

    document.getElementById("fbm-legend-wrap").appendChild(clone);
}


var pieCanvasFBM = new Chart(document.getElementById("reports-piecanvas-fbm").getContext("2d")).Pie(null,{segmentStrokeWidth : 3,animationSteps : 1});

var contClone = [];
var wdata = null;
    var projDat = {};
    var lineCanvas = null;
$(document).ready(function() {
    
    sortContrib(4);
    // line graph
    var data = {
        labels: [],
        datasets: []
    };
    
    for(var d in contributions) {
        if(contributions[d].flag == "1") continue;
        contClone.push(contributions[d]);
    }

    var last=parseInt(contClone[0].details.date.split("-")[1]);
    var sum = 0;

    // count months
    var cmt = parseInt(contClone[contClone.length-1].details.date.split("-")[1]);
    for(var h=last ; h<=cmt ; h++) {
        data.labels.push(h);
    }

    var gruntmonthset = [];
    for(var g in Pie.grunts) {
        // initialize
        var monthset = [];
        for(var i=0 ; i<=data.labels.length ; i++) {
            monthset.push(0);
        }
        //
        var gmtotal = 0;
        for(var h in contClone) {
            var cur = contClone[h];
            if(parseInt(Pie.grunts[g].grunt_id) != parseInt(cur.grunt_id) || cur.details.date == undefined || cur.flag == "1") continue;
            if((contClone[h].details.date.match(/-/g) || []).length) {
                var ttmp = new Date(cur.details.date);
                cur.details.date = ttmp.toDateInputValue();
            }
            var m = parseInt(cur.details.date.split("-")[1]);
            console.log(m);
            if(m > last) {
                monthset[data.labels.indexOf(last)] = gmtotal;
                last = m;
                
            }
            gmtotal += parseInt(cur.details.tv);
        }
        monthset[data.labels.indexOf(last)] = gmtotal;

        for(var t=1; t<monthset.length; t++) {
            if(monthset[t] == 0) monthset[t] += monthset[t-1];
        }

        console.log(monthset);
        last = parseInt(contClone[0].details.date.split("-")[1]);
        gruntmonthset.push(monthset);
    }

    last = 0;
    for(var h in gruntmonthset) {
        var dat = [];
        for(var gmsi in gruntmonthset[h]) {
            if(h > last) {
                dat.push(gruntmonthset[h][gmsi]+gruntmonthset[h-1][gmsi]);
            } else
                dat.push(gruntmonthset[h][gmsi]);
        }
        
        data.datasets.push(
            {
                fillColor: piecol[h%10],
                strokeColor: "#FFFFFF",
                pointColor: piecol[h%10],
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: dat
            }
        );
    }

    data.datasets.reverse();
    for(var i in data.labels) 
        data.labels[i] = getMonthString(data.labels[i]);
    console.log(data);

    // TEMPLATE
    var lineOps = {bezierCurve:false, showTooltips:false}

    lineCanvas = new Chart(document.getElementById("reports-linecanvas").getContext("2d")).Line(data, lineOps);
    // lineCanvas.tooltip(function(key, y, e, graph) { return 'Some String' });

    // FBM
    initFBM();

    // fbc
    var pieCanvasFBC = new Chart(document.getElementById("reports-piecanvas-fbc").getContext("2d")).Pie(null, {segmentStrokeWidth : 3,animationSteps : 1});
    sortContrib(0);
    var fbclabels = ["Time","Expenses","Supplies and Equipments","Sales","Royalty","Facilities", "Other"];

    var fbcdat = [];
    for(var i=0 ; i<fbclabels.length-1 ; i++) 
        fbcdat.push(0);

    for(var g in contClone) {
        var cur = contClone[g];
        if(cur.flag == "1" || parseInt(cur.details.contrib) > 8) continue;
        var contrib = parseInt(cur.details.contrib)-1;
        if(contrib > 2) {
            contrib -= 1;
        }
        fbcdat[contrib] += parseInt(cur.details.tv);
    }

    // clean
    if(fbcdat.length > 6)
        fbcdat.length = 6;

    for(var g in fbcdat) {
        var color = parseInt(piecol.length)-parseInt(g)-3;
        var d = {value: fbcdat[g], color:piecol[color], label:fbclabels[g]};
        pieCanvasFBC.addData(d);
    }
    

    var md = document.getElementById("colorBox-model");
    $(md).remove();

    wdata = data;

});

</script>