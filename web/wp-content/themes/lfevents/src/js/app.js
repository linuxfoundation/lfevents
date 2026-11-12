/**
 * App JS.
 *
 * @package FoundationPress
 */

import Foundation from './libraries/foundation-explicit-pieces';

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
	'.header-global',
	function () {
		removeHeaderHeight();
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
		$( '.header-global__hamburger' ).click(
			function( e ) {
				e.preventDefault();
				if ( ! isMobile ) {
					return;
				}
				$( this ).toggleClass( 'is-active' );
				$( 'body' ).toggleClass( 'menu-is-active' );
				$( '.header-global__mobile-menu-container' ).toggleClass( 'is-active' );
			},
		);

		// click actions on menus.
		$( '.menu-item-has-children > a' ).click(
			function( e ) {
				e.preventDefault();
				if ( isMobile ) {
					// add is-open-mobile class to open menus.
					$( this ).parent().toggleClass( 'is-open-mobile' );
					$( this ).parent().children( '.sub-menu:first' ).slideToggle( 250 );
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
					// make sure no inline style is set from slideToggle.
					$( this ).children( '.sub-menu' ).removeAttr( 'style' );
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
			$( '.header-global__hamburger' ).removeClass( 'is-active' );
			$( 'body' ).removeClass( 'menu-is-active' );
			$( '.header-global__mobile-menu-container' ).removeClass( 'is-active' );

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

// Registration attribution, with nothing stored in the browser (no consent needed). When this page's
// address carries the visitor's UTM parameters, copy them onto this page's links to the same event's
// pages and onto registration links (register.linuxfoundation.org), so they still reach Cvent after a
// detour such as the FAQ. Registration links always lose UTMs written on them, so a visitor who
// arrived without UTMs sends none: fixed UTMs would overwrite their real source. `ref` marks
// registrations that started on this site.
( function () {
	var registerHost = 'register.linuxfoundation.org';
	var registerRef = 'events-site';
	var utm = [];
	var eventPrefix = '';

	try {
		new URLSearchParams( window.location.search ).forEach(
			function ( value, name ) {
				if ( 0 === name.indexOf( 'utm_' ) && value ) {
					utm.push( [ name, value ] );
				}
			}
		);
		// An event's pages share the first path segment: /kubecon-cloudnativecon-north-america/...
		var segment = window.location.pathname.split( '/' )[1];
		eventPrefix = segment ? '/' + segment + '/' : '';
	} catch ( e ) {
		return;
	}

	function useVisitorUtm( url ) {
		Array.from( url.searchParams.keys() ).forEach(
			function ( name ) {
				if ( 0 === name.indexOf( 'utm_' ) ) {
					url.searchParams.delete( name );
				}
			}
		);
		utm.forEach(
			function ( pair ) {
				url.searchParams.append( pair[0], pair[1] );
			}
		);
	}

	function isSameEventPage( url ) {
		if ( ! utm.length || ! eventPrefix || url.origin !== window.location.origin ) {
			return false;
		}
		// A link to this same page (such as #faq) must stay an in-page jump, not a reload.
		if ( url.pathname === window.location.pathname && url.search === window.location.search ) {
			return false;
		}
		return 0 === url.pathname.replace( /\/?$/, '/' ).indexOf( eventPrefix );
	}

	function decorate( link ) {
		try {
			var url = new URL( link.href );
			if ( 'https:' !== url.protocol && 'http:' !== url.protocol ) {
				return;
			}
			if ( url.hostname === registerHost ) {
				useVisitorUtm( url );
				if ( ! url.searchParams.has( 'ref' ) ) {
					url.searchParams.set( 'ref', registerRef );
				}
			} else if ( isSameEventPage( url ) ) {
				useVisitorUtm( url );
			} else {
				return;
			}
			if ( url.href !== link.href ) {
				link.href = url.href;
			}
		} catch ( e ) {
			// A link this can't parse is left as it is; nothing else on the page is affected.
		}
	}

	document.querySelectorAll( 'a[href]' ).forEach( decorate );
	// Links rendered after load are decorated when they are opened: a click, a middle-click
	// (`auxclick`) or the context menu ("Open in new tab", "Copy link").
	[ 'click', 'auxclick', 'contextmenu' ].forEach(
		function ( type ) {
			document.addEventListener(
				type,
				function ( e ) {
					var link = e.target.closest && e.target.closest( 'a[href]' );
					if ( link ) {
						decorate( link );
					}
				},
				true
			);
		}
	);
}() );

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

// Listen to tab events to enable outlines.
document.body.addEventListener(
	'keyup',
	function( e ) {
		if ( e.which === 9 ) { /* tab */
			document.documentElement.classList.remove( 'no-focus-outline' );
		}
	}
);

// Open the Transcend consent manager from any "Cookie Settings" link.
document.addEventListener(
	'click',
	function( e ) {
		const link = e.target.closest( 'a' );

		if ( ! link || link.textContent.trim().toLowerCase() !== 'cookie settings' ) {
			return;
		}

		e.preventDefault();

		if ( window.transcend && typeof window.transcend.showConsentManager === 'function' ) {
			window.transcend.showConsentManager();
		}
	}
);
