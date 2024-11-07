<?php
    $numbers = get_sub_field( 'numbers' );
?>

<?php if ( $numbers ) : ?>
	<div class="c-columns c-numbers c-numbers__wrap">
		<?php foreach ( $numbers as $item ) : ?>
			<div class="c-columns__column c-numbers__column">
				<?php if ( isset( $item['title'] ) && $item['title'] ) : ?>
					<p class="c-numbers__title t-size-22 t-size-24--desktop ui-color--black-1 ui-font-weight--semibold">
						<?php echo $item['title']; ?>
					</p>
				<?php endif; ?>

				<?php if ( isset( $item['number'] ) && $item['number'] ) : ?>
					<p class="c-numbers__number t-size-44 t-size-72--desktop ui-font-weight--bold" <?php echo ( isset( $item['number_color'] ) && $item['number_color'] ) ? 'style="color:' . $item['number_color'] . ';"' : ''; ?>>
						<?php echo $item['number']; ?>
					</p>
				<?php endif; ?>

				<?php if ( isset( $item['text'] ) && $item['text'] ) : ?>
					<p class="c-numbers__text t-size-22 t-size-24--desktop ui-color--black-1 ui-font-weight--semibold">
						<?php echo $item['text']; ?>
					</p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
