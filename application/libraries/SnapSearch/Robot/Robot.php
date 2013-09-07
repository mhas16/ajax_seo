<?php

namespace SnapSearch\Robot;

class Robot {

	protected $options;

	public function __construct(Options $options = null){
		echo "I am the robot";
		$options->my_options();
		$this->options = ($options) ? $options : new Options;
	}

}