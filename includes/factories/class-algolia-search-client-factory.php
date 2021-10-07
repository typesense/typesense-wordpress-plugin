<?php
/**
 * Algolia_Search_Client_Factory class file.
 *
 * @since   1.6.0
 * @package WebDevStudios\WPSWA
 */

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Typesense\Client as TypesenseClient;
use Typesense\Exceptions\ConfigError;

/**
 * Class Algolia_Search_Client_Factory
 *
 * @since 1.6.0
 */
class Algolia_Search_Client_Factory {

    /**
     * Create an Algolia SearchClient.
     *
     * @author WebDevStudios <contact@webdevstudios.com>
     * @since  1.6.0
     *
     * @param string $app_id  The Algolia Application ID.
     * @param string $api_key The Algolia API Key.
     *
     * @return TypesenseClient
     * @throws ConfigError
     *
     */
	public static function create( string $app_id, string $api_key ): TypesenseClient {

		$integration_name = (string) apply_filters(
			'algolia_ua_integration_name',
			'WP Search with Algolia'
		);

		$integration_version = (string) apply_filters(
			'algolia_ua_integration_version',
			ALGOLIA_VERSION
		);

		UserAgent::addCustomUserAgent(
			$integration_name,
			$integration_version
		);

		global $wp_version;

		UserAgent::addCustomUserAgent(
			'WordPress',
			$wp_version
		);

		$http_client = Algolia_Http_Client_Interface_Factory::create();

		Algolia::setHttpClient( $http_client );

        return new TypesenseClient(
            [
                'api_key' => $api_key,
                'nodes' => self::convert_app_id_to_typesense_nodes($app_id),
                'client' => $http_client,
            ]
        );
	}

    private static function convert_app_id_to_typesense_nodes($app_id): array
    {
        $urls = explode(',', $app_id);
        return array_map(
            function ($url) {
                $url_components = parse_url($url);
                return [
                    'host' => array_key_exists('host', $url_components) ? $url_components['host'] : 'undefined-host',
                    'port' => array_key_exists('port', $url_components) ? $url_components['port'] : '443',
                    'protocol' => array_key_exists('scheme', $url_components) ? $url_components['scheme'] : 'https'
                ];
            }, $urls);
    }
}
