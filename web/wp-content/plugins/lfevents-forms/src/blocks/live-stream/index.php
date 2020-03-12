<?php
/**
 * Live Stream Form
 *
 * Register block and output markup.
 *
 * @since   1.0.0
 * @package CGB
 */

defined( 'ABSPATH' ) || exit;

register_block_type(
	'lf/form-live-stream',
	array(
		'render_callback' => 'lfevents_form_live_stream',
	)
);

function lfevents_form_live_stream( $attributes ) { // phpcs:disable
	ob_start();

	$action       = isset( $attributes['action'] ) ? $attributes['action'] : '';
	$required_url = isset( $attributes['requiredUrl'] ) ? $attributes['requiredUrl'] : '';

	?>
	<div class="lfevents-forms form-live-stream">
		<form id="ls-form" action="<?php echo $action; ?>" method="POST">
			<div class="grid-x grid-margin-x">
				<label class="cell medium-6" for="FirstName">First name *
					<input type="text" name="FirstName" placeholder="First name" required="">
				</label>

				<label class="cell medium-6" for="LastName">Last name *
					<input type="text" name="LastName" placeholder="Last name" required="">
				</label>

				<label class="cell medium-6" for="Company">Company *
					<input type="text" name="Company" placeholder="Company" required="">
				</label>

				<label class="cell medium-6" for="EmailAddress">Email *
					<input type="email" name="EmailAddress" placeholder="Email address" required="">
				</label>
			</div>

			<div data-callback="onSubmitLS" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>

			<input class="button expanded" type="submit" value="SUBMIT" id="submitbtn">
		</form>
		<div id="message"></div>
		<script src="https://www.recaptcha.net/recaptcha/api.js" async="" defer=""></script>
		<script>
			function onSubmitLS(token) {
				var f = $( "#ls-form" )
				$.ajax(
					{
						url: f.attr( "action" ),
						type: 'POST',
						data: f.serialize(),
						beforeSend: function() {
							$( "#ls-form" ).toggle();
							$( "#message" ).html( "Thank you for your submission.  Your request is being processed..." ).addClass( "callout success" );
						},
						success: function(data) {
							var msg = $( data ).find( "p" ).text();
							$( "#message" ).html( msg );
							window.location.href = "<?php echo esc_url( $required_url ); ?>";
						},
						error: function(xhr, status, error) {
							var errorMessage = xhr.status + ': ' + xhr.statusText;
							$( "#message" ).html( "There was an error processing your submission.  Please try again or contact us directly at events@linuxfoundation.org.<br>(" + errorMessage + ")" ).removeClass( "success" ).addClass( "alert" );
							alert( "There was an error processing your submission.  Please try again or contact us directly at events@linuxfoundation.org." );
						}
					}
				);
			}

			$( document ).ready(
				function() {
					var f = $( "#ls-form" )
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
		</script>
	</div>

	<?php

	return ob_get_clean();
}
