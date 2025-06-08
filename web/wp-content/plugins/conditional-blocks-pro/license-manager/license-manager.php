<?php
/**
 * CONBLOCKPRO License Manager Class
 * 
 * @version 1.0.2
 * 
 * @package CONBLOCKPRO_License_Manager
 */

if ( ! class_exists( 'CONBLOCKPRO_License_Manager' ) ) {

	class CONBLOCKPRO_License_Manager {
		private $store_url;
		private $item_id;
		private $item_name;
		private $plugin_file;
		private $version;
		private $author;
		private $option_prefix;
		private $text_domain;
		private $docs_url;
		private $account_url;
		private $upgrade_url;

		/**
		 * Constructor
		 * 
		 * @param array $config Configuration array with the following keys:
		 * store_url: URL of the EDD store
		 * item_id: Product ID in EDD
		 * item_name: Product name in EDD
		 * plugin_file: Main plugin file path
		 * version: Plugin version
		 * author: Plugin author
		 * option_prefix: Prefix for WordPress options (optional)
		 * text_domain: Text domain for translations (optional)
		 * docs_url: Documentation URL (optional)
		 * account_url: Account management URL (optional)
		 * upgrade_url: License renewal URL (optional)
		 */
		public function __construct( $config ) {
			$this->store_url = $config['store_url'];
			$this->item_id = $config['item_id'];
			$this->item_name = $config['item_name'];
			$this->plugin_file = $config['plugin_file'];
			$this->version = $config['version'];
			$this->author = $config['author'];
			$this->option_prefix = $config['option_prefix'] ?? sanitize_title( $this->item_name );
			$this->docs_url = $config['docs_url'] ?? '';
			$this->account_url = $config['account_url'] ?? '';
			$this->upgrade_url = $config['upgrade_url'] ?? '';

			$this->init();
		}

		/**
		 * Initialize the license manager
		 */
		private function init() {
			// Check if this is an AJAX request
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$this->init_ajax_handlers();
				return;
			}

			// Load all non-AJAX functionality
			$this->init_regular_hooks();
		}


		/**
		 * Initialize AJAX handlers only
		 */
		private function init_ajax_handlers() {
			// Only add AJAX handlers
			add_action( 'wp_ajax_' . $this->option_prefix . '_activate_license', [ $this, 'ajax_activate_license' ] );
			add_action( 'wp_ajax_' . $this->option_prefix . '_deactivate_license', [ $this, 'ajax_deactivate_license' ] );
			add_action( 'wp_ajax_' . $this->option_prefix . '_check_license_status', [ $this, 'ajax_check_license_status' ] );
		}

		/**
		 * Initialize regular WordPress hooks
		 */
		private function init_regular_hooks() {
			// Only run hooks on single sites or the network admin in a multisite network
			if ( ! is_multisite() || is_network_admin() ) {
				// Load updater
				add_action( 'init', [ $this, 'load_updater' ] );

				if ( is_admin() || is_network_admin() ) {
					// UI elements
					add_action( 'admin_notices', [ $this, 'admin_notices' ] );
					add_action( 'admin_footer', [ $this, 'render_license_modal' ] );
					add_action( 'network_admin_footer', [ $this, 'render_license_modal' ] );
					add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

					// Plugin row modifications
					add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_file ), [ $this, 'add_license_action_link' ] );
					add_filter( 'network_admin_plugin_action_links_' . plugin_basename( $this->plugin_file ), [ $this, 'add_license_action_link' ] );
					add_action( 'after_plugin_row_' . plugin_basename( $this->plugin_file ), [ $this, 'show_license_notice_row' ], 10, 3 );
				}

				$this->setup_license_check_schedule();
			}
		}


		/**
		 * Setup the license check schedule
		 */
		private function setup_license_check_schedule() {
			// Add cron schedule for license check
			add_action( $this->option_prefix . '_check_license', [ $this, 'scheduled_license_check' ] );

			// Schedule the event if not already scheduled
			if ( ! wp_next_scheduled( $this->option_prefix . '_check_license' ) ) {
				wp_schedule_event( time(), 'daily', $this->option_prefix . '_check_license' );
			}

			// Clean up schedule on plugin deactivation
			register_deactivation_hook( $this->plugin_file, [ $this, 'deactivate_license_schedule' ] );
		}

		/**
		 * Load the updater class
		 */
		public function load_updater() {

			if (
				! is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX ||
				( defined( 'DOING_CRON' ) || ! current_user_can( 'manage_options' ) )
			) {
				return;
			}

			$license_key = trim( $this->get_license_key() );

			if ( ! class_exists( 'CONBLOCKPRO_EDD_SL_Plugin_Updater' ) ) {
				require_once dirname( $this->plugin_file ) . '/license-manager/license-updater.php';
			}

			new CONBLOCKPRO_EDD_SL_Plugin_Updater(
				$this->store_url,
				$this->plugin_file,
				array(
					'version' => $this->version,
					'license' => $license_key,
					'item_id' => $this->item_id,
					'author' => $this->author,
					'beta' => false,
				)
			);
		}

		/**
		 * Get license key option name
		 */
		private function get_license_key_option() {
			return $this->option_prefix . '_license_key';
		}

		/**
		 * Get license status option name
		 */
		private function get_license_status_option() {
			return $this->option_prefix . '_license_status';
		}

		/**
		 * Get license key
		 */
		public function get_license_key() {
			return get_site_option( $this->get_license_key_option() );
		}

		/**
		 * Get license status
		 */
		public function get_license_status() {
			return get_site_option( $this->get_license_status_option() );
		}
		/**
		 * Make API request
		 */
		private function api_request( $action, $license ) {
			// Prevent hammering the API
			$cache_key = 'api_request_' . md5( $action . $license );
			if ( get_transient( $cache_key ) ) {
				return new WP_Error( 'too_many_requests', __( 'Please wait before trying again.', 'conditional-blocks' ) );
			}

			$api_params = array(
				'edd_action' => $action,
				'license' => $license,
				'item_id' => $this->item_id,
				'item_name' => rawurlencode( $this->item_name ),
				'url' => home_url(),
				'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
			);

			// Set a short-lived transient to prevent multiple requests
			set_transient( $cache_key, true, 5 );

			return wp_remote_post( $this->store_url, array(
				'timeout' => 15,
				'sslverify' => false,
				'body' => $api_params
			) );
		}

		/**
		 * Admin notices
		 */
		public function admin_notices() {
			if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {
				switch ( $_GET['sl_activation'] ) {
					case 'false':
						$message = urldecode( $_GET['message'] );
						?>
						<div class="error">
							<p><?php echo wp_kses_post( $message ); ?></p>
						</div>
						<?php
						break;
				}
			}
		}

		/**
		 * Get error message for license response
		 * 
		 * @param string $error_code The error code from the license response
		 * @param string $expires Optional expiration date for expired licenses
		 * @return string The error message
		 */
		private function get_error_message( $error_code, $expires = '' ) {
			switch ( $error_code ) {
				case 'expired':
					return sprintf(
						__( 'Your license key expired on %s.', 'conditional-blocks' ),
						date_i18n(
							get_option( 'date_format' ),
							strtotime( $expires, current_time( 'timestamp' ) )
						)
					);
				case 'disabled':
				case 'revoked':
					return __( 'Your license key has been disabled.', 'conditional-blocks' );
				case 'missing':
					return __( 'Invalid license.', 'conditional-blocks' );
				case 'invalid':
				case 'site_inactive':
					return __( 'Your license is not active for this URL.', 'conditional-blocks' );
				case 'item_name_mismatch':
					return sprintf(
						__( 'This appears to be an invalid license key for %s.', 'conditional-blocks' ),
						$this->item_name
					);
				case 'no_activations_left':
					return __( 'Your license key has reached its activation limit.', 'conditional-blocks' );
				default:
					return __( 'An error occurred, please try again.', 'conditional-blocks' );
			}
		}

		/**
		 * Check if license is valid
		 * 
		 * @return boolean
		 */
		public function is_license_valid() {
			$status = $this->get_license_status();
			return $status === 'valid';
		}

		/**
		 * Get license status class
		 * 
		 * @return string CSS class for the license status
		 */
		private function get_license_status_class() {
			$status = $this->get_license_status();
			switch ( $status ) {
				case 'valid':
					return 'valid';
				case 'expired':
					return 'expired';
				case 'disabled':
				case 'revoked':
					return 'revoked';
				case 'site_inactive':
				case 'inactive':
					return 'inactive';
				default:
					return 'invalid';
			}
		}

		/**
		 * Get human readable license status
		 * 
		 * @return string Human readable status
		 */
		private function get_license_status_text() {
			$status = $this->get_license_status();
			switch ( $status ) {
				case 'valid':
					return __( 'License Active', 'conditional-blocks' );
				case 'expired':
					return __( 'License Expired', 'conditional-blocks' );
				case 'disabled':
				case 'revoked':
					return __( 'License Revoked', 'conditional-blocks' );
				case 'site_inactive':
				case 'inactive':
					return __( 'License Inactive', 'conditional-blocks' );
				default:
					return __( 'No License', 'conditional-blocks' );
			}
		}

		/**
		 * Check license status with API
		 * 
		 * @return object|false License data or false on failure
		 */
		public function check_license() {
			$license = $this->get_license_key();
			if ( ! $license ) {
				return false;
			}

			$response = $this->api_request( 'check_license', $license );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			return $license_data;
		}

		/**
		 * Add license action link to plugin row
		 */
		public function add_license_action_link( $links ) {
			$license_link = sprintf(
				'<a href="#" class="manage-license-trigger" data-prefix="%s">%s</a>',
				esc_attr( $this->option_prefix ),
				esc_html__( 'Manage License', 'conditional-blocks' )
			);
			array_unshift( $links, $license_link );
			return $links;
		}

		/**
		 * Enqueue required scripts
		 */
		public function enqueue_scripts( $hook ) {
			// Only load on plugins.php or plugins.php-network page and when not doing AJAX
			$allowed_hooks = [ 'plugins.php', 'plugins.php-network' ];
			if ( ! in_array( $hook, $allowed_hooks, true ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return;
			}

			// Only enqueue if we're actually going to use them
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Check if style is already enqueued
			if ( ! wp_style_is( $this->option_prefix . '-modal', 'enqueued' ) ) {
				wp_enqueue_style(
					$this->option_prefix . '-modal',
					plugin_dir_url( $this->plugin_file ) . 'license-manager/assets/license-modal.css',
					[],
					$this->version
				);
			}

			// Check if script is already enqueued by another instance
			if ( ! wp_script_is( 'conblockpro_license_manager', 'enqueued' ) ) {
				wp_enqueue_script(
					'conblockpro_license_manager',
					plugin_dir_url( $this->plugin_file ) . 'license-manager/assets/license-modal.js',
					[],
					time(),
					true
				);
			}

			// Always localize the script for this instance
			wp_localize_script(
				'conblockpro_license_manager',
				"conblockpro_license_manager",
				[ 
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'prefix' => $this->option_prefix,
					'pluginSlug' => plugin_basename( $this->plugin_file ),
					'nonce' => wp_create_nonce( $this->option_prefix . '_nonce' ),
					'i18n' => [ 
						'activating' => __( 'Activating...', 'edd-license-manager' ),
						'deactivating' => __( 'Deactivating...', 'edd-license-manager' ),
						'error' => __( 'An error occurred, please try again.', 'edd-license-manager' )
					]
				]
			);
		}

		/**
		 * Render license modal HTML
		 */
		public function render_license_modal() {
			$screen = get_current_screen();
			if ( 'plugins' !== $screen->id && 'plugins-network' !== $screen->id ) {
				return;
			}
			?>
			<div id="<?php echo esc_attr( $this->option_prefix ); ?>-license-modal" class="license-modal" style="display:none;">
				<div class="license-modal-content">
					<span class="license-modal-close">&times;</span>
					<span>
						<h2><?php echo esc_html( sprintf( __( '%s License', 'conditional-blocks' ), $this->item_name ) ); ?></h2>
						<div class="license-status">
							<span class="status <?php echo esc_attr( $this->get_license_status_class() ); ?>">
								<?php echo esc_html( $this->get_license_status_text() ); ?>
							</span>
						</div>
					</span>
					<div class="license-modal-body">
						<div class="license-input">
							<label for="<?php echo esc_attr( $this->option_prefix ); ?>-license-key">
								<?php esc_html_e( 'License Key:', 'conditional-blocks' ); ?>
							</label>
							<input type="text" id="<?php echo esc_attr( $this->option_prefix ); ?>-license-key"
								value="<?php echo esc_attr( $this->get_license_key() ); ?>" <?php echo $this->is_license_valid() ? 'disabled' : ''; ?>>
						</div>
						<div class="license-message"></div>
						<div class="license-expiry"></div>
						<div class="license-actions">
							<?php if ( $this->is_license_valid() ) : ?>
								<button type="button" class="button button-secondary check-license">
									<?php esc_html_e( 'Check Status', 'conditional-blocks' ); ?>
								</button>
								<button type="button" class="button button-secondary deactivate-license">
									<?php esc_html_e( 'Deactivate License', 'conditional-blocks' ); ?>
								</button>
							<?php else : ?>
								<button type="button" class="button button-primary activate-license">
									<?php esc_html_e( 'Activate License', 'conditional-blocks' ); ?>
								</button>
							<?php endif; ?>
						</div>

						<?php if ( $this->docs_url || $this->account_url || $this->upgrade_url ) : ?>
							<div class="license-links">
								<?php if ( $this->docs_url ) : ?>
									<a href="<?php echo esc_url( $this->docs_url ); ?>" target="_blank" class="">
										<?php esc_html_e( 'Documentation', 'conditional-blocks' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $this->account_url ) : ?>
									<a href="<?php echo esc_url( $this->account_url ); ?>" target="_blank" class="">
										<?php esc_html_e( 'Manage Account', 'conditional-blocks' ); ?>
									</a>
								<?php endif; ?>

								<?php if ( $this->upgrade_url ) : ?>
									<a href="<?php echo esc_url( $this->upgrade_url ); ?>" target="_blank" class="">
										<?php esc_html_e( 'Renew or Upgrade License', 'conditional-blocks' ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * AJAX handler for license activation
		 */
		public function ajax_activate_license() {
			check_ajax_referer( $this->option_prefix . '_nonce', 'nonce' );

			$license_key = sanitize_text_field( $_POST['license_key'] );

			// Perform activation
			$response = $this->api_request( 'activate_license', $license_key );
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( is_wp_error( $response ) || ! $license_data->success ) {
				wp_send_json_error( [ 
					'message' => $this->get_error_message(
						$license_data->error ?? 'invalid',
						$license_data->expires ?? ''
					)
				] );
			}

			update_site_option( $this->get_license_key_option(), $license_key );
			update_site_option( $this->get_license_status_option(), $license_data->license );

			wp_send_json_success( [ 
				'message' => __( 'License activated successfully', 'conditional-blocks' )
			] );
		}

		/**
		 * AJAX handler for license deactivation
		 */
		public function ajax_deactivate_license() {
			check_ajax_referer( $this->option_prefix . '_nonce', 'nonce' );

			$license = $this->get_license_key();
			$response = $this->api_request( 'deactivate_license', $license );
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( is_wp_error( $response ) || ! $license_data->success ) {
				wp_send_json_error( [ 
					'message' => __( 'Failed to deactivate license', 'conditional-blocks' )
				] );
			}

			delete_site_option( $this->get_license_status_option() );

			wp_send_json_success( [ 
				'message' => __( 'License deactivated successfully', 'conditional-blocks' )
			] );
		}

		/**
		 * Show license notice in plugin row
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory
		 * @param array  $plugin_data An array of plugin data
		 * @param string $status      Status filter currently applied to the plugin list
		 */
		public function show_license_notice_row( $plugin_file, $plugin_data, $status ) {
			if ( $this->is_license_valid() ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			$colspan = $wp_list_table->get_column_count();
			?>
			<tr class="plugin-update-tr active" data-plugin-slug="<?php echo esc_attr( plugin_basename( $this->plugin_file ) ); ?>">
				<td colspan="<?php echo esc_attr( $colspan ); ?>" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-warning notice-alt">
						<p>
							<?php
							printf(
								/* translators: %s: Plugin name */
								esc_html__( 'License key required for features & updates. %s', 'conditional-blocks' ),
								sprintf(
									'<button type="button" class="button-link manage-license-trigger" data-prefix="%s">%s</button>',
									esc_attr( $this->option_prefix ),
									esc_html__( 'Activate License', 'conditional-blocks' )
								)
							);
							?>
						</p>
					</div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Scheduled license check
		 */
		public function scheduled_license_check() {
			if ( ! $this->is_license_valid() ) {
				return;
			}

			$license_data = $this->check_license();

			if ( $license_data && isset( $license_data->license ) ) {
				update_site_option( $this->get_license_status_option(), $license_data->license, 'no' );
			}
		}

		/**
		 * Clean up scheduled events on plugin deactivation
		 */
		public function deactivate_license_schedule() {
			wp_clear_scheduled_hook( $this->option_prefix . '_check_license' );
		}

		/**
		 * AJAX handler for checking license status
		 */
		public function ajax_check_license_status() {
			check_ajax_referer( $this->option_prefix . '_nonce', 'nonce' );

			$license_data = $this->check_license();

			if ( ! $license_data ) {
				wp_send_json_error( [ 
					'message' => __( 'Failed to check license status', 'conditional-blocks' )
				] );
			}

			$expires = isset( $license_data->expires ) ? $license_data->expires : false;

			// Check for lifetime license before formatting date
			if ( $expires && ( $expires === 'lifetime' || $expires === '0000-00-00 00:00:00' ) ) {
				$expiry_date = __( 'Never', 'conditional-blocks' );
			} elseif ( $expires ) {
				$expiry_date = date_i18n( get_option( 'date_format' ), strtotime( $expires ) );
			} else {
				$expiry_date = __( 'Never', 'conditional-blocks' ); // Should ideally not happen if license is valid
			}

			wp_send_json_success( [ 
				'status' => $license_data->license,
				'expires' => $expiry_date,
				'message' => sprintf(
					__( 'License: %s (Expires: %s)', 'conditional-blocks' ),
					ucfirst( $license_data->license ),
					$expiry_date
				)
			] );
		}
	}
}