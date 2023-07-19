<?php
/**
 * Template part for displaying the WYSIWYG module
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// don't show if instagram plugin is inactive
if ( !class_exists( 'SB_Instagram_Feed' ) ) {
	return;
}

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');

$shortcode = get_sub_field('cp_module_part_shortcode');
if(!$shortcode) {
	$shortcode = '[instagram-feed]';
}

?>


<div id="<?php echo 'module-' . $index; ?>" class="module module-instagram module-setting-bg-<?php echo $bg_color; ?> module-padded-top module-padded-bottom module-padded-sm <?php echo 'module-' . $index; ?>">

	<div class="container-fluid">
		
		<?php echo do_shortcode($shortcode); ?>

	</div>

</div>