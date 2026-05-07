( function ( $ ) {
	'use strict';

	$( function () {
		$( '.wdcs-looking-section' ).each( function () {
			var $slider = $( this ).find( '.wdcs-looking-slider' );

			$slider.slick( {
				slidesToShow  : 4,
				slidesToScroll: 1,
				dots          : false,
				arrows        : true,
				infinite      : false,
				speed         : 400,
				prevArrow: '<button class="slick-prev slick-arrow" aria-label="Previous" type="button">'
					+ '<span class="wdcs-arrow-chevron">&#10094;</span>'
					+ '</button>',
				nextArrow: '<button class="slick-next slick-arrow" aria-label="Next" type="button">'
					+ '<span class="wdcs-arrow-chevron">&#10095;</span>'
					+ '<span class="wdcs-arrow-label">MORE</span>'
					+ '</button>',
				responsive: [
					{
						breakpoint: 1024,
						settings: { slidesToShow: 3 },
					},
					{
						breakpoint: 768,
						settings: { slidesToShow: 2 },
					},
					{
						breakpoint: 480,
						settings: { slidesToShow: 1 },
					},
				],
			} );
		} );
	} );
} )( jQuery );
