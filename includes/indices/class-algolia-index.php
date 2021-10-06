<?php
/**
 * Algolia_Index class file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

use Typesense\Client;

/**
 * Class Algolia_Index
 *
 * @since 1.0.0
 */
abstract class Algolia_Index {

	/**
	 * The Client instance.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var Client
	 */
	public $client;

	/**
	 * Whether this index is enabled or not.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var bool
	 */
	private $enabled = false;

	/**
	 * Index name prefix.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $name_prefix = '';

	/**
	 * What this index contains.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @var string|null Should be one of posts, terms or users or left null.
	 */
	protected $contains_only;

	/**
	 * Name of the collection.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 */
	protected $name;

	/**
	 * Whether the reindexing operation is running or not.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  2.1.0
	 *
	 * @var bool
	 */
	protected $reindexing = false;

	/**
	 * Get the admin name for this index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string The name displayed in the admin UI.
	 */
	abstract public function get_admin_name();

	/**
	 * Check if this index contains the given type.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $type The type to check against.
	 *
	 * @return bool
	 */
	final public function contains_only( $type ) {
		if ( null === $this->contains_only ) {
			return false;
		}

		return $this->contains_only === $type;
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
	abstract public function supports( $item );

	/**
	 * Assert if the given item is supported.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to check against.
	 *
	 * @throws RuntimeException If the given item is not supported.
	 */
	public function assert_is_supported( $item ) {
		if ( ! $this->supports( $item ) ) {
			throw new RuntimeException( 'Item is no supported on this index.' );
		}
	}

	/**
	 * Set the Client.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param Client $client The Client instance.
	 */
	final public function set_client( $client ) {
		$this->client = $client;
	}

	/**
	 * Get the Client.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return Client The Client instance.
	 *
	 * @throws LogicException If the Client has not been set.
	 */
	final protected function get_client() {
		if ( null === $this->client ) {
			throw new LogicException( 'Client has not been set.' );
		}

		return $this->client;
	}

	/**
	 * Search.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string     $query    The query.
	 * @param array|null $args     The args.
	 * @param string     $order_by The order by.
	 * @param string     $order    The order.
	 *
	 * @return array
	 */
	final public function search( $query, $args = null, $order_by = null, $order = 'desc' ) {
		return $this->get_collection()->search( $query, $args );
	}

	/**
	 * Set enabled.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param bool $flag Enabled or not.
	 */
	final public function set_enabled( $flag ) {
		$this->enabled = (bool) $flag;
	}

	/**
	 * Check if this index is enabled.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return bool
	 */
	final public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * Set the index name prefix.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string $prefix The prefix to set.
	 */
	final public function set_name_prefix( $prefix ) {
		$this->name_prefix = (string) $prefix;
	}

	/**
	 * Sync item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to sync.
	 *
	 * @return void
	 */
	public function sync( $post ) {
		$this->create_collection_if_not_existing();
		$records = $this->get_records( $post );
		try{
			$this->update_records( $post, $records );
		}
		catch(Exception $e){
			throw $e;
		}
		return;
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
	abstract protected function should_index( $item );

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
	abstract protected function get_records( $item );

	/**
	 * Update records.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item    The item to update records for.
	 * @param array $records The records.
	 *
	 * @return void
	 */
	public function update_post_records( $item, $records) {
		try{
			$this->client->collections['posts']->documents->create($records);
		}
		catch(Exception $e){
			//Ignore error;
		}
		try{
			$this->client->collections['posts']->documents[$records['id']]->update($records);
		}
		catch(Exception $f){
			throw $f;
		}
	}

	public function update_term_records( $item, $records) {
		try{
			$this->client->collections['terms']->documents->create($records);
		}
		catch(Exception $e){
			//Ignore error;
		}
		try{
			$this->client->collections['terms']->documents[$records['id']]->update($records);
		}
		catch(Exception $f){
			throw $f;
		}
	}

	/**
	 * Get index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 */
	public function get_collection() {
		//return $this->client->collections[(string) $this->get_name()];
		return $this->client->collections['posts'];
	}

	/**
	 * Get name.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param string|null $prefix The prefix.
	 *
	 * @return string
	 */
    public function get_name( $prefix = null ) {
        if ( null === $prefix ) {
            $prefix = $this->name_prefix;
        }

        return $prefix . $this->get_id();
    }

	/**
	 * Re index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $page Page of the index.
	 *
	 * @throws InvalidArgumentException If the page is less than 1.
	 */
	public function re_index( $page ) {
		$page = (int) $page;

		if ( $page < 1 ) {
			throw new InvalidArgumentException( 'Page should be superior to 0.' );
		}

		if ( 1 === $page ) {
			$this->create_index_if_not_existing();
		}

		$batch_size = (int) $this->get_re_index_batch_size();

		if ( $batch_size < 1 ) {
			throw new InvalidArgumentException( 'Re-index batch size can not be lower than 1.' );
		}

		$items_count = $this->get_re_index_items_count();

		$max_num_pages = (int) max( ceil( $items_count / $batch_size ), 1 );

		$items = $this->get_items( $page, $batch_size );

		$records = array();

		/**
		 * Set the reindexing bit to true.
		 */
		$this->reindexing = true;

		foreach ( $items as $item ) {
			if ( ! $this->should_index( $item ) ) {
				$this->delete_item( $item );
				continue;
			}

			do_action( 'algolia_before_get_records', $item );
			$item_records = $this->get_records( $item );
			$records      = array_merge( $records, $item_records );
			do_action( 'algolia_after_get_records', $item );

			$this->update_records( $item, $item_records );
		}

		if ( ! empty( $records ) ) {

			/**
			 * Filters the records to be reindexed.
			 *
			 * @since 2.1.0
			 *
			 * @param array  $records  Array of records to re-index.
			 * @param int    $page     Page to re-index.
			 * @param string $index_id The index ID without prefix.
			 */
			$records = apply_filters(
				'algolia_re_index_records',
				$records,
				$page,
				$this->get_id()
			);

			try {
				$sanitized_records = $this->sanitize_json_data( $records );
			} catch ( \Throwable $throwable ) {
				error_log( $throwable->getMessage() ); // phpcs:ignore -- Need a real logger.
			}
		}

		// Don't saveObjects if sanitize_json_data failed.
		if ( ! empty( $sanitized_records ) ) {

			$index = $this->get_index();

			try {
				$index->saveObjects( $sanitized_records );
			} catch ( \Throwable $throwable ) {
				error_log( $throwable->getMessage() ); // phpcs:ignore -- Need a real logger.
			}
		}

		/**
		 * Set the reindexing bit back to false.
		 */
		$this->reindexing = false;

		if ( $page === $max_num_pages ) {
			do_action( 'algolia_re_indexed_items', $this->get_id() );
		}
	}

	/**
	 * Create index if it doesn't exist.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param bool $clear_if_existing Whether to clear an existing index or not.
	 */
	/**
	 * Push settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function push_settings() {
		$index = $this->get_index();

		// This will create the index if it does not exist.
		$settings = $this->get_settings();
		$index->setSettings( $settings );

		// Push synonyms.
		$synonyms = $this->get_synonyms();
		if ( ! empty( $synonyms ) ) {
			$index->saveSynonyms( $synonyms );
		}

		$this->sync_replicas();
	}

	/**
	 * Sanitize JSON data.
	 *
	 * Sanitize data to allow non UTF-8 content to pass.
	 * Here we use a private function introduced in WP 4.1.
	 *
	 * Since WPSWA v 1.1.0, minimum suppported WordPress version is 5.0.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $data Variable (usually an array or object) to encode as JSON.
	 *
	 * @return mixed The sanitized data that shall be encoded to JSON.
	 *
	 * @throws Exception If depth limit is reached.
	 */
	protected function sanitize_json_data( $data ) {
		return _wp_json_sanity_check( $data, 512 );
	}

	/**
	 * Get re-index items count.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return int
	 */
	abstract protected function get_re_index_items_count();

	/**
	 * Check if this is the last page to re-index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param int $page The page to check.
	 *
	 * @return bool
	 */
	protected function is_last_page_to_re_index( $page ) {
		return (int) $page >= $this->get_re_index_max_num_pages();
	}

	/**
	 * Get the max number of pages for re-indexing.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return int
	 */
	public function get_re_index_max_num_pages() {
		$items_count = $this->get_re_index_items_count();

		return (int) ceil( $items_count / $this->get_re_index_batch_size() );
	}

	/**
	 * De-index items.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function de_index_items() {
		$index_name = $this->get_name();
		$this->client->deleteIndex( $index_name );

		do_action( 'Typesense_de_indexed_items', $this->get_id() );
	}

	/**
	 * Get re-index batch size.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return int
	 */
	protected function get_re_index_batch_size() {
		$batch_size = (int) apply_filters( 'Typesense_indexing_batch_size', 100 );
		$batch_size = (int) apply_filters( 'Typesense_' . $this->get_id() . '_indexing_batch_size', $batch_size );

		return $batch_size;
	}

	/**
	 * Get settings.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	abstract protected function get_settings();

	/**
	 * Get synonyms.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	abstract protected function get_synonyms();

	/**
	 * Get ID.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return string
	 */
	abstract public function get_id();

	//abstract public function create_collection_if_not_existing();
	public function create_collection_if_not_existing(){
		$postsSchema=[
			'name' => 'posts',
			'fields' => [
			  ['name' => 'post_content', 'type' => 'string'],
			  ['name' => 'post_title', 'type' => 'string'],
			  ['name' => 'comment_count', 'type' => 'int64'],
			  ['name' => 'is_sticky', 'type' => 'int32'],
			  ['name' => 'post_excerpt', 'type' => 'string'],
			  ['name' => 'post_date', 'type' => 'string'],
			  ['name' => 'post_type', 'type' => 'string',"facet"=>true],
			  ['name' => 'post_id', 'type' => 'string'],
			  ['name' => 'post_modified', 'type' => 'string'],
			  ['name' => 'id', 'type' => 'string'],
			  ['name' => 'permalink', 'type' => 'string'],
			  ['name' => 'category', 'type' => 'string','facet'=>true]
			],
			'default_sorting_field' => 'comment_count'
		  ];

		  $termsSchema=[
			'name' => 'terms',
			'fields' => [
			  ['name' => 'term_id', 'type' => 'string'],
			  ['name' => 'id', 'type' => 'string'],
			  ['name' => 'taxonomy', 'type' => 'string'],
			  ['name' => 'name', 'type' => 'string'],
			  ['name' => 'description', 'type' => 'string'],
			  ['name' => 'slug', 'type' => 'string'],
			  ['name' => 'posts_count', 'type' => 'int64'],
			  ['name' => 'permalink', 'type' => 'string'],
			],
			'default_sorting_field' => 'posts_count'
		  ];

		try{
			$this->client->collections->create($postsSchema);
		  }
		catch(Exception $e){

		}

		try{
		  	$this->client->collections->create($termsSchema);
		  }
		catch(Exception $e){
			  return;
		  }
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
	abstract protected function get_items( $page, $batch_size );

	/**
	 * Get default autocomplete config.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_default_autocomplete_config() {
		return array(
			'index_id'        => $this->get_id(),
			'index_name'      => $this->get_name(),
			'label'           => $this->get_admin_name(),
			'admin_name'      => $this->get_admin_name(),
			'position'        => 10,
			'max_suggestions' => 5,
			'tmpl_suggestion' => 'autocomplete-post-suggestion',
		);
	}

	/**
	 * To array method.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function to_array() {
		$replicas = $this->get_replicas();

		$items = array();
		foreach ( $replicas as $replica ) {
			$items[] = array(
				'name' => $replica->get_replica_index_name( $this ),
			);
		}

		return array(
			'name'     => $this->get_name(),
			'id'       => $this->get_id(),
			'enabled'  => $this->enabled,
			'replicas' => $items,
		);
	}

	/**
	 * Get replicas.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_replicas() {
		$replicas = (array) apply_filters( 'algolia_index_replicas', array(), $this );
		$replicas = (array) apply_filters( 'algolia_' . $this->get_id() . '_index_replicas', $replicas, $this );

		$filtered = array();
		// Filter out invalid inputs.
		foreach ( $replicas as $replica ) {
			if ( ! $replica instanceof Algolia_Index_Replica ) {
				continue;
			}
			$filtered[] = $replica;
		}

		return $filtered;
	}

	/**
	 * Sync replicas.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	private function sync_replicas() {
		$replicas = $this->get_replicas();
		if ( empty( $replicas ) ) {
			// No need to go further if there are no replicas!
			return;
		}

		$replica_index_names = array();

		/**
		 * Loop over the replicas.
		 *
		 * @author WebDevStudios <contact@webdevstudios.com>
		 * @since  1.0.0
		 *
		 * @var Algolia_Index_Replica $replica
		 */
		foreach ( $replicas as $replica ) {
			$replica_index_names[] = $replica->get_replica_index_name( $this );
		}

		$this->get_index()->setSettings(
			array(
				'replicas' => $replica_index_names,
			)
		);

		$client = $this->get_client();

		// Ensure we re-push the master index settings each time.
		$settings = $this->get_settings();

		/**
		 * Loop over the replicas.
		 *
		 * @author WebDevStudios <contact@webdevstudios.com>
		 * @since  1.0.0
		 *
		 * @var Algolia_Index_Replica $replica
		 */
		foreach ( $replicas as $replica ) {
			$settings['ranking'] = $replica->get_ranking();
			$replica_index_name  = $replica->get_replica_index_name( $this );
			$index               = $client->initIndex( $replica_index_name );
			$index->setSettings( $settings );
		}
	}

	/**
	 * Delete item.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @param mixed $item The item to delete.
	 */
	//abstract public function delete_item( $item );

	public function delete_item( $post ) {
		try{
			$key = (string)$post->ID;
			$this->client->collections['posts']->documents[$key]->delete();
		}
		catch(Exception $e){
			throw $e;
		}
	}
	/**
	 * Check if the index exists in Typesense.
	 *
	 * Returns true if the index exists in Typesense.
	 * false otherwise.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 *
	 * @return bool
	 *
	 * @throws TypesenseException If the index does not exist in Typesense.
	 */
	public function exists() {
		try {
			$this->get_index()->getSettings();
		} catch ( TypesenseException $exception ) {
			if ( $exception->getMessage() === 'Index does not exist' ) {
				return false;
			}

			error_log( $exception->getMessage() ); // phpcs:ignore -- Legacy.

			return false;
		}

		return true;
	}

	/**
	 * Clear the index.
	 *
	 * @author WebDevStudios <contact@webdevstudios.com>
	 * @since  1.0.0
	 */
	public function clear() {
		$this->get_index()->clearObjects();
	}
}
