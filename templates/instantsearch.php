<?php
/**
 * WP Search With Algolia instantsearch template file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @version 1.7.0
 * @package WebDevStudios\WPSWA
 */

get_header();

?>

	<div id="ais-wrapper">
		<main id="ais-main">
			<div id="algolia-search-box">
				<svg class="search-icon" width="25" height="25" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M24.828 31.657a16.76 16.76 0 0 1-7.992 2.015C7.538 33.672 0 26.134 0 16.836 0 7.538 7.538 0 16.836 0c9.298 0 16.836 7.538 16.836 16.836 0 3.22-.905 6.23-2.475 8.79.288.18.56.395.81.645l5.985 5.986A4.54 4.54 0 0 1 38 38.673a4.535 4.535 0 0 1-6.417-.007l-5.986-5.986a4.545 4.545 0 0 1-.77-1.023zm-7.992-4.046c5.95 0 10.775-4.823 10.775-10.774 0-5.95-4.823-10.775-10.774-10.775-5.95 0-10.775 4.825-10.775 10.776 0 5.95 4.825 10.775 10.776 10.775z" fill-rule="evenodd"></path></svg>
			</div>
			<div id="algolia-stats"></div>
			<div id="algolia-hits"></div>
			<div id="algolia-pagination"></div>
		</main>
		<aside id="ais-facets">
			<h3>Categories</h3>
			<section class="ais-facets" id="facet-categories"></section>
			<h3>Post Type</h3>
			<section class="ais-facets" id="facet-post-types"></section>
		</aside>
	</div>

	<script type="text/html" id="tmpl-instantsearch-hit">
		<article itemtype="http://schema.org/Article">
			<div class="ais-hits--content">
				<h2 itemprop="name headline"><a href = {{data.permalink}} title="{{ data.post_title }}" class="ais-hits--title-link" itemprop="url">{{{ data._highlightResult.post_title.value }}}</a></h2>
				<div class="excerpt">
					<p>
			<# if ( data._snippetResult['post_content'] ) { #>
			  <span class="suggestion-post-content ais-hits--content-snippet">{{{ data._snippetResult['post_content'].value }}}</span>
			<# } #>
					</p>
				</div>
			</div>
			<div class="ais-clearfix"></div>
		</article>
	</script>

	<script type="text/javascript">
		jQuery(function() {
			if(jQuery('#algolia-search-box').length > 0) {

				const typesenseInstantsearchAdapter = new TypesenseInstantsearchAdapter({
					server: {
						apiKey: algolia.api_key, // Be sure to use the search-only-api-key
						nodes: [
							{
								host: algolia.host,
								port: algolia.port,
								protocol: "http"
							}
						]
					},
					additionalSearchParameters: {
						queryBy: 'post_title,post_content',
					},
				});
				const searchClient = typesenseInstantsearchAdapter.searchClient;

				const search = instantsearch({
					searchClient,
					indexName: 'posts'
				});

				/* Search box widget */
				search.addWidgets([
					instantsearch.widgets.searchBox({
						container: '#algolia-search-box',
						placeholder: 'Search for...',
						wrapInput: true,
					}),
					instantsearch.widgets.configure({
						hitsPerPage: 8,
					}),
				]);

				search.addWidget(
					instantsearch.widgets.hits({
						container: '#algolia-hits',
						hitsPerPage: 3,
						templates: {
							empty: 'No results were found for "<strong>{{query}}</strong>".',
							item: wp.template('instantsearch-hit')
						},
						transformData: {
							item: function (hit) {

								function replace_highlights_recursive (item) {
								  if( item instanceof Object && item.hasOwnProperty('value')) {
									  item.value = _.escape(item.value);
									  item.value = item.value.replace(/__ais-highlight__/g, '<em>').replace(/__\/ais-highlight__/g, '</em>');
								  } else {
									  for (var key in item) {
										  item[key] = replace_highlights_recursive(item[key]);
									 }
								  }
								  return item;
								}

								hit._highlightResult = replace_highlights_recursive(hit._highlightResult);
								hit._snippetResult = replace_highlights_recursive(hit._snippetResult);

								return hit;
							}
						}
					})
				);
				/* Stats widget */
				search.addWidget(
					instantsearch.widgets.stats({
						container: '#algolia-stats'
					})
				);

				/* Pagination widget */
				search.addWidget(
					instantsearch.widgets.pagination({
						container: '#algolia-pagination'
					})
				);

				/* Category menu widget */
				search.addWidget(
					instantsearch.widgets.menu({
						container: '#facet-categories',
						attribute:'category',
						sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
						limit: 10,
						templates: {
							header: '<h3 class="widgettitle">Categories</h3>'
						},
					})
				);

				/* Post types menu widget */
				search.addWidget(
					instantsearch.widgets.menu({
						container: '#facet-post-types',
						attribute: 'post_type',
						sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
						limit: 10,
						templates: {
							header: '<h3 class="widgettitle">Post Type</h3>'
						},
					})
				);
				/* Start */
				search.start();

				jQuery( '#algolia-search-box input' ).attr( 'type', 'search' ).trigger( 'select' );
			}
		});
	</script>

<?php

get_footer();












































