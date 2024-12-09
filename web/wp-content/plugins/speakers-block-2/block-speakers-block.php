<?php
/**
 * Plugin Name:       Speakers Block 2
 * Description:       Inserts a Speakers showcase in a page/post which opens each individual speakers details in a modal.
 * Plugin URI: https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.3.0
 * Author:            cjyabraham, <a href="https://www.thetwopercent.co.uk">James Hunt</a>
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       speakers-block-2
 *
 * @package           WordPress
 */

/**
 * Register the block
 */
function lf_speakers_block_2_block_init() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'lf_speakers_block_2_callback',
		)
	);
}
add_action( 'init', 'lf_speakers_block_2_block_init' );

/**
 * Callback for speakers block.
 *
 * @param array $attributes Atts.
 */
function lf_speakers_block_2_callback( $attributes ) {
	if ( empty( $attributes['speakers'] ) ) {
		return;
	}

	$speakers_ids = array_map(
		function ( $speaker ) {
			return intval( $speaker['value'] );
		},
		$attributes['speakers']
	);

	global $post;
	$sched_event_id = null;
	if ( function_exists( 'lfe_get_event_parent_id' ) ) {
		$parent_id      = lfe_get_event_parent_id( $post );
		$sched_event_id = get_post_meta( $parent_id, 'lfes_sched_event_id', true );
	}

	$speakers_query = new WP_Query(
		array(
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'post_type'              => 'lfe_speaker',
			'post_status'            => 'publish',
			'posts_per_page'         => 500,
			'post__in'               => $speakers_ids,
			'orderby'                => 'post__in',
		)
	);

	if ( $speakers_query->have_posts() ) {

		$align  = 'align';
		$align .= $attributes['align'] ?? 'wide';

		wp_enqueue_script(
			'modal',
			get_template_directory_uri() . '/dist/js/modal.js',
			array( 'jquery' ),
			filemtime( get_template_directory() . '/dist/js/modal.js' ),
			true
		);

		ob_start();
		?>
		<div class="sb2-block-wrapper <?php echo esc_html( $align ); ?>">
			<?php
			while ( $speakers_query->have_posts() ) :
				$speakers_query->the_post();
				include 'includes/speaker-block.php';
			endwhile;
			wp_reset_postdata();
	}
	?>
		</div>
	<?php
	$block_content = ob_get_clean();
	return $block_content;
}
