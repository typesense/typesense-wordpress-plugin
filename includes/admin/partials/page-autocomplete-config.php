<?php
/**
 * Autocomplete config admin template partial.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @package WebDevStudios\WPSWA
 */

?>

<table class="widefat table-autocomplete">
	<thead>
		<tr>
			<th style="width: 20px;"></th>
			<th style="width: 75px;"><?php esc_html_e( 'Enable', 'wp-search-with-typesense' ); ?></th>
			<th><?php esc_html_e( 'Index', 'wp-search-with-typesense' ); ?></th>
			<th><?php esc_html_e( 'Label', 'wp-search-with-typesense' ); ?></th>
			<th style="width: 75px;"><?php esc_html_e( 'Max. Suggestions', 'wp-search-with-typesense' ); ?></th>
			<th><?php esc_html_e( 'Actions', 'wp-search-with-typesense' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $indices as $index ) : // phpcs:ignore -- This is an admin partial.  ?>
		<tr>
			<td>
				<span class="dashicons dashicons-move"></span>
				<input type="hidden" class="position-input" name="typesense_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][position]"  value="<?php echo (int) $index['position']; ?>" />
			</td>
			<td>
				<input type="checkbox" name="typesense_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][enabled]" <?php echo $index['enabled'] ? 'checked="checked"' : ''; ?>/>
			</td>
			<td>
				<?php echo esc_html( $index['admin_name'] ); ?><br>
				<small style="color: #999">
					<?php
					printf(
						/* translators: placeholder is the name of an Typesense search index. */
						esc_html__( 'Index name: %s', 'wp-search-with-typesense' ),
						esc_html( $index['index_id'] )
					);
					?>
				</small>
			</td>
			<td>
				<input type="text" name="typesense_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][label]"  value="<?php echo esc_attr( $index['label'] ); ?>" />
			</td>
			<td>
				<input style="width: 40px; text-align: center;" type="number" name="typesense_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][max_suggestions]"  value="<?php echo (int) $index['max_suggestions']; ?>" />
			</td>
			<td>
				<button type="button" class="typesense-reindex-button button button-primary" data-index="<?php echo esc_attr( $index['index_id'] ); ?>"><?php esc_html_e( 'Re-index', 'wp-search-with-typesense' ); ?></button>
				<button type="button" class="typesense-push-settings-button button" data-index="<?php echo esc_attr( $index['index_id'] ); ?>"><?php esc_html_e( 'Push Settings', 'wp-search-with-typesense' ); ?></button>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p class="description" id="home-description">
	<?php esc_html_e( 'Configure here the indices you want to display in the dropdown menu.', 'wp-search-with-typesense' ); ?>
	<br />
	<?php esc_html_e( 'Use the `Max. Suggestions` column to configure the number of entries that will be displayed by section.', 'wp-search-with-typesense' ); ?>
	<br />
	<?php esc_html_e( 'Use the `Position` column to reflect the order of the sections in the dropdown menu.', 'wp-search-with-typesense' ); ?>
</p>
