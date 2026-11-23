/**
 * Front-end behaviour for the Social Share block:
 * - "Copy link" copies the post URL.
 * - "Instagram" opens the phone's share menu (where Instagram is listed).
 *   On computers, where Instagram can't receive a shared link, it copies the
 *   link and tells the reader to paste it into Instagram.
 */
( function () {
	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}
		return new Promise( function ( resolve, reject ) {
			var field = document.createElement( 'textarea' );
			field.value = text;
			field.setAttribute( 'readonly', '' );
			field.style.position = 'fixed';
			field.style.opacity = '0';
			document.body.appendChild( field );
			field.select();
			try {
				document.execCommand( 'copy' ) ? resolve() : reject();
			} catch ( e ) {
				reject( e );
			}
			document.body.removeChild( field );
		} );
	}

	function notify( block, message ) {
		var box = block.querySelector( '.sss__notice' );
		if ( ! box ) {
			return;
		}
		box.textContent = message;
		box.hidden = false;
		clearTimeout( box._sssTimer );
		box._sssTimer = setTimeout( function () {
			box.hidden = true;
		}, 4000 );
	}

	function isPhoneOrTablet() {
		return window.matchMedia && window.matchMedia( '(pointer: coarse)' ).matches;
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-sss-action]' );
		if ( ! button ) {
			return;
		}
		var block = button.closest( '.sss' );
		if ( ! block ) {
			return;
		}
		var url = block.getAttribute( 'data-url' ) || window.location.href;
		var title = block.getAttribute( 'data-title' ) || document.title;
		var action = button.getAttribute( 'data-sss-action' );

		if ( action === 'copy' ) {
			copyText( url ).then(
				function () {
					notify( block, 'Link copied.' );
				},
				function () {
					notify( block, 'Could not copy. Link: ' + url );
				}
			);
			return;
		}

		if ( action === 'instagram' ) {
			if ( navigator.share && isPhoneOrTablet() ) {
				navigator.share( { title: title, url: url } ).catch( function () {} );
				return;
			}
			copyText( url ).then(
				function () {
					notify( block, 'Link copied. Paste it into your Instagram story, bio or a message.' );
				},
				function () {
					notify( block, 'Copy this link into Instagram: ' + url );
				}
			);
		}
	} );
} )();
