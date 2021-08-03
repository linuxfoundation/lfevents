<?php
/**
 * Add Admin notices.
 *
 * @package conditional-blocks-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * conblockpro_admin_notices.
 *
 * Easily handle notices across screens
 *
 * @class    conblockpro_admin_notices
 * @version  1.0.0
 * @author   Morgan Hvidt
 */
class conblockpro_admin_notices {
	/**
	 * Transient name
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var str $transient_name
	 */
	protected $transient_name = 'conblockpro_transient_notices';
	/**
	 * Construct the transient notices class
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
	}
	/**
	 * Display notices
	 *
	 * @since 1.0.0
	 */
	public function display_notices() {

		$notices = get_transient( $this->transient_name );

		if ( $notices ) {

			foreach ( $notices as $notice ) {

				echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . '"><p>';
				echo $notice['message'];
				echo '</p></div>';
			}
		}

		delete_transient( $this->transient_name );

	}
	/**
	 * Add notice
	 *
	 * @since 1.0.0
	 * @param str $type info|error|warning.
	 * @param str $message string.
	 */
	public function add_notice( $type = false, $message = false ) {
		if ( ! $type || ! $message ) {
			return;
		}
		$notices = get_transient( $this->transient_name );
		$notices = $notices ? $notices : array();

		if ( in_array( $message, $notices, true ) ) {
			return;
		}

		$notices[ $message ] = array(
			'type'    => $type,
			'message' => $message,
		);

		set_transient( $this->transient_name, $notices, 30 );
	}
}
