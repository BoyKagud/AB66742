<?php
require_once("Model.php");
new Model();
if( isset( $_POST['action'] ) && function_exists($_POST['action']) ) {
	$_POST['action']($_POST['args']);
}

function updateTransaction($args) {
	Model::updateTransaction($args);
}

function addLogDate($args) {
	print_r($args);
	Model::addLogDate($args);
}

// function sendNotif() {

// 	$mailIntro = "Hello Pie Slicer beta tester!<br><br>Thank you for testing the Pie Slicer and providing valuable feedback. As you may know, we officially launched the Pie Slicer last month and have been accepting paid accounts. We are winding down our beta program and will no longer be supporting the free beta version of the software soon. Below are your current beta pie accounts:";
// 	$mailOutro = "<strong>The beta accounts will be deleted on December 16, 2015</strong>. If you would like to keep using the Pie Slicer you will need to upgrade to a paid account, which start at $5 per pie per month for unlimited participants. If you do not upgrade your pie will be deleted.<br><br>Please let us know if you have any questions or comments!<br>Sincerely,<br>Mike Moyer and the Pie Slicer Team";

// 	$model = array();
// 	$betaFunds = Model::getBetas();
// 	// build data structure
// 	// print_r($betaFunds);
// 	foreach ($betaFunds as $row) {
// 		if(!isset($model[$row["id"]])) $model[$row["id"]] = array("email"=>$row["email"], "pies"=>array());
// 		array_push($model[$row["id"]]["pies"], $row["name"]);
// 	}

// 	foreach ($model as $user) {
// 		$pies = ""; $pctr = 1;
// 		foreach ($user["pies"] as $pie) {
// 			$pies .= $pctr.".) ".$pie."<br>";
// 			$pctr++;
// 		}

// 		sleep(5);
// 		echo $mail = $mailIntro."<br><br>".$pies."<br>".$mailOutro;


// 		$headers  = 'MIME-Version: 1.0' . "\r\n";
// 		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// 		$headers .= 'From: Pie Slicer <no-reply@SlicingPie.com>' . "\r\n";
// 		mail($user['email'], 
// 			"Pie Slicer is Out of Beta!", 
// 			$mail,
// 			$headers);
// 		mail("ricardo.emong@gmail.com", 
// 			"Pie Slicer is Out of Beta!", 
// 			"Mail sent to ".$user['email'],
// 			$headers);
// 	}
// }

function checkSubs() {
	require_once("Controller.php");
	if(file_exists("../controller/Payments_Controller.php"))
		require_once("../controller/Payments_Controller.php");
	else
		require_once("controller/Payments_Controller.php");
	$controller = New Payments_Controller($args);
	$subs = $controller->getAllSubs();
	$count = 0;
	foreach ($subs as $val) {
		if(!isSubExists($val)) {
			// echo $val."\n";
			$count++;
			$controller->cancelSubscription($val);
		}
	}
	// echo $count;
}

function isSubExists($token) {
	return Model::isSubExists($token);
}

function execMailList() {

	$users = Model::getAllUsers();

	// Get cURL resource
	$curl = curl_init();
	foreach ($users as $user) {
		echo $user["email"]."\n";
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'http://mailer850.instymailer.com/subscribe',
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS => array(
		        "name" => $user["name"],
		        "email" => $user["email"],
		        "list" => 'AwZx8d9WBp9I3V5H2s0haA'
		    )
		));
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		sleep(1);
	}
	// Close request to clear up some resources
	curl_close($curl);
}

function updateCard($args) {
	require_once("Controller.php");
	if(file_exists("../controller/Payments_Controller.php"))
		require_once("../controller/Payments_Controller.php");
	else
		require_once("controller/Payments_Controller.php");
	$controller = New Payments_Controller($args);

	echo $controller->updateCard($args);
}

function nFund($args) {
	$fund = $args;
	require_once("Controller.php");
	if(file_exists("../controller/Payments_Controller.php"))
		require_once("../controller/Payments_Controller.php");
	else
		require_once("controller/Payments_Controller.php");
	$controller = New Payments_Controller($args);
	if($args["extra"]["BT_cutomerID"] == "0" || $args["action"] == "signup") {
		$pcont = $controller->createCustomer($args);
		// print_r($pcont);
		$args["customer_id"] = $pcont["customer_id"];
		if($args["action"] != "signup") {
			$tmp = $args["fund"];
			foreach($tmp as $y=>$x) {
				$fund[$x["name"]] = $x["value"];
			}
		}

		$pcont = $controller->createSubscription(0,$fund["subscription"],$args["customer_id"],$args);
	// print_r($pcont);

		// update user
		Model::updateUserToken( isset($args["id"]) ? $args["id"] : $args["glead"], $args["customer_id"]);
	}
	else 
		$pcont = $controller->createSubscription(0,$args["subscription"],$args["extra"]["BT_cutomerID"],$args["extra"]);
	print_r($pcont);
	if(isset($pcont["error"])) {
		echo $pcont["error"];
		return;
	}
	if($pcont["token"] != "" || strlen($pcont["token"]) > 3 || isset($pcont["token"]) ) {
		if(isset($fund["leader"]) && isset($fund["fundName"]))
			echo Model::createFund($fund["leader"], $fund["fundName"], $pcont["token"]);
		else 
			echo Model::createFund($args["glead"], $args["fundName"], $pcont["token"]);
	}
	else echo "Error: Token - ".$pcontp["token"];
	checkSubs();
}

function upgradePie($args) {
	require_once("Controller.php");
	if(file_exists("../controller/Payments_Controller.php"))
		require_once("../controller/Payments_Controller.php");
	else
		require_once("controller/Payments_Controller.php");
	$controller = New Payments_Controller($args);
	$pcont = $controller->createCustomer($args);
	$args["customer_id"] = $pcont["customer_id"];

	$pcont = $controller->createSubscription(0,$args["subscription"],$args["customer_id"],$args);
	echo $pcont["token"];
	// update user
	Model::updateUserToken( isset($args["id"]) ? $args["id"] : $args["glead"], $args["customer_id"]);
}

function getContributions($args) {	
	// echo 21345; return;
	$contribs = Model::getContributions($args['gid'], $args['fid']);
	// $contribs['details'] = json_decode($contribs['details'], true);
	
	// print_r($contribs);

	$contribs = json_encode($contribs);
	echo $contribs;
}

function delContrib($args) {
	print_r($args);
	Model::delContribs($args);
}

function addContrib($args) {
	$args["details"] = json_encode($args["details"]);
	$args["details"] = str_replace("\\r\\n", "<br/>", trim($args["details"]));
	// echo $args["details"]."\n";
	echo Model::addContrib($args["gid"], $args["fid"], $args["details"]);
	// print_r($args);
}

function fpass($email) {
	$pass = generateRandomString();
	$email = $email["value"];

	$args = Model::getGruntByEmail($email);

	// generate pass token
	$gen = generateRandomString(30);
	$tid = Model::generateResetToken($args["id"], $gen);

	$lnk = "https://www.slicingpie.com/pieslicer/Passreset?token=".$gen."&token_id=".$tid."&gid=".$args['id'];

	// TODO: CREATE PASS TABLE

	$args = array("alerts"=>array(array("name"=>"passChange", "email"=>$email, "gname"=>$args["name"], "lnk"=>$lnk)));
	print_r($args);
	sendNotifs($args);
}

function changePass($args) {
	// verify token
	$tk = Model::getTokenByID($args[2]['value']);
	if($tk['grunt'] == $args[0]['value']) {
		// execute change
		Model::changePass($args[0]['value'], $args[3]['value']);
		Model::deleteTokenByID($args[2]['value']);
	}
}

function generateRandomString($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function save($pie) {
	// deal with Pie/Fund data and well
	$enc = json_encode($pie["well"]);
	$dec = json_decode($enc, true);
	if(!isset($dec["grunts"]))
		$enc = substr($enc, 0, -1).', "grunts":[]}';
	$settings = json_encode($pie["settings"]);
	$projects = json_encode($pie["projects"]);
	// $projects["projects"] = $projects;
	$args = array("name"=>$pie["name"], "subscriptionID"=>$pie["subscriptionID"], "TBV"=>$pie["TBV"], "well"=>$enc, "settings"=>"{$settings}", "projects"=>"{$projects}");

	Model::updatePie($pie["id"], $args);

	// die(print_r($pie["grunts"]));
	// deal with grunts
	Model::updateGrunts($pie["grunts"]);

	// print_r($args);
}

function sendNotifs($args) {
	if(isset($args["alerts"])) {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: SlicingPie.com <no-reply@SlicingPie.com>' . "\r\n";
		foreach ($args["alerts"] as $alert) {
			switch($alert["name"]) {
				case "passChange": if(!isset($alert["lnk"]) || $alert["lnk"] == "" || strlen($alert["lnk"]) < 2) break;
									mail($alert["email"], 
									"Pie Slicer Password Reset", 
									"Hi ".$alert["gname"].",<br /><br />"
										."To change your password please click the link below.<br/><br/>"
										."<a href='".$alert["lnk"]."'>".$alert["lnk"]."</a><br/><br/>"
										."Good luck with your work! <br /><br />"
										."Sincerely,<br /><br />"
										."The Slicing Pie Pie Slicer Robot<br /><br />",
									$headers);
									break;
				case "newContributions" : sendContribsEmail($alert["contributor"], $alert["type"], $alert["contrib"], $alert["fid"], $alert["pieTBV"], $alert["pname"], $alert["plead"]);
									break;
				case "signup" : break;
				case "wellWithdraw": break;
				case "wellDeposit": break;
				default: continue;
			}
		}
	}
	// print_r($args);
}

function signup($form) {
	if(Model::isGruntExists($form["email"]))
		die("Email already exist");
	$gid = Model::signup($form);
	$form["glead"] = $gid;
	$form["fundName"] = $form["fundname"];
	$form["action"] = "signup";
	nFund($form);
}

function sendContribsEmail($contributor, $type, $contrib, $fid, $TBV, $pname, $plead) {

	$grunts = Model::get_grunts($fid, $plead, $plead);

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: SlicingPie.com <no-reply@SlicingPie.com>' . "\r\n";
	foreach($grunts as $grunt) {
		$galerts = (is_array($grunt["alerts"]["alerts"]) ? $grunt["alerts"]["alerts"] : (is_array($grunt["alerts"]) ? $grunt["alerts"] : -1));
		if(in_array("contributions", $galerts))
			mail($grunt["email"], 
					"Pie Slicer Alert: New Contributions for ".$pname, 
					"Hi ".$grunt["name"].",<br /><br />"
						."Hooray! Work is getting done for your company. Here are the details of the latest contribution:<br /><br />"
						."<strong>Name:</strong> ".$contributor["name"]."<br /><br />"
						."<strong>Contributed:</strong> ".$type."<br /><br />"
						."<strong>Description:</strong> ".$contrib["desc"]." <br /><br />"
						."<strong>Value:</strong> ".$contrib["amount"]."<br /><br />"
						."<strong>Reimbursement:</strong> ".$contrib["reim"]."<br /><br />"
						."<strong>Slices:</strong> ".$contrib["tv"]."<br /><br />"
						."The whole pie now has ".number_format($TBV)." slices. You have ".number_format($grunt["share"]["tbv"])." which is ".round(($grunt["share"]["tbv"]/$TBV)*100, 2)."% of the whole pie. <br /><br />"
						."Good luck with your work! <br /><br />"
						."Sincerely,<br /><br />"
						."The Slicing Pie Pie Slicer Robot<br /><br />", 
					$headers);
	}
}

function getBillingInfo($args) {
	$ret = Model::getBillingInfo($args["grunt"]);
	echo json_encode($ret);
	return $ret;
}

function updateContrib($args) {
	Model::updateContrib($args["contrib"]);
}

function addGrunt($args) {
	echo json_encode(Model::addGrunt($args));
}

function authLogin($args) {
	$grunt = Model::getGruntByEmail($args["email"]);
	if(isset($grunt["email"]) && md5($args["password"]) == $grunt["password"] ) {
		echo $grunt["id"];
	}
	echo 0;
}

function delFund($args) {
	// send email
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: SlicingPie.com <no-reply@SlicingPie.com>' . "\r\n";
	$headers .= 'Reply-To: '.$args["email"]."\r\n";
	$headers .= 'Cc: mikedmoyer@gmail.com' . "\r\n";
	$tmparr = array("grunt"=>$args["gid"]);
	$grunt = getBillingInfo($tmparr);
	mail("support@slicingpie.com", 
		"PieSlicer: Account Cancelation", 
		"An account has just been deleted <br/>"
		."Pie Name: ".$args["pname"]."<br/>"
		."Pie Leader: ".$grunt["first_name"]." ".$grunt["last_name"]."<br/>"
		."Feedback: ".$args["msg"], 
		$headers);

	// discontinue subscription
	if($args["sid"] != "0") {
		require_once("../controller/Payments_Controller.php");
		$pc = new Payments_Controller();
		$pc->cancelSubscription($args["sid"]);
	}

	Model::delFund($args["pid"]);
}

function resetFund($args) {
	Model::resetFund($args);
}

function getMD5($args) {
	echo md5($args["string"]);
}

function removeGrunt($args) {
	echo Model::removeGrunt($args);
}

function partialRemGrunt($args) {
	echo 134512345;
	echo Model::partialRemGrunt($args);
}

function delContribs($args) {
	Model::delContribs($args);
}

function createCSV($dat) {
	$fname = $dat["fname"];
	$fname= "../tmp/".$fname."_".time().".csv";
	$args = $dat["args"];
	// print_r($args[0]);
	$headers = "Contribution ID,Contributor,Contribution,Value,Reimbursement,Date,Project,Description,Slices,Flag";
	$file = fopen($fname, "w");
	fputcsv($file,explode(',',$headers));

	foreach ($args as $contrib) {
		$string =array($contrib["id"],
				$contrib["grunt"],
				$contrib["contrib"],
				$contrib["amount"],
				$contrib["reim"],
				$contrib["date"],
				$contrib["project"],
				$contrib["desc"],
				$contrib["tv"],
				$contrib["flag"]);
		fputcsv($file,$string);
	}

	fclose($file);
	echo $fname;
}

function deleteTmp($args) {
	print_r($args);
	echo 23452345;
	unlink($args["fname"]);
}

?>