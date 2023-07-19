<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package CannaBuilder
 */

$logo = get_theme_mod('cp_setting_logo_footer', '');

// Social Links
$social_title = get_theme_mod('cp_setting_social_title', '');
$social_content = get_theme_mod('cp_setting_social_content', '');
$facebook = get_theme_mod('cp_setting_social_facebook', '');
$instagram = get_theme_mod('cp_setting_social_instagram', '');
$twitter = get_theme_mod('cp_setting_social_twitter', '');
$tiktok = get_theme_mod('cp_setting_social_tiktok', '');
$youtube = get_theme_mod('cp_setting_social_youtube', '');
$pinterest = get_theme_mod('cp_setting_social_pinterest', '');
$linkedin = get_theme_mod('cp_setting_social_linkedin', '');

// Footer Information
$footer_area_1_title = get_theme_mod('cp_setting_footer_area_1_title', '');
$footer_area_1_content = get_theme_mod('cp_setting_footer_area_1_content', '');
$footer_area_2_title = get_theme_mod('cp_setting_footer_area_2_title', '');
$footer_area_2_content = get_theme_mod('cp_setting_footer_area_2_content', '');
$disclaimer = get_theme_mod('cp_setting_footer_disclaimer', '');
?>

</main><!-- #main -->

<?php do_action('cp_after_content'); ?>

<?php do_action('cp_before_footer'); ?>

<footer id="colophon" class="site-footer site-footer-dark">
	<div class="container">
		<div class="flex">
			<div class="footer-item footer-item-logo">
				<?php if ($logo && $logo['url']) : ?>
					<img src="<?php echo $logo['url']; ?>" alt="<?php bloginfo('name'); ?>" />
				<?php else : ?>
					<img src="<?php echo get_template_directory_uri() . '/dist/img/logos/logo-white.svg' ?>" alt="<?php bloginfo('name'); ?>" />
				<?php endif; ?>
			</div>

			<?php if ($footer_area_1_title || $footer_area_1_content) : ?>
				<div class="footer-item">
					<?php if ($footer_area_1_title) : ?>
						<h4><?php echo $footer_area_1_title; ?></h4>
					<?php endif; ?>
					<?php if ($footer_area_1_content) : ?>
						<?php echo apply_filters('the_content', $footer_area_1_content); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($footer_area_2_title || $footer_area_2_content) : ?>
				<div class="footer-item">
					<?php if ($footer_area_2_title) : ?>
						<h4><?php echo $footer_area_2_title; ?></h4>
					<?php endif; ?>
					<?php if ($footer_area_2_content) : ?>
						<?php echo apply_filters('the_content', $footer_area_2_content); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (has_nav_menu('quick-links-1')) : ?>
				<div class="footer-item">
					<h4><?php echo wp_get_nav_menu_name('quick-links-1'); ?></h4>
					<?php
					wp_nav_menu(array(
						'theme_location' => 'quick-links-1',
						'menu_id' => 'quick-links-1',
						'menu_class' => 'site-footer-menu',
						'container'	=>	'ul',
						'depth' => 1
					));
					?>
				</div>
			<?php endif; ?>

			<?php if (has_nav_menu('quick-links-2')) : ?>
				<div class="footer-item">
					<h4><?php echo wp_get_nav_menu_name('quick-links-2'); ?></h4>
					<?php
					wp_nav_menu(array(
						'theme_location' => 'quick-links-2',
						'menu_id' => 'quick-links-2',
						'menu_class' => 'site-footer-menu',
						'container'	=>	'ul',
						'depth' => 1
					));
					?>
				</div>
			<?php endif; ?>

			<?php if (has_nav_menu('quick-links-3')) : ?>
				<div class="footer-item">
					<h4><?php echo wp_get_nav_menu_name('quick-links-3'); ?></h4>
					<?php
					wp_nav_menu(array(
						'theme_location' => 'quick-links-3',
						'menu_id' => 'quick-links-3',
						'menu_class' => 'site-footer-menu',
						'container'	=>	'ul',
						'depth' => 1
					));
					?>
				</div>
			<?php endif; ?>

			<?php if ($facebook || $instagram || $twitter || $youtube || $tiktok || $pinterest || $linkedin) : ?>
				<div class="footer-item">

					<?php if ($social_title) : ?>
						<h4><?php echo $social_title; ?></h4>
					<?php endif; ?>

					<?php if ($social_content) : ?>
						<?php echo apply_filters('the_content', $social_content); ?>
					<?php endif; ?>

					<ul class="footer-social-links">

						<?php if ($facebook) : ?>
							<li><a href="<?php echo $facebook; ?>" target="_blank">
									<span class="sr-only">Follow us on Facebook</span>
									<?php get_template_part('template-parts/icons/icon', 'facebook'); ?>
								</a></li>
						<?php endif; ?>

						<?php if ($instagram) : ?>
							<li><a href="<?php echo $instagram; ?>" target="_blank">
									<span class="sr-only">Follow us on instagram</span>
									<?php get_template_part('template-parts/icons/icon', 'instagram'); ?>
								</a></li>
						<?php endif; ?>

						<?php if ($twitter) : ?>
							<li><a href="<?php echo $twitter; ?>" target="_blank">
									<span class="sr-only">Follow us on twitter</span>
									<?php get_template_part('template-parts/icons/icon', 'twitter'); ?>
								</a></li>
						<?php endif; ?>

						<?php if ($youtube) : ?>
							<li><a href="<?php echo $youtube; ?>" target="_blank">
									<span class="sr-only">Follow us on youtube</span>
									<?php get_template_part('template-parts/icons/icon', 'youtube'); ?>
								</a></li>
						<?php endif; ?>

						<?php if ($tiktok) : ?>
							<li><a href="<?php echo $tiktok; ?>" target="_blank">
									<span class="sr-only">Follow us on tiktok</span>
									<?php get_template_part('template-parts/icons/icon', 'tiktok'); ?>
								</a></li>
						<?php endif; ?>

						<?php if ($pinterest) : ?>
							<li><a href="<?php echo $pinterest; ?>" target="_blank">
									<span class="sr-only">Follow us on pinterest</span>
									<?php get_template_part('template-parts/icons/icon', 'pinterest'); ?>
								</a></li>
						<?php endif; ?>

						<?php if ($linkedin) : ?>
							<li><a href="<?php echo $linkedin; ?>" target="_blank">
									<span class="sr-only">Follow us on linkedin</span>
									<?php get_template_part('template-parts/icons/icon', 'linkedin'); ?>
								</a></li>
						<?php endif; ?>

					</ul>
				</div>

			<?php endif; ?>

		</div>
	</div>
</footer><!-- #colophon -->

<div class="site-attribution site-attribution-dark">
	<div class="container">

		<?php if ($disclaimer) : ?>
			<div class="site-footer-disclaimer">
				<?php echo $disclaimer; ?>
			</div>
		<?php endif; ?>

		<div class="flex">
			<div class="flex-item">
				<p>Copyright &copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?> <?php echo has_nav_menu('footer') ? '<span class="separator">|</span>' : '' ?></p>

				<?php if (has_nav_menu('footer')) : ?>
					<?php
					wp_nav_menu(array(
						'theme_location' => 'footer',
						'menu_id' => 'footer-menu',
						'menu_class' => 'site-policies-menu',
						'container'	=>	'ul',
						'depth' => 1
					));
					?>
				<?php endif; ?>
			</div>
			<p class="site-by"><span>Site by</span> <a href="https://cannaplanners.com/" target="_blank"><span class="sr-only">CannaPlanners</span><svg class="cp-footer-logo" xmlns="http://www.w3.org/2000/svg" width="31" height="32" viewBox="0 0 31 32">
						<g fill="none" fill-rule="evenodd" transform="translate(0 .309)">
							<circle class="cp-footer-logo-background" cx="16" cy="16" r="10" fill="#FFF" />
							<path class="cp-footer-logo-flower" fill="#F04E38" d="M16.076 31h-1.128a2.622 2.622 0 01-1.601-1.064l-1.421-2.018a2.65 2.65 0 00-2.637-1.082l-2.428.438a2.649 2.649 0 01-3.08-3.06l.423-2.43a2.648 2.648 0 00-1.099-2.63l-2.027-1.407A2.62 2.62 0 010 16.136v-1.129a2.622 2.622 0 011.064-1.601l2.018-1.42A2.648 2.648 0 004.164 9.35L3.726 6.92a2.648 2.648 0 013.058-3.08l2.433.423a2.646 2.646 0 002.628-1.099l1.408-2.028A2.63 2.63 0 0115.391 0h.076a2.629 2.629 0 012.127 1.123l1.42 2.02a2.65 2.65 0 002.636 1.08l2.429-.437a2.648 2.648 0 013.08 3.059l-.424 2.432a2.65 2.65 0 001.1 2.629l2.028 1.407a2.647 2.647 0 01.013 4.34l-2.018 1.421a2.65 2.65 0 00-1.082 2.636l.438 2.43a2.647 2.647 0 01-3.059 3.078l-2.432-.422a2.65 2.65 0 00-2.629 1.099l-1.408 2.027A2.62 2.62 0 0116.076 31zm2.528-14.232l5.395-1.376-5.437-1.202 2.05-3.975-3.903 2.181-1.377-5.395-1.202 5.437-4.075-2.167 2.281 4.02-5.396 1.377 5.438 1.202-2.075 3.963 3.928-2.169 1.377 5.395 1.202-5.437 3.99 2.12-2.196-3.974z" />
						</g>
					</svg></a></p>
		</div>
	</div>
</div> <!-- .site-attribution -->

<?php do_action('cp_after_footer'); ?>

</div><!-- #page -->

<?php
if (class_exists('acf')) {

	$page_builder = get_field('cp_page_builder');

	if ($page_builder && array_search('cp_module_gallery', array_column($page_builder, 'acf_fc_layout')) !== false) {
		get_template_part('template-parts/partial', 'photoswipe');
	}
}
?>

<?php if (class_exists('WooCommerce')) : ?>

	<?php
	$search = get_theme_mod('cp_setting_header_search', '');
	?>

	<?php if ($search) : ?>
		<?php get_template_part('template-parts/partial', 'modal-search'); ?>
	<?php endif; ?>

<?php endif; ?>

<?php wp_footer(); ?>

</body>

</html>