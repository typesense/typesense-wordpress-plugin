<?php
/**
 * Typesense_Admin_Page_Settings class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Admin_Page_Settings
 *
 * @since 1.0.0
 */
class Typesense_Admin_Page_Settings {

	/**
	 * Admin page slug.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $slug = 'typesense-account-settings';

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
	private $section = 'typesense_section_settings';

	/**
	 * Admin page option group.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $option_group = 'typesense_settings';

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
	 * Typesense_Admin_Page_Settings constructor.
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
		add_action( 'admin_notices', array( $this, 'display_errors' ) );

		// Display a link to this page from the plugins page.
		add_filter( 'plugin_action_links_' . TYPESENSE_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * Add action links.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param array $links Array of action links.
	 *
	 * @return array
	 */
	public function add_action_links( array $links ) {
		return array_merge(
			$links, array(
				'<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->slug ) ) . '">' . esc_html__( 'Settings', 'wp-search-with-typesense' ) . '</a>',
			)
		);
	}

	/**
	 * Add admin menu page.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string|void The resulting page's hook_suffix.
	 */
	public function add_page() {
		$api = $this->plugin->get_api();
		add_submenu_page(
			'typesense',
			esc_html__( 'Settings', 'wp-search-with-typesense' ),
			esc_html__( 'Settings', 'wp-search-with-typesense' ),
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
			'typesense_api_key',
			esc_html__( 'API key', 'wp-search-with-typesense' ),
			array( $this, 'api_key_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'typesense_host',
			esc_html__( 'Server Host', 'wp-search-with-typesense' ),
			array( $this, 'host_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'typesense_port',
			esc_html__( 'Server Port', 'wp-search-with-typesense' ),
			array( $this, 'port_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'typesense_index_name_prefix',
			esc_html__( 'Index name prefix', 'wp-search-with-typesense' ),
			array( $this, 'index_name_prefix_callback' ),
			$this->slug,
			$this->section
		);

		add_settings_field(
			'typesense_powered_by_enabled',
			esc_html__( 'Remove Typesense powered by logo', 'wp-search-with-typesense' ),
			array( $this, 'powered_by_enabled_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'typesense_port' );
		register_setting( $this->option_group, 'typesense_host');
		register_setting( $this->option_group, 'typesense_api_key' );
	}

	/**
	 * Application ID callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function application_id_callback() {

		$settings      = $this->plugin->get_settings();
		$setting       = $settings->get_application_id();
		$disabled_html = $settings->is_application_id_in_config() ? ' disabled' : '';
?>
		<input type="text" name="typesense_application_id" class="regular-text" value="<?php echo esc_attr( $setting ); ?>" <?php echo esc_html( $disabled_html ); ?>/>
		<p class="description" id="home-description"><?php esc_html_e( 'Your Typesense Application ID.', 'wp-search-with-typesense' ); ?></p>
<?php
	}

	/**
	 * Search API key callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function search_api_key_callback() {
		$settings      = $this->plugin->get_settings();
		$setting       = $settings->get_search_api_key();
		$disabled_html = $settings->is_search_api_key_in_config() ? ' disabled' : '';

?>
		<input type="text" name="typesense_search_api_key" class="regular-text" value="<?php echo esc_attr( $setting ); ?>" <?php echo esc_html( $disabled_html ); ?>/>
		<p class="description" id="home-description"><?php esc_html_e( 'Your Typesense Search-only API key (public).', 'wp-search-with-typesense' ); ?></p>
<?php
	}

	public function host_callback() {
		$settings      = $this->plugin->get_settings();
		$setting       = $settings->get_host();

?>
		<input type="text" name="typesense_host" class="regular-text" value="<?php echo esc_attr( $setting ); ?>"/>
		<p class="description" id="home-description"><?php esc_html_e( 'Your Typesense Server Host.', 'wp-search-with-typesense' ); ?></p>
<?php
	}

	public function port_callback() {
		$settings      = $this->plugin->get_settings();
		$setting       = $settings->get_port();
?>
		<input type="text" name="typesense_port" class="regular-text" value="<?php echo esc_attr( $setting ); ?>"/>
		<p class="description" id="home-description"><?php esc_html_e( 'Your Typesense Server Port.', 'wp-search-with-typesense' ); ?></p>
<?php
	}
	/**
	 * Admin API key callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function api_key_callback() {
		$settings      = $this->plugin->get_settings();
		$setting       = $settings->get_api_key();
?>
		<input type="text" name="typesense_api_key" class="regular-text" value="<?php echo esc_attr( $setting ); ?>"/>
		<p class="description" id="home-description"><?php esc_html_e( 'Your Typesense API key.', 'wp-search-with-typesense' ); ?></p>
<?php
	}

	/**
	 * Index name prefix callback.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function index_name_prefix_callback() {
		$settings          = $this->plugin->get_settings();
		$index_name_prefix = $settings->get_index_name_prefix();
		$disabled_html     = $settings->is_index_name_prefix_in_config() ? ' disabled' : '';
?>
		<input type="text" name="typesense_index_name_prefix" value="<?php echo esc_attr( $index_name_prefix ); ?>" <?php echo esc_html( $disabled_html ); ?>/>
		<p class="description" id="home-description"><?php esc_html_e( 'This prefix will be prepended to your index names.', 'wp-search-with-typesense' ); ?></p>
<?php
	}

	/**
	 * Powered by enabled callback.
	 *
	 * @author Richard Aber <richard.aber@webdevstudios.com>
	 * @since  2020-07-24
	 */
	public function powered_by_enabled_callback() {
		$powered_by_enabled = $this->plugin->get_settings()->is_powered_by_enabled();
		$checked            = '';
		if ( ! $powered_by_enabled ) {
			$checked = ' checked';
		}
		echo "<input type='checkbox' name='typesense_powered_by_enabled' value='no' " . esc_html( $checked ) . ' />' .
			'<p class="description" id="home-description">' . esc_html( __( 'This will remove the Typesense logo from the autocomplete and the search page. We require that you keep the Typesense logo if you are using a free plan.', 'wp-search-with-typesense' ) ) . '</p>';
	}

	/**
	 * Sanitize application ID.
	 *
	 * @author Richard Aber <richard.aber@webdevstudios.com>
	 * @since  2020-07-24
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_application_id( $value ) {
		if ( $this->plugin->get_settings()->is_application_id_in_config() ) {
			$value = $this->plugin->get_settings()->get_application_id();
		}
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			add_settings_error(
				$this->option_group,
				'empty',
				esc_html__( 'Application ID should not be empty.', 'wp-search-with-typesense' )
			);

		}

		return $value;
	}

	/**
	 * Sanitize search API key.
	 *
	 * @author Richard Aber <richard.aber@webdevstudios.com>
	 * @since  2020-07-24
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_api_key( $value ) {
		//if ( $this->plugin->get_settings()->is_search_api_key_in_config() ) {
			$value = $this->plugin->get_settings()->get_api_key();
		//}
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			add_settings_error(
				$this->option_group,
				'empty',
				esc_html__( 'API key should not be empty.', 'wp-search-with-typesense' )
			);
		}

		return $value;
	}

	public function sanitize_host( $value ) {
		$value = $this->plugin->get_settings()->get_host();
		$value = sanitize_text_field( $value );

		if ( empty( $value ) ) {
			add_settings_error(
				$this->option_group,
				'empty',
				esc_html__( 'Host should not be empty.', 'wp-search-with-typesense' )
			);
		}

		return $value;
	}

	public function sanitize_port( $value ) {
		$value = $this->plugin->get_settings()->get_port();
		$value = sanitize_text_field( $value );
		return $value;
	}
	/**
	 * Determine if the index name prefix is valid.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $index_name_prefix The index name prefix.
	 *
	 * @return bool
	 */
	public function is_valid_index_name_prefix( $index_name_prefix ) {
		$to_validate = str_replace( '_', '', $index_name_prefix );

		return ctype_alnum( $to_validate );
	}

	/**
	 * Sanitize the index name prefix.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return bool|mixed|string|void
	 */
	public function sanitize_index_name_prefix( $value ) {
		if ( $this->plugin->get_settings()->is_index_name_prefix_in_config() ) {
			$value = $this->plugin->get_settings()->get_index_name_prefix();
		}

		if ( $this->is_valid_index_name_prefix( $value ) ) {
			return $value;
		}

		add_settings_error(
			$this->option_group,
			'wrong_prefix',
			esc_html__( 'Indices prefix can only contain alphanumeric characters and underscores.', 'wp-search-with-typesense' )
		);

		$value = get_option( 'typesense_index_name_prefix' );

		return $this->is_valid_index_name_prefix( $value ) ? $value : 'wp_';
	}

	/**
	 * Sanitize the powered by enabled setting.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string
	 */
	public function sanitize_powered_by_enabled( $value ) {
		return 'no' === $value ? 'no' : 'yes';
	}

	/**
	 * Display the page.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function display_page() {
		require_once dirname( __FILE__ ) . '/partials/form-options.php';
	}

	/**
	 * Display errors.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function display_errors() {
		settings_errors( $this->option_group );
	}

	/**
	 * Print the settings section.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function print_section_settings() {
		echo '<p>' . esc_html__( 'Configure your Typesense account credentials. You can find them in the "API Keys" section of your Typesense dashboard.', 'wp-search-with-typesense' ) . '</p>';
		echo '<p>' . esc_html__( 'Once you provide your Typesense Application ID and API key, this plugin will be able to securely communicate with Typesense servers.', 'wp-search-with-typesense' ) . ' ' . esc_html__( 'We ensure your information is correct by testing them against the Typesense servers upon save.', 'wp-search-with-typesense' ) . '</p>';
		/* translators: the placeholder contains the URL to Typesense's website. */
		echo '<p>' . wp_kses_post( sprintf( __( 'No Typesense account yet? <a href="%s">Follow this link</a> to create one for free in a couple of minutes!', 'wp-search-with-typesense' ), 'https://www.typesense.com/users/sign_up' ) ) . '</p>';
	}
}
