<?php
    $buttons = get_sub_field( 'buttons' );
?>

<?php if ( $buttons ) : ?>
	<div class="c-columns__actions">
	<?php foreach ( $buttons as $item ) : ?>
		<a class="c-btn c-btn--primary c-btn--arrowed c-columns__action" href="<?php echo esc_url( $item['button']['url'] ); ?>" target="<?php echo esc_attr( $item['button']['target'] ); ?>">
			<?php echo $item['button']['title']; ?>
		</a>
	<?php endforeach; ?>
	</div>
<?php endif; ?>
