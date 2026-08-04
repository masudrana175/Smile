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
		var $btn = $( this );
		var $row = $btn.closest( '.wdcs-section-row' );

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

		$list.sortable( {
			handle     : '.wdcs-drag-handle',
			placeholder: 'wdcs-sortable-placeholder',
			axis       : 'y',
			update     : syncJetEngine,
		} );

		function buildItem( slug, label, jetengineId ) {
			var $nameInput = $( '<input type="text" class="wdcs-active-name" name="wdcs_active_sections_name[]">' )
				.attr( 'placeholder', label );

			var $top = $( '<div class="wdcs-active-top"></div>' )
				.append( $( '<span class="wdcs-drag-handle dashicons dashicons-menu"></span>' ) )
				.append( $nameInput )
				.append( $( '<button type="button" class="wdcs-remove-active">&times;</button>' ) );

			return $( '<li class="wdcs-active-item"></li>' )
				.attr( 'data-slug', slug )
				.attr( 'data-jetengine', jetengineId || '' )
				.append( $top )
				.append( $( '<input type="hidden" name="wdcs_active_sections_slug[]">' ).val( slug ) );
		}

		function updateEmpty() {
			var empty = $list.children( '.wdcs-active-item' ).length === 0;
			$( '.wdcs-active-empty' ).toggle( empty );
		}

		function updateAvailButtons() {
			var active = {};
			$list.children( '.wdcs-active-item' ).each( function () {
				active[ $( this ).data( 'slug' ) ] = true;
			} );
			$( '.wdcs-avail-item' ).each( function () {
				var added = !! active[ $( this ).data( 'slug' ) ];
				$( this ).find( '.wdcs-add-to-active' ).prop( 'disabled', added );
				$( this ).toggleClass( 'wdcs-avail-added', added );
			} );
		}

		function syncJetEngine() {
			var activeIds = {};
			$list.children( '.wdcs-active-item' ).each( function () {
				var id = $( this ).data( 'jetengine' );
				if ( id ) activeIds[ id ] = true;
			} );
			$( '.wdcs-avail-item' ).each( function () {
				var id = $( this ).data( 'jetengine' );
				if ( ! id ) return;
				$( '#' + id ).toggle( !! activeIds[ id ] );
			} );
		}

		// Add section to active list (one instance per section only).
		$( document ).on( 'click', '.wdcs-add-to-active', function () {
			var $avail = $( this ).closest( '.wdcs-avail-item' );
			var slug   = $avail.data( 'slug' );
			if ( $list.find( '.wdcs-active-item[data-slug="' + slug + '"]' ).length ) {
				return;
			}
			$list.append( buildItem( slug, $avail.data( 'label' ), $avail.data( 'jetengine' ) ) );
			updateEmpty();
			updateAvailButtons();
			syncJetEngine();
		} );

		// Add a new independent Content Builder section.
		$( document ).on( 'click', '.wdcs-add-cb-section', function ( e ) {
			e.stopPropagation();
			var $btn = $( this );
			if ( $btn.prop( 'disabled' ) ) return;
			$btn.prop( 'disabled', true );
			setTimeout( function () { $btn.prop( 'disabled', false ); }, 600 );

			var sectionId = 'sec_' + Date.now() + '_' + Math.floor( Math.random() * 1000 );
			var slug      = 'wdcs_cb_' + sectionId;
			$list.append( buildItem( slug, 'Content Section', '' ) );
			updateEmpty();
			// Notify the Content Builder meta box to add a matching section panel.
			$( document ).trigger( 'wdcs:cb:new-section', [ sectionId ] );
		} );

		// Click active item → scroll to its section or JetEngine meta box.
		$( document ).on( 'click', '.wdcs-active-item', function ( e ) {
			if ( $( e.target ).closest( '.wdcs-remove-active, .wdcs-active-name, .wdcs-drag-handle' ).length ) {
				return;
			}

			var slug = $( this ).data( 'slug' ) || '';

			// Content Builder section → scroll to its panel in the builder meta box.
			if ( slug.indexOf( 'wdcs_cb_' ) === 0 ) {
				var sectionId = slug.replace( 'wdcs_cb_', '' );
				var $cbSection = $( '.wdcs-cb-section[data-id="' + sectionId + '"]' );
				if ( ! $cbSection.length ) return;
				// Expand if collapsed.
				var $body = $cbSection.find( '.wdcs-cb-section-body' );
				if ( $body.is( ':hidden' ) ) {
					$body.show();
					$cbSection.find( '.wdcs-cb-section-toggle .dashicons' )
						.removeClass( 'dashicons-arrow-down-alt2' )
						.addClass( 'dashicons-arrow-up-alt2' );
				}
				$( 'html, body' ).animate( { scrollTop: $cbSection.offset().top - 50 }, 300 );
				$cbSection.addClass( 'wdcs-metabox-highlight' );
				setTimeout( function () { $cbSection.removeClass( 'wdcs-metabox-highlight' ); }, 1500 );
				return;
			}

			// Elementor section → scroll to its JetEngine meta box.
			var id = $( this ).data( 'jetengine' );
			if ( ! id ) return;
			var $target = $( '#' + id );
			if ( ! $target.length ) return;
			$( 'html, body' ).animate( { scrollTop: $target.offset().top - 50 }, 300 );
			$target.addClass( 'wdcs-metabox-highlight' );
			setTimeout( function () { $target.removeClass( 'wdcs-metabox-highlight' ); }, 1500 );
		} );

		// Remove section from active list.
		$( document ).on( 'click', '.wdcs-remove-active', function () {
			$( this ).closest( '.wdcs-active-item' ).remove();
			updateEmpty();
			updateAvailButtons();
			syncJetEngine();
		} );

		// Initial state.
		syncJetEngine();
		updateEmpty();
		updateAvailButtons();
		setTimeout( syncJetEngine, 600 );
	} );

} )( jQuery, window.wdcsSections || { sections: {}, sectionCount: 0 } );
