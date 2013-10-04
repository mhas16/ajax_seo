<?php

use SnapSearch\Robot\Robot;
use SnapSearch\Robot\Options;

class Test extends CI_Controller{

	public function __construct() {
		parent::__construct();
		$this->load->model('Twitter_model');
		$this->load->model('Robot_model');
	}

	public function index() {
		$robot = new Robot(new Options);

		\SnapSearch\Robot\Options::my_options();
	}

	public function twitter_test(){

		$query = $this->Twitter_model->read('statuses/user_timeline.json');

		Template::compose(false, $query, 'json');

	}

	public function test_shell(){
		$descriptor_spec = array(

			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),

			);

		$cmd = 'traceroute -m 2 polycademy.com';

		$process = proc_open($cmd, $descriptor_spec, $pipes);

		if(is_resource($process)){

			while (!feof($pipes[1])){

				$output = fgets($pipes[1]);
				echo $output;
				ob_flush();
				flush();

			} 

			fclose($pipes[1]);

			$errors = stream_get_contents($pipes[2]);

			fclose($pipes[2]);

			$exit_code = proc_close($process);

			echo $exit_code;
			
		}

		
	}

	public function test_robot(){
		$data = array(
			'url'	=> 'http://google.com',
		);

		var_dump($this->Robot_model->create($data));
	}


}