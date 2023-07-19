<?php
/**
 * Template part for displaying the page builder modules
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

$page_id = cp_get_page_id();

if( $post->post_password && post_password_required() ) {

	get_template_part('template-parts/partial', 'password-protected');

} else {

	if( class_exists('acf') ) {

		if( have_rows('cp_page_builder', $page_id) ):

			// loop through the rows of data
			$counter = 1;

			$current_bg = '';

			while ( have_rows('cp_page_builder', $page_id) ) : the_row();

				$module = str_replace('cp_module_', '', get_row_layout());
				$module = str_replace('_', '-', $module);

				$bg_color = get_sub_field('cp_module_setting_background_color');
				if(!$bg_color) {
					$bg_color = 'custom';
				}

				if($current_bg != $bg_color) {
					if($counter != 1) {
						echo '</div>';
					}
					echo '<div class="module-group module-group-bg-'.$bg_color.'">';
					$current_bg = $bg_color;
				}

				get_template_part('template-parts/modules/module', $module, ['index' => $counter]);

				$counter++;

			endwhile;

		else :

			// no layouts found

		endif;

	}
}

?>

