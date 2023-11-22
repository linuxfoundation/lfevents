<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CONBLOCKPRO_Puri_SL_UI' ) ) {
	/**
	 * PURI-SL version 2.0.1
	 */
	class CONBLOCKPRO_Puri_SL_UI {

		protected $plugin    = array();
		protected $ui_config = array();

		/**
		 * Instantiate the conblockpro_Puri_SL_UI class
		 */
		public function __construct( $plugin_data, $plugin_ui_config ) {

			$this->plugin    = $plugin_data;
			$this->ui_config = $plugin_ui_config;

			// Handle AJAX actions.
			add_action( 'wp_ajax_' . $this->plugin['base'] . '-conblockpro-puri-sl-operations', array( $this, 'edd_operations' ) );

			// Run the admin UI.
			add_action( 'admin_init', array( $this, 'run_ui' ), 10 );
		}

		public function run_ui() {

			global $pagenow;

			if ( $pagenow !== 'plugins.php' ) {
				return;
			}

			// Additional Data.
			$this->plugin['license']        = trim( $this->plugin['license'], '' );
			$this->plugin['license_status'] = get_site_option( $this->plugin['slug'] . '_license_status', false );
			$this->plugin['ajax_url']       = admin_url( 'admin-ajax.php' );

			// WP Multsite.
			if ( is_multisite() ) {
				// Check if only managed in the network admin.
				if ( is_plugin_active_for_network( plugin_basename( $this->plugin['file_path'] ) ) && ! is_network_admin() ) {
					return;
				}
			}

			$this->maybe_check_current_status();

			// Enqueue Scripts.
			add_action( 'admin_print_scripts-plugins.php', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_print_styles-plugins.php', array( $this, 'enqueue_styles' ) );

			// Insert the manage license link.
			$prefix = is_network_admin() ? 'network_admin_' : '';

			add_filter( $prefix . 'plugin_action_links_' . $this->plugin['base'], array( $this, 'insert_license_link' ), 10 );

			// Set the license html.
			if ( ! empty( $this->plugin['license'] ) && $this->plugin['license_status'] === 'valid' ) {
				add_action( 'after_plugin_row_' . $this->plugin['base'], array( $this, 'html_row_manage_license' ), 8, 3 );
			} else {
				add_action( 'after_plugin_row_' . $this->plugin['base'], array( $this, 'html_row_enter_license' ), 8, 3 );
			}
		}

		/**
		 * Enqueue Scripts
		 */
		public function enqueue_scripts() {
			wp_register_script( 'conblockpro_puri_sl_ui_script', plugins_url( 'puri-sl-ui.js', __FILE__ ), false, $this->plugin['version'] );

			wp_enqueue_script( 'conblockpro_puri_sl_ui_script' );

			wp_localize_script( 'conblockpro_puri_sl_ui_script', 'conblockpro_puri_sl', $this->plugin );
		}

		/**
		 * Enqueue Styles
		 */
		public function enqueue_styles() {
			wp_register_style( 'conblockpro_puri_sl_ui_style', plugins_url( 'puri-sl-ui.css', __FILE__ ), false, $this->plugin['version'] );
			wp_enqueue_style( 'conblockpro_puri_sl_ui_style' );
		}

		/**
		 * Add a License Link to Plugin
		 */
		public function insert_license_link( $links ) {
			// License GUI.
			if ( ! empty( $this->plugin['license'] ) && $this->plugin['license_status'] === 'valid' ) {

				$text_license = __( 'Manage License', 'conditional-blocks' );

				$license_link = '<a href="javascript:void(0);" class="conblockpro-puri-sl-manage-link">' . $text_license . ' </a>';

				array_unshift( $links, $license_link );
			}

			if ( ! empty( $this->ui_config['support_url'] ) ) {

				$text_support = __( 'Support', 'conditional-blocks' );

				$support_link = '<a href="' . $this->ui_config['support_url'] . '" target="_blank">' . $text_support . ' </a>';

				array_unshift( $links, $support_link );
			}

			return $links;
		}

		/**
		 * Adds row on the plugin table. Provides GUI to enter License
		 */
		public function html_row_enter_license() {
			$format           = __( 'Click here to enter your license key for %s to get security updates.', 'conditional-blocks' );
			$text_description = sprintf( $format, '<strong>' . $this->plugin['name'] . '</strong>' );

			$text_placeholder = __( 'Enter Your License', 'conditional-blocks' );
			$text_button      = __( 'Activate License', 'conditional-blocks' );

			?>
			<tr class="plugin-update-tr active">
				<td class="plugin-update colspanchange" colspan="100%">
					<div class="update-message notice inline notice-error notice-alt conblockpro-puri-sl-wrapper">
						<p><a href="javascript:void(0);" class="conblockpro-puri-sl-manage-link"><?php echo $text_description; ?></a></p>
						<div class="conblockpro-puri-sl-row" style="display:none">
							<input class="conblockpro-puri-sl-license-key" style="margin-right:-14px; border-top-right-radius:0px; border-bottom-right-radius:0px; border-right:0px;" type="text" value="<?php echo esc_html( $this->plugin['license'] ); ?>"  placeholder="<?php echo $text_placeholder; ?>"/>
							<button class="button conblockpro-puri-sl-button" data-action=<?php echo $this->plugin['base'] . '-conblockpro-puri-sl-operations'; ?> data-operation="activate_license" data-nonce="<?php echo wp_create_nonce( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?>"> <span class="dashicons dashicons-update"></span> <?php echo $text_button; ?></button>
						</div>
					</div>
					<div class="conblockpro-puri-sl-message"></div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Adds row on the plugin table. Provides GUI to deactivate, check Expiry of license etc.
		 */
		public function html_row_manage_license() {

			$text_change_license = __( 'Change License', 'conditional-blocks' );

			if ( $this->plugin['beta'] && $this->plugin['beta'] ) {
				$text_change_beta = __( 'Beta Opt-out', 'conditional-blocks' );
				$beta_style       = 'background:#fff8e5;';
			} else {
				$text_change_beta = __( 'Beta Opt-in', 'conditional-blocks' );
				$beta_style       = '';
			}

			if ( ! $this->plugin['beta'] ) {
				$beta_style = 'display:none';
			}

			$text_check_expiry     = __( 'Check Expiration', 'conditional-blocks' );
			$text_deactive_license = __( 'Deactivate License', 'conditional-blocks' );

			?>

			<tr class="conblockpro-puri-sl-row plugin-update-tr active update" style="display: none">

			<td colspan="100%" class="plugin-update colspanchange">

					<div class="conblockpro-puri-sl-row update-message inline notice-alt">
						<input class="conblockpro-puri-sl-license-key" type="password" autocomplete="off" style="margin-right:-14px; border-top-right-radius:0px; border-bottom-right-radius:0px; border-right:0px;" value="<?php echo esc_html( $this->plugin['license'] ); ?>"/>

						<button class="button conblockpro-puri-sl-button" data-action=<?php echo esc_html( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?> data-operation="change_license" data-nonce="<?php echo wp_create_nonce( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?>" style="margin-left:-4px; border-top-left-radius:0px; border-bottom-left-radius:0px;"> <span class="dashicons dashicons-update"></span> <?php echo esc_html( $text_change_license ); ?></button>

						<button class="button conblockpro-puri-sl-button"  style="<?php echo esc_attr( $beta_style ); ?>" data-action=<?php echo esc_html( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?> data-operation="change_beta" data-nonce="<?php echo wp_create_nonce( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?>" style="margin-left:-4px; border-top-left-radius:0px; border-bottom-left-radius:0px;"> <span class="dashicons dashicons-hammer"></span> <?php echo esc_html( $text_change_beta ); ?></button>

						<button class="button conblockpro-puri-sl-button" data-action=<?php echo esc_html( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?> data-operation="check_expiry" data-nonce="<?php echo wp_create_nonce( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?>"> <span class="dashicons dashicons-update"></span> <?php echo esc_html( $text_check_expiry ); ?></button>

						<button class="button conblockpro-puri-sl-button" data-action=<?php echo $this->plugin['base'] . '-conblockpro-puri-sl-operations'; ?> data-operation="deactivate_license" data-nonce="<?php echo wp_create_nonce( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?>"> <span class="dashicons dashicons-update"></span> <?php echo esc_html( $text_deactive_license ); ?></button>

						<div class="conblockpro-puri-sl-message"></div>
					</div>
				</td>
			</tr>
			<?php
		}

		/**
		 *  Display admin notice if plugin license key is not yet entered
		 */
		public function display_admin_notice() {

			$format           = __( 'Almost done - Activate license for %s to properly work on your site', 'conditional-blocks' );
			$text_description = sprintf( $format, '<strong>' . $this->plugin['name'] . '</strong>' );
			$text_placeholder = __( 'Enter Your License', 'conditional-blocks' );
			$text_button      = __( 'Activate License', 'conditional-blocks' );

			?>
			<div class="conblockpro-puri-sl-notice notice notice-warning is-dismissible">
				<p><?php echo $text_description; ?>
					<input class="conblockpro-puri-sl-license-key" type="text" placeholder="<?php echo $text_placeholder; ?>"/>
					<button class="button conblockpro-puri-sl-button" data-action=<?php echo $this->plugin['base'] . '-conblockpro-puri-sl-operations'; ?> data-operation="activate_license" data-nonce="<?php echo wp_create_nonce( $this->plugin['base'] . '-conblockpro-puri-sl-operations' ); ?>"> <span class="dashicons dashicons-update"></span> <?php echo $text_button; ?></button>
				</p>
			</div>
			<?php
		}

		/**
		 * Different EDD Operations executed on Ajax call
		 */
		public function edd_operations() {

			$operation = $_POST['operation'];

			if ( empty( $operation ) || wp_verify_nonce( $_POST['nonce'], $this->plugin['base'] . '-conblockpro-puri-sl-operations' ) === false ) {
				$this->send_json( __( 'Something went wrong', 'conditional-blocks' ), 'error' );
			}

			switch ( $operation ) {
				case 'change_beta':
					if ( ! $this->plugin['beta'] ) {
						update_site_option( $this->plugin['slug'] . '_beta', true );
						$this->send_json( __( 'You enabled beta updates. Warning beta updates are for testing purposes only. ', 'conditional-blocks' ), false, false );
					} else {
						update_site_option( $this->plugin['slug'] . '_beta', false );
						$this->send_json( __( 'You have disabled beta updates.', 'conditional-blocks' ), false, false );
					}
					break;
				case 'activate_license':
					$license = ! empty( $_POST['license'] ) ? $_POST['license'] : $this->send_json( __( 'License field can not be empty', 'conditional-blocks' ) );

					$license = sanitize_text_field( $license );

					$license_data = $this->validate_license( $license, $this->plugin['item_id'], $this->plugin['store_url'] );
					if ( $license_data->license === 'valid' ) {
						update_site_option( $this->plugin['slug'] . '_license_status', $license_data->license );
						update_site_option( $this->plugin['license_option_name'], $license );
						update_site_option( $this->plugin['slug'] . '_license_checked', time() );
						// Force Update.
						set_site_transient( 'update_plugins', null );

						$this->send_json( __( 'License successfully activated', 'conditional-blocks' ), false, true );
					}
					break;
				case 'deactivate_license':
					$license_data = $this->invalidate_license( $this->plugin['license'], $this->plugin['item_id'], $this->plugin['store_url'] );
					if ( $license_data->license === 'deactivated' || $license_data->license === 'failed' ) {
						delete_site_option( $this->plugin['license_option_name'] );
						delete_site_option( $this->plugin['slug'] . '_license_status' );
						delete_site_option( $this->plugin['slug'] . '_license_key' );
						$this->send_json( __( 'License deactivated for this site', 'conditional-blocks' ), false, true );
					}
					break;
				case 'change_license':
					$new_license = ! empty( $_POST['license'] ) ? $_POST['license'] : wp_send_json_error( __( 'License field can not be empty', 'conditional-blocks' ) );
					$new_license = sanitize_text_field( $new_license );
					$old_license = $this->plugin['license'];
					if ( $new_license !== $old_license ) {
						$license_data = $this->validate_license( $new_license, $this->plugin['item_id'], $this->plugin['store_url'] );
						if ( $license_data->license === 'valid' ) {
							$license_data = $this->invalidate_license( $old_license, $this->plugin['item_id'], $this->plugin['store_url'] );
							if ( $license_data->license === 'deactivated' || $license_data->license === 'failed' ) {
								update_site_option( $this->plugin['slug'] . '_license_key', $new_license );
								$this->send_json( __( 'License Successfully Changed.', 'conditional-blocks' ) );
							}
						}
					} else {
						$this->send_json( __( 'You entered the same license, use another one.', 'conditional-blocks' ), 'error' );
					}
					break;
				case 'check_expiry':
					// Force Update.
					set_site_transient( 'update_plugins', null );
					$this->check_expiry( $this->plugin['license'], $this->plugin['item_id'], $this->plugin['store_url'] );
					break;
				default:
					$this->send_json( __( 'Something went wrong', 'conditional-blocks' ), 'error' );
			}

		}

		/**
		 *  Validate License
		 */
		public function validate_license( $license, $plugin_id, $store_url ) {

			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_id'    => $plugin_id,
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				$store_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'conditional-blocks' );
				}

				$this->send_json( $message, 'error' );

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch ( $license_data->error ) {

						case 'expired':
							$message = sprintf(
								__( 'Your license key expired on %s.', 'conditional-blocks' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'disabled':
						case 'revoked':
							$message = __( 'Your license key has been disabled.', 'conditional-blocks' );
							break;

						case 'missing':
							$message = __( 'Invalid license.', 'conditional-blocks' );
							break;

						case 'invalid':
						case 'site_inactive':
							$message = __( 'Your license is not active for this URL.', 'conditional-blocks' );
							break;

						case 'item_name_mismatch':
							$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'conditional-blocks' ), $this->plugin['name'] );
							break;

						case 'no_activations_left':
							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default:
							$message = __( 'An error occurred, please try again.' );
							break;
					}

					 $this->send_json( $message, 'error' );
				} else {
					return $license_data;
				}
			}
		}

		/**
		 * Invalidate License for current website. This will decrease the site count.
		 */
		public function invalidate_license( $license, $plugin_id, $store_url ) {
			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_id'    => $plugin_id,
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				$store_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'conditional-blocks' );
				}
				return $this->send_json( $message, 'error' );
			}

			// decode the license data.
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			return $license_data;
		}

		/**
		 * Check License Expiry.
		 */
		public function check_expiry( $license, $plugin_id, $store_url ) {

			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_id'    => $plugin_id,
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				$store_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// Make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.', 'conditional-blocks' );
				}
				return $this->send_json( $message, 'error' );
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $license_data->license == 'valid' ) {
				$this->send_json( __( 'License expiry: ', 'conditional-blocks' ) . $license_data->expires );
			} elseif ( $license_data->license == 'expired' ) {
				$this->send_json( __( 'License expired on: ', 'conditional-blocks' ) . $license_data->expires );
			} elseif ( $license_data->license == 'disabled' ) {
				$this->send_json( __( 'license has been disabled.', 'conditional-blocks' ) );
			} elseif ( $license_data->license == 'invalid' ) {
				$this->send_json( __( 'Invalid license key', 'conditional-blocks' ) );
			} else {
				$this->send_json( $license_data->license, 'error' );
			}
		}

		public function send_json( $message, $type = false, $reload = false ) {

			$help_message = ' <a href="' . $this->plugin['store_url'] . '" target="_blank">' . __( 'Need help?', 'conditional-blocks' ) . ' </a>';

			$message = $message . $help_message;

			$data            = array();
			$data['message'] = $message;
			$data['reload']  = $reload;

			if ( $type === 'error' ) {
				wp_send_json_error( $data );
			} else {
				wp_send_json_success( $data );
			}
		}

		/**
		 * maybe_check_current_status.
		 *
		 * @return void
		 */
		public function maybe_check_current_status() {

			if ( empty( $this->plugin['license'] ) ) {
				return;
			}

			$checked = (int) get_site_option( $this->plugin['slug'] . '_license_checked', 0 );
			// Check only once every 24 hours.
			if ( time() < ( $checked + 86400 ) ) {
				return;
			}

			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $this->plugin['license'],
				'item_id'    => $this->plugin['item_id'],
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				$this->plugin['store_url'],
				array(
					'timeout'   => 5,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// Make sure the response came back okay.
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				update_site_option( $this->plugin['slug'] . '_license_status', ! empty( $license_data->license ) ? $license_data->license : false );
			}

			update_site_option( $this->plugin['slug'] . '_license_checked', time() );
		}
	}
}
