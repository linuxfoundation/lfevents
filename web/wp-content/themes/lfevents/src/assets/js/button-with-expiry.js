/**
 * Comment.
 *
 * @package Block LF Button With Expiry
 */

const button = document.querySelector( '.wp-block-button__link' )
if ( button ) {
    const button_wrp = document.querySelector( '.wp-block-lf-button-with-expiry' )
    const button_style = button.getAttribute( 'style' )
    const disabled = document.querySelector( '.wp-block-lf-button-with-expiry .disabled' )
    button_wrp.setAttribute( 'style', button_style )
    button.setAttribute( 'style', '' )
    disabled.parentElement.classList.add( 'disabled' )
}
