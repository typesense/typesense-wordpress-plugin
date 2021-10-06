<?php
/**
 * Algolia_Scripts class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.5.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Algolia_Scripts
 *
 * @since 1.5.0
 */
class Typesense_Scripts {

	/**
	 * Algolia_Scripts constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
	}

	/**
	 * Register scripts.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 */
	public function register_scripts() {

		$in_footer = Typesense_Utils::get_scripts_in_footer_argument();

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$ais_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG
			? '.development'
			: '.production';

		wp_register_script(
			'algolia-search',
            TYPESENSE_PLUGIN_URL . 'js/algoliasearch/dist/algoliasearch-lite.umd.js',
			[
				'jquery',
				'underscore',
				'wp-util',
			],
			TYPESENSE_VERSION,
			$in_footer
		);
//Typesense scripts
	wp_register_script(
		'typesense-js',
		TYPESENSE_PLUGIN_URL . 'js/typesense-js/dist/typesense.js',
		[
			'jquery',
			'underscore',
			'wp-util',
		],
		TYPESENSE_VERSION,
		$in_footer
	);

	wp_register_script(
		'typesense-min-js',
		TYPESENSE_PLUGIN_URL . 'js/typesense-js/dist/typesense.min.js',
		[
			'jquery',
			'underscore',
			'wp-util',
		],
		TYPESENSE_VERSION,
		$in_footer
	);

	wp_register_script(
		'typesense-min-js-map',
		TYPESENSE_PLUGIN_URL . 'js/typesense-js/dist/typesense.min.js.map',
		[
			'jquery',
			'underscore',
			'wp-util',
		],
		TYPESENSE_VERSION,
		$in_footer
	);
		wp_register_script(
			'typesense-adapter',
			TYPESENSE_PLUGIN_URL . 'js/typesense-instantsearch-adapter/dist/typesense-instantsearch-adapter.min.js',
			[
				'jquery',
				'underscore',
				'wp-util',
			],
			TYPESENSE_VERSION,
			$in_footer
		);

		wp_register_script(
			'typesense-adapter-map',
			TYPESENSE_PLUGIN_URL . 'js/typesense-instantsearch-adapter/dist/typesense-instantsearch-adapter.min.js.map',
			[
				'jquery',
				'underscore',
				'wp-util',
			],
			TYPESENSE_VERSION,
			$in_footer
		);
//Typesense scripts
		wp_register_script(
			'algolia-autocomplete',
			TYPESENSE_PLUGIN_URL . 'js/autocomplete.js/dist/autocomplete' . $suffix . '.js',
			[
				'jquery',
				'underscore',
				'wp-util',
				'algolia-search',
			],
			TYPESENSE_VERSION,
			$in_footer
		);

		wp_register_script(
			'algolia-autocomplete-noconflict',
			TYPESENSE_PLUGIN_URL . 'js/autocomplete-noconflict.js',
			[
				'algolia-autocomplete',
			],
			TYPESENSE_VERSION,
			$in_footer
		);

		wp_register_script(
			'algolia-instantsearch',
			ALGOLIA_PLUGIN_URL . 'js/instantsearch.js/dist/instantsearch' . $ais_suffix . $suffix . '.js',
			[
				'jquery',
				'underscore',
				'wp-util',
				'algolia-search',
			],
			TYPESENSE_VERSION,
			$in_footer
		);
	}
}
