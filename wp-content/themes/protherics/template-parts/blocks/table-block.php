<?php
$title = get_field('title');
$text = get_field('text');
$table_headings = get_field('table_headings');
$has_heading = !empty($table_headings['heading_1']) || !empty($table_headings['heading_2']) || !empty($table_headings['heading_3']);
$table = get_field('table');
?>
<section class="l-section l-table-block">
	<div class="l-inner">
		<div class="c-table-block__wrap">
			<h2 class="c-table-block__title t-size-22 t-size-32--desktop"><?php echo $title; ?></h2>
			<p class="c-table-block__description t-size-16 t-size-20--desktop"><?php echo $text; ?></p>
			<div class="c-table-block__container">
			<?php if ($has_heading): ?>
				<div class="c-table-block__row-headings">
					<h3 class="c-table-block__heading"><?php echo $table_headings['heading_1']; ?></h3>
					<h3 class="c-table-block__heading"><?php echo $table_headings['heading_2']; ?></h3>
					<h3 class="c-table-block__heading"><?php echo $table_headings['heading_3']; ?></h3>
				</div>
			<?php endif; ?>
			<?php if (!empty($table)): ?>
				<?php foreach ($table as $row): ?>
					<?php $has_row = !empty($row['col_1']) || !empty($row['col_2']) || !empty($row['col_3']); ?>
					<?php if ($has_row): ?>
						<div class="c-table-block__row">
							<span class="ctable-block__text"><?php echo $row['col_1']; ?></span>
							<p class="ctable-block__text"><?php echo $row['col_2']; ?></p>
							<span class="ctable-block__text"><?php echo $row['col_3']; ?></span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>	
			</div>
		</div>
	</div>
</section>