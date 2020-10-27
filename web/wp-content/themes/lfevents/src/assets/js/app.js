/**
 * Comment.
 *
 * @package FoundationPress
 */

import './lib/foundation-explicit-pieces';
import cookieBanner from './cookie-banner.js';

$( document ).foundation();

// Prevents menu items being clickable if has children.
$( '.page_item_has_children a[href="#"]' ).click(
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


// ShareASale tracking:
// if sas_m_awin cookie is set then add its value as the sscid querystring param
// on any links to cvent
$( 'a[href*="cvent.com"]' ).each(
	function () {
		var link = $( this ).attr( 'href' );
		var complement = '';
		var cookie = readCookie("sas_m_awin");
		var filling = JSON.parse(cookie);

		if ( filling['clickId'] ) {
			complement = 'sscid=' + filling['clickId']
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
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}
