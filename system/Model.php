<?php
require_once("SQLHelper.php");
class Model {

	private static $sqlHelper;
	public function __construct() {
		Model::$sqlHelper = SQLHelper::get_instance();
	}

	public static function isGruntExists($email) {
		return Model::$sqlHelper->isGruntExists($email);
	}

	public static function isSubExists($token) {
		return Model::$sqlHelper->isSubExists($token);
	}

	public static function addLogDate($args) {
		return Model::$sqlHelper->addLogDate($args["grunt"], $args["fund"], $args["ldate"]);
	}

	public static function getBetas() {
		return Model::$sqlHelper->getBetas();
	}

	public static function signup($form) {
		unset($form["subscription"]);
		unset($form["fundname"]);
		unset($form["pword2"]);
		unset($form["nonce"]);
		$form["password"] = md5($form["password"]);
		return Model::$sqlHelper->regUser($form);
	}

	public static function updateUserToken($uid, $token) {
		return Model::$sqlHelper->updateUserToken($uid, $token);
	}

	public static function generateResetToken($gid, $tid) {
		return Model::$sqlHelper->generateResetToken($gid, $tid);
	}

	public static function updateTransaction($args) {
		// die(print_r($args));
		if(isset($args["BT_cutomerID"]))
			Model::$sqlHelper->updateGruntCID($args["BT_cutomerID"], $args["user_id"]);
		return Model::$sqlHelper->updateTransID($args["fund_id"],$args["token"]);
	}

	public static function getContributions($gid, $fid) {
		return Model::$sqlHelper->getContributions($gid, $fid);
	}

	public static function getAllUsers() {
		return Model::$sqlHelper->getAllUsers();
	}

	public static function get_session_data() {
		return Model::$sqlHelper->get_session_data($_SESSION['user_id'], $_SESSION["fund_id"]);
	}

	public static function get_grunts($fid, $gid, $lead) {
		return Model::$sqlHelper->getGruntsDS($fid, $gid, $lead);
	}

	public static function addContrib($gid, $fid, $details) {
		return Model::$sqlHelper->addContribution($gid, $fid, $details);
	}

	public static function updatePie($fid, $args) {
		return Model::$sqlHelper->updateFund($fid, $args);
	}

	public static function updateGrunts($args) {
		return Model::$sqlHelper->updateAffiliations($args);
	}

	public static function delFund($pid) {
		return Model::$sqlHelper->delFund($pid);
	}

	public static function resetFund($args) {
		return Model::$sqlHelper->resetFund($args);
	}

	public static function getTokenByID($tid) {
		return Model::$sqlHelper->getTokenByID($tid);
	}

	public static function deleteTokenByID($tid) {
		return Model::$sqlHelper->deleteTokenByID($tid);
	}

	public static function changePass($id, $pass) {
		return Model::$sqlHelper->changePass($id, $pass);
	}

	public static function updateContrib($contrib) {
		return Model::$sqlHelper->updateContrib($contrib);
	}

	public static function getBillingInfo($gid) {
		return Model::$sqlHelper->getBillingInfo($gid);
	}

	public static function addGrunt($args) {
		return Model::$sqlHelper->addGrunt($args);
	}

	public static function getGruntByEmail($args) {
		return Model::$sqlHelper->getGruntByEmail($args);
	}

	public static function get_funds($gid) {
		return Model::$sqlHelper->get_user_funds($gid);
	}

	public static function removeGrunt($args) {
		return Model::$sqlHelper->delGrunt($args);
	}

	public static function delContribs($args) {
		return Model::$sqlHelper->delContribs($args);
	}

	public static function createFund($l, $f, $token) {
		return Model::$sqlHelper->createFund($l, $f, $token);
	}

}