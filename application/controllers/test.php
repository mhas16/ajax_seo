<?php

use SnapSearch\Robot\Robot;
use SnapSearch\Robot\Options;

class Test extends CI_Controller{

	public function __construct() {
		parent::__construct();
	}

	public function index() {
		$robot = new Robot(new Options);

		\SnapSearch\Robot\Options::my_options();
	}

}