<?php

global $post;

$terms = get_the_terms( $post->ID, 'category' );
if ( empty( $terms ) ) $terms = array();
$term_list = wp_list_pluck( $terms, 'slug' );
$args = array(
	'post_type' => 'post',
	'posts_per_page' => 3,
	'post_status' => 'publish',
	'post__not_in' => array( $post->ID ),
	'tax_query' => array(
		array(
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => $term_list
		)
	)
);

$the_query = new WP_Query( $args );

?>

<?php if ( $the_query->have_posts() ) : ?>

	<div class="post-detail-related">
		
		<div class="container">

			<div class="post-detail-related-header">
				<h2>You may enjoy these, as well</h2>
			</div>

			<div class="flex-col flex-col-3">
				<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
					<?php get_template_part('template-parts/partial', 'post-card'); ?>
				<?php endwhile; ?>
			</div>

		</div>

	</div>

	<?php wp_reset_postdata(); ?>

<?php endif; ?>