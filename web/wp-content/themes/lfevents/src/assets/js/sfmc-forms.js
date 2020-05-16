/**
 * JS code to run only on pages that use a SFMC form.
 *
 * @package WordPress
 */

/**
 * Callback for form submission.
 *
 * @param {*} token callback token.
 */
function onSubmit(token) {
	var f = $( "#sfmc-form" )
	var message = document.getElementById( "message" );
	$.ajax(
		{
			url: f.attr( "action" ),
			type: 'POST',
			data: f.serialize(),
			beforeSend: function() {
				$( "#sfmc-form" ).toggle();
				$( "#message" ).html( "Thank you for your submission.  Your request is being processed..." ).addClass( "callout success" );
				message.scrollIntoView( { behavior: "smooth", block: 'center' } );
			},
			success: function(data) {
				var msg = $( data ).find( "p" ).text();
				$( "#message" ).html( msg );
				message.scrollIntoView( { behavior: "smooth", block: 'center' } );
			},
			error: function(xhr, status, error) {
				var errorMessage = xhr.status + ': ' + xhr.statusText;
				$( "#message" ).html( "There was an error processing your submission.  Please try again or contact us directly at events@linuxfoundation.org.<br>(" + errorMessage + ")" ).removeClass( "success" ).addClass( "alert" );
				message.scrollIntoView( { behavior: "smooth", block: 'center' } );
			}
		}
	);
}
window.onSubmit = onSubmit; // need to do this to export the onSubmit function from the module scope created by WebPack.

$( document ).ready(
	function() {
		var f = $( "#sfmc-form" )
		f.on(
			"click",
			"#submitbtn",
			function(e) {
				if (f[0].checkValidity()) {
					e.preventDefault();
					grecaptcha.execute();
				}
			}
		);
	}
);
