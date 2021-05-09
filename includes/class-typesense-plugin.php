<?php
/**
 * Typesense_Plugin class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Plugin
 *
 * @since 1.0.0
 */
class Typesense_Plugin {

	const NAME = 'typesense';

	/**
	 * Instance of Typesense_API.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_API
	 */
	protected $api;

	/**
	 * Instance of Typesense_Settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Settings
	 */
	private $settings;

	/**
	 * Instance of Typesense_Autocomplete_Config.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Autocomplete_Config
	 */
	private $autocomplete_config;

	/**
	 * Array of indices.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var array
	 */
	private $indices;

	/**
	 * Array of watchers.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var array
	 */
	private $changes_watchers;

	/**
	 * Instance of Typesense_Styles.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 *
	 * @var Typesense_Styles
	 */
	private $styles;

	/**
	 * Instance of Typesense_Scripts.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 *
	 * @var Typesense_Scripts
	 */
	private $scripts;

	/**
	 * Instance of Typesense_Template_Loader.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Template_Loader
	 */
	private $template_loader;

	/**
	 * Instance of Typesense_Compatibility.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Compatibility
	 */
	private $compatibility;

	/**
	 * Get the singleton instance of Typesense_Plugin.
	 *
	 * @author     WebDevStudios <contact@webdevstudios.com>
	 * @since      1.0.0
	 * @deprecated 1.6.0 Use Typesense_Plugin_Factory::create()
	 * @see        Typesense_Plugin_Factory::create()
	 *
	 * @return Typesense_Plugin
	 */
	public static function get_instance() {
		//_deprecated_function( __METHOD__, '1.6.0', 'Typesense_Plugin_Factory::create();' );
		return Typesense_Plugin_Factory::create();
	}

	/**
	 * Typesense_Plugin constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->settings      = new Typesense_Settings();
		$this->api           = new Typesense_API( $this->settings );
		$this->scripts       = new Typesense_Scripts();
		$this->styles        = new Typesense_Styles();
		add_action( 'init', array( $this, 'load' ), 20 );
	}

	/**
	 * Load.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function load() {
		$this->template_loader     = new Typesense_Template_Loader( $this );
		$this->autocomplete_config = new Typesense_Autocomplete_Config( $this );
		$this->load_indices();
		// Load admin or public part of the plugin.
		if ( is_admin() ) {
			new Typesense_Admin( $this );
		}
	}

	/**
	 * Get the plugin name.
	 *
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string The name of the plugin.
	 */
	public function get_name() {
		return self::NAME;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return TYPESENSE_VERSION;
	}

	/**
	 * Get the Aloglia_API.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Typesense_API
	 */
	public function get_api() {
		return $this->api;
	}

	/**
	 * Get the Typesense_Settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Typesense_Settings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Override WordPress native search.
	 *
	 * Replaces native WordPress search results by Typesense ranked results.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return void
	 */
	private function override_wordpress_search() {
		// Do not override native search if the feature is not enabled.
		if ( ! $this->settings->should_override_search_in_backend() ) {
			return;
		}

		$index_id = $this->settings->get_native_search_index_id();
		$index    = $this->get_index( $index_id );

		if ( null === $index ) {
			return;
		}

		new Typesense_Search( $index );
	}

	/**
	 * Get the Typesense_Autocomplete_Config.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Typesense_Autocomplete_Config
	 */
	public function get_autocomplete_config() {
		return $this->autocomplete_config;
	}

	/**
	 * Load indices.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function load_indices() {
		//$synced_indices_ids = $this->settings->get_synced_indices_ids();

		$client            = $this->get_api()->get_client();
		$index_name_prefix = $this->settings->get_index_name_prefix();
		$this->indices=array();
		$this->indices[] = new Typesense_Posts_Index('post');

		$this->indices[1] = new Typesense_Terms_Index( 'category' );

		$this->indices[0]->set_client( $client);
		$this->indices[1]->set_client( $client);

		$this->changes_watchers=array();
		$this->changes_watchers[] = new Typesense_Post_Changes_Watcher( $this->indices[0] );

		$this->changes_watchers[0]->watch();
	}


	/**
	 * Get indices.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return array
	 */
	public function get_indices() {
		return $this->indices;
	}

	/**
	 * Get index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $index_id The ID of the index to get.
	 *
	 * @return Typesense_Index|null
	 */
	public function get_index( $index_id ) {
		foreach ( $this->indices as $index ) {
			if ( $index_id === $index->get_id() ) {
				return $index;
			}
		}

		return null;
	}

	/**
	 * Get the plugin path.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_path() {
		return untrailingslashit( TYPESENSE_PATH );
	}

	/**
	 * Get the templates path.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_templates_path() {
		return (string) apply_filters( 'typesense_templates_path', 'typesense/' );
	}

	/**
	 * Get the Typesense_Template_Loader.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Typesense_Template_Loader
	 */
	public function get_template_loader() {
		return $this->template_loader;
	}

	/**
	 * Get the Typesense_Styles.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 *
	 * @return Typesense_Styles
	 */
	public function get_styles() {
		return $this->styles;
	}

	/**
	 * Get the Typesense_Scripts.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.5.0
	 *
	 * @return Typesense_Scripts
	 */
	public function get_scripts() {
		return $this->scripts;
	}
}
