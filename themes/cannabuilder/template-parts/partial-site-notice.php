<?php

$enabled = get_theme_mod( 'cp_setting_site_notice_enabled', false );
$position = get_theme_mod( 'cp_setting_site_notice_position', false );
$notice = get_theme_mod( 'cp_setting_site_notice', false );

if($notice) {
	$hash = md5($notice);
}

?>

<?php if($enabled && $notice) : ?>

	<div class="site-notice site-notice-<?php echo $position; ?>" data-site-notice="<?php echo $hash; ?>" style="display: none;">
		<div class="container">
			<div class="flush">
				<?php echo $notice; ?>
			</div>
		</div>
		<button data-site-notice-close class="btn-reset"><span class="sr-only">Close Notice</span><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"><path fill="#FFF" fill-rule="evenodd" d="M24.704.565L14.077 11.193 4.541.565a1.934 1.934 0 00-2.734 2.733l9.536 10.629L.566 24.7A1.934 1.934 0 003.3 27.435L13.93 16.81l9.537 10.624a1.932 1.932 0 102.73-2.734l-9.54-10.624L27.434 3.3a1.931 1.931 0 10-2.73-2.733V.565z"/></svg></button>
	</div>

<?php endif; ?>