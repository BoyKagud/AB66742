<?php
class Home_Controller extends Controller {

	private $model;

	public function __construct() {
		$this->model = parent::model();
		if(isset($_SESSION["user_id"])) {
			if(isset($_SESSION["fund_id"])) {
				$funds = $this->model->get_ufunds($_SESSION["user_id"]);
				$DOM = $this->model->get_DOM();
				if(is_array($DOM)) {
					$DOM["funds"] = $funds;
					if($DOM != false)
						parent::view($DOM);
					else
						die("no data found");
				} else {
					unset($_SESSION["fund_id"]);
					$funds = $this->model->get_ufunds($_SESSION["user_id"]);
					parent::view($funds); // choose fund and reg to session
				}
			} else {
				$funds = $this->model->get_ufunds($_SESSION["user_id"]);
				parent::view($funds); // choose fund and reg to session
			}
		} else {
			parent::view(false);
		}

	}

}
