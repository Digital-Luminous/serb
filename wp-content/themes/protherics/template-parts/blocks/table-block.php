<section class="l-section l-table-block">
	<div class="l-inner">
		<div class="c-table-block__wrap">
			<h2 class="c-table-block__title t-size-22 t-size-32--desktop">Table styles</h2>
			<p class="c-table-block__description t-size-16 t-size-20--desktop">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta. </p>
			<div class="c-table-block__container">
				<div class="c-table-block__row-headings">
					<h3 class="c-table-block__heading">Organisation Name</h3>
					<h3 class="c-table-block__heading">Organisation Name</h3>
					<h3 class="c-table-block__heading">Financial Amount (£)</h3>
				</div>
				<?php
					for ($i = 0; $i < 3; $i++) : ?>
						<div class="c-table-block__row">
							<span class="ctable-block__text">Epilepsy Society</span>
							<p class="ctable-block__text">Project grant to update and print emergency administration booklet</p>
							<span class="ctable-block__text">£5,000</span>
						</div>
					<?php endfor; ?>
			</div>
		</div>
	</div>
</section>