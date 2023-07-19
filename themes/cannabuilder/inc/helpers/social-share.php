<?php

function get_social_share_links($url) {
	
	global $post;

	return [
		'facebook' => 'https://www.facebook.com/sharer.php?u=' . $url,
		'twitter' => 'https://twitter.com/share?url=' . $url . '&text=' . urlencode(get_the_title()),
		'pinterest' => 'https://pinterest.com/pin/create/button/?url='.urlencode(get_the_permalink()).'&media='.urlencode(get_the_post_thumbnail_url($post->ID, 'large')).'&description='.urlencode(get_the_title()),
		'linkedin' => 'https://www.linkedin.com/shareArticle?mini=true&url='.$url.'?title='.urlencode(get_the_title()),
		'email' => 'mailto:?Subject='.get_the_title().'&amp;Body=Check out this link! ' . $url,
		'url' => $url
	];
}