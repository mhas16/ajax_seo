<?php

use Michelf\MarkdownExtra;

/**
 *	This is the blog model class that is responsible for the data to and from the Blog database table 
 */

class Blog_model extends CI_Model {
	
	protected $markdown_parser;

	// Declaring the $errors variable
	protected $errors;

	// v---- Needs more elaboration !!!!! ----v
	// calling the parent (CI_Model) construct function to make sure we get any needed dependencies !! 
	public function __construct() {
		
		parent::__construct();
		// All of the following methods needs validation to be done!!
		
		$this->markdown_parser = new MarkdownExtra();

		// Loading the validation library - Adding alias to it
		$this->load->library('form_validation', false, 'validator');

	}

	
/**
 * 	Starting to creat our CRUD functions for the model
 */

	/**
	 *  Defining the create() method
	 * 	Accepts $data as an Array
	 */
	public function create($input_data) {

		$data = elements(array('title', 'content', 'authorId'), $input_data, null);
		$data['date'] = date('Y-m-d H:i:s');

		$this->validator->set_rules(array(
			array(
				'field'	=> 'title',
				'label'	=> 'Title',
				'rules'	=> 'required|trim|max_length[100]|htmlspecialchars',
			),
			array(
				'field'	=> 'content',
				'label'	=> 'Content',
				'rules'	=> 'required|trim|htmlspecialchars',
			),
			array(
				'field'	=> 'authorId',
				'label'	=> 'Author Id',
				'rules'	=> 'required|trim|is_natural_no_zero'
			)
		));

		$this->validator->set_data($data);

		if ($this->validator->run() == false) {
			$this->errors = array(
				'validation_error'	=> $this->validator->error_array(),
			);
			return false;
		}

		// Calling the insert() method of the db class to insert the data recived from the controller into the table
		$query = $this->db->insert('blog', $data);

		// Checking the result of the query
		if (!$query){

			// Recording the error message, number and the last query for debugging
			$msg = $this->db->error()['message'];
			$num = $this->db->error()['code'];
			$last_query = $this->db->last_query();

			//	Logging the error to the ??? ---v
			log_message('error', "Problem inserting into Blogs table $msg ($num) using this query: $last_query");

			//	Preparing an error message to be desplayed for the users
			$this->errors = array(
				'system_error' => 'Problem inserting new blog post.'
			);

			return false;

		}else{

			//	Return the id of the row inserted in the table
			return $this->db->insert_id();
		
		}
	}

	public function read($id) {

		$query = $this->db->get_where('blog', array('id' => $id));

		if ($query->num_rows() > 0) {
			
			$row = $query->row();
			$data = array(
				'id'			=> $row->id,
				'title'			=> $row->title,
				'permalink'		=> url_title($row->title, '_'),
				'content'		=> $row->content,
				'contentParsed'	=> $this->parse_markdown($row->content),
				'date'			=> $row->date,
				'authorId'		=> $row->authorId,
			);
			return $data;

		}else{

			$this->errors = array(
				'error' => 'No blog post found.'
			);
			return false;

		}

	}

	public function read_all($limit = false, $offset = false) {

		$limit = ($limit) ? (int) $limit : 20;
		$offset = ($offset) ? (int) $offset : 0;

		$this->db->order_by('date', 'DESC');
		$this->db->limit($limit, $offset);
		$query = $this->db->get('blog');

		if ($query->num_rows() > 0) {

			foreach ($query->result() as $row) {
				$data[] = array(
					'id'			=> $row->id,
					'title'			=> $row->title,
					'permalink'		=> url_title($row->title, '_'),
					'content'		=> $row->content,
					'contentParsed'	=> $this->parse_markdown($row->content),
					'date'			=> $row->date,
					'authorId'		=> $row->authorId,
				);
			}
			return $data;

		}else{

			$this->errors = array(
				'error' => 'No blog posts found.'
			);
			return false;

		}

	}

	public function update($id, $input_data) {

		$data = elements(array('title', 'content', 'authorId'), $input_data, null);

		$this->validator->set_rules(array(
			array(
				'field'	=> 'title',
				'label'	=> 'Title',
				'rules'	=> 'trim|max_length[100]|htmlspecialchars',
			),
			array(
				'field'	=> 'content',
				'label'	=> 'Content',
				'rules'	=> 'trim|htmlspecialchars',
			),
			array(
				'field'	=> 'authorId',
				'label'	=> 'Author Id',
				'rules'	=> 'trim|is_natural_no_zero'
			)
		));

		if ($this->validator->run() == false){
			$this->errors = array(
				'validation_error'	=> $this->validator->error_array();
			);
			return false;
		}

		$this->db->where('id', $id);
		$query = $this->db->update('blog', $data);

		if ($this->db->affected_rows() > 0){

			return true;

		}else{

			$this->errors = array(
				'error' => 'Nothing to update.'
			);
			return false;

		}

	}

	public function delete($id) {

		$this->db->where('id', $id);
		$query = $this->db->delete('blog');

		if ($this->db->affected_rows() > 0){

			return true;

		}else{

			$this->errors = array(
				'error' => 'Nothing to delete.'
			);
			return false;

		}

	}

	public function get_errors() {
		return $this->errors;
	}

	protected function parse_markdown($input) {

		return $this->markdown_parser->transform($input);

	}

}