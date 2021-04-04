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
			// Here we check that all requirements for the PHP API Client are met.
			// If they are not, instantiating the client will throw exceptions.
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

		//$application_id = $this->settings->get_application_id();
		//$api_key        = $this->settings->get_api_key();
		/*
		if (
			empty( $application_id ) ||
			empty( $api_key )
		) {
			return null;
		}
		*/
		$this->client = new Client(
			[
			  'api_key'         => '123',
			  'nodes'           => [
				[
				  'host'     => 'localhost',
				  'port'     => '8108',
				  'protocol' => 'http',
				],
			  ],
			  'connection_timeout_seconds' => 2,
			]
		  );

		return $this->client;
	}

	/**
	 * Assert that the credentials are valid.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $application_id The Typesense Application ID.
	 * @param string $api_key        The Typesense Admin API Key.
	 *
	 * @return void
	 *
	 * @throws Exception If the Typesense Admin API Key does not have correct ACLs.
	 */
	/*
	public static function assert_valid_credentials( $application_id, $api_key ) {

		$client = Typesense_Search_Client_Factory::create(
			(string) $application_id,
			(string) $api_key
		);

		// This checks if the API Key is an Admin API key.
		// Admin API keys have no scopes so we need a separate check here.
		try {
			$client->listApiKeys();

			return;
		} catch ( Exception $exception ) { // phpcs:ignore --- intentionally empty catch.
		}

		// If this call does not succeed, then the application_ID or API_key is/are wrong.
		// This will raise an exception.
		$key = $client->getApiKey( (string) $api_key );

		$required_acls = array(
			'addObject',
			'deleteObject',
			'listIndexes',
			'deleteIndex',
			'settings',
			'editSettings',
		);

		$missing_acls = array();
		foreach ( $required_acls as $required_acl ) {
			if ( ! in_array( $required_acl, $key['acl'], true ) ) {
				$missing_acls[] = $required_acl;
			}
		}

		if ( ! empty( $missing_acls ) ) {
			throw new Exception(
				'Your admin API key is missing the following ACLs: ' . implode( ', ', $missing_acls )
			);
		}
	}

	/**
	 * Check if the credentials are valid.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $application_id The Typesense Application ID.
	 * @param string $api_key        The Typesense Admin API Key.
	 *
	 * @return bool
	public static function is_valid_credentials( $application_id, $api_key ) {
		try {
			self::assert_valid_credentials( $application_id, $api_key );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the Search API Key is valid.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $application_id The Typesense Application ID.
	 * @param string $search_api_key The Typesense Search API Key.
	 *
	 * @return bool
	public static function is_valid_search_api_key( $application_id, $search_api_key ) {

		$client = Typesense_Search_Client_Factory::create(
			(string) $application_id,
			(string) $search_api_key
		);

		// If this call does not succeed, the application_ID and/or API_key are wrong.
		try {
			$acl = $client->getApiKey( $search_api_key );
		} catch ( TypesenseException $e ) {
			return false;
		}

		// We expect a search only key for security reasons. Will be used in front.
		$scopes = array_flip( $acl['acl'] );
		if ( ! isset( $scopes['search'] ) ) {
			return false;
		}
		unset( $scopes['search'] );

		if ( isset( $scopes['settings'] ) ) {
			unset( $scopes['settings'] );
		}

		if ( isset( $scopes['listIndexes'] ) ) {
			unset( $scopes['listIndexes'] );
		}

		// Short circuit ACL checks for local development.
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
			return true;
		}

		if ( ! empty( $scopes ) ) {
			// The API key has more permissions than allowed.
			return false;
		}

		// We do expect a search key without unlimited TTL.
		if ( 0 !== $acl['validity'] ) {
			return false;
		}

		return true;
	}
	*/
}