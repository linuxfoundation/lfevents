/**
 * Comment.
 *
 * @package FoundationPress
 */

import './lib/foundation-explicit-pieces';
import cookieBanner from './cookie-banner.js';

$( document ).foundation();

// Global.
( function( global ) {
	// Generic throttle function.
	// window.utils.isThrottled() - how to use.
	function throttle( callback, wait, immediate = false ) {
		let timeout = null;
		let initialCall = true;
		return function() {
			const callNow = immediate && initialCall;
			const next = () => {
				callback.apply( this, arguments );
				timeout = null;
			};
			if ( callNow ) {
				initialCall = false;
				next();
			}
			if ( ! timeout ) {
				timeout = setTimeout( next, wait );
			}
		};
	}

	// Global bundle.
	global.utils = {
		isThrottled: throttle,
	};
}( window ) );

// Prevents menu items being clickable if has children.
$( '.event-menu .page_item_has_children a[href="#"]' ).click(
	function ( e ) {
		e.preventDefault();
	}
);

// Init cookie banner on load.
$( document ).ready(
	() => { cookieBanner.init(); }
);

// Add event listener to button in banner.
document.getElementById( 'cookie-banner-button' ).addEventListener(
	'click',
	() => { cookieBanner.acceptCookie(); }
);


// Removes height from sticky container.
let removeHeaderHeight = function(){
	if ($( window ).width() < 1024) {
		$( '.sticky-container' ).css( 'height', 'auto' );
	}
}

// Activates on event header.
$( document ).on(
	'on.zf.toggler',
	'header.sticky',
	function () {
		removeHeaderHeight();
	}
);

// Activates on global header.
$( document ).on(
	'on.zf.toggler',
	'.main-header',
	function () {
		removeHeaderHeight();
	}
);

// Home page slide show.
$( document ).ready(
	function() {

		var bg_images = document.getElementsByClassName( 'bg-images' );
		if (bg_images.length === 0) {
			return;
		}

		$( '.bg-images > .bg-image:gt(0)' ).hide();
		setInterval(
			function() {
				$( '.bg-images > .bg-image:first' )
				.fadeOut( 1000 )
				.next()
				.fadeIn( 1000 )
				.end()
				.appendTo( '.bg-images' );
			},
			4000
		);

	}
);

// Non-Event page menu.
$( document ).ready(
	function() {

		// Mobile check.
		let isMobile = checkMobile();
		function checkMobile() {
			return ( ( $( window ).width() <= 1024 ) );
		}

		// Mobile Menu hamburger (hidden on desktop).
		$( '.lf-hamburger' ).click(
			function( e ) {
				e.preventDefault();
				if ( ! isMobile ) {
					return;
				}
				$( this ).toggleClass( 'is-active' );
				$( 'body' ).toggleClass( 'menu-is-active' );
				$( '.mobile-menu-wrapper' ).toggleClass( 'is-active' );
			},
		);

		$( 'li.menu-item-has-children > a' ).click(
			function( e ) {
				e.preventDefault();
				if ( isMobile ) {
					$( this ).parent().children( '.sub-menu:first' ).slideToggle( 500 );
				} else {
					// Stop empty menu parents jumping to top of screen on click.
					if ( $( this ).attr( 'href' ) === '#' ) {
						e.preventDefault();
					}
				}
			},
		);

		// add is-open class to maintain current state in open menus.
		$( '.menu-item-has-children' ).hover(
			function() {
				if ( ! isMobile ) {
					$( this ).removeClass( 'is-open' );
					$( this ).addClass( 'is-open' );
				}
			},
			function() {
				if ( ! isMobile ) {
					$( this ).removeClass( 'is-open' );
				}
			}
		);

		// Resize check for is mobile.
		function resizeHandle() {
			isMobile = checkMobile();
		}

		// Update on resize.
		$( window ).on( 'resize', window.utils.isThrottled( resizeHandle, 200, true ) );
	}
);

// ShareASale tracking:
// if sas_m_awin cookie is set then add its value as the sscid querystring param on any links to cvent.
$( 'a[href*="cvent.com"]' ).each(
	function () {
		var link = $( this ).attr( 'href' );
		var complement = '';
		var cookie = readCookie( "sas_m_awin" );
		if ( cookie ) {
			var filling = JSON.parse( cookie );
			if ( filling['clickId'] ) {
				complement = 'sscid=' + filling['clickId']
			}
		}
		if (link && complement) {
			if (link.indexOf( '?' ) != -1) {
				$( this ).attr( 'href', link + '&' + complement );
			} else {
				$( this ).attr( 'href', link + '?' + complement );
			}
		}
	}
);

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split( ';' );
	var ca_len = ca.length;
	for (var i = 0; i < ca_len; i++) {
		var c = ca[i];
		while (c.charAt( 0 ) == ' ') {
			c = c.substring( 1, c.length );
		}
		if (c.indexOf( nameEQ ) == 0) {
			return c.substring( nameEQ.length, c.length );
		}
	}
	return null;
}
