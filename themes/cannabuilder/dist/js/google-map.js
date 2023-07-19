jQuery(document).ready(function( $ ) {
	$('.module-google-map').each(function(index, mapNode) {
		var center = {
			lat: $(mapNode).data('lat'),
			lng: $(mapNode).data('lng')
		};

		var map = new google.maps.Map(mapNode, {
			zoom: 12,
			center: center,
			styles: [{"featureType":"administrative.country","elementType":"geometry","stylers":[{"visibility":"simplified"},{"hue":"#ff0000"}]}]
		});

		var marker = new google.maps.Marker({
			position: center,
			icon: '/wp-content/themes/cannabuilder/dist/img/modules/google-maps/marker-icon.png',
			map: map
		});
	});
});
