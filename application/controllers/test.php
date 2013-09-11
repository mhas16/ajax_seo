<?php

use SnapSearch\Robot\Robot;
use SnapSearch\Robot\Options;

class Test extends CI_Controller{

	public function __construct() {
		parent::__construct();
		$this->load->model('Twitter_model');
	}

	public function index() {
		$robot = new Robot(new Options);

		\SnapSearch\Robot\Options::my_options();
	}

	public function twitter_test(){

		$query = $this->Twitter_model->read('statuses/user_timeline.json');

		Template::compose(false, $query, 'json');

	}

}