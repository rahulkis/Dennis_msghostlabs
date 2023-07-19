<?php
/**
 * Template part for displaying 
 *
 * @link https://www.advancedcustomfields.com/resources/flexible-content/
 *
 * @package CannaBuilder
 */

// settings
$index = $args['index'];
$bg_color = get_sub_field('cp_module_setting_background_color');
$columns = get_sub_field('cp_module_setting_columns');

// content
$title = get_sub_field('cp_module_part_title');
?>

<div id="<?php echo 'module-' . $module_id; ?>" class="module module-brands module-padded-top module-padded-bottom module-setting-bg-<?php echo $bg_color; ?> module-setting-columns-<?php echo $columns; ?> <?php echo 'module-' . $index; ?>">

	<div class="container">
		
		<?php if ( $title ) : ?>
			<div class="module-part-heading padded">
				<h2 class="module-part-title">
					<span data-widowfix><?php echo $title; ?></span>
				</h2>
			</div>
		<?php endif; ?>

        <input type="text" name="s" placeholder="Search Brands" class="brands-search-input">
        <?php
        $sort_by = get_sub_field('cp_module_setting_sort_by');
        $order   = get_sub_field('cp_module_setting_order');

        $args = array(
            'post_type'      => 'cp_brand',
            'posts_per_page' => 100,
        );

        if ( $sort_by ) {
            $args['orderby'] = $sort_by;
        }

        if ( $order ) {
            $args['order'] = $order;
        }

        $brands = new WP_Query($args);
        if ( $brands->have_posts() ) : ?>

            <div class="brands flex-col flex-col-<?php echo $columns; ?>">
                <?php while ( $brands->have_posts()) : $brands->the_post(); ?>

                    <div class="brand flex-col-item flush">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'full', [ 'class' => 'brand-image' ] ); ?>
                            <p class="title"><?php the_title(); ?></p>
                            <p class="brand-excerpt">    
                                <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                            </p>
                            <p class="brand-link">
                                Learn More
                            </p>
                        </a>
                    </div>

                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
		
	</div>

</div>