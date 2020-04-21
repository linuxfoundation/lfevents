<?php
/**
 * Newsletter Form
 *
 * Register block and output markup.
 *
 * @since   1.0.0
 * @package CGB
 */

defined( 'ABSPATH' ) || exit;

register_block_type(
	'lf/form-newsletter',
	array(
		'render_callback' => 'lfevents_form_newsletter',
	)
);

function lfevents_form_newsletter( $attributes ) { // phpcs:disable
	ob_start();

	$action = isset( $attributes['action'] ) ? $attributes['action'] : '';
	$style = isset( $attributes['style'] ) ? $attributes['style'] : '';


	if ( empty( $action ) ) {
		return;
	}

	?>
	<div class="lfevents-forms form-newsletter <?php echo esc_attr( $style ); ?>">
		<div id="message"></div>

		<form id="sfmc-form" action="<?php echo esc_url( $action ); ?>">

			<div class="grid-x grid-margin-x">
				<label class="cell medium-6" for="FirstName">
					<input type="text" name="FirstName" placeholder="First name" required="">
				</label>

				<label class="cell medium-6" for="LastName">
					<input type="text" name="LastName" placeholder="Last name" required="">
				</label>

				<label for="EmailAddress">
					<input type="email" name="EmailAddress" placeholder="Email address" required="">
				</label>

				<input type="hidden" name="ownerid" value="00541000002w50ZAAQ">
				<input type="hidden" id="txtUrl" name="txtUrl" value="" readonly="">
				<div data-callback="onSubmit" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>

				<script>
					document.getElementById('txtUrl').value = window.location.href;
				</script>

				<input class="button expanded" type="submit" value="SIGN UP!" id="submitbtn">
			</div>
		</form>

		<script src="https://www.recaptcha.net/recaptcha/api.js" async="" defer=""></script>
	</div>

	<?php

	return ob_get_clean();
}
