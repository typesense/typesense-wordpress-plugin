<?php
/**
 * Typesense_Admin_Page_Autocomplete class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Admin_Page_Autocomplete
 *
 * @since 1.0.0
 */
class Typesense_Admin_Page_Autocomplete {

	/**
	 * Admin page slug.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $slug = 'typesense';

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
	private $section = 'typesense_section_autocomplete';

	/**
	 * Admin page option group.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $option_group = 'typesense_autocomplete';

	/**
	 * The Typesense_Settings object.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Settings
	 */
	private $settings;

	/**
	 * The Typesense_Autocomplete_Config object.
	 *
	 * @since 1.0.0
	 *
	 * @var Typesense_Autocomplete_Config
	 */
	private $autocomplete_config;

	/**
	 * Typesense_Admin_Page_Autocomplete constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param Typesense_Settings            $settings            The Typesense_Settings object.
	 * @param Typesense_Autocomplete_Config $autocomplete_config The Typesense_Autocomplete_Config object.
	 */
	public function __construct(Typesense_Settings $settings, Typesense_Autocomplete_Config $autocomplete_config) {
		$this->settings            = $settings;
		$this->autocomplete_config = $autocomplete_config;

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		//add_action( 'admin_notices', array( $this, 'display_errors' ) );

		// @todo: Listen for de-index to remove from autocomplete.
	}

	/**
	 * Add menu pages.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function add_page() {
		add_menu_page(
			'Typesense Search',
			esc_html__( 'Typesense Search', 'wp-search-with-typesense' ),
			'manage_options',
			'typesense',
			array( $this, 'display_page' ),
			''
		);
		add_submenu_page(
			'typesense',
			esc_html__( 'Autocomplete', 'wp-search-with-typesense' ),
			esc_html__( 'Autocomplete', 'wp-search-with-typesense' ),
			$this->capability,
			$this->slug,
			array( $this, 'display_page' )
		);
	}

	/**
	 * Add and register settings.
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
			'typesense_autocomplete_enabled',
			esc_html__( 'Enable autocomplete', 'wp-search-with-typesense' ),
			array( $this, 'autocomplete_enabled_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'typesense_autocomplete_config',
			esc_html__( 'Configuration', 'wp-search-with-typesense' ),
			array( $this, 'autocomplete_config_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'typesense_autocomplete_enabled', array( $this, 'sanitize_autocomplete_enabled' ) );
		register_setting( $this->option_group, 'typesense_autocomplete_config', array( $this, 'sanitize_autocomplete_config' ) );
	}

	/**
	 * Index Recorrds Callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function index_records_callback() {
		require_once dirname( __FILE__ ) . '/partials/index-records.php';
	}

	/**
	 * Callback to print the autocomplete enabled checkbox.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function autocomplete_enabled_callback() {
		$value    = $this->settings->get_autocomplete_enabled();
		$indices  = $this->autocomplete_config->get_form_data();
		$checked  = 'yes' === $value ? 'checked ' : '';
		$disabled = empty( $indices ) ? 'disabled ' : '';
?>
		<input type='checkbox' name='typesense_autocomplete_enabled' value='yes' <?php echo esc_html( $checked . ' ' . $disabled ); ?>/>
<?php
	}

	/**
	 * Sanitize the Autocomplete enabled setting.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $value The original value.
	 *
	 * @return string
	 */
	public function sanitize_autocomplete_enabled( $value ) {

		add_settings_error(
			$this->option_group,
			'autocomplete_enabled',
			esc_html__( 'Autocomplete configuration has been saved. Make sure to hit the "re-index" buttons of the different indices that are not indexed yet.', 'wp-search-with-typesense' ),
			'updated'
		);

		return 'yes' === $value ? 'yes' : 'no';
	}

	/**
	 * Autocomplete Config Callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function autocomplete_config_callback() {
		$indices = $this->autocomplete_config->get_form_data();

		require_once dirname( __FILE__ ) . '/partials/page-autocomplete-config.php';
	}

	/**
	 * Sanitize Autocomplete Config.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param array $values Array of autocomplete config values.
	 *
	 * @return array|mixed
	 */
	public function sanitize_autocomplete_config( $values ) {
		return $this->autocomplete_config->sanitize_form_data( $values );
	}

	/**
	 * Display the page.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function display_page() {
		require_once dirname( __FILE__ ) . '/partials/page-autocomplete.php';
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

		$is_enabled = 'yes' === $this->settings->get_autocomplete_enabled();
		$indices    = $this->autocomplete_config->get_config();

		if ( true === $is_enabled && empty( $indices ) ) {
			/* translators: placeholder contains the URL to the autocomplete configuration page. */
			$message = sprintf( __( 'Please select one or multiple indices on the <a href="%s">Typesense: Autocomplete configuration page</a>.', 'wp-search-with-typesense' ), esc_url( admin_url( 'admin.php?page=' . $this->slug ) ) );
			echo '<div class="error notice">
					  <p>' . esc_html__( 'You have enabled the Typesense Autocomplete feature but did not choose any index to search in.', 'wp-search-with-typesense' ) . '</p>
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
		echo '<p>' . esc_html__( 'The autocomplete feature adds a find-as-you-type dropdown menu to your search bar(s).', 'wp-search-with-typesense' ) . '</p>';
	}
}
