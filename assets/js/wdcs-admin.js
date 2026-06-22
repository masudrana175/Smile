( function ( $, config ) {
	'use strict';

	/* =========================================================
	   Lightbox – shared between options page and side panel
	   ========================================================= */
	function openLightbox( src ) {
		var $lb = $( '#wdcs-lightbox' );
		if ( ! $lb.length ) {
			return;
		}
		$lb.find( '#wdcs-lightbox-img' ).attr( 'src', src );
		$lb.fadeIn( 180 );
		$( 'body' ).addClass( 'wdcs-lightbox-open' );
	}

	function closeLightbox() {
		$( '#wdcs-lightbox' ).fadeOut( 150 );
		$( 'body' ).removeClass( 'wdcs-lightbox-open' );
	}

	$( document ).on( 'click', '.wdcs-js-enlarge', function () {
		openLightbox( $( this ).data( 'full' ) || $( this ).attr( 'src' ) );
	} );

	$( document ).on( 'click', '.wdcs-js-close-lightbox', closeLightbox );

	$( document ).on( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			closeLightbox();
		}
	} );

	/* =========================================================
	   Options page – WordPress media uploader
	   ========================================================= */
	$( document ).on( 'click', '.wdcs-upload-btn', function ( e ) {
		e.preventDefault();
		var $btn        = $( this );
		var targetName  = $btn.data( 'target' );
		var $input      = $( '[name="' + targetName + '"]' );
		var $card       = $btn.closest( '.wdcs-option-card' );

		var frame = wp.media( {
			title  : 'Select Section Preview Image',
			button : { text: 'Use this image' },
			multiple: false,
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url = attachment.url;
			$input.val( url );

			// Update the preview in the card.
			var $preview = $card.find( '.wdcs-option-preview-wrap' );
			var $thumb   = $preview.find( '.wdcs-option-thumb' );
			if ( $thumb.length ) {
				$thumb.attr( 'src', url ).data( 'full', url );
			} else {
				$preview.html(
					'<img src="' + url + '" class="wdcs-option-thumb wdcs-js-enlarge" data-full="' + url + '" title="Click to enlarge">'
				);
			}

			// Show remove button if not already there.
			if ( ! $btn.siblings( '.wdcs-remove-btn' ).length ) {
				$btn.after( '<button type="button" class="button wdcs-remove-btn">Remove</button>' );
			}
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.wdcs-remove-btn', function () {
		var $card = $( this ).closest( '.wdcs-option-card' );
		$card.find( '.wdcs-image-url' ).val( '' );
		$card.find( '.wdcs-option-preview-wrap' ).html(
			'<div class="wdcs-option-placeholder"><span class="dashicons dashicons-format-image"></span><span>No image</span></div>'
		);
		$( this ).remove();
	} );

	/* =========================================================
	   Post edit side panel – show / hide JetEngine meta boxes
	   ========================================================= */
	function toggleMetaBox( jetengineId, visible ) {
		if ( ! jetengineId ) {
			return;
		}
		var $box = $( '#' + jetengineId );
		if ( ! $box.length ) {
			return;
		}
		$box.toggle( visible );
	}

	function syncAll() {
		$( '.wdcs-sections-picker input[type="checkbox"]' ).each( function () {
			var $cb     = $( this );
			var slug    = $cb.val();
			var checked = $cb.is( ':checked' );
			var section = config.sections[ slug ];
			var id      = section ? section.jetengineId : '';

			toggleMetaBox( id, checked );
			$cb.closest( '.wdcs-section-item' ).toggleClass( 'is-checked', checked );
		} );
	}

	$( function () {
		syncAll();
		setTimeout( syncAll, 600 );

		$( document ).on(
			'change',
			'.wdcs-sections-picker input[type="checkbox"]',
			syncAll
		);
	} );

} )( jQuery, window.wdcsSections || { sections: {} } );
