<?php
/**
 * Template part for displaying a Google Map
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// index
$index = $args['index'];

//Google Map
$google_map = get_sub_field('cp_module_part_google_map');
?>
<?php if ( $google_map ) : ?>
	<div id="<?php echo 'module-' . $index; ?>" class="module module-google-map <?php echo 'module-' . $index; ?>" data-lat="<?php echo $google_map['lat'] ?>" data-lng="<?php echo $google_map['lng']; ?>">

	</div><!-- /.module-google-map -->
<?php endif ?>
