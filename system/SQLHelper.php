<?php

class SQLHelper {

	private $user;
	private $pass;
	private $host;
	private $dbName;
	private $conn;
	private $sql;

	private $TBL_USERS = "grunts";
	private $TBL_FUNDS = "funds";
	private $TBL_CONTRIBUTIONS = "contributions";
	private $TBL_SHARE_DETAILS = "shares";
	private $TBL_AFFILIATIONS = "affiliations";
	private $DEFAULT_PIC_URL = "";

	protected static $instance = null;

	public static function get_instance() {

		if(SQLHelper::$instance == null)
			SQLHelper::$instance = new SQLHelper();
		return SQLHelper::$instance;
	}

	protected function __construct() {
		// $this->user = "root";
		// $this->pass = "";
		// $this->host = "localhost";
		// $this->dbName = "gfunds_gruntfunds";

		// gfunds.com
		$this->user = "withanch_grunt";
		$this->pass = "?,a%1hhrp}yx";
		$this->host = "localhost";
		$this->dbName = "withanch_gruntfunds";

		// live
		// $this->user = "mmoyer_pslicer";
		// $this->pass = "34*7;NFcF#pz";
		// $this->host = "localhost";
		// $this->dbName = "mmoyer_pieslicer";
		
		$this->connect();
		$this->init();
	}

	private function connect() {
		$this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->dbName);
	}

	private function init() {

		// CREATE USERS TABLE
		$query = "CREATE TABLE IF NOT EXISTS ".$this->TBL_USERS." ("
				."`id` INT NOT NULL AUTO_INCREMENT, "
				."`name` VARCHAR(25) NOT NULL, "
				."`password` VARCHAR(100) NOT NULL, "
				."`email` VARCHAR(50) NOT NULL, "
				."`image` VARCHAR(100) NOT NULL, "
				."PRIMARY KEY(id)"
				.");";
		$this->runQuery($query);

		// CREATE FUNDS TABLE
		$query = "CREATE TABLE IF NOT EXISTS ".$this->TBL_FUNDS." ("
				."`id` INT NOT NULL AUTO_INCREMENT, "
				."`name` VARCHAR(25) NOT NULL, "
				."`TBV` INT NOT NULL DEFAULT 0, "
				."`grunt_leader` INT NOT NULL, "
				."`grunts` TEXT NOT NULL, " // INCLUDE SHARE DETAILS
				."`well` TEXT NOT NULL, "
				."`subscription` VARCHAR(10) NOT NULL, "
				."PRIMARY KEY(id), "
				."FOREIGN KEY(grunt_leader) REFERENCES ".$this->TBL_USERS."(id)"
				.");";
		$this->runQuery($query);

		// CREATE AFFILIATIONS TABLE
		$query = "CREATE TABLE IF NOT EXISTS ".$this->TBL_AFFILIATIONS." ("
				."`id` INT NOT NULL AUTO_INCREMENT, "
				."`grunt_id` INT NOT NULL, "
				."`fund_id` INT NOT NULL, "
				."`status` VARCHAR(15) NOT NULL DEFAULT 'ACTIVE', "
				."`share` TEXT NOT NULL, "
				."PRIMARY KEY(id), "
				."FOREIGN KEY(grunt_id) REFERENCES ".$this->TBL_USERS."(id), "
				."FOREIGN KEY(fund_id) REFERENCES ".$this->TBL_FUNDS."(id)"
				.");";
		$this->runQuery($query);

		// CREATE CONTRIBUTIONS TABLE
		$query = "CREATE TABLE IF NOT EXISTS ".$this->TBL_CONTRIBUTIONS." ("
				."`id` INT NOT NULL AUTO_INCREMENT, "
				."`grunt_id` INT NOT NULL, "
				."`fund_id` INT NOT NULL, "
				."`details` TEXT NOT NULL, "
				."PRIMARY KEY(id), "
				."FOREIGN KEY(grunt_id) REFERENCES ".$this->TBL_USERS."(id), "
				."FOREIGN KEY(fund_id) REFERENCES ".$this->TBL_FUNDS."(id)"
				.");";
		$this->runQuery($query);

	}

	private function insert($table, $values) {
		$head = "INSERT INTO {$table}";
		$columns = "";
		$val = "";
		foreach($values as $key => $k) {
			$columns .= "`".$key."`, ";
			$val .= "'".$k."', ";
		}
		$columns = "(".substr($columns, 0, -2).")";
		$val = "VALUES(".substr($val, 0, -2).");";
		$query = $head.$columns.$val;
		// echo $query;
		return $this->runQueryReturnId($query);
	}

	private function isUnique($id) {
		$query = "SELECT * FROM ".TABLE_ACTIVE_GAMES." WHERE p1={$id} AND p2=-1";
		$k = $this->fetchByQuery($query);
		if( $k->num_rows == 0 ) return true;
		else return false;
	}

	private function concat($table, $values, $id) { // TO BE EVALUATED
		$query = "UPDATE {$table} SET ";
		$reps=0;
		foreach($values as $key => $k) {
			if($reps>0) $query .= ", ";
			if(is_string($k)) $query .= "`{$key}`=CONCAT(`{$key}`, '{$k}')";
			else $query .= " `{$key}`=CONCAT(`{$key}`, {$k})";
			$reps++;
		}
		$query .= " WHERE `token`='{$id}';";
		// echo $query;
		if($reps==0) return false;
		return $this->runQuery($query);
		// return $query;
	}

	private function update($table, $values, $id) {
		$query = "UPDATE {$table} SET ";
		$reps=0;
		foreach($values as $key => $k) {
			if($reps>0) $query .= ", ";
			if(is_string($k)) $query .= "{$key}='{$k}'";
			else $query .= " {$key}={$k}";
			$reps++;
		}
		$query .= " WHERE id={$id};";
		// echo $query;
		if($reps==0) return false;
		return $this->runQuery($query);
	}

	private function delete($table, $id) {
		$query = "DELETE FROM {$table} WHERE id={$id};";
		return $this->runQuery($query);
	}

	private function getResults($table, $id) {
		$query = "SELECT * FROM {$table} WHERE id={$id}";
		$result = $this->fetchByQuery($query);
		$row = $result->fetch_array();
		$result->free();
		return $row;
	}

	public function getBetas() {
		$query = 'SELECT `grunts`.id, `grunts`.email, `funds`.name, `funds`.subscriptionID FROM `funds` INNER JOIN `grunts` ON `funds`.grunt_leader=`grunts`.id WHERE `funds`.subscriptionID="0" AND (`grunts`.email NOT LIKE "%lakeshark%" OR `grunts`.email NOT LIKE "slicingpie%") AND (`grunts`.id>56) ORDER BY `grunts`.id ASC';
		$res = array();
		$result = $this->fetchByQuery($query);
		while($row = $result->fetch_assoc()) {
			array_push($res, $row);
		}
		return $res;
	}

	public function getAllUsers() {
		$query = 'SELECT `grunts`.email, `grunts`.name FROM `grunts` WHERE  (`grunts`.email NOT LIKE "%lakeshark%" OR `grunts`.email NOT LIKE "slicingpie%") AND (`grunts`.id>56) AND `grunts`.email LIKE "%@%" AND `grunts`.email NOT LIKE "%test%"';
		$res = array();
		$result = $this->fetchByQuery($query);
		while($row = $result->fetch_assoc()) {
			array_push($res, $row);
		}
		return $res;
	}

	public function generateResetToken($gid, $tid) {
		return $this->insert("tmp_pass_resettoken", array("grunt"=>$gid, "token"=>$tid));
	}

	public function regUser($user) {
		$user["image"] = "view/images/user-".rand(1,5).".jpg";
		$user["name"] = $user["first_name"]." ".$user["last_name"];
		return $this->insert($this->TBL_USERS, $user);
	}

	public function getContributions($gid, $fid) {
		$query = "SELECT contributions.id, contributions.grunt_id, contributions.fund_id, contributions.details, contributions.flag, grunts.name FROM ".$this->TBL_CONTRIBUTIONS." LEFT JOIN grunts ON contributions.grunt_id=grunts.id WHERE contributions.fund_id={$fid}";
		if($gid > 0) $query .= " AND contributions.grunt_id={$gid}";
		$result = $this->fetchByQuery($query);
		$contribs = array();

		while($row = $result->fetch_assoc()) {
			$row['details'] = json_decode($row['details'], true);
			if(isset($row["amount"])) {
				$row["amount"] = intval($row["amount"]);
			}
			array_push($contribs, $row);
		}
		return $contribs;
	}

	public function get_user_funds($gid, $flag = true) {
		$query = "SELECT affiliations.fund_id, (SELECT name FROM funds WHERE funds.id=affiliations.fund_id) as fund_name FROM affiliations LEFT JOIN grunts ON affiliations.grunt_id=grunts.id WHERE affiliations.grunt_id={$gid}";
		$result = $this->fetchByQuery($query);
		$funds = array();
		// die("".$result->num_rows);
		if($result->num_rows > 1) {
			while($row = $result->fetch_assoc()) {
				array_push($funds, array("id"=>$row["fund_id"], "name"=>$row["fund_name"]));
			}
		} else {
			$row = $result->fetch_array();
			$funds = array(array("id"=>$row["fund_id"], "name"=>$row["fund_name"]));
		}
		// if(count($funds) == 1 )
		// 	return $this->get_session_data($gid, $funds[0]["id"]);
		// else
			return $funds;
	}

	public function addLogDate($gid, $fid, $date) {
		$query = "UPDATE ".$this->TBL_AFFILIATIONS." SET last_logged_in='{$date}' WHERE grunt_id={$gid} AND fund_id={$fid}";
		// echo $query;
		$result = $this->runQuery($query);
	}

	public function get_session_data(&$gid, &$fid) {
		$query = "SELECT * FROM funds WHERE id={$fid}";
		$result = $this->fetchByQuery($query);
		$fund = $result->fetch_array();
		if(!is_array($fund))
			return -1;
		eliminateIntIndex($fund);

		$grunts = $this->getGruntsDS($fid, $gid, $fund["grunt_leader"]);
		$fund["glead_object"] = $this->getGruntByID($fund["grunt_leader"]);
		eliminateIntIndex($fund["glead_object"]);
		$fund["settings"] = json_decode($fund["settings"],true);
		// die(print_r($fund["settings"]));
		$fund["projects"] = json_decode($fund["projects"], true);
		if(isset($fund["projects"]["projects"]))
			$fund["projects"] = $fund["projects"]["projects"];
		$fund["TBV"] = json_decode($fund["TBV"], true);
		$fund["grunt_leader"] = json_decode($fund["grunt_leader"], true);
		$fund["id"] = json_decode($fund["id"], true);
		$fund["well"] = json_decode($fund["well"], true);
		$fund["well"]["amount"] = intval($fund["well"]["amount"]);
		foreach($fund["well"]["grunts"] as $wellShare) {
			$wellShare["gid"] = intval($wellShare["gid"]);
			$wellShare["pct"] = floatval($wellShare["pct"]);
		}
		$fund["grunts"] = $grunts;
		// die(print_r($fund));
		return $fund;
	}

	public function getGruntsDS($fid, $gid, $leader) {
		$query = "SELECT affiliations.id as affid, affiliations.grunt_id, affiliations.fund_id, affiliations.share, affiliations.status, affiliations.jobtitle, affiliations.grunt_type, affiliations.alerts, affiliations.last_logged_in,grunts.name, grunts.email, grunts.image, grunts.id as gid FROM affiliations INNER JOIN grunts ON affiliations.grunt_id=grunts.id WHERE affiliations.fund_id=".$fid;
		// if($gid != $leader)
		// 	$query .= " AND affiliations.grunt_id=".$gid;
		$result = $this->fetchByQuery($query);
		// die($query);
		$grunts = array();
		while($row = $result->fetch_assoc()) {
			// die(print_r($row));
			$row["share"] = json_decode($row["share"], true);
			$row["share"]["GHRR"] = intval($row["share"]["GHRR"]);
			$row["share"]["tbv"] = intval($row["share"]["tbv"]);
			$row["alerts"] = json_decode($row["alerts"], true);
			$row["alerts"] = $row["alerts"]["alerts"];
			$row["gid"] = intval($row["gid"]);
			$row["affid"] = intval($row["affid"]);
			unset($row["password"]);
			$row["fund_id"] = intval($row["fund_id"]);
			$row["grunt_id"] = intval($row["grunt_id"]);
			$row["grunt_type"] = intval($row["grunt_type"]);
			array_push($grunts, $row);
		}
		// die(print_r($grunts));
		return $grunts;
	}

	public function getTransactions() {
		require_once("controller/Payments_Controller");
		new Payments_Controller();
		
	}

	public function updateTransID($fid, $token) {
		$this->update($this->TBL_FUNDS, array("last_transaction"=>$token), $fid);
	}

	public function updateGruntCID($cid, $gid) {
		$this->update($this->TBL_USERS, array("BT_cutomerID"=>$cid), $gid);
	}

	public function getTokenByID($tid) {
		$query = "SELECT * FROM tmp_pass_resettoken WHERE id='{$tid}'";
		$result = $this->fetchByQuery($query);
		$row = $result->fetch_array();
		$result->free();
		return $row;
	}

	public function isSubExists($token) {
		$query = "SELECT * FROM ".$this->TBL_FUNDS." WHERE subscriptionID='{$token}'";
		$result = $this->fetchByQuery($query);
		$num = $result->num_rows;
		return $num>0 ? true:false;
	}

	public function deleteTokenByID($tid) {
		$this->delete("tmp_pass_resettoken", $tid);
	}

	public function changePass($id, $pass) {
		$pass = md5($pass);
		$grunt = $this->getGruntByID($id);
			
		$this->update($this->TBL_USERS, array("password"=>$pass), $grunt["id"]);

		return $grunt["name"];
	}

	public function addContribution($gid, $fid, $details) {
		return $this->insert($this->TBL_CONTRIBUTIONS, array("grunt_id"=>$gid, 
														"fund_id"=>$fid, 
														"details"=>preg_replace('/\n/', "<br/>", trim($details))));
	}

	public function createFund($lead, $fn, $token) {
		$fid = $this->insert($this->TBL_FUNDS, array("name"=>$fn,
											"subscriptionID"=>$token,
											"grunt_leader"=>intval($lead),
											"well"=>'{"amount":"0", "grunts":[]}'));

		$this->insert($this->TBL_AFFILIATIONS, array("grunt_id"=>intval($lead), 
														"fund_id"=>$fid, 
														"share"=>'{"GHRR":"50","tbv":"0","fair_market_salary":"50000","hourlyRate":"25"}'));
		return $fid;
	}

	public function updateFund($fid, $args) {
		echo $this->update($this->TBL_FUNDS, $args, $fid);
	}

	public function updateUserToken($uid, $token) {
		$this->update($this->TBL_USERS, array("BT_cutomerID"=>$token), $uid);
	}

	public function updateAffiliations($args) {
		foreach($args as $aff) {
			// update affiliations

			$info = array("share"=>json_encode($aff["share"]), "status"=>intval($aff["status"]), "jobtitle"=>$aff["jobtitle"], "grunt_type"=>$aff["grunt_type"], "alerts"=>json_encode($aff["alerts"]));
			$this->update($this->TBL_AFFILIATIONS, $info, $aff["affid"]);

			// grunt
			// die(print_r($aff));
			$info = array("name"=>$aff["name"], "image"=>$aff["image"], "email"=>$aff["email"]);
			if(isset($aff["password"]) && $aff["password"] != "**********")
				$info["password"] = $aff["password"];

			if(isset($aff["first_name"]) &&
				isset($aff["last_name"]) &&
				isset($aff["address_1"]) &&
				isset($aff["address_2"]) &&
				isset($aff["city"]) &&
				isset($aff["zip"]) &&
				isset($aff["phone"])) {
				$info["first_name"] = $aff["first_name"];
				$info["last_name"] = $aff["last_name"];
				$info["address_1"] = $aff["address_1"];
				$info["address_2"] = $aff["address_2"];
				$info["city"] = $aff["city"];
				$info["zip"] = $aff["zip"];
				$info["phone"] = $aff["phone"];
				print_r($info);
			}

			$this->update($this->TBL_USERS, $info, $aff["gid"]);
		}
	}

	public function delFund($pid) {
		// delete contribs
		$query = "DELETE FROM ".$this->TBL_CONTRIBUTIONS." WHERE fund_id=".$pid;
		$this->runQuery($query);
		// echo $query."</br>";
		// delete affs
		$query = "DELETE FROM ".$this->TBL_AFFILIATIONS." WHERE fund_id=".$pid;
		$this->runQuery($query);

		// echo $query."</br>";
		// delete fund
		$this->delete($this->TBL_FUNDS, $pid);
		// echo $query."</br>";
	}

	public function resetFund($args) {
		// delete contribs
		$query = "DELETE FROM ".$this->TBL_CONTRIBUTIONS." WHERE fund_id=".$args["pid"];
		$this->runQuery($query);

		if($args["remG"] == 1 || $args["remG"] == "1") {
			$query = "DELETE FROM ".$this->TBL_AFFILIATIONS." WHERE fund_id=".$args["pid"]." AND grunt_id!=".$args["lead"].";";
			$this->runQuery($query);
		} else {
			$query = "UPDATE ".$this->TBL_AFFILIATIONS." SET status=0 WHERE fund_id=".$args["pid"];
			$this->runQuery($query);
		}
	}

	public function updateContrib($contrib) {
		$info = array("details"=>json_encode($contrib["details"]));
		$this->update($this->TBL_CONTRIBUTIONS, $info, $contrib["id"]);
	}

	public function getBillingInfo($gid) {
		$query = "SELECT * FROM ".$this->TBL_USERS." WHERE id=".$gid;

		$result = $this->fetchByQuery($query);
		// die($query);
		$info = array();
		while($row = $result->fetch_assoc()) {
			$info = $row;
		}
		return $info;
	}

	public function addGrunt($args) {
		// die(print_r($args));
		if(!isset($args["lead"]) || strlen($args["lead"]) < 1 )
			die();
		if(intval($args["fexec"]) == 1) 
			die(print_r($args));
		$pieID = $args["fid"];
		unset($args["fid"]);

		$share = $args["share"];
		unset($args["share"]);

		if( !isset($args["accType"]) )
			$gtype = 3;
		else 
			$gtype = intval($args["accType"]);

		$pass = strtolower($args["name"]);
		$args["password"] = md5($pass);

		// mail template
		$body = "Congratulations! You have been invited to join the ".stripslashes($args["pname"])." team and earn slices in the ".$args["pname"]." pie!<br /><br />"
				."Visit <a href='www.slicingpie.com/pieslicer'>SlicingPie.com/pieslicer</a> to login<br />";

		// add grunt
		$share = json_encode($share);
		$grunt = $this->getGruntByEmail($args["email"]);
		$grunt["grunt_exists"] = false;
		$grunt["aff_exists"] = false;
		$bak = $args;
		if(!isset($grunt["id"])) {
			$body .= " Username: ".$args["email"]." <br />Temporary Password: ".$pass;
			unset($args["fexec"]);
			unset($args["accType"]);
			unset($args["pname"]);
			unset($args["lead"]);
			// die(print_r($args));
			$args["image"] = "view/images/user-".rand(1,5).".jpg";
			$grunt["id"] = $this->insert($this->TBL_USERS, $args);
		} else {
			$grunt["grunt_exists"] = true;
		}

		$args = $bak;
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Pie Slicer <no-reply@SlicingPie.com>' . "\r\n";

		// add affiliation
		if(!$this->isAffExists($pieID, $grunt["id"])) {
			$this->insert($this->TBL_AFFILIATIONS, array("grunt_id"=>$grunt["id"], 
															"fund_id"=>$pieID, 
															"share"=>$share, 
															"grunt_type"=>$gtype));




			mail($args["email"],
				"SlicingPie: ".$args["lead"]." Invited You to ".$args["pname"], 
				$body
				, $headers);

		} else {
			$grunt["aff_exists"] = false;
		}

		$query = "SELECT * FROM affiliations LEFT JOIN grunts ON affiliations.grunt_id=grunts.id WHERE affiliations.fund_id=".$pieID." AND grunt_id=".$grunt["id"];
		// die($query);
		$result = $this->fetchByQuery($query);
		$result = $result->fetch_assoc();
		$result["gid"] = intval($result["id"]);
		$result["share"] = json_decode($result["share"]);

		$result["grunt_exists"] = $grunt["grunt_exists"];
		$result["aff_exists"] = $grunt["aff_exists"];

		return $result;

	}

	public function isGruntExists($email) {
		$dup = $this->getGruntByEmail($email);
		if(isset($dup["id"]))
			return true;
		return false;
	}

	public function getGruntByEmail($email) {
		$query = "SELECT * FROM ".$this->TBL_USERS." WHERE email='{$email}'";
		$result = $this->fetchByQuery($query);
		$row = $result->fetch_array();
		$result->free();
		return $row;
	}

	public function getGruntByID($id) {
		$query = "SELECT * FROM ".$this->TBL_USERS." WHERE id='{$id}'";
		$result = $this->fetchByQuery($query);
		$row = $result->fetch_array();
		$result->free();
		return $row;
	}

	public function delGrunt($args) {
		return $this->delete($this->TBL_AFFILIATIONS, $args["id"]);
	}

	public function delContribs($args) {
		$query = "UPDATE ".$this->TBL_CONTRIBUTIONS." SET flag=1 WHERE ";
		if(is_array($args["cid"])) {
			$k = 0;
			foreach ($args["cid"] as $cd) {
				$query .= $k==0 ? "id={$cd} " : "OR id={$cd} ";
				$k++;
			}
		} else
			$query .= (isset($args["cid"]) ? "id=".$args["cid"] : "grunt_id=".$args["gid"]." AND fund_id=".$args["fid"]);
		print_r($args);
		echo $query;
		$this->runQuery($query);
	}

	private function isAffExists($fid, $gid) {
		$query = "SELECT COUNT(*) AS count FROM ".$this->TBL_AFFILIATIONS." WHERE fund_id=".$fid." AND grunt_id=".$gid;
		$res = $this->fetchByQuery($query);
		$res = $res->fetch_row();

		if($res[0] > 0) return true;
		else return false;
	}

	protected function fetchByQuery($query) {
		if($res = $this->conn->query($query)) return $res;
		// die(mysqli_error($this->conn));
		return false;
	}

	protected function runQuery($query) {
		if($this->conn->query($query)) return true;
		// die(mysqli_error($this->conn));
		return false;
	}

	protected function runQueryReturnId($query) {
		if($this->conn->query($query))
			return $this->conn->insert_id;
		// die(mysqli_error($this->conn));
		// echo $this->conn->insert_id;
	}

	private function runMultiQuery($query) {
		$this->conn->multi_query($query);
	}

}