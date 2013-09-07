<?php

class Robot extends CI_Controller{

	public function __construct(){

		parent::__construct();

	}
	
	public function show(){

		echo 'Hello Second';

	}

	public function create(){

		echo 'This is the Create function';

	}

	public function update(){

		echo 'This is the Update funcion';

	}

	public function delete(){

		echo 'This is the Delete function';

	}

}