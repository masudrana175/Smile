( function ( $ ) {
	'use strict';

	$( function () {
		var $section = $( '.wdcs-doctors-section' );

		if ( ! $section.length ) {
			return;
		}

		$section.each( function () {
			var $wrap  = $( this );
			var $tabs  = $wrap.find( '.wdcs-doctor-tab' );
			var $slider = $wrap.find( '.wdcs-doctors-slider' );

			// Init Slick.
			$slider.slick( {
				slidesToShow  : 1,
				slidesToScroll: 1,
				dots          : false,
				arrows        : true,
				infinite      : true,
				speed         : 400,
				adaptiveHeight: true,
				prevArrow     : '<button class="slick-prev slick-arrow" aria-label="Previous" type="button">&#10094;</button>',
				nextArrow     : '<button class="slick-next slick-arrow" aria-label="Next" type="button">&#10095;</button>',
			} );

			// Sync slider → tabs.
			$slider.on( 'afterChange', function ( _e, _slick, currentIndex ) {
				setActiveTab( currentIndex );
			} );

			// Sync tabs → slider.
			$tabs.on( 'click', function () {
				var index = parseInt( $( this ).data( 'index' ), 10 );
				$slider.slick( 'slickGoTo', index );
			} );

			function setActiveTab( index ) {
				$tabs.removeClass( 'is-active' );
				$tabs.filter( '[data-index="' + index + '"]' ).addClass( 'is-active' );
			}
		} );
	} );
} )( jQuery );
