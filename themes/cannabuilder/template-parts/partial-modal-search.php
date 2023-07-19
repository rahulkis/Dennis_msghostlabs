<?php

	$post_type = get_theme_mod( 'cp_setting_search_post_type', 'product' );

?>

<div class="modal modal-move site-search" id="modal-search" aria-hidden="true">

	<div class="modal-backdrop" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="modal-dialog" role="dialog" aria-labelledby="modal-search-title">
		
		<div class="modal-header">
			<button type="button" class="btn-reset" data-a11y-dialog-hide aria-label="Close this dialog window">
				<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 0 0-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 0 0 3.3 27.435L13.93 16.81l9.537 10.624a1.93 1.93 0 1 0 2.73-2.734l-9.54-10.624L27.434 3.3a1.93 1.93 0 1 0-2.73-2.733z"/></svg>
			</button>
		</div>
		
		<div class="modal-content" role="document">
			
			<h1 class="h2" id="modal-search-title">Search</h1>
			
			<form method="GET" action="/" class="site-search-form">
				<label for="s" class="sr-only">Search</label>
				<input type="search" placeholder="Search..." id="s" name="s">
				<input type="hidden" name="post_type" value="<?php echo $post_type; ?>">
				<button type="submit" class="btn-primary">Submit</button>
			</form>

		</div>
	</div>

</div>