<?php
	$logo = get_theme_mod( 'cp_setting_logo_header', '' );
	$logo_scrolled = false;
	$header_bg = get_theme_mod('cp_setting_header_background', 'solid');

	if($header_bg == 'transparent') {
		$logo_scrolled = get_theme_mod( 'cp_setting_logo_header_scrolled', '' );
		if(!$logo_scrolled || !$logo_scrolled['url']) {
			$logo_scrolled = $logo;
		}
	}
?>

<div class="site-branding">

	<a href="<?php echo site_url() ?>">

		<span class="sr-only">home</span>

		<?php if($logo && $logo['url']) : ?>

			<img class="site-branding-logo" src="<?php echo $logo['url']; ?>" alt="<?php bloginfo('name'); ?>" />
			<?php if($logo_scrolled && $logo_scrolled['url']) : ?>
				<img class="site-branding-logo-scrolled" src="<?php echo $logo_scrolled['url']; ?>" alt="<?php bloginfo('name'); ?>" />
			<?php endif; ?>

		<?php else : ?>

			<?php if($header_bg == 'transparent') : ?>

				<img class="site-branding-logo" src="<?php echo get_template_directory_uri() . '/dist/img/logos/logo-white.svg' ?>" alt="<?php bloginfo('name'); ?>" />

				<img class="site-branding-logo-scrolled" src="<?php echo get_template_directory_uri() . '/dist/img/logos/logo-black.svg' ?>" alt="<?php bloginfo('name'); ?>" />

			<?php else : ?>

				<img class="site-branding-logo" src="<?php echo get_template_directory_uri() . '/dist/img/logos/logo-black.svg' ?>" alt="<?php bloginfo('name'); ?>" />

			<?php endif; ?>

		<?php endif; ?>
		
	</a>

</div><!-- .site-branding -->