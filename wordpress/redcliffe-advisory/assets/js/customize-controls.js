/*
 * Inside Appearance > Customize: the row-by-row list editor (inc/customizer-list-control.php).
 *
 * Each row is a set of boxes; the buttons add, remove and move rows. After
 * every change the rows are written into the hidden field WordPress watches,
 * one row per line with the parts separated by "|", so the preview and the
 * saved value are exactly what the theme prints.
 */
( function ( $ ) {
	function rowMarkup( columns, cells ) {
		var row = $( '<li class="rad-list-row"><span class="rad-list-cells"></span><span class="rad-list-actions"><button type="button" class="button rad-list-up" title="Move up" aria-label="Move up">&uarr;</button><button type="button" class="button rad-list-down" title="Move down" aria-label="Move down">&darr;</button><button type="button" class="button-link button-link-delete rad-list-remove">Remove</button></span></li>' );
		var boxes = row.find( '.rad-list-cells' );

		$.each( columns, function ( n, name ) {
			var label = $( '<label class="rad-list-cell"><span class="screen-reader-text"></span><input type="text" /></label>' );
			label.find( 'span' ).text( name );
			label.find( 'input' ).attr( 'placeholder', name ).val( cells && cells[ n ] ? cells[ n ] : '' );
			boxes.append( label );
		} );

		return row;
	}

	function setUp( control ) {
		var columns = control.data( 'columns' ) || [];
		var defaults = control.data( 'defaults' ) || [];
		var rows = control.find( '.rad-list-rows' );
		var value = control.find( '.rad-list-value' );

		function save() {
			var lines = [];
			rows.children().each( function () {
				var cells = $( this ).find( 'input' ).map( function () {
					// "|" separates the parts of a row, so one typed into a box becomes a slash.
					return $.trim( this.value ).replace( /\|/g, '/' );
				} ).get();
				if ( cells.join( '' ) ) {
					lines.push( cells.join( ' | ' ) );
				}
			} );
			value.val( lines.join( '\n' ) ).trigger( 'change' );
		}

		control.on( 'input', '.rad-list-cell input', save );

		control.on( 'click', '.rad-list-add', function () {
			var row = rowMarkup( columns, [] );
			rows.append( row );
			row.find( 'input' ).first().trigger( 'focus' );
			save();
		} );

		control.on( 'click', '.rad-list-remove', function () {
			$( this ).closest( '.rad-list-row' ).remove();
			save();
		} );

		control.on( 'click', '.rad-list-up', function () {
			var row = $( this ).closest( '.rad-list-row' );
			row.prev().before( row );
			save();
		} );

		control.on( 'click', '.rad-list-down', function () {
			var row = $( this ).closest( '.rad-list-row' );
			row.next().after( row );
			save();
		} );

		control.on( 'click', '.rad-list-reset', function () {
			rows.empty();
			$.each( defaults, function ( _n, cells ) {
				rows.append( rowMarkup( columns, cells ) );
			} );
			// An empty value is how the theme knows to use the design's own rows.
			value.val( '' ).trigger( 'change' );
		} );
	}

	$( function () {
		$( '.rad-list' ).each( function () {
			setUp( $( this ) );
		} );
	} );
} )( window.jQuery );
