<?php
/**
 * Cookie Banner
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 * @author James Hunt
 */

?>

<?php
// Setup default values.
$cookies_text        = 'This website uses cookies to offer you a better browsing experience. ';
$cookies_link_text   = 'Find out more about how we use cookies and how you can change your settings.';
$cookies_button_text = 'Accept';
$cookies_link        = 'https://www.linuxfoundation.org/cookies/';

// Checking lfeventsci otherwise it's Asia - so replace values?
if ( ! is_lfeventsci() ) {
	$cookies_text        = '本网站使用cookies为您提供更好的浏览体验。';
	$cookies_link_text   = '了解更多关于我们如何使用cookies和如何更改您的设置。';
	$cookies_button_text = '接受';
}
?>

<p id="cookie-banner"><?php echo esc_html( $cookies_text ); ?> <?php echo sprintf( '<a target="_blank" href="%s">%s</a>', esc_html( $cookies_link ), esc_html( $cookies_link_text ) ); ?><button id="cookie-banner-button" tabindex="0"
 role="button">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.959 17l-4.5-4.319 1.395-1.435 3.08 2.937 7.021-7.183 1.422 1.409-8.418 8.591z"/></svg>
<?php echo esc_html( $cookies_button_text ); ?></button></p>
