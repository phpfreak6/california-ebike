<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<script type="text/html" id="tmpl-sst-tic-row">
	<tr class="tic-row" data-id="{{ data.id }}">
		<td>
			<h4>{{ data.label }} ({{ data.id }})</h4>
			<p>{{ data.title }}</p>
		</td>
		<td width="1%">
			<button type="button" class="button button-primary sst-select-done"><?php _e( 'Select', 'simplesalestax' ); ?></button>
		</td>
	</tr>
</script>

<script type="text/html" id="tmpl-sst-tic-select-modal">
	<div class="wc-backbone-modal">
		<div class="wc-backbone-modal-content sst-select-tic-modal-content woocommerce">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1><?php _e( 'Select TIC', 'simplesalestax' ); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text"><?php _e( 'Close modal panel', 'simplesalestax' ); ?></span>
					</button>
				</header>
				<article>
					<form action="" method="post">
						<input name="search" class="sst-tic-search" placeholder="<?php _e( 'Start typing to search', 'simplesalestax' ); ?>" type="text" data-list=".sst-tic-list">
						<table>
							<tbody class="sst-tic-list"></tbody>
						</table>
						<input type="hidden" name="tic" value="">
						<input type="submit" id="btn-ok" name="btn-ok" value="Submit" style="display: none;">
					</form>
				</article>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>