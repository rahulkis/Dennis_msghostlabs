<?php

/**
 * Return html for responsive image
 *
 * @param  array $image ACF Image Array
 * @param  string $size Largest image size that will be used
 * @param  array $breakpoints Breakpoints to display different sized images. Defaults to ['992px' => '50vw'].
 * In this example 992px is the breakpoint and 50vw is the width of the image at this breakpoint.
 * @param  array $additonal_atts Additional attributes to add to image tag. Example: ['style' => 'object-position: 75% 50%;']
 * @return Image tag HTML
 */
function cannabuilder_acf_responsive_image($image, $size, $breakpoints = ['992px' => '50vw'], $additional_atts = []) {

	if(!$image) {
		return false;
	}

	$image['sizes']['full'] = $image['url'];
	$image['sizes']['full-width'] = $image['width'];
	$image['sizes']['full-height'] = $image['height'];

	$sizes = [
		'full',
		'full',
		'large',
		'medium_large'
	];

	$size_index = array_search($size, $sizes);

	if ($size_index !== false) {
		//Start img tag
		$output = '<img';

		//Start srcset
		$output .= ' srcset="';

		//Output possilbe src sizes
		for ($i = $size_index; $i < count($sizes); $i++) {
			$output .= $image['sizes'][$sizes[$i]] . ' ' . $image['sizes'][$sizes[$i] . '-width'] . 'w';

			if ($i !== count($sizes) - 1) {
				$output .= ', ';
			}
		}

		//Close srcset
		$output .= '"';

		//Open sizes
		$output .= ' sizes="';

		//Output breakpoints
		foreach ($breakpoints as $breakpoint => $width) {
			$output .= '(min-width: ' . $breakpoint . ') ' . $width . ', ';
		}

		//Output default size
		$output .= '100vw';

		//Close sizes
		$output .= '"';

		// src
		$output .= ' src="' . $image['sizes'][$size] . '"';

		//alt
		$output .= ' alt="' . $image['alt'] . '"';

		//Loop though additional attributes
		foreach ($additional_atts as $attribute => $value) {
			$output .= ' ' . $attribute . '="' . $value . '"';
		}

		//Close img tag
		$output .= '>';

		return $output;

	} else {
		return false;
	}
}