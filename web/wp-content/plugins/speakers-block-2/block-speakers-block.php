<?php
/**
 * Plugin Name:       Speakers Block 2
 * Description:       Gutenberg block which allows for insertion of a Speakers showcase in a page/post. It requires an existing Speakers CPT already setup <a href="https://github.com/linuxfoundation/lfevents/blob/main/web/wp-content/mu-plugins/custom/lfevents/admin/class-lfevents-admin.php#L164">like this</a>.
 * Plugin URI: https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/speakers-block
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.2.0
 * Author:            cjyabraham, <a href="https://www.thetwopercent.co.uk">James Hunt</a>
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-speakers-block-2
 *
 * @package           cgb
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 */
function cgb_block_speakers_block_2_block_init() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'speakers_block_2_callback',
		)
	);
}
add_action( 'init', 'cgb_block_speakers_block_2_block_init' );

/**
 * Callback for speakers block.
 *
 * @param array $attributes Atts.
 */
function speakers_block_2_callback( $attributes ) {
	if ( empty( $attributes['speakers'] ) ) {
		return;
	}

	$speakers_ids = array_map(
		function ( $speaker ) {
			return intval( $speaker['value'] );
		},
		$attributes['speakers']
	);

	$persons_query = new WP_Query(
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

	if ( $persons_query->have_posts() ) {

		$align = 'align';
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
		<div class="people-wrapper <?php echo esc_html( $align ); ?>">
			<?php
			while ( $persons_query->have_posts() ) :
				$persons_query->the_post();
				include( 'people-item.php' );
			endwhile;
			wp_reset_postdata();
		}
		?>
		</div>
	<?php
	$block_content = ob_get_clean();
	return $block_content;
}
