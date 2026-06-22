( function ( $, config ) {
	'use strict';

	/* =========================================================
	   Lightbox
	   ========================================================= */
	function openLightbox( src ) {
		var $lb = $( '#wdcs-lightbox' );
		if ( ! $lb.length ) return;
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
		if ( e.key === 'Escape' ) closeLightbox();
	} );


	/* =========================================================
	   Options page – Add / Remove unlimited sections
	   ========================================================= */
	var sectionCount = config.sectionCount || 0;

	$( '#wdcs-add-section' ).on( 'click', function () {
		var template = $( '#wdcs-row-template' ).html();
		if ( ! template ) return;

		var html = template.replace( /__IDX__/g, sectionCount );
		var $row = $( '<div class="wdcs-section-row" data-index="' + sectionCount + '">' + html + '</div>' );
		$( '#wdcs-sections-list' ).append( $row );
		$row.find( 'input[type="text"]' ).first().focus();
		sectionCount++;
	} );

	$( document ).on( 'click', '.wdcs-remove-section', function () {
		$( this ).closest( '.wdcs-section-row' ).fadeOut( 180, function () {
			$( this ).remove();
		} );
	} );


	/* =========================================================
	   Options page – WordPress media uploader per row
	   ========================================================= */
	$( document ).on( 'click', '.wdcs-upload-btn', function ( e ) {
		e.preventDefault();
		var $btn  = $( this );
		var $row  = $btn.closest( '.wdcs-section-row' );

		var frame = wp.media( {
			title   : 'Select Section Preview Image',
			button  : { text: 'Use this image' },
			multiple: false,
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url        = attachment.url;

			// Update hidden URL input.
			$row.find( '.wdcs-image-url' ).val( url );

			// Update thumb.
			var $thumbWrap = $row.find( '.wdcs-row-thumb' );
			var $img       = $thumbWrap.find( '.wdcs-row-thumb-img' );
			if ( $img.length ) {
				$img.attr( 'src', url ).data( 'full', url );
			} else {
				$thumbWrap.html(
					'<img src="' + url + '" class="wdcs-row-thumb-img wdcs-js-enlarge" data-full="' + url + '" alt="Preview">'
				);
			}
		} );

		frame.open();
	} );


	/* =========================================================
	   Post edit side panel – show / hide JetEngine meta boxes
	   ========================================================= */
	function toggleMetaBox( jetengineId, visible ) {
		if ( ! jetengineId ) return;
		var $box = $( '#' + jetengineId );
		if ( ! $box.length ) return;
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
		$( document ).on( 'change', '.wdcs-sections-picker input[type="checkbox"]', syncAll );
	} );

} )( jQuery, window.wdcsSections || { sections: {}, sectionCount: 0 } );
