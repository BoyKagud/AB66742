<?php

class Home_Model extends Model {
	
	public function get_DOM() {
		return parent::get_session_data();
	}

	public function get_ufunds($gid) {
		return parent::get_funds($gid);
	}

}