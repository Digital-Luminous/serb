<?php
	$slides = get_field( 'slides' );
?>

<?php if ( $slides ) : ?>
	<div class="c-image-text-slider">
		<?php // HTML/JS markup for slider (left/right arrows, dot indicators, timed auto-advance) ?>
		<?php foreach ( $slides as $slide ) :
			$image = $slide['image'];
			$title = $slide['title'];
			$text  = $slide['text'];
		?>
			<div class="c-image-text-slider__slide">
				<?php // image left, title + text right ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
