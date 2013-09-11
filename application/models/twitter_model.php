<?php

use Guzzle\Http\Client;


class Twitter_model extends CI_Model {

	protected $twitter_client;

	public function __construct() {
		parent::__construct();

		// Create a client to work with the Twitter API
		$this->twitter_client = new Client('https://api.twitter.com/{version}', array(
		    'version' => '1.1'
		));

		// Sign all requests with the OauthPlugin
		$this->twitter_client->addSubscriber(new Guzzle\Plugin\Oauth\OauthPlugin(array(
		    'consumer_key'  => $_ENV['secrets']['twitter_consumer_key'],
		    'consumer_secret' => $_ENV['secrets']['twitter_consumer_secret'],
		    'token'       => $_ENV['secrets']['twitter_token'],
		    'token_secret'  => $_ENV['secrets']['twitter_token_secret'],
		)));

	}

	public function read($path) {

		return $this->twitter_client->get($path)->send()->json();
		// >>> {"public_gists":6,"type":"User" ...

	}

	public function create() {

	}

}