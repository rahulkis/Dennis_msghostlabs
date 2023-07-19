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

?>

	</main><!-- #main -->

</div><!-- #page -->

<?php
	if ( class_exists('acf') ) {

		$page_builder = get_field('cp_page_builder');

		if ($page_builder && array_search('cp_module_gallery', array_column($page_builder, 'acf_fc_layout')) !== false) {
			get_template_part( 'template-parts/partial', 'photoswipe' );
		}
	}
?>


<?php wp_footer(); ?>

</body>
</html>
