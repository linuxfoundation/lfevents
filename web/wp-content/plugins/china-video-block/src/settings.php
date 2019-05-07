<?php
/**
 * Creates a settings menu for the block.
 *
 * @since   1.0.0
 * @package CGB
 */

/**
 * Sets up the settings menus.
 */
function cvb_settings_init() {
	register_setting( 'cvb', 'cvb_options' );

	add_settings_section(
		'cvb_section_developers',
		__( 'Settings for the China Video Block plugin.', 'cvb' ),
		'cvb_section_developers_cb',
		'cvb'
	);

	add_settings_field(
		'cvb_ipinfo_token',
		__( 'IPInfo.io token', 'cvb' ),
		'cvb_ipinfo_token_cb',
		'cvb',
		'cvb_section_developers',
		[
			'label_for' => 'cvb_ipinfo_token',
			'class' => 'cvb_row',
			'cvb_custom_data' => 'custom',
		]
	);
}

add_action( 'admin_init', 'cvb_settings_init' );

/**
 * Developer section callback.
 *
 * @param array $args Args for the callback.
 */
function cvb_section_developers_cb( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"></p>
	<?php
}

/**
 * IPInfo callback.
 *
 * @param array $args Args for the callback.
 */
function cvb_ipinfo_token_cb( $args ) {
	$options = get_option( 'cvb_options' );
	?>
	<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
	value="<?php echo esc_attr( $options['cvb_ipinfo_token'] ); ?>"
	name="cvb_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	>
	<p class="description">
	<?php esc_html_e( 'The China Video Block uses ipinfo.io to detect whether a user is in China.  Please provide a token for this service.', 'cvb' ); ?>
	</p>
	<?php
}

/**
 * Setups up the options page under Settings menu.
 */
function cvb_options_page() {
	add_options_page(
		'China Video Block',
		'China Video Block',
		'manage_options',
		'cvb',
		'cvb_options_page_html'
	);
}

add_action( 'admin_menu', 'cvb_options_page' );


/**
 * Callback functions.
 */
function cvb_options_page_html() {
	// check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// show error/update messages.
	settings_errors( 'cvb_messages' );
	?>
	<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
	<?php
	// output security fields for the registered setting "cvb".
	settings_fields( 'cvb' );
	// output setting sections and their fields
	// (sections are registered for "cvb", each field is registered to a specific section).
	do_settings_sections( 'cvb' );
	// output save settings button.
	submit_button( 'Save Settings' );
	?>
	</form>
	</div>
	<?php
}
