<?php 
/**
 * Standard "gray" info-window template file . 
 * 
 * The content of this file will be displayed in the map markers info-window.
 *
 * You can modify this file to apply custom changes. However, it is not recomended
 * to make the changes directly in this file,
 * because your changes will be overwritten with the next update of the plugin.
 * 
 * Instead, you can copy or move this template ( the folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site, 
 * and apply your changes from there. 
 * 
 * The custom template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/info-window-templates/standard/
 * 
 * Once the template folder is in the theme's folder, you will be able to select 
 * it in the form editor.
 *
 * $gmw  - the form being used ( array )
 * $post - the post being displayed ( object )
 */
?>
<div class="gmw-info-window-inner standard">

	<?php do_action( 'gmw_info_window_start', $post, $gmw ); ?>
	
	<?php gmw_info_window_featured_image( $post, $gmw ); ?>	
	
	<?php do_action( 'gmw_standard_iw_template_before_title', $post, $gmw ); ?>
	
	<h4 class="title" style="margin: 10px 0;">
		<?php echo esc_html( $post->post_title ); ?>
	</h4>
								
	<?php do_action( 'gmw_info_window_before_address', $post, $gmw ); ?>

	<?php gmw_info_window_address( $post, $gmw ); ?>
    
    <?php gmw_info_window_directions_link( $post, $gmw ); ?>
    
    <?php gmw_info_window_distance( $post, $gmw ); ?>
    
	<?php do_action( 'gmw_info_window_before_excerpt', $post, $gmw ); ?>

    <?php gmw_info_window_post_excerpt( $post, $gmw ); ?>

	<?php do_action( 'gmw_info_window_before_location_meta', $post, $gmw ); ?>

    <?php gmw_info_window_location_meta( $post, $gmw, false ); ?>
	
    <?php do_action( 'gmw_info_window_end', $post, $gmw ); ?>
    
</div>  