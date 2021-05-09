<?php
/**
 * Typesense_Post_Changes_Watcher class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Post_Changes_Watcher
 *
 * @since 1.0.0
 */
use Typesense\Client;

class Typesense_Post_Changes_Watcher implements Typesense_Changes_Watcher {

	/**
	 * Typesense_Index instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Typesense_Index
	 */
	private $index;

	/**
	 * Deleted posts array.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Array
	 */
	private $posts_deleted = array();

	/**
	 * hanges_Watcher constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param Typesense_Index $index Typesense_Index instance.
	 */
	public function __construct( Typesense_Index $index ) {
		$this->index = $index;
	}

	/**
	 * Watch WordPress events.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 */
	public function watch() {
		// Fires once a post has been saved.
		add_action( 'publish_post', array( $this, 'sync_item' ) );

		// Fires before a post is deleted, at the start of wp_delete_post().
		// At this stage the post metas are still available, and we need them.
		add_action( 'before_delete_post', array( $this, 'delete_item' ) );

		// Handle meta changes after the change occurred.
		//add_action( 'added_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		//add_action( 'updated_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		//add_action( 'deleted_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
	}

	/**
	 * Sync item.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @param int $post_id The post ID to sync.
	 *
	 * @return void
	 */
	public function sync_item( $post_id ) {
		$document = [
			'post_id' => '1',
			'id' => '3',
			'post_content' => 'New world',
			'post_title' => 'Dummy text 2',
			'post_excerpt' => 'dcd',
			'post_type' => 'wecfwec',
			'is_sticky' => 1,

			'post_modified' => 'fwecfwe',
			'post_date' => 'cwe',

			'comment_count' => 2,

		];
		try {
			$post = get_post( (int) $post_id );
			$this->index->sync( $post );
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() ); // phpcs:ignore -- Legacy.
		}
	}

	/**
	 * Delete item.
	 *
	 * @author  WebDevStudios <contact@webdevstudios.com>
	 * @since   1.0.0
	 *
	 * @param int $post_id The post ID to delete.
	 *
	 * @return void
	 */
	public function delete_item( $post_id ) {

		$post = get_post( (int) $post_id );

		try {
			$this->index->delete_item( $post );
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() ); // phpcs:ignore -- Legacy.
		}
	}

	/**
	 * Watch meta changes for item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string|array $meta_id   The meta ID.
	 * @param int          $object_id The post ID.
	 * @param string       $meta_key  The meta key.
	 *
	 * @return void
	 */
	public function on_meta_change( $meta_id, $object_id, $meta_key ) {
		$keys = array( '_thumbnail_id' );
		$keys = (array) apply_filters( '_watch_post_meta_keys', $keys, $object_id );

		if ( ! in_array( $meta_key, $keys, true ) ) {
			return;
		}

		$this->sync_item( $object_id );
	}
}
