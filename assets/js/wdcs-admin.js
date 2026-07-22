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

			$row.find( '.wdcs-image-url' ).val( url );

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
	   Post edit side panel – drag-and-drop sections builder
	   ========================================================= */
	$( function () {
		var $list = $( '#wdcs-active-list' );
		if ( ! $list.length ) return;

		// Init jQuery UI Sortable.
		$list.sortable( {
			handle     : '.wdcs-drag-handle',
			placeholder: 'wdcs-sortable-placeholder',
			axis       : 'y',
			update     : syncJetEngine,
		} );

		function buildItem( slug, label, jetengineId ) {
			return $( '<li class="wdcs-active-item"></li>' )
				.attr( 'data-slug', slug )
				.attr( 'data-jetengine', jetengineId || '' )
				.append( $( '<span class="wdcs-drag-handle dashicons dashicons-menu"></span>' ) )
				.append( $( '<span class="wdcs-active-label"></span>' ).text( label ) )
				.append( $( '<button type="button" class="wdcs-remove-active">&times;</button>' ) )
				.append( $( '<input type="hidden" name="wdcs_active_sections[]">' ).val( slug ) );
		}

		function updateEmpty() {
			var empty = $list.children( '.wdcs-active-item' ).length === 0;
			$( '.wdcs-active-empty' ).toggle( empty );
		}

		function syncJetEngine() {
			// Collect unique jetengine IDs currently in the active list.
			var activeIds = {};
			$list.children( '.wdcs-active-item' ).each( function () {
				var id = $( this ).data( 'jetengine' );
				if ( id ) activeIds[ id ] = true;
			} );
			// Show meta box if its ID is in the active list, hide otherwise.
			$( '.wdcs-avail-item' ).each( function () {
				var id = $( this ).data( 'jetengine' );
				if ( ! id ) return;
				$( '#' + id ).toggle( !! activeIds[ id ] );
			} );
		}

		// Add section to active list.
		$( document ).on( 'click', '.wdcs-add-to-active', function () {
			var $avail = $( this ).closest( '.wdcs-avail-item' );
			$list.append( buildItem(
				$avail.data( 'slug' ),
				$avail.data( 'label' ),
				$avail.data( 'jetengine' )
			) );
			updateEmpty();
			syncJetEngine();
		} );

		// Remove section from active list.
		$( document ).on( 'click', '.wdcs-remove-active', function () {
			$( this ).closest( '.wdcs-active-item' ).remove();
			updateEmpty();
			syncJetEngine();
		} );

		// Initial state.
		syncJetEngine();
		updateEmpty();
		setTimeout( syncJetEngine, 600 );
	} );

} )( jQuery, window.wdcsSections || { sections: {}, sectionCount: 0 } );
