<?php

    $popup_id = get_field('cp_popup_id');

?>

<div class="modal modal-popup" id="<?php echo $popup_id; ?>" aria-hidden="true">

	<div class="modal-backdrop" tabindex="-1" data-a11y-dialog-hide></div>

	 <div class="modal-dialog" role="dialog" aria-labelledby="<?php echo $popup_id; ?>-title">
							
		<div class="modal-header">
			<button type="button" class="btn-reset" data-a11y-dialog-hide aria-label="Close this dialog window">
				<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 0 0-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 0 0 3.3 27.435L13.93 16.81l9.537 10.624a1.93 1.93 0 1 0 2.73-2.734l-9.54-10.624L27.434 3.3a1.93 1.93 0 1 0-2.73-2.733z"/></svg>
			</button>
		</div>
							
	    <div class="modal-content flush" role="document">
				
		    <h3 id="<?php echo $popup_id; ?>-title" class="sr-only"><?php the_title(); ?></h3>

            <?php the_content(); ?>
					
	    </div>

	</div>

</div>