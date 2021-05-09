<?php
/**
 * Typesense_Settings class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Settings
 *
 * @since 1.0.0
 */
class Typesense_Settings {

	/**
	 * Typesense_Settings constructor.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 */
	public function __construct() {
		add_option( 'typesense_application_id', '' );
		add_option( 'typesense_search_api_key', '' );
		add_option( 'typesense_api_key', '' );
		add_option( 'typesense_post_count', 0 );
		add_option( 'typesense_api_key', '' );
		add_option( 'typesense_host', 'localhost' );
		add_option( 'typesense_port', '8108' );
		add_option( 'typesense_synced_indices_ids', array() );
		add_option( 'typesense_autocomplete_enabled', 'no' );
		add_option( 'typesense_autocomplete_config', array() );
		add_option( 'typesense_override_native_search', 'native' );
		add_option( 'typesense_index_name_prefix', 'wp_' );
		add_option( 'typesense_api_is_reachable', 'no' );
		add_option( 'typesense_powered_by_enabled', 'yes' );
	}

	/**
	 * Get the Typesense Application ID.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public function get_application_id() {
		if ( ! $this->is_application_id_in_config() ) {

			return (string) get_option( 'typesense_application_id', '' );
		}

		$this->assert_constant_is_non_empty_string( TYPESENSE_APPLICATION_ID, 'TYPESENSE_APPLICATION_ID' );

		return TYPESENSE_APPLICATION_ID;
	}

	/**
	 * Get the Typesense Search-Only API Key.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public function get_search_api_key() {
		if ( ! $this->is_search_api_key_in_config() ) {

			return (string) get_option( 'typesense_search_api_key', '' );
		}

		$this->assert_constant_is_non_empty_string( TYPESENSE_SEARCH_API_KEY, 'TYPESENSE_SEARCH_API_KEY' );

		return TYPESENSE_SEARCH_API_KEY;
	}

	/**
	 * Get the Typesense Admin API Key
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public function get_api_key() {
		return (string) get_option( 'typesense_api_key', '' );
	}

	public function get_host() {
			return (string) get_option( 'typesense_host', '' );
	}

	public function get_port() {
		return (string) get_option( 'typesense_port', '' );
	}

	/**
	 * Get the excluded post types.
	 *
	 * @author     WebDevStudios <contact@webdevstudios.com>
	 * @since      1.0.0
	 * @deprecated 1.7.0 Use Typesense_Settings::get_excluded_post_types()
	 * @see        Typesense_Settings::get_excluded_post_types()
	 *
	 * @return array
	 */
	public function get_post_types_blacklist() {
		_deprecated_function(
			__METHOD__,
			'1.7.0',
			'Typesense_Settings::get_excluded_post_types();'
		);

		return $this->get_excluded_post_types();
	}

	/**
	 * Get the excluded post types.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.7.0
	 *
	 * @return array
	 */
	public function get_excluded_post_types() {

		// Default array of excluded post types.
		$excluded = [ 'nav_menu_item' ];

		/**
		 * Filters excluded post types.
		 *
		 * @since      1.0.0
		 * @deprecated 1.7.0 Use {@see 'typesense_excluded_post_types'} instead.
		 *
		 * @param array $excluded The excluded post types.
		 */
		$excluded = (array) apply_filters_deprecated(
			'typesense_post_types_blacklist',
			[ $excluded ],
			'1.7.0',
			'typesense_excluded_post_types'
		);

		/**
		 * Filters excluded post types.
		 *
		 * @since 1.7.0
		 *
		 * @param array $excluded The excluded post types.
		 */
		$excluded = (array) apply_filters( 'typesense_excluded_post_types', $excluded );

		// Native WordPress.
		$excluded[] = 'revision';

		// Native to Typesense Search plugin.
		$excluded[] = 'typesense_task';
		$excluded[] = 'typesense_log';

		// Native to WordPress VIP platform.
		$excluded[] = 'kr_request_token';
		$excluded[] = 'kr_access_token';
		$excluded[] = 'deprecated_log';
		$excluded[] = 'async-scan-result';
		$excluded[] = 'scanresult';

		return array_unique( $excluded );
	}

	/**
	 * Get synced indices IDs.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return array
	 */
	public function get_synced_indices_ids() {
		$ids = array();

		// Gather indices used in autocomplete experience.
		$config = $this->get_autocomplete_config();
		foreach ( $config as $index ) {
			if ( isset( $index['index_id'] ) ) {
				$ids[] = $index['index_id'];
			}
		}

		// Push index used in instantsearch experience.
		// Todo: we should allow users to index without using the shipped search UI or backend implementation.
		if ( $this->should_override_search_in_backend() || $this->should_override_search_with_instantsearch() ) {
			$ids[] = $this->get_native_search_index_id();
		}

		return (array) apply_filters( 'typesense_get_synced_indices_ids', $ids );
	}

	/**
	 * Get excluded taxonomies.
	 *
	 * @author     WebDevStudios <contact@webdevstudios.com>
	 * @since      1.0.0
	 * @deprecated 1.7.0 Use Typesense_Settings::get_excluded_taxonomies()
	 * @see        Typesense_Settings::get_excluded_taxonomies()
	 *
	 * @return array
	 */
	public function get_taxonomies_blacklist() {
		_deprecated_function(
			__METHOD__,
			'1.7.0',
			'Typesense_Settings::get_excluded_taxonomies();'
		);

		return $this->get_excluded_taxonomies();
	}

	/**
	 * Get excluded taxonomies.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.7.0
	 *
	 * @return array
	 */
	public function get_excluded_taxonomies() {

		// Default array of excluded taxonomies.
		$excluded = [
			'nav_menu',
			'link_category',
			'post_format',
		];

		/**
		 * Filters excluded taxonomies.
		 *
		 * @since      1.0.0
		 * @deprecated 1.7.0 Use {@see 'typesense_excluded_taxonomies'} instead.
		 *
		 * @param array $excluded The excluded taxonomies.
		 */
		$excluded = (array) apply_filters_deprecated(
			'typesense_taxonomies_blacklist',
			[ $excluded ],
			'1.7.0',
			'typesense_excluded_taxonomies'
		);

		$excluded = (array) apply_filters( 'typesense_excluded_taxonomies', $excluded );

		return $excluded;
	}

	/**
	 * Get the autocomplete enabled option setting.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string Can be 'yes' or 'no'.
	 */
	public function get_autocomplete_enabled() {
		return get_option( 'typesense_autocomplete_enabled', 'no' );
	}

	/**
	 * Get the autocomplete config.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return array
	 */
	public function get_autocomplete_config() {
		return (array) get_option( 'typesense_autocomplete_config', array() );
	}

	/**
	 * Get the override native search option setting.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string Can be 'native' 'backend' or 'instantsearch'.
	 */
	public function get_override_native_search() {
		$search_type = get_option( 'typesense_override_native_search', 'native' );

		// BC compatibility.
		if ( 'yes' === $search_type ) {
			$search_type = 'backend';
		}

		return $search_type;
	}

	/**
	 * Determines whether we should override search in backend.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function should_override_search_in_backend() {
		return $this->get_override_native_search() === 'backend' || $this->should_override_search_with_instantsearch();
	}

	/**
	 * Determines whether we should override search with instantsearch.js.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function should_override_search_with_instantsearch() {
		$value = $this->get_override_native_search() === 'instantsearch';

		return (bool) apply_filters( 'typesense_should_override_search_with_instantsearch', $value );
	}

	/**
	 * Get native search index ID.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public function get_native_search_index_id() {
		return (string) apply_filters( 'typesense_native_search_index_id', 'searchable_posts' );
	}

	/**
	 * Get the index name prefix.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public function get_index_name_prefix() {
		if ( ! $this->is_index_name_prefix_in_config() ) {

			return (string) get_option( 'typesense_index_name_prefix', 'wp_' );
		}

		$this->assert_constant_is_non_empty_string( TYPESENSE_INDEX_NAME_PREFIX, 'TYPESENSE_INDEX_NAME_PREFIX' );

		return TYPESENSE_INDEX_NAME_PREFIX;
	}

	/**
	 * Makes sure that constants are non empty strings.
	 *
	 * This makes sure that we fail early if the environment configuration is wrong.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @param mixed  $value         The constant value to check.
	 * @param string $constant_name The constant name to check.
	 *
	 * @throws RuntimeException If the constant is not a string or is empty.
	 */
	protected function assert_constant_is_non_empty_string( $value, $constant_name ) {
		if ( ! is_string( $value ) ) {
			throw new RuntimeException( sprintf( 'Constant %s in wp-config.php should be a string, %s given.', $constant_name, gettype( $value ) ) );
		}

		if ( 0 === mb_strlen( $value ) ) {
			throw new RuntimeException( sprintf( 'Constant %s in wp-config.php cannot be empty.', $constant_name ) );
		}
	}

	/**
	 * Determines if the TYPESENSE_APPLICATION_ID is defined.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function is_application_id_in_config() {
		return defined( 'TYPESENSE_APPLICATION_ID' );
	}

	/**
	 * Determines if the TYPESENSE_SEARCH_API_KEY is defined.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function is_search_api_key_in_config() {
		return defined( 'TYPESENSE_SEARCH_API_KEY' );
	}

	/**
	 * Determines if the TYPESENSE_API_KEY is defined.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function is_api_key_in_config() {
		return defined( 'TYPESENSE_API_KEY' );
	}

	/**
	 * Determines if the TYPESENSE_INDEX_NAME_PREFIX is defined.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 * @return bool
	 */
	public function is_index_name_prefix_in_config() {
		return defined( 'TYPESENSE_INDEX_NAME_PREFIX' );
	}

	/**
	 * Get the API is reachable option setting.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function get_api_is_reachable() {
		$enabled = get_option( 'typesense_api_is_reachable', 'no' );

		return 'yes' === $enabled;
	}

	/**
	 * Set the API is reachable option setting.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @param bool $flag If the API is reachable or not, 'yes' or 'no'.
	 */
	public function set_api_is_reachable( $flag ) {
		$value = (bool) true === $flag ? 'yes' : 'no';
		update_option( 'typesense_api_is_reachable', $value );
	}

	/**
	 * Determine if powered by is enabled.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function is_powered_by_enabled() {
		$enabled = get_option( 'typesense_powered_by_enabled', 'yes' );

		return 'yes' === $enabled;
	}

	/**
	 * Enable the powered by option setting.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 */
	public function enable_powered_by() {
		update_option( 'typesense_powered_by_enabled', 'yes' );
	}

	/**
	 * Disable the powered by option setting.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 */
	public function disable_powered_by() {
		update_option( 'typesense_powered_by_enabled', 'no' );
	}
}
