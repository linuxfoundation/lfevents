<?php
/**
 * Staff Listing Shortcode
 *
 * @package WordPress
 * @subpackage lf-theme
 * @since 1.0.0
 */

/**
 * Add shortcode.
 *
 * @param array $atts Attributes.
 */
function add_staff_shortcode( $atts ) {

	$query = new WP_Query(
		array(
			'post_type'      => 'lfe_staff',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => 200,
			'no_found_rows'  => true,
			'meta_key'       => 'lfes_staff_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => array(
				'meta_value_num' => 'DESC',
				'title'          => 'ASC',
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return;
	}

	ob_start();
	?>
	<div class="team-wrapper">

	<?php
	while ( $query->have_posts() ) {
		$query->the_post();

		$id                = get_the_ID();
		$title             = get_post_meta( $id, 'lfes_staff_title', true );
		$fallback_image_id = get_option( 'lfe-generic-staff-image-id' ) ?? '';
		?>

		<div class="team-item">

		<?php
		if ( has_post_thumbnail() ) {
			echo wp_get_attachment_image(
				get_post_thumbnail_id(),
				'profile-200',
				false,
				array(
					'class'   => 'team-photo',
					'loading' => 'lazy',
				)
			);
		} elseif ( $fallback_image_id ) {
			echo wp_get_attachment_image(
				$fallback_image_id,
				'profile-200',
				false,
				array(
					'class'   => 'team-photo',
					'loading' => 'lazy',
				)
			);
		} else {
			?>
<svg width="200" height="200" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"
xmlns:xlink="http://www.w3.org/1999/xlink">
<defs>
<linearGradient id="customGradient" gradientTransform="rotate(100)">
<stop offset="5%"  stop-color="#0099cc" />
<stop offset="95%" stop-color="#003366" />
</linearGradient>
</defs>
<circle cx="5" cy="5" r="5" fill="url('#customGradient')" />
</svg>
			<?php
		}
		?>
		<h5 class="team-title"><?php echo esc_html( get_the_title() ); ?></h5>
		<p class="team-description"><?php echo esc_html( $title ); ?></p>
		</div>

		<?php
	}
	?>
</div>
				<?php
				$block_content = ob_get_clean();
				return $block_content;
}
add_shortcode( 'staff', 'add_staff_shortcode' );
