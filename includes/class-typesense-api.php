<?php
/**
 * Typesense_API class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

use Typesense\Client;

/**
 * Class Typesense_API
 *
 * @since 1.0.0
 */
class Typesense_API {

	/**
	 * The Client instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * The Typesense_Settings instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Settings
	 */
	private $settings;
	public $dummy;
	/**
	 * Typesense_API constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param Typesense_Settings $settings The Typesense_Settings instance.
	 */
	public function __construct( Typesense_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Check if the Aloglia API is reachable.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return bool
	 */
	public function is_reachable() {
		if ( ! $this->settings->get_api_is_reachable() ) {
			return false;
		}

		try {
			$client = $this->get_client();
		} catch ( Exception $e ) {
			return false;
		}

		return null !== $client;
	}

	/**
	 * Get the Client.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Client|null
	 */
	public function get_client(){
		$api_key        = (string)$this->settings->get_api_key();
		$host			= (string)$this->settings->get_host();
		$port			= (string)$this->settings->get_port();

		$this->client = new Client(
			[
			  'api_key'         => $api_key,
			  'nodes'           => [
				[
				  'host'     => $host,
				  'port'     => $port,
				  'protocol' => 'http',
				],
			  ],
			  'connection_timeout_seconds' => 2,
			]
		  );

		return $this->client;
	}
}