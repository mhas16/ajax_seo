<?php

use Guzzle\Http\Client;

class Robot_model extends CI_Model{
	
	protected $client_request;

	public function __construct(){
		parent::__construct();

		$this->client_request = new Client();
	}

	public function create($payload){
		$request = $this->client_request->post('http://127.0.0.1:8989', array(), json_encode($payload));
		try{
			$response = $request->send()->json();
			return $response;
		}catch(Expception $e){
			var_dump($e);
		}
	}

}