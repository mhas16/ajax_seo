<?php

class Secrets {

	public static function load() {

		$secrets_path = FCPATH . '/secrets';
		$secrets_loaded = false;

		if(file_exists($secrets_path) AND is_dir($secrets_path)) {

			foreach (new DirectoryIterator($secrets_path) as $file) {
				
				if ($file->isDot() OR $file->getExtension() != 'php') continue;

				$secrets_loaded = true;
				
				include_once($file->getPathname());	

			}
		}

		if($secrets_loaded){

			foreach ($secrets as $key => $value) {
				
				$_ENV['secrets'][$key] = $value;
			
			}

			unset($secrets);
		
		}

	}

}