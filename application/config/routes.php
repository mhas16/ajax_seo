<?php

Pigeon::map(function($r){

	$r->route('api', false, function($r){

		$r->resources('blog');
		$r->resource('v1/robot');
		
		$r->route('test', 'test/index');
		$r->route('test/twitter_test', 'test/twitter_test');	

	});


	$r->route('cli', false, function($r){

		$r->route('migrate', 'migrate/index');
		$r->route('migrate/latest', 'migrate/latest');
		$r->route('migrate/current', 'migrate/current');
		$r->route('migrate/version/(:num)', 'migrate/version/$1');
		$r->route('migrate/restart',  'migrate/restart');
		$r->route('migrate/restart/(:num)',  'migrate/restart/$1');

	});

	$r->route('(.*)', 'home#index');

});

$route = Pigeon::draw();

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;