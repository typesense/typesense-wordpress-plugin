<?php
/**
 * Form override search option admin template partial.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

?>

<div class="input-radio">
	<label>
		<input type="radio" value="native"
			name="typesense_override_native_search" <?php checked( $value, 'native' ); ?>>
		<?php esc_html_e( 'Do not use Typesense', 'wp-search-with-typesense' ); ?>
	</label>
	<div class="radio-info">
		<?php
		echo wp_kses(
			__(
				'Do not use Typesense for searching at all.<br/>This is only a valid option if you wish to search on your content from another website.',
				'wp-search-with-typesense'
			),
			[
				'br' => [],
			]
		);
		?>
	</div>

	<label>
		<input type="radio" value="backend"
			name="typesense_override_native_search" <?php checked( $value, 'backend' ); ?>>
		<?php esc_html_e( 'Use Typesense in the backend', 'wp-search-with-typesense' ); ?>
	</label>
	<div class="radio-info">
		<?php
		echo wp_kses(
			__(
				'With this option WordPress search will be powered by Typesense behind the scenes.<br/>This will allow your search results to be typo tolerant.<br/><b>This option does not support filtering and displaying instant search results but has the advantage to play nicely with any theme.</b>',
				'wp-search-with-typesense'
			),
			[
				'br' => [],
				'b'  => [],
			]
		);
		?>
	</div>

	<label>
		<input type="radio" value="instantsearch"
			name="typesense_override_native_search" <?php checked( $value, 'instantsearch' ); ?>>
		<?php esc_html_e( 'Use Typesense with Instantsearch.js', 'wp-search-with-typesense' ); ?>
	</label>
	<div class="radio-info">
		<?php
		echo wp_kses(
			__(
				'This will replace the search page with an instant search experience powered by Typesense.<br/>By default you will be able to filter by post type, categories, tags and authors.<br/>Please note that the plugin is shipped with some sensible default styling rules<br/>but it could require some CSS adjustments to provide an optimal search experience.',
				'wp-search-with-typesense'
			),
			[
				'br' => [],
			]
		);
		?>
	</div>
</div>
