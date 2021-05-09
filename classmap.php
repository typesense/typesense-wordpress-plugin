<?php
/**
 * WP Search With Typesense "Classmap" file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

if ( ! defined( 'TYPESENSE_PATH' ) ) {
	exit();
}


require_once TYPESENSE_PATH . 'includes/libraries/typesensesearch-client-php/vendor/autoload.php';


require_once TYPESENSE_PATH . 'includes/factories/class-typesense-plugin-factory.php';

require_once TYPESENSE_PATH . 'includes/indices/class-typesense-index.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-posts-index.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-terms-index.php';

require_once TYPESENSE_PATH . 'includes/watchers/class-typesense-changes-watcher.php';
require_once TYPESENSE_PATH . 'includes/watchers/class-typesense-post-changes-watcher.php';

require_once TYPESENSE_PATH . 'includes/class-typesense-api.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-autocomplete-config.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-compatibility.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-plugin.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-search.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-settings.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-template-loader.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-utils.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-styles.php';
require_once TYPESENSE_PATH . 'includes/class-typesense-scripts.php';
/*
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-index.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-index-replica.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-searchable-posts-index.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-posts-index.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-terms-index.php';
require_once TYPESENSE_PATH . 'includes/indices/class-typesense-users-index.php';
*/

/*
require_once TYPESENSE_PATH . 'includes/watchers/class-typesense-changes-watcher.php';
require_once TYPESENSE_PATH . 'includes/watchers/class-typesense-post-changes-watcher.php';
require_once TYPESENSE_PATH . 'includes/watchers/class-typesense-term-changes-watcher.php';
require_once TYPESENSE_PATH . 'includes/watchers/class-typesense-user-changes-watcher.php';
*/
if ( is_admin() ) {
	require_once TYPESENSE_PATH . 'includes/admin/class-typesense-admin.php';
	require_once TYPESENSE_PATH . 'includes/admin/class-typesense-admin-page-autocomplete.php';
	require_once TYPESENSE_PATH . 'includes/admin/class-typesense-admin-page-native-search.php';
	require_once TYPESENSE_PATH . 'includes/admin/class-typesense-admin-page-settings.php';
}
