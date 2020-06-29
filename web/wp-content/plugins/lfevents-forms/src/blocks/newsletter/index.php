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
	$style = isset( $attributes['style'] ) ? $attributes['style'] : 'box';


	if ( empty( $action ) ) {
		return;
	}

	?>
	<div class="lfevents-forms form-newsletter <?php echo esc_attr( $style ); ?>">
		<div id="sfmc-message1"></div>

		<form id="sfmc-form1" action="<?php echo esc_url( $action ); ?>">

			<div class="newsletter__form">
				<label class="medium-6" for="FirstName">
					<input type="text" name="FirstName" placeholder="First name" required="">
				</label>

				<label class="medium-6" for="LastName">
					<input type="text" name="LastName" placeholder="Last name" required="">
				</label>

				<label for="EmailAddress">
					<input type="email" name="EmailAddress" placeholder="Email address" required="">
				</label>

				<input class="button" type="submit" value="SIGN UP!" id="sfmc-submit1">

				<input type="hidden" name="ownerid" value="00541000002w50ZAAQ">
				<input type="hidden" id="txtUrl" name="txtUrl" value="" readonly="">
				<div id="recaptcha-form1" style="display:none;"></div>

				<script>
					document.getElementById('txtUrl').value = window.location.href;
				</script>
			</div>
		</form>
	</div>

	<?php

	return ob_get_clean();
}
