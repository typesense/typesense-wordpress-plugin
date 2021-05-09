<?php
/**
 * Typesense_Posts_Index class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

/**
 * Class Typesense_Posts_Index
 *
 * @since 1.0.0
 */
use Typesense\Client;

class Typesense_Posts_Index  extends Typesense_Index{

	/**
	 * The post type.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * What this index contains.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $contains_only = 'posts';

	/**
	 * Typesense_Posts_Index constructor.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $post_type The post type.
	 */
	public function __construct($post_type) {
		$this->post_type = (string) $post_type;
	}

	/**
	 * Check if this index supports the given item.
	 *
	 * A performing function that return true if the item can potentially
	 * be subject for indexation or not. This will be used to determine if an item is part of the index
	 * As this function will be called synchronously during other operations,
	 * it has to be as lightweight as possible. No db calls or huge loops.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to check against.
	 *
	 * @return bool
	 */
	public function supports( $item ) {
		return $item instanceof WP_Post && $item->post_type === $this->post_type;
	}

	/**
	 * Get the admin name for this index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string The name displayed in the admin UI.
	 */
	public function get_admin_name() {
		$post_type = get_post_type_object( $this->post_type );

		return null === $post_type ? $this->post_type : $post_type->labels->name;
	}

	/**
	 * Check if the item should be indexed.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to check.
	 *
	 * @return bool
	 */
	protected function should_index( $item ) {
		return $this->should_index_post( $item );
	}

	/**
	 * Check if the post should be indexed.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post The post to check.
	 *
	 * @return bool
	 */
	private function should_index_post( WP_Post $post ) {
		$post_status = $post->post_status;

		if ( 'inherit' === $post_status ) {
			$parent_post = ( $post->post_parent ) ? get_post( $post->post_parent ) : null;
			if ( null !== $parent_post ) {
				$post_status = $parent_post->post_status;
			} else {
				$post_status = 'publish';
			}
		}

		$should_index = 'publish' === $post_status && empty( $post->post_password );

		//return (bool) apply_filters( 'algolia_should_index_post', $should_index, $post );
		return (bool)$should_index;
	}

	/**
	 * Get records for the item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to get records for.
	 *
	 * @return array
	 */
	protected function get_records( $item ) {
		return $this->get_post_records( $item );
	}

	/**
	 * Get records for the post.
	 *
	 * Turns a WP_Post in a collection of records to be pushed to Typesense.
	 * Given every single post is splitted into several Typesense records,
	 * we also attribute an objectID that follows a naming convention for
	 * every record.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post The post to get records for.
	 *
	 * @return array
	 */
	private function get_post_records(  $post ) {
		$shared_attributes = $this->get_post_shared_attributes( $post );
		$content = apply_filters( 'the_content', $post->post_content ); // phpcs:ignore -- Legitimate use of Core hook.
		$shared_attributes['post_content'] = strip_tags($content);
		$records = array();

		$records                 = $shared_attributes;
		$records['id']     = (string)$post->ID;
		return $records;
	}

	function prefix_console_log_message( $message ) {

		$message = htmlspecialchars( stripslashes( $message ) );
		//Replacing Quotes, so that it does not mess up the script
		$message = str_replace( '"', "-", $message );
		$message = str_replace( "'", "-", $message );
	
		return "<script>console.log('{$message}')</script>";
	}
	/**
	 * Get post shared attributes.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post The post to get shared attributes for.
	 *
	 * @return array
	 */
	private function get_post_shared_attributes( $post ) {
		$shared_attributes                        = array();
		$shared_attributes['post_id']             = (string)$post->ID;
		$shared_attributes['post_type']           = $post->post_type;
		$shared_attributes['post_title']          = $post->post_title;
		$shared_attributes['post_excerpt']        = apply_filters( 'the_excerpt', $post->post_excerpt ); // phpcs:ignore -- Legitimate use of Core hook.
		$shared_attributes['post_date']           = (string)get_post_time( 'U', false, $post );
		$shared_attributes['post_modified']       = (string)get_post_modified_time( 'U', false, $post );
		$shared_attributes['comment_count']       = (int) $post->comment_count;

		$val = (wp_list_pluck(wp_get_object_terms($post->ID,'category'),'name'));
		$shared_attributes['category'] = (string)$val[0];
		
		$shared_attributes['permalink']      = get_permalink( $post );
		
		$shared_attributes['is_sticky'] = is_sticky( $post->ID ) ? 1 : 0;

		return $shared_attributes;
	}

	/**
	 * Get settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	protected function get_settings() {
		$settings = array(
			'attributesToIndex'     => array(
				'unordered(post_title)',
				'unordered(taxonomies)',
				'unordered(content)',
			),
			'customRanking'         => array(
				'desc(is_sticky)',
				'desc(post_date)',
				'asc(record_index)',
			),
			'attributeForDistinct'  => 'post_id',
			'distinct'              => true,
			'attributesForFaceting' => array(
				'taxonomies',
				'taxonomies_hierarchical',
				'post_author.display_name',
			),
			'attributesToSnippet'   => array(
				'post_title:30',
				'content:30',
			),
			'snippetEllipsisText'   => '…',
		);

		$settings = (array) apply_filters( 'algolia_posts_index_settings', $settings, $this->post_type );
		$settings = (array) apply_filters( 'algolia_posts_' . $this->post_type . '_index_settings', $settings );

		return $settings;
	}

	/**
	 * Get synonyms.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	protected function get_synonyms() {
		$synonyms = (array) apply_filters( 'algolia_posts_index_synonyms', array(), $this->post_type );
		$synonyms = (array) apply_filters( 'algolia_posts_' . $this->post_type . '_index_synonyms', $synonyms );

		return $synonyms;
	}

	/**
	 * Get post object ID.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $post_id      The WP_Post ID.
	 * @param int $record_index The split record index.
	 *
	 * @return string
	 */
	private function get_post_object_id( $post_id, $record_index ) {
		/**
		 * Allow filtering of the post object ID.
		 *
		 * @since 1.3.0
		 *
		 * @param string $post_object_id The Typesense objectID.
		 * @param int    $post_id        The WordPress post ID.
		 * @param int    $record_index   Index of the split post record.
		 */
		/*
		 return apply_filters(
			'algolia_get_post_object_id',
			$post_id . '-' . $record_index,
			$post_id,
			$record_index
		);
		*/
		$id = '1';
		return $id;
	}
	/**
	 * Update post records.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post    The post to update records for.
	 * @param array   $records The records.
	 */
	public function update_records( WP_Post $post, array $records ) {
		parent::update_post_records( $post, $records );
	}

	/**
	 * Get ID.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_id() {
		return 'posts_' . $this->post_type;
	}

	/**
	 * Get re-index items count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return int
	 */
	protected function get_re_index_items_count() {
		$query = new WP_Query(
			array(
				'post_type'        => $this->post_type,
				'post_status'      => 'any', // Let the `should_index` take care of the filtering.
				'suppress_filters' => true,
			)
		);

		return (int) $query->found_posts;
	}



	/**
	 * Get items.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $page       The page.
	 * @param int $batch_size The batch size.
	 *
	 * @return array
	 */
	protected function get_items( $page, $batch_size ) {
		$query = new WP_Query(
			array(
				'post_type'        => $this->post_type,
				'posts_per_page'   => $batch_size,
				'post_status'      => 'any',
				'order'            => 'ASC',
				'orderby'          => 'ID',
				'paged'            => $page,
				'suppress_filters' => true,
			)
		);

		return $query->posts;
	}

	/**
	 * Delete item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to delete.
	 */

	/**
	 * Get post records count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int
	 */
	private function get_post_records_count( $post_id ) {
		return (int) get_post_meta( (int) $post_id, 'algolia_' . $this->get_id() . '_records_count', true );
	}

	/**
	 * Get post records count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param WP_Post $post  The post.
	 * @param int     $count The count of records.
	 */
	private function set_post_records_count( WP_Post $post, $count ) {
		update_post_meta( (int) $post->ID, 'algolia_' . $this->get_id() . '_records_count', (int) $count );
	}
}
