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


	public function test_js() {
		echo '

			<html>
				<head>
					<title>Test of inline script</title>
					<script>

						var requests = [];

						var oldOpen = XMLHttpRequest.prototype.open;
						XMLHttpRequest.prototype.open = function(){
							
							this.addEventListener("loadstart", function(event){
								requests.push(event.target);
							}, false);

							this.addEventListener("loadend", function(event){

								for( var i = requests.length - 1; i >= 0; i-- ){
									if( requests[i] === event.target ){
										requests.splice(i, 1);
									}
								}

							}, false);

							oldOpen.apply(this, arguments);
						};

						setTimeout(function(){

							var checkAjaxRequests = function(){
								setTimeout(function(){
									if (requests.length === 0){
										console.log("All AJAX requests finished.");
										//pass through to next operation
									}else{
										checkAjaxRequests();
									}
								}, 500);
							};

							checkAjaxRequests();

						}, 1000);






						var xhr1 = new XMLHttpRequest();
						xhr1.open("get", "../blog/26");
						xhr1.send();

						var xhr2 = new XMLHttpRequest();
						xhr2.open("get", "../blog/15");
						xhr2.send();


					</script>
				</head>
				<body>
				</body>
			</html>

		';
	} 

}