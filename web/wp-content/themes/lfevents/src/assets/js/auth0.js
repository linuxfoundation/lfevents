/**
 * Auth0 config
 *
 * @package WordPress
 */

var devConfig = {
	domain: "linuxfoundation-dev.auth0.com",
	clientId: "4pLE05Pv325pX1ufdpOS2emphpbTHau6",
};
var prodConfig = {
	domain: "sso.linuxfoundation.org",
	clientId: "vwdLUhNE0M273cLwwIgvGTlHIrnYfCXm",
};
var productionURLs = [
	'lfasiallc.com',
	'events.linuxfoundation.org'
];

var currentOrigin = window.location.origin || "";
var isProd = productionURLs.some(
	function( prodDomain ) {
		return currentOrigin.indexOf( prodDomain ) > -1
	}
);

var config = isProd ? prodConfig : devConfig;

WordpressAuth0SPALibInit( config );
