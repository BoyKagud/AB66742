<html>
<head>
    <script src="<?php echo SCRIPTS_DIRECTORY; ?>/jquery.js"></script>
    <script src="<?php echo SCRIPTS_DIRECTORY; ?>/bootstrap.min.js"></script>
<script src="<?php echo SCRIPTS_DIRECTORY; ?>/init.js"></script>
    <script src="<?php echo SCRIPTS_DIRECTORY; ?>/spin.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/layout.css">
	<link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/choose-fund.css">
    <link rel="stylesheet" type="text/css" href="<?php echo STYLES_DIRECTORY; ?>/bootstrap.min.css">
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
</head>
<body>


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

    <?php 
        require_once("controller/Payments_Controller.php");
        $pc = new Payments_Controller();
        $clToken = $pc->clientToken;
     ?>
    <div class="popup" id="popup"  style="position:absolute; top:0;left:0; width:100%;height:100%;background:url('view/images/lightboxbg.png');display:none;">
        <!-- <div style="z-index:100;width:100%;height:100%;position:absolute;background:url('view/images/lightboxbg.png');">
                    <div style="text-align:center;margin:auto;margin-top:20%;color:#f5f5f5;">
                        <strong>Hmmmmm... something is not quite right. </strong></br>
                        <strong>We're working on it. </strong></br>
                        <strong>Please try again in a little while.</strong>
                        <br/><br/>
                        <strong>Please contact <a href="http://form.jotformpro.com/form/41825700042949">PieSlicer Support</a></strong>
                    </div>
                </div> -->
        <div class="contribution-form-wrap" style="display:block;">
    <!-- Add new Pie -->
            <div class="contribution-form" id="newFund-wrap" style="display:block;">
                <div class="contribution-form-header widget-header">Create New Pie<div style="color:#000;" class="wright"><span style="color:#fff;" class="glyphicon glyphicon-info-sign wright infoglyph2" data-content="info.4"></span></div></div>
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
                        </select>
                    </div>
                    <div id="cfpass-wrap" class="form-group">
                        <label>Password</label>
                        <input class="form-control" name="cfpass" type="password" id="cf-passconf" placeholder="Enter Password" />
                    </div>

                    <script src="https://js.braintreegateway.com/v2/braintree.js"></script>
                    <div id="dropin"></div>
                    <script type="text/javascript">
                    var uid = <?php echo $_SESSION["user_id"] ?>;
                    var ajaxUrl = "system/functions.php";

                    var grunt = undefined;
                    loadSpinner("Fetching User Data...");
                    $.post(ajaxUrl, {action:"getBillingInfo", args:{grunt:<?php echo $_SESSION["user_id"]; ?>}}, function(data) {
                        console.log(data);
                        grunt = JSON.parse(data);
                        setBraintree(); 
                        hideSpinner();
                    });

                    function clone(obj) {
                        if (null == obj || "object" != typeof obj) return obj;
                        var copy = obj.constructor();
                        for (var attr in obj) {
                            if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
                        }
                        return copy;
                    }

                    function setBraintree() {
                        braintree.setup('<?php echo $clToken; ?>', 'dropin', {
                          container: 'dropin',
                          paymentMethodNonceReceived: function (event, nonce) {
                            loadSpinner("Baking your Pie...");
                            console.log(nonce);
                            var dat = $($("#newFund-wrap form")[0]).serializeArray();
                            grunt.extra = [];
                            var env = clone(grunt);

                            env.glead = grunt.id;
                            env.fundName = dat[1].value;
                            env.subscription = dat[2].value;
                            env.extra = grunt;

                            env.nonce = nonce;
                            env.fund = dat;

                            var info = {action:"nFund", args:env};
                            console.log("\n\nasdfasdfas\n\n");
                            jQuery.post(ajaxUrl, info, function(fid) {
                                console.log(fid);
                                var nform = document.createElement("form");
                                nform.setAttribute("method", "post");
                                var inpt = document.createElement("input");
                                inpt.setAttribute("type", "hidden");
                                inpt.setAttribute("name", "fund_id");
                                inpt.setAttribute("value", parseInt(fid));
                                $("body")[0].appendChild(nform);
                                nform.appendChild(inpt);
                                nform.submit();
                                event.preventDefault();
                                setTimeout(function() {hideSpinner()}, 10);
                            });
                          }
                        });
                    }
                    </script>
                <button class="btn btn-danger" style="margin-top:12px;" id="btn-cfund" >Create Pie</button>
                <button class="btn btn-danger" style="margin-top:12px;float:right;" id="btn-lgout" >Logout</button>
                </form>

            </div>
        </div>
    </div>
	<div class="wrap-choose-fund">
        <input id="inpt-gid" type="hidden" value="<?php echo $_SESSION['user_id']; ?>" />
        <form id="cf-form" method="post" action="index.php">
            <select id="cf-fund-choose" name="fund_id" class="form-intro">
                <option value="-1">Select Existing Pie</option>
                <?php
                foreach($args as $fund) {
                ?>
                <option value="<?php echo $fund['id']; ?>"><?php echo $fund['name']; ?></option>
                <?php
                }
                ?>
                <!--<input type="submit" />-->
            </select>
        </form>
        <br />
        <!-- <button id="btn-cfundnew" class="btn btn-danger">Create New</Button> -->
        <div style="width:100%; height:1px; clear:both;"></div>
    </div>

    <script type="text/javascript">
        if($("#cf-fund-choose option").length == 2) {
            if($($("#cf-fund-choose option")[1]).val() == "")
                $($("#cf-fund-choose option")[1]).remove();
            $("#cf-fund-choose").val($($("#cf-fund-choose option")[1]).val());
            if($($("#cf-fund-choose option")[1]).length > 0) {
                goToPie();
            }
            else {
                $("#popup").css("display", "block");
            }
        }
    	$("#cf-fund-choose").change(function() {
            goToPie();
    	});

        function goToPie() {
            $("#cf-form").submit();
        }

        $("body #btn-lgout").click(function() {
            event.preventDefault();
            var lgform = document.createElement("form");
            lgform.setAttribute("method", "post");
            var inp = document.createElement("input");
            inp.setAttribute("name", "logout");
            inp.setAttribute("value", 1);
            lgform.appendChild(inp);
            lgform.submit();
        });
    </script>

</body>
</html>