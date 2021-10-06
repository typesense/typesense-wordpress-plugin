<?php
/**
 * Typesense_Admin_Page_Native_Search class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Admin_Page_Native_Search
 *
 * @since 1.0.0
 */
class Typesense_Admin_Page_Native_Search {

	/**
	 * Admin page slug.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $slug = 'typesense-search-page';

	/**
	 * Admin page capabilities.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * Admin page section.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $section = 'typesense_section_native_search';

	/**
	 * Admin page option group.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $option_group = 'typesense_native_search';

	/**
	 * The Typesense_Plugin instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Plugin
	 */
	private $plugin;

	/**
	 * Typesense_Admin_Page_Native_Search constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param Typesense_Plugin $plugin The Typesense_Plugin instance.
	 */
	public function __construct( Typesense_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		//add_action( 'admin_notices', array( $this, 'display_errors' ) );
	}

	/**
	 * Add submenu page.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function add_page() {
		add_submenu_page(
			'typesense',
			esc_html__( 'Search Page', 'wp-search-with-typesense' ),
			esc_html__( 'Search Page', 'wp-search-with-typesense' ),
			$this->capability,
			$this->slug,
			array( $this, 'display_page' )
		);
		
	}

	/**
	 * Add settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function add_settings() {
		add_settings_section(
			$this->section,
			null,
			array( $this, 'print_section_settings' ),
			$this->slug
		);

		add_settings_field(
			'typesense_override_native_search',
			esc_html__( 'Search results', 'wp-search-with-typesense' ),
			array( $this, 'override_native_search_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'typesense_override_native_search', array( $this, 'sanitize_override_native_search' ) );
	}

	/**
	 * Override native search callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function override_native_search_callback() {
		$value = $this->plugin->get_settings()->get_override_native_search();

		require_once dirname( __FILE__ ) . '/partials/form-override-search-option.php';
	}

	/**
	 * Sanitize override native search.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return array
	 */
	public function sanitize_override_native_search( $value ) {

		if ( 'backend' === $value ) {
			add_settings_error(
				$this->option_group,
				'native_search_enabled',
				esc_html__( 'WordPress search is now based on Typesense!', 'wp-search-with-typesense' ),
				'updated'
			);
		} elseif ( 'instantsearch' === $value ) {
			add_settings_error(
				$this->option_group,
				'native_search_enabled',
				esc_html__( 'WordPress search is now based on Typesense instantsearch.js!', 'wp-search-with-typesense' ),
				'updated'
			);
		} else {
			$value = 'native';
			add_settings_error(
				$this->option_group,
				'native_search_disabled',
				esc_html__( 'You chose to keep the WordPress native search instead of Typesense. If you are using the autocomplete feature of the plugin we highly recommend you turn Typesense search on instead of the WordPress native search.', 'wp-search-with-typesense' ),
				'updated'
			);
		}

		return $value;
	}

	/**
	 * Display the page.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function display_page() {
		require_once dirname( __FILE__ ) . '/partials/page-search.php';
	}

	/**
	 * Display the errors.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function display_errors() {
		settings_errors( $this->option_group );

		if ( defined( 'TYPESENSE_HIDE_HELP_NOTICES' ) && TYPESENSE_HIDE_HELP_NOTICES ) {
			return;
		}

		$settings = $this->plugin->get_settings();

		if ( ! $settings->should_override_search_in_backend() && ! $settings->should_override_search_with_instantsearch() ) {
			return;
		}

		$maybe_get_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		$searchable_posts_index = $this->plugin->get_index( 'searchable_posts' );
		if ( false === $searchable_posts_index->is_enabled() && ( ! empty( $maybe_get_page ) ) && $maybe_get_page === $this->slug ) {
			/* translators: placeholder contains the link to the indexing page. */
			$message = sprintf( __( 'Searchable posts index needs to be checked on the <a href="%s">Typesense: Indexing page</a> for the search results to be powered by Typesense.', 'wp-search-with-typesense' ), esc_url( admin_url( 'admin.php?page=typesense-indexing' ) ) );
			echo '<div class="error notice">
					  <p>' . wp_kses_post( $message ) . '</p>
				  </div>';
		}
	}

	/**
	 * Prints the section text.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function print_section_settings() {
		echo '<p>' . esc_html__( 'By enabling this plugin to override the native WordPress search, your search results will be powered by Typesense\'s typo-tolerant & relevant search algorithms.', 'wp-search-with-typesense' ) . '</p>';

		// @Todo: replace this with a check on the searchable_posts_index.
		$indices = $this->plugin->get_indices(
			array(
				'enabled'  => true,
				'contains' => 'posts',
			)
		);

		if ( empty( $indices ) ) {
			echo '<div class="error-message">' .
					esc_html( __( 'You have no index containing only posts yet. Please index some content on the `Indexing` page.', 'wp-search-with-typesense' ) ) .
					'</div>';
		}
	}
}
