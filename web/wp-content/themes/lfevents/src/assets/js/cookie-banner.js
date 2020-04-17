/**
 * Cookie Banner code. Checks and sets cookie based on acceptance.
 *
 * @package WordPress
 * @author James Hunt
 */

export default {
	init() {
		// checks for cookie, if not present, shows banner.
		if (document.cookie.indexOf( "cookieaccepted" ) < 0) {
			document.getElementById( "cookie-banner" ).style.cssText = 'visibility: visible; opacity: 1';
		}
	},

	// Creates cookie and hides banner.
	acceptCookie() {
		document.cookie = "cookieaccepted=1; expires=Thu, 18 Dec 2030 12:00:00 UTC; path=/",document.getElementById( "cookie-banner" ).style.cssText = 'visibility: hidden; opacity: 0';
	}
}
