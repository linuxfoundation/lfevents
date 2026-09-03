<?php
/**
 * Admin screen for the Sessionize data cache.
 *
 * Because block content is now served from a server-side cache rather than
 * fetched live in the browser, "the schedule looks out of date" becomes an
 * invisible, server-side problem. This screen surfaces when each event last
 * synced, how big its payload is and what the last error was, and offers a
 * manual refresh so an editor does not have to wait for the next cron run.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Sessionize cache admin screen.
 */
class Sessionize_Admin {

	/**
	 * Menu slug for the admin screen.
	 *
	 * @var string
	 */
	const SLUG = 'sessionize-blocks';

	/**
	 * Registers admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		add_action( 'admin_post_sessionize_refresh', array( __CLASS__, 'handle_refresh' ) );
	}

	/**
	 * Adds the Tools submenu page.
	 *
	 * @return void
	 */
	public static function register_page() {
		add_management_page(
			__( 'Sessionize Data', 'sessionize-blocks' ),
			__( 'Sessionize Data', 'sessionize-blocks' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Handles a manual refresh request.
	 *
	 * @return void
	 */
	public static function handle_refresh() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to refresh Sessionize data.', 'sessionize-blocks' ) );
		}

		$api_code = isset( $_POST['api_code'] ) ? sanitize_text_field( wp_unslash( $_POST['api_code'] ) ) : '';

		check_admin_referer( 'sessionize_refresh_' . $api_code );

		$result = Sessionize_Store::refresh( $api_code );

		if ( ! is_wp_error( $result ) ) {
			Sessionize_Cron::purge_edge_cache( $api_code );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'               => self::SLUG,
					'sessionize_message' => is_wp_error( $result ) ? 'error' : 'success',
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	/**
	 * Renders the admin screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$codes = Sessionize_Registry::codes();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice flag set by our own redirect.
		$message = isset( $_GET['sessionize_message'] ) ? sanitize_key( wp_unslash( $_GET['sessionize_message'] ) ) : '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sessionize Data', 'sessionize-blocks' ); ?></h1>

			<?php if ( 'success' === $message ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Sessionize data refreshed.', 'sessionize-blocks' ); ?></p>
				</div>
			<?php elseif ( 'error' === $message ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Refresh failed. The previously cached data is still being served — see the last error below.', 'sessionize-blocks' ); ?></p>
				</div>
			<?php endif; ?>

			<p class="description">
				<?php esc_html_e( 'Schedule and speaker data is fetched from Sessionize on the server and cached here, then rendered into pages as HTML. Events are discovered automatically from published posts containing a Sessionize block.', 'sessionize-blocks' ); ?>
			</p>

			<?php if ( empty( $codes ) ) : ?>
				<p><?php esc_html_e( 'No Sessionize blocks have been found yet. Publish a post or page containing one and it will appear here.', 'sessionize-blocks' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'API code', 'sessionize-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Last synced', 'sessionize-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Sessions', 'sessionize-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Speakers', 'sessionize-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Cached size', 'sessionize-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Last error', 'sessionize-blocks' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'sessionize-blocks' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $codes as $code ) : ?>
							<?php
							$meta         = Sessionize_Store::meta( $code );
							$last_success = isset( $meta['last_success'] ) ? (int) $meta['last_success'] : 0;
							$last_error   = isset( $meta['last_error'] ) ? (string) $meta['last_error'] : '';
							?>
							<tr>
								<td><code><?php echo esc_html( $code ); ?></code></td>
								<td>
									<?php
									if ( $last_success > 0 ) {
										printf(
											/* translators: %s: human readable time difference, e.g. "5 mins". */
											esc_html__( '%s ago', 'sessionize-blocks' ),
											esc_html( human_time_diff( $last_success ) )
										);
									} else {
										esc_html_e( 'Never', 'sessionize-blocks' );
									}
									?>
								</td>
								<td><?php echo esc_html( isset( $meta['sessions'] ) ? number_format_i18n( (int) $meta['sessions'] ) : '—' ); ?></td>
								<td><?php echo esc_html( isset( $meta['speakers'] ) ? number_format_i18n( (int) $meta['speakers'] ) : '—' ); ?></td>
								<td><?php echo esc_html( isset( $meta['bytes'] ) ? size_format( (int) $meta['bytes'] ) : '—' ); ?></td>
								<td>
									<?php if ( '' !== $last_error ) : ?>
										<span class="description"><?php echo esc_html( $last_error ); ?></span>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="sessionize_refresh">
										<input type="hidden" name="api_code" value="<?php echo esc_attr( $code ); ?>">
										<?php wp_nonce_field( 'sessionize_refresh_' . $code ); ?>
										<button type="submit" class="button"><?php esc_html_e( 'Refresh now', 'sessionize-blocks' ); ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}
}
