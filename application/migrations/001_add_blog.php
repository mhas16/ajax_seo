<?php

class Migration_add_blog extends CI_Migration {

	public function up() {

		// id, title, content, date, authorId
		$this->dbforge->add_field('id');
		$this->dbforge->add_field(array(
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
			),
			'content' => array(
				'type' => 'TEXT',
			),
			'date' => array(
				'type' => 'DATETIME',
			),
			'authorId' => array(
				'type' => 'INT',
			),
		));

		$this->dbforge->create_table('blog');
		
		$this->db->query('ALTER TABLE `blog` ENGINE = MYISAM');

		$this->db->query('ALTER TABLE `blog` ADD FULLTEXT(`content`)');

	}



	public function down() {

		$this->dbforge->drop_table('blog');

	}

}