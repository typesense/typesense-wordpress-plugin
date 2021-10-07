<?php
/**
 * Algolia_API class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\SearchClient;
use Typesense\Exceptions\ConfigError;

/**
 * Class Algolia_API
 *
 * @since 1.0.0
 */
class Algolia_API {

	/**
	 * The SearchClient instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var SearchClient
	 */
	private $client;

	/**
	 * The Algolia_Settings instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Algolia_Settings
	 */
	private $settings;

	/**
	 * Algolia_API constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param Algolia_Settings $settings The Algolia_Settings instance.
	 */
	public function __construct( Algolia_Settings $settings ) {
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
			// Here we check that all requirements for the PHP API SearchClient are met.
			// If they are not, instantiating the client will throw exceptions.
			$client = $this->get_client();
		} catch ( Exception $e ) {
			return false;
		}

		return null !== $client;
	}

	/**
	 * Get the SearchClient.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Typesense\Client|null
     * @throws ConfigError
     *
	 */
	public function get_client(): ?\Typesense\Client {

		$application_id = $this->settings->get_application_id();
		$api_key        = $this->settings->get_api_key();

		if (
			empty( $application_id ) ||
			empty( $api_key )
		) {
			return null;
		}

		if ( null === $this->client ) {
			$this->client = Algolia_Search_Client_Factory::create(
				(string) $this->settings->get_application_id(),
				(string) $this->settings->get_api_key()
			);
		}

		return $this->client;
	}

	/**
	 * Assert that the credentials are valid.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $application_id The Algolia Application ID.
	 * @param string $api_key        The Algolia Admin API Key.
	 *
	 * @return void
	 *
	 * @throws ConfigError
	 */
	public static function assert_valid_credentials( $application_id, $api_key ) {

		$client = Algolia_Search_Client_Factory::create(
			(string) $application_id,
			(string) $api_key
		);

        $client->debug->retrieve();
	}

	/**
	 * Check if the credentials are valid.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $application_id The Algolia Application ID.
	 * @param string $api_key        The Algolia Admin API Key.
	 *
	 * @return bool
	 */
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
	 * @param string $application_id The Algolia Application ID.
	 * @param string $search_api_key The Algolia Search API Key.
	 *
	 * @return bool
	 */
	public static function is_valid_search_api_key( $application_id, $search_api_key ) {

		$client = Algolia_Search_Client_Factory::create(
			(string) $application_id,
			(string) $search_api_key
		);

		try {
            $client->debug->retrieve();
        } catch (Exception $e) {
            return false;
        }
		return true;
	}
}
