(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.wdcs-cb-btn' ).forEach( function ( btn ) {
			var origBg    = btn.style.backgroundColor;
			var origColor = btn.style.color;
			var hoverBg   = btn.dataset.hoverBg    || '';
			var hoverColor = btn.dataset.hoverColor || '';

			if ( ! hoverBg && ! hoverColor ) {
				return;
			}

			btn.addEventListener( 'mouseenter', function () {
				if ( hoverBg )    btn.style.backgroundColor = hoverBg;
				if ( hoverColor ) btn.style.color = hoverColor;
			} );

			btn.addEventListener( 'mouseleave', function () {
				btn.style.backgroundColor = origBg;
				btn.style.color = origColor;
			} );
		} );
	} );
} )();
