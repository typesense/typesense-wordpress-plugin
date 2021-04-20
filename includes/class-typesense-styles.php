<?php
/**
 * Algolia_Styles class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.5.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Algolia_Styles
 *
 * @since 1.5.0
 */
class Typesense_Styles {

	/**
	 * Algolia_Styles constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ] );
	}

	/**
	 * Register styles.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 */
	public function register_styles() {

		wp_register_style(
			'algolia-autocomplete',
			TYPESENSE_PLUGIN_URL . 'css/algolia-autocomplete.css',
			[],
			TYPESENSE_VERSION
		);

		wp_register_style(
			'algolia-instantsearch-native',
			TYPESENSE_PLUGIN_URL . 'css/algolia-instantsearch.css',
			[],
			TYPESENSE_VERSION
		);

		wp_register_style(
			'algolia-instantsearch',
			'https://cdn.jsdelivr.net/npm/instantsearch.css@7/themes/algolia-min.css',
			[],
			TYPESENSE_VERSION
		);
	}
}
