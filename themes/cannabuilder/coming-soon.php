<?php
	$logo = get_theme_mod( 'cp_setting_coming_soon_logo', '' );
	$content = get_theme_mod( 'cp_setting_coming_soon_content', '' );
	$background_image = get_theme_mod( 'cp_setting_coming_soon_background_image', '' );
?>

<!doctype html>
<html <?php language_attributes(); ?>>
	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<meta name="format-detection" content="telephone=no">
		
		<?php wp_head(); ?>

	</head>

	<body class="template-coming-soon">

		<div class="coming-soon">

			<div class="coming-soon-content flush">
				<?php if($logo && $logo['url']) : ?>
					<p><img class="site-branding-logo" src="<?php echo $logo['url']; ?>" alt="<?php bloginfo('name'); ?>" /></p>
				<?php endif; ?>

				<?php if($content) : ?>
					<?php echo apply_filters('the_content', $content); ?>
				<?php endif; ?>
			</div>

			<?php if($background_image) : ?>
				<?php echo wp_get_attachment_image($background_image['id'], 'full', false, [
					'class' => 'coming-soon-bg-image position-50-50'
				]); ?>
			<?php endif; ?>


		</div>

	<?php wp_footer(); ?>

	</body>

</html>