<?php
	$api_url = home_url() . '/wp-json/news/v1/all';
?>

<section class="l-section s-regular-bottom">
	<div class="l-inner">
		<div class="c-timeline-news">
			<h2 class="c-timeline-news__heading ui-font-weight--semibold t-size-22 t-size-32--desktop ui-color--purple-1">News archive</h2>

			<div class="c-timeline-news__content">
				<div class="c-tabs js-tabs" data-api="<?php echo $api_url; ?>">
					<nav class="c-tabs__nav">
						<ul class="c-tabs-nav-list js-tabs-list"></ul>
					</nav>
					<span class="c-tabs__separator"></span>

					<div class="c-tabs__body">
						<div class="c-news-archives">
							<div class="c-news-archives__list-legend u-hide-mobile">
								<p class="c-news-archives__legend-info c-news-archives__legend-info--date ui-font-weight--semibold t-size-16--desktop"><?php _e( 'Date', 'protherics' ); ?></p>
								<p class="c-news-archives__legend-info ui-font-weight--semibold t-size-16--desktop"><?php _e( 'Title', 'protherics' ); ?></p>
							</div>
							<ul class="c-news-archives__list js-archive-list"></ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
