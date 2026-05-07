( function ( $ ) {
	'use strict';

	$( function () {

		/* =====================================================
		   Doctors Slider  [wdcs_doctors]
		   ===================================================== */
		$( '.wdcs-doctors-section' ).each( function () {
			var $wrap   = $( this );
			var $tabs   = $wrap.find( '.wdcs-doctor-tab' );
			var $slider = $wrap.find( '.wdcs-doctors-slider' );

			$slider.slick( {
				slidesToShow  : 1,
				slidesToScroll: 1,
				dots          : false,
				arrows        : true,
				infinite      : false,
				speed         : 400,
				adaptiveHeight: true,
				prevArrow: '<button class="slick-prev slick-arrow" aria-label="Previous" type="button">'
					+ '<span class="wdcs-arrow-chevron">&#10094;</span>'
					+ '</button>',
				nextArrow: '<button class="slick-next slick-arrow" aria-label="Next" type="button">'
					+ '<span class="wdcs-arrow-chevron">&#10095;</span>'
					+ '<span class="wdcs-arrow-label">MORE</span>'
					+ '</button>',
			} );

			// Sync slider → tabs.
			$slider.on( 'afterChange', function ( _e, _slick, currentIndex ) {
				$tabs.removeClass( 'is-active' );
				$tabs.filter( '[data-index="' + currentIndex + '"]' ).addClass( 'is-active' );
			} );

			// Sync tabs → slider.
			$tabs.on( 'click', function () {
				$slider.slick( 'slickGoTo', parseInt( $( this ).data( 'index' ), 10 ) );
			} );
		} );

		/* =====================================================
		   What Are You Looking For  [wdcs_looking_for]
		   ===================================================== */
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
					{ breakpoint: 1024, settings: { slidesToShow: 3 } },
					{ breakpoint: 768,  settings: { slidesToShow: 2 } },
					{ breakpoint: 480,  settings: { slidesToShow: 1 } },
				],
			} );
		} );

		/* =====================================================
		   Dentistry Services  [wdcs_services]
		   ===================================================== */
		$( '.wdcs-services-section' ).each( function () {
			var $slider = $( this ).find( '.wdcs-services-slider' );

			$slider.slick( {
				slidesToShow  : 3,
				slidesToScroll: 3,
				rows          : 2,
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
					{ breakpoint: 1024, settings: { slidesToShow: 2, slidesToScroll: 2, rows: 2 } },
					{ breakpoint: 768,  settings: { slidesToShow: 1, slidesToScroll: 2, rows: 2 } },
					{ breakpoint: 480,  settings: { slidesToShow: 1, slidesToScroll: 1, rows: 1 } },
				],
			} );
		} );

	} );
} )( jQuery );
