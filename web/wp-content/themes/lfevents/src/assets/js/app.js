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
