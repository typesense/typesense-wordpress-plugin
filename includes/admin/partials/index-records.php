<table class="widefat table-autocomplete">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Index', 'wp-search-with-typesense' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<button type="button" class="typesense-reindex-button button button-primary" ><?php esc_html_e( 'Re-index', 'wp-search-with-typesense' ); ?></button>
			</td>
		</tr>

	</tbody>
</table>
<p class="description" id="home-description">
	<?php esc_html_e( 'Configure here the indices you want to display in the dropdown menu.', 'wp-search-with-typesense' ); ?>
	<br />
	<?php esc_html_e( 'Use the `Max. Suggestions` column to configure the number of entries that will be displayed by section.', 'wp-search-with-typesense' ); ?>
	<br />
	<?php esc_html_e( 'Use the `Position` column to reflect the order of the sections in the dropdown menu.', 'wp-search-with-typesense' ); ?>
</p>
