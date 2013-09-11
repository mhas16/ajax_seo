<?php

class Twitter_test extends CI_Controller {

	public function __construct() {

		parent::__construct();
		$this->load->model('Twitter_model');
		
	}

	public function show() {

		$query = $this->Twitter_model->read('statuses/user_timeline.json');

		var_dump(json_decode($query));

	}

}