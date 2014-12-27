<?php
if(!class_exists("Controller")) {
	if(file_exists("system/Controller.php"))
		require_once("system/Controller.php");
	else
		require_once("../system/Controller.php");
}
class Payments_Controller extends Controller {

	private $model;
	public $clientToken;

	public function __construct($args = array()) {
		$getPage = $args["getPage"];
		$nonce = $args["payment_method_nonce"];
		if(file_exists("controller/braintree/lib/Braintree.php"))
			require_once("controller/braintree/lib/Braintree.php");
		else
			require_once("../controller/braintree/lib/Braintree.php");

		Braintree_Configuration::environment('production');
		Braintree_Configuration::merchantId('wxzm55ttsv9mk4zv');
		Braintree_Configuration::publicKey('8bny8bmrc48vqctv');
		Braintree_Configuration::privateKey('6bdfe2ff9a74f8cf86b95893f36737e6');


		// Braintree_Configuration::environment('sandbox');
		// Braintree_Configuration::merchantId('swqw3gz75nbwdwft');
		// Braintree_Configuration::publicKey('8m2ztpwzgp92mk44');
		// Braintree_Configuration::privateKey('31fa50f625a60029ab3472cfc6bcb5b0');

		$this->clientToken = Braintree_ClientToken::generate();
	}

	public function getAllSubs() {
		$collection = Braintree_Subscription::search(array(
		  Braintree_SubscriptionSearch::status()->in(
		    array(Braintree_Subscription::ACTIVE)
		  )
		));
		return $collection->_ids;
	}

	public function cancelSubscription($sid) {
		$result = Braintree_Subscription::cancel($sid);
	}

	public function updateCard($args) {

		$customer = Braintree_Customer::find($args["BT_customerID"]);
		$token = $customer->creditCards[0]->token;

		$result = Braintree_Customer::update(
		  $args["BT_customerID"],
		  array(
		    'creditCard' => array(
		        'paymentMethodNonce' => $args["nce"],
		        'options' => array(
		            'updateExistingToken' => $token
		        )
		     )
		  )
		);

		echo $customer;
	}

	public function getPlanID($token) {
		switch($token) {
			case "annual": return "vfs6";
			case "triannual": return "tc3r";
			case "monthly": return "fhdw";
			case "forever": return "2pdb";
			default: return -1;
		}
	}

	public function createSubscription($pmtoken=0, $planId, $glead=0, $extra=array()) {
		$result = null;
		if($pmtoken==0 && $glead==0) return -1;
		if($glead == "" && $extra["BT_cutomerID"] == "0") {
			unset($extra["BT_cutomerID"]);
			unset($extra["subscription"]);
			$customer = $this->createCustomer($extra);
			$glead = $customer["customer_id"];
		} 
		$customer = Braintree_Customer::find($glead);
		if(count($customer->creditCards) > 0)
			$payment_method_token = $customer->creditCards[0]->token;
		else
			$payment_method_token = $customer->paypalAccounts[0]->token;

		try {
		    $customer = Braintree_Customer::find($glead);

		    $result = Braintree_Subscription::create(array(
		        'paymentMethodToken' => $payment_method_token,
		        'planId' => $this->getPlanID($planId)
		    ));
		    // echo $result;
		    // print_r($result);
		    // echo "PLAN ID = ".$planId;
			$ret["token"] = $result->subscription->id;
			if($ret["token"] == -1) $ret["error"] = "Customer account error";
		} catch (Braintree_Exception_NotFound $e) {
		   	$ret["error"] = $e->deepAll();
		}

	    return $ret;
	}

	public function createCustomer($args) {
		// $args["Pie"] = json_decode(stripslashes($args["Pie"]),true);
		// if(empty($args)) return;
		$ret = array();
		$result = Braintree_Customer::create(array(
		    'firstName' => $args["first_name"],
		    'lastName' => $args["last_name"],
		    'email' => $args["email"],
		    'phone' => $args["phone"],
		    'paymentMethodNonce' => $args["nonce"]
		));
		$ret["customer_id"] = $result->customer->id;
		
		if ($result->success) {
			if(isset($args["subscription"])) {
				$args["BT_cutomerID"] = $result->customer->id;
				$ret["BT_cutomerID"] = $result->customer->id;
				try {
				    $customer_id = $args["BT_cutomerID"];
				    $customer = Braintree_Customer::find($customer_id);
				    $payment_method_token = $customer->creditCards[0]->token;

					$args["planId"] = $this->getPlanID($args["subscription"]);

				    $result = Braintree_Subscription::create(array(
				        'paymentMethodToken' => $payment_method_token,
				        'planId' => $args["planId"]
				    ));
					$ret["token"] = $result->subscription->id;
					if($ret["token"] == -1) echo "Customer account error";
				} catch (Braintree_Exception_NotFound $e) {
				    echo("Failure: no customer found with ID " . $_GET["customer_id"]);
				}
				$ret["fund_id"] = $_SESSION["fund_id"];
				$ret["user_id"] = $_SESSION["user_id"];
			}
		} else {
			if(!isset($args["subscription"]) && !$result->success)
			    foreach($result->errors->deepAll() AS $error) 
			        echo($error->code . ": " . $error->message . "\n");
		}

		return $ret;
	}
}


	// echo $clientToken;

	// transaction
	// $result = Braintree_Transaction::sale(array(
	//   'amount' => '100.00',
	//   'paymentMethodNonce' => 'nonce-from-the-client'
	// ));
	// echo $result;

	// $result = Braintree_Customer::create(array(
	//     'firstName' => 'Mike',
	//     'lastName' => 'Jones',
	//     'company' => 'Jones Co.',
	//     'paymentMethodNonce' => 'nonce-from-the-client'
	// ));
	// if ($result->success) {
	//     echo($result->customer->id);
	//     echo($result->customer->creditCards[0]->token);
	// } else {
	//     foreach($result->errors->deepAll() AS $error) {
	//         echo($error->code . ": " . $error->message . "\n");
	//     }
	// }
?>