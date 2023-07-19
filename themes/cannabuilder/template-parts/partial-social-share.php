<?php
	
	global $post;

	$url = get_the_permalink($post);
	
	$links = get_social_share_links($url);

?>

<ul class="social-share">
	
	<?php if($links['facebook']) : ?>
		<li><a href="<?php echo $links['facebook']; ?>">
			<span class="sr-only">Share this post on Facebook</span>
			<?php get_template_part('template-parts/icons/icon', 'facebook'); ?>
		</a></li>
	<?php endif; ?>

	<?php if($links['pinterest']) : ?>
		<li><a href="<?php echo $links['pinterest']; ?>">
			<span class="sr-only">Pin this post on Pinterest</span>
			<?php get_template_part('template-parts/icons/icon', 'pinterest'); ?>
		</a></li>
	<?php endif; ?>

	<?php if($links['twitter']) : ?>
		<li><a href="<?php echo $links['twitter']; ?>">
			<span class="sr-only">Share this post on Twitter</span>
			<?php get_template_part('template-parts/icons/icon', 'twitter'); ?>
		</a></li>
	<?php endif; ?>

	<?php if($links['email']) : ?>
		<li><a href="<?php echo $links['email']; ?>">
			<span class="sr-only">Share this post via Email</span>
			<?php get_template_part('template-parts/icons/icon', 'email'); ?>
		</a></li>
	<?php endif; ?>

	<?php if($links['url']) : ?>
		<li><button class="social-share-clipboard" data-clipboard-text="<?php echo $links['url']; ?>">
			<span class="sr-only">Copy this post to your clipboard</span>
			<?php get_template_part('template-parts/icons/icon', 'link'); ?>
		</button><span class="social-share-tooltip">Copied to clipboard!</span></li>
	<?php endif; ?>

</ul>