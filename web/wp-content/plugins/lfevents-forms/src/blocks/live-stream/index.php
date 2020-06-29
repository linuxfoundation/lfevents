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
	$redirect_url = isset( $attributes['redirectUrl'] ) ? $attributes['redirectUrl'] : '';

	if ( empty( $action ) || empty( $redirect_url ) ) {
		return;
	}

	?>
	<div class="lfevents-forms form-live-stream">
		<form id="sfmc-form1" action="<?php echo $action; ?>" data-redirect="<?php echo esc_url( $redirect_url ); ?>" method="POST">
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

			<div id="recaptcha-form1" style="display:none;"></div>

			<input class="button expanded" type="submit" value="SUBMIT" id="sfmc-submit1">
		</form>
		<div id="sfmc-message1"></div>
	</div>

	<?php

	return ob_get_clean();
}
