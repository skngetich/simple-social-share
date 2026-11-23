/**
 * Settings → Social Share: reorder networks and update the preview live.
 * Changes are only kept when you click "Save Changes".
 */
( function () {
	var list = document.getElementById( 'sss-network-list' );
	var previewBox = document.getElementById( 'sss-preview' );
	if ( ! list || ! previewBox ) {
		return;
	}

	function block() {
		return previewBox.querySelector( '.sss' );
	}

	function field( name ) {
		return document.querySelector( '[data-sss-field="' + name + '"]' );
	}

	function swapClass( el, prefix, values, value ) {
		values.forEach( function ( v ) {
			el.classList.remove( prefix + v );
		} );
		el.classList.add( prefix + value );
	}

	function syncNetworks() {
		var b = block();
		if ( ! b ) {
			return;
		}
		var ul = b.querySelector( '.sss__list' );
		list.querySelectorAll( 'li[data-key]' ).forEach( function ( row ) {
			var key = row.getAttribute( 'data-key' );
			var btn = ul.querySelector( '.sss__btn--' + key );
			if ( ! btn ) {
				return;
			}
			var item = btn.closest( 'li' );
			item.hidden = ! row.querySelector( 'input[type=checkbox]' ).checked;
			ul.appendChild( item ); // Re-appending puts items in row order.
		} );
	}

	function syncLook() {
		var b = block();
		if ( ! b ) {
			return;
		}

		// Heading.
		var text = field( 'heading' ).value.trim();
		var heading = b.querySelector( '.sss__heading' );
		if ( ! heading ) {
			heading = document.createElement( 'p' );
			heading.className = 'sss__heading';
			b.insertBefore( heading, b.firstChild );
		}
		heading.textContent = text;
		heading.hidden = ! text;

		// Labels, layout, alignment, size, shape.
		var labels = field( 'show_labels' ).checked;
		b.classList.toggle( 'sss--labels', labels );
		b.classList.toggle( 'sss--icons', ! labels );
		swapClass( b, 'sss--', [ 'row', 'column' ], field( 'layout' ).value );
		swapClass( b, 'sss--justify-', [ 'left', 'center', 'right' ], field( 'justify' ).value );
		swapClass( b, 'sss--', [ 'small', 'medium', 'large' ], field( 'size' ).value );
		swapClass( b, 'sss--', [ 'rounded', 'circle', 'square' ], field( 'shape' ).value );

		// Colours.
		var custom = field( 'color_mode' ).value === 'custom';
		swapClass( b, 'sss--', [ 'brand', 'custom' ], custom ? 'custom' : 'brand' );
		document.getElementById( 'sss-custom-colors' ).hidden = ! custom;
		if ( custom ) {
			b.style.setProperty( '--sss-bg', field( 'custom_background' ).value );
			b.style.setProperty( '--sss-fg', field( 'custom_text' ).value );
		} else {
			b.style.removeProperty( '--sss-bg' );
			b.style.removeProperty( '--sss-fg' );
		}
	}

	// Up / down arrows.
	list.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-move]' );
		if ( ! button ) {
			return;
		}
		var row = button.closest( 'li' );
		if ( button.getAttribute( 'data-move' ) === '-1' && row.previousElementSibling ) {
			list.insertBefore( row, row.previousElementSibling );
		} else if ( button.getAttribute( 'data-move' ) === '1' && row.nextElementSibling ) {
			list.insertBefore( row.nextElementSibling, row );
		}
		button.focus();
		syncNetworks();
	} );

	list.addEventListener( 'change', syncNetworks );

	document.querySelectorAll( '[data-sss-field]' ).forEach( function ( input ) {
		input.addEventListener( 'input', syncLook );
		input.addEventListener( 'change', syncLook );
	} );

	syncNetworks();
	syncLook();
} )();
