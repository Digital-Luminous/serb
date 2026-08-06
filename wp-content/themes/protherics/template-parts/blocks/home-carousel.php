<?php
	$background_image = get_field( 'background_image' );
	$tiles             = get_field( 'tiles' );
?>

<?php if ( $tiles ) : ?>
	<div class="c-home-carousel">
		<?php // $background_image is shared across the whole section ?>
		<?php foreach ( $tiles as $tile ) :
			$title      = $tile['title'];
			$text_title = $tile['text_title'];
			$text       = $tile['text'];
			$cta        = $tile['cta'];
		?>
			<div class="c-home-carousel__tile">
				<?php // HTML/CSS markup for tile (hover expand/collapse, purple curved overlay) ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
