<?php
define("TEMPLATES_DIRECTORY", "view/templates");
class View {

	public function get_header($args) {
		require('view/header.php');
	}

	public function get_footer() {
		require('view/footer.php');
	}

	public function get_home_url() {
		return HOME_URL;
	}

	public function get_template($template = "", $args = null) {
		if($template == "") {
			$child = explode("_",get_calling_class());
			$child = $child[0];
		}
		else 
			$child = $template;
		require(TEMPLATES_DIRECTORY."/".$child.".php");
	}

}