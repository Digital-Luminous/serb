<?php
	$theme_path = get_template_directory_uri();
?>

<section class="l-section s-regular-bottom">
	<div class="l-inner">
		<div class="c-insights-listing">
			<div class="c-insights-listing__search">
				<div class="c-search-bar">
					<div class="c-search-bar__field-wrapper">
						<img class="c-search-bar__icon c-search-bar__icon--decor js-injected-svg" src="<?php echo $theme_path . '/front/static/images/icon-search.svg'; ?>" alt="Search icon">
						<span class="sr-only"><?php _e( 'Search', 'protherics' ); ?></span>
						<input class="c-search-bar__field ui-color--black-2 js-insights-search-input" type="text" name="search" placeholder="<?php _e( 'Search insights', 'protherics' ); ?>" value="">
					</div>
				</div>
			</div>

			<div class="c-insights-listing__content js-insights-list-wrapper">
				<ul class="c-insights-list js-insights-list"></ul>
			</div>
			<nav class="c-pagination t-size-14 js-insights-pagination">
				<ul class="c-pagination__list js-insights-pagination-list"></ul>
			</nav>
		</div>

	</div>
</section>
