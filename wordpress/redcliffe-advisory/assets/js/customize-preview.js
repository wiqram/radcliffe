/*
 * Inside Appearance > Customize: a pencil on every photograph.
 *
 * WordPress puts a pencil next to each editable piece of text by itself (see
 * the selective-refresh partials in inc/customizer.php). It cannot put one on
 * an <img>, which has no inside to put it in, so this adds one beside each
 * photograph that opens the matching "photograph" control.
 */
( function ( api, $ ) {
	if ( ! api ) {
		return;
	}

	function addPencils() {
		$( '[data-rad-img]' ).each( function () {
			var img = $( this );

			if ( img.data( 'radPencil' ) ) {
				return;
			}
			img.data( 'radPencil', true );

			var key = img.attr( 'data-rad-img' );
			var setting = 'rad_' + key.replace( /[.-]/g, '_' );
			var parent = img.parent();

			if ( 'static' === parent.css( 'position' ) ) {
				parent.css( 'position', 'relative' );
			}

			var button = $( '<button type="button" class="customize-partial-edit-shortcut-button rad-image-pencil" aria-label="Change this photograph"></button>' );
			button.html( '<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path fill="currentColor" d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02L5.27 17l-1.3-1.29 1.23-3.36 8.02-8.01c.39-.39 1.19-.42 1.64.03zM12.53 6.2l1.44-1.44L11.65 2.5l-1.44 1.44L12.53 6.2zM9.16 9.58l-.58.58L5.3 13.44l1.43 1.43 3.28-3.28 4.87-4.87-1.43-1.43-4.29 4.29z"/></svg>' );
			button.on( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();
				api.preview.send( 'focus-control-for-setting', setting );
			} );

			// The same wrapper WordPress uses, so the pencil gets the same look.
			var wrapper = $( '<span class="customize-partial-edit-shortcut rad-image-pencil-wrap"></span>' );
			wrapper.css( { position: 'absolute', left: '12px', top: '12px', zIndex: 20 } );
			wrapper.append( button );
			img.after( wrapper );
		} );
	}

	// api.preview only exists once the preview has connected to the Customizer.
	api.bind( 'preview-ready', function () {
		addPencils();
		if ( api.selectiveRefresh ) {
			api.selectiveRefresh.bind( 'partial-content-rendered', addPencils );
		}
	} );
} )( window.wp && window.wp.customize, window.jQuery );
