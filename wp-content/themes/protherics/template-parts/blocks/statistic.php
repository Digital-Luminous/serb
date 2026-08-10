<?php
	$stats = get_field( 'stats' );
?>

<?php if ( $stats ) : ?>
	<section class="c-statistic s-regular-bottom">
		<div class="c-statistic__inner l-inner">
			<div class="c-statistic__pill">
				<?php foreach ( $stats as $stat ) :
					$title_above = $stat['title_above'];
					$number      = $stat['number'];
					$show_plus   = $stat['show_plus'];
					$title_below = $stat['title_below'];

					if ( ! $title_above && ! $number && ! $title_below ) {
						continue;
					}
				?>
					<div class="c-statistic__item">
						<?php if ( $title_above ) : ?>
							<p class="c-statistic__title c-statistic__title--above"><?php echo esc_html( $title_above ); ?></p>
						<?php endif; ?>
						<?php if ( $number ) : ?>
							<p class="c-statistic__number" data-statistic-number="<?php echo esc_attr( $number ); ?>">
								<span data-statistic-number-value>0</span><?php if ( $show_plus ) : ?><span class="c-statistic__plus">+</span><?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( $title_below ) : ?>
							<p class="c-statistic__title c-statistic__title--below"><?php echo esc_html( $title_below ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
