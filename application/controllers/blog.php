<?php

class Blog extends CI_Controller {

	public function __construct() {

		parent::__construct();
		$this->load->model('Blog_model');

	}
 
/**
 * Defining the index() function for requesting data from Model for Reading all blog posts
 */

	public function index() {

		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');

		$query = $this->Blog_model->read_all($limit, $offset);

		if ($query) {

			$content = $query;
			$code = 'success';

		}else{

			$this->output->set_status_header(404);
			$content = current($this->Blog_model->get_errors());
			$code = key($this->Blog_model->get_errors());

		}

		$output = array (
			'content'	=> $content,
			'code'		=> $code,
		);

		Template::compose(false, $output, 'json');

	}


	public function show($id) {

		$query = $this->Blog_model->read($id);

		if ($query){

			$content = $query;
			$code = 'success';

		}else{

			$this->output->set_status_header(404);
			$content = current($this->Blog_model->get_errors());
			$code = key($this->Blog_model->get_errors());

		}

		$output = array (
			'content'	=> $content,
			'code'		=> $code,
		);

		Template::compose(false, $output, 'json');
	
	}

	public function create() {

		//	Capturing the JSON data from the input stream
		$data = $this->input->json(false);

		// Currently hardcoding the author id, will need to come from PolyAuth later
		$data['authorId'] = 1;

		//	Passing the JSON data to the blog_model to insert into table and capturing the result (Inserted row ID) 
		$query = $this->Blog_model->create($data);

		//	Checking the result of the operation
			// if Successfull
		if ($query){

			// Set the header status code to 201 -> Created
			$this->output->set_status_header(201);

			// Preparing the Inserted Row ID and Code
			$content = $query;
			$code = 'success';

			//	if not successfull
		}else{

			//	capturing the value of the array element (Error message)
			$content = current($this->Blog_model->get_errors());
			//	capturing the key of the of the element to get the type of error (error, system_error, etc...)
			$code = key($this->Blog_model->get_errors());

			// Setting the Status header on the type of error
			if ($code == 'validation_error'){
				$this->output->set_status_header(400);

			}elseif ($code == 'system_error'){
				$this->output->set_status_header(500);
			}

		}

		// preparing the output message to be sent to the browser
		$output = array (
			'content'	=> $content,
			'code'		=> $code,
		);

		//??	
		Template::compose(false, $output, 'json');

	}


	public function update($id) {

		$data = $this->input->json(false);
		
		$query = $this->Blog_model->update($id, $data);

		if ($query) {

			$content = $id;
			$code = 'success';

		}else{

			$content = current($this->Blog_model->get_errors());
			$code = key($this->Blog_model->get_errors());

		}

		$output = array (
			'content'	=> $content,
			'code'		=> $code,
		);

		Template::compose(false, $output, 'json');

	}

	public function delete($id) {

		$query = $this->Blog_model->delete($id);

		if ($query) {

			$content = $id;
			$code = 'success';

		}else{

			$content = current($this->Blog_model->get_errors());
			$code = key($this->Blog_model->get_errors());

		}

		$output = array (
			'content'	=> $content,
			'code'		=> $code,
		);

		Template::compose(false, $output, 'json');


	}


}