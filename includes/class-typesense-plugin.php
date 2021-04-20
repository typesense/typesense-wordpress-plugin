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
		//$this->compatibility = new Typesense_Compatibility();
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
		if ( $this->api->is_reachable() ) {
			//$this->load_indices();
			//$this->override_wordpress_search();
			//$this->autocomplete_config = new Typesense_Autocomplete_Config( $this );
			//$this->template_loader     = new Typesense_Template_Loader( $this );
		}
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
		// Add a searchable posts index.
		/*
		$searchable_post_types = get_post_types(
			array(
				'exclude_from_search' => false,
			), 'names'
		);
		$searchable_post_types = (array) apply_filters( 'typesense_searchable_post_types', $searchable_post_types );
		$this->indices[]       = new Typesense_Searchable_Posts_Index( $searchable_post_types );

		// Add one posts index per post type.
		$post_types = get_post_types();

		$excluded_post_types = $this->settings->get_excluded_post_types();
		foreach ( $post_types as $post_type ) {
			// Skip excluded post types.
			if ( in_array( $post_type, $excluded_post_types, true ) ) {
				continue;
			}

			$this->indices[] = new Typesense_Posts_Index( $post_type );
		}

		// Add one terms index per taxonomy.
		$taxonomies          = get_taxonomies();
		//$excluded_taxonomies = $this->settings->get_excluded_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			// Skip excluded taxonomies.
			//if ( in_array( $taxonomy, $excluded_taxonomies, true ) ) {
			//	continue;
			//}

			$this->indices[] = new Typesense_Terms_Index( $taxonomy );
		}

		// Add the users index.
		/*$this->indices[] = new Typesense_Users_Index();

		// Allow developers to filter the indices.
		$this->indices = (array) apply_filters( 'typesense_indices', $this->indices );
		*/
		//foreach ( $this->indices as $index ) {
		//$this->indices[0]->set_name_prefix( $index_name_prefix );
		$this->indices[0]->set_client( $client);
		$this->changes_watchers=array();
		$this->changes_watchers[] = new Typesense_Post_Changes_Watcher( $this->indices[0] );

		$this->indices[1]->set_client( $client);
		/*'
			if ( in_array( $index->get_id(), $synced_indices_ids, true ) ) {
				$index->set_enabled( true );

				if ( $index->contains_only( 'posts' ) ) {
					$this->changes_watchers[] = new Typesense_Post_Changes_Watcher( $index );
				} elseif ( $index->contains_only( 'terms' ) ) {
					$this->changes_watchers[] = new Typesense_Term_Changes_Watcher( $index );
				} elseif ( $index->contains_only( 'users' ) ) {
					$this->changes_watchers[] = new Typesense_User_Changes_Watcher( $index );
				}
			}
*/
		//}
/*
		$this->changes_watchers = (array) apply_filters( 'typesense_changes_watchers', $this->changes_watchers );

		foreach ( $this->changes_watchers as $watcher ) {
			$watcher->watch();
		}
*/
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
		/*
		if ( empty( $args ) ) {
			return $this->indices;
		}

		$indices = $this->indices;

		if ( isset( $args['enabled'] ) && true === $args['enabled'] ) {
			$indices = array_filter(
				$indices, function( $index ) {
					return $index->is_enabled();
				}
			);
		}

		if ( isset( $args['contains'] ) ) {
			$contains = (string) $args['contains'];
			$indices  = array_filter(
				$indices, function( $index ) use ( $contains ) {
					return $index->contains_only( $contains );
				}
			);
		}
*/
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
