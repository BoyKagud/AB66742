<?php

class Home_View extends View {
	
	function __construct($vars) {
		// die(print_r($vars["funds"]));
		if($vars == false) die("dead"); // redirect to WP
 		if(isset($vars["TBV"])) {
 			$this->get_header($vars);
			$this->get_template("Home", $vars);
			$this->get_template("Summary");
			$this->get_template("Reports");
			$this->get_template("Contributions");
			$this->get_template("Settings");
			$this->get_template("Help");
			$this->get_footer();
		} else 
			$this->get_template("choose_fund", $vars);
	}

}