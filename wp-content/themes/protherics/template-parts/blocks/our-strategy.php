<?php
	$heading    = get_field( 'heading' );
	$subheading = get_field( 'subheading' );
	$tiles      = get_field( 'tiles' );

	$has_heading = ! empty( $heading ) || ! empty( $subheading );

	if ( ! $has_heading && ! $tiles ) {
		return;
	}

	$truncate = function( $string, $limit ) {
		$string = trim( wp_strip_all_tags( $string ) );

		if ( '' === $string ) {
			return '';
		}

		if ( mb_strlen( $string ) <= $limit ) {
			return $string;
		}

		return rtrim( mb_substr( $string, 0, $limit - 1 ) ) . '…';
	};

	$title_limit        = 50;
	$body_limit          = 190;
	$purple_limit_first = 110;
	$purple_limit_last  = 190;
?>

<section class="c-our-strategy s-regular-bottom">
	<div class="c-our-strategy__inner l-inner">
		<?php if ( $has_heading ) : ?>
			<div class="c-our-strategy__header">
				<?php if ( $heading ) : ?>
					<h2 class="c-our-strategy__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<h3 class="c-our-strategy__subheading"><?php echo esc_html( $subheading ); ?></h3>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $tiles ) : ?>
			<div class="c-our-strategy__grid">
				<?php foreach ( $tiles as $index => $tile ) :
					$tile_type = isset( $tile['tile_type'] ) ? $tile['tile_type'] : 'standard';
					$is_purple = 'purple' === $tile_type;
					$text      = '';
					$title     = '';
					$body      = '';

					if ( $is_purple ) {
						$purple_limit = ( 0 === $index ) ? $purple_limit_first : $purple_limit_last;
						$text         = $truncate( $tile['text'], $purple_limit );
					} else {
						$title = $truncate( $tile['title'], $title_limit );
						$body  = $truncate( $tile['body'], $body_limit );
					}

					if ( $is_purple && ! $text ) {
						continue;
					}

					if ( ! $is_purple && ! $title && ! $body ) {
						continue;
					}
				?>
					<div class="c-our-strategy__tile c-our-strategy__tile--<?php echo $is_purple ? 'purple' : 'standard'; ?>">
						<?php if ( $is_purple ) : ?>
							<p class="c-our-strategy__tile-text"><?php echo esc_html( $text ); ?></p>
						<?php else : ?>
							<?php if ( $title ) : ?>
								<h4 class="c-our-strategy__tile-title"><?php echo esc_html( $title ); ?></h4>
							<?php endif; ?>
							<?php if ( $body ) : ?>
								<p class="c-our-strategy__tile-body"><?php echo esc_html( $body ); ?></p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
