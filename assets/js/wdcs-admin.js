( function ( $, config ) {
	'use strict';

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

		// Small delay to catch any meta boxes JetEngine injects late.
		setTimeout( syncAll, 600 );

		$( document ).on(
			'change',
			'.wdcs-sections-picker input[type="checkbox"]',
			syncAll
		);
	} );

} )( jQuery, window.wdcsSections || { sections: {} } );
