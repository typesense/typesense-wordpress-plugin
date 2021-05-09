<?php
/**
 * Typesense_Plugin_Factory class file.
 *
 * @since   1.6.0
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Plugin_Factory
 *
 * Responsible for creating a shared instance of the main Typesense_Plugin object.
 *
 * @since 1.6.0
 */
class Typesense_Plugin_Factory {

	/**
	 * Create and return a shared instance of the Typesense_Plugin.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.6.0
	 *
	 * @return Typesense_Plugin The shared plugin instance.
	 */
	public static function create(): Typesense_Plugin {

		/**
		 * The static instance to share, else null.
		 *
		 * @since  1.6.0
		 *
		 * @var null|Typesense_Plugin $plugin
		 */
		static $plugin = null;

		if ( null !== $plugin ) {
			return $plugin;
		}

		$plugin = new Typesense_Plugin();

		return $plugin;
	}
}
