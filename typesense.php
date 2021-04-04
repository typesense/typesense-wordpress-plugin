<?php
/**
 * Plugin Name:       WP Search with Typesense
 * Description:       Integrate the powerful Typesense search service with WordPress
 * Version:           1.0.0
 * Text Domain:       wp-search-with-typesense
 * Domain Path:       /languages
 *
 * @since   1.0.0
 * @package WebDevStudios\WPSWA
 */


// Nothing to see here if not loaded in WP context.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The Typesense Search plugin version.
define( 'TYPESENSE_VERSION', '1.0.0' );

define( 'TYPESENSE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( 'TYPESENSE_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

if ( ! defined( 'TYPESENSE_PATH' ) ) {
	define( 'TYPESENSE_PATH', __DIR__ . '/' );
}



require_once TYPESENSE_PATH . 'classmap.php';

$typesense = Typesense_Plugin_Factory::create();


