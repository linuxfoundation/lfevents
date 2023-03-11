<?php
/**
 * Plugin Name:       Speakers Block
 * Description:       Gutenberg block which allows for insertion of a Speakers showcase in a page/post. It requires an existing Speakers CPT already setup <a href="https://github.com/LF-Engineering/lfevents/blob/main/web/wp-content/mu-plugins/custom/lfevents/admin/class-lfevents-admin.php#L164">like this</a>.
 * Plugin URI: https://github.com/LF-Engineering/lfevents/tree/master/web/wp-content/plugins/speakers-block
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.2.0
 * Author:            cjyabraham, <a href="https://www.thetwopercent.co.uk">James Hunt</a>
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-speakers-block
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
function cgb_block_speakers_block_block_init() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'speakers_block_callback',
		)
	);
}
add_action( 'init', 'cgb_block_speakers_block_block_init' );

/**
 * Callback for speakers block.
 *
 * @param array $attributes Atts.
 */
function speakers_block_callback( $attributes ) {
	if ( empty( $attributes['speakers'] ) ) {
		return;
	}

	$speakers_ids = array_map(
		function ( $speaker ) {
			return intval( $speaker['value'] );
		},
		$attributes['speakers']
	);

	$query = new WP_Query(
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

	if ( ! $query->have_posts() ) {
		return;
	}

	$align      = 'align';
	$align     .= $attributes['align'] ?? 'wide';
	$classes    = $attributes['className'] ?? '';
	$color_mode    = $attributes['colorMode'] ?? '';

	$bg_color_1 = $attributes['color1'] ?? '';
	$bg_color_2 = $attributes['color2'] ?? '';
	$text_value = $attributes['textColor'] ?? '#FFFFFF';

	// Check for textColor value - "white" was used in previous version.
	if ( '#FFFFFF' == $text_value || 'white' == $text_value ) {
		$text_color     = 'has-white-color has-text-color';
		$gradient_color = 'rgba(255,255,255,0.15)';
	} else {
		$text_color     = 'has-black-color has-text-color';
		$gradient_color = 'rgba(33,35,38,0.15)';
	}

	// If no color mode is set, it might be an old block, so should have at least custom color set.
	// Or custom-colors is set.
	if ( ( ! $color_mode && ( $bg_color_1 || $bg_color_2 ) )
	|| ( 'is-style-custom-colors' == $color_mode )
	) {

		// color 1.
		$bg_color_1 ? $bg_color_1 : 'transparent';

		// color 2, or color1, or if not transparent.
		$bg_color_2 ? $bg_color_2 : ($bg_color_1 ? $bg_color_1 : 'transparent');

		$inline_styles = 'style="background: linear-gradient(90deg, ' . $bg_color_1 . ' 0%, ' . $bg_color_2 . ' 100%);"';

	} elseif ( 'is-style-event-gradient' == $color_mode ) {

		// No inline styles, handle with class.
		$inline_styles = '';

	} else {
		// generic grey styles if really nothing is set.
		$inline_styles = 'style="background: linear-gradient(90deg, #f3f4f5 0%, #D5D9D3 100%);"';
	}

	// get a random int to preface speaker ids throughout the block.
	// this almost eliminates chances of a namespace conflict between 2 speaker blocks on the same page.
	$id_preface = rand() . 'speaker-';

	ob_start();
	?>

	<section class="speakers-section <?php echo esc_html( $align ); ?> <?php echo esc_html( $classes ); ?> <?php echo esc_html( $color_mode ); ?> <?php echo esc_html( $text_color ); ?>" <?php echo $inline_styles; ?>>

		<ul class="speaker-list grid-x">

			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$id       = get_the_ID();
				$linkedin = get_post_meta( $id, 'lfes_speaker_linkedin', true );
				$twitter  = get_post_meta( $id, 'lfes_speaker_twitter', true );
				$website  = get_post_meta( $id, 'lfes_speaker_website', true );
				?>
				<li id="<?php
				echo esc_html( $id_preface . $id );
				?>"
				class="speaker cell small-6 medium-4 xxlarge-3"
				data-toggler=".open"
				style="background: linear-gradient(-45deg, transparent 30%, <?php echo esc_html( $gradient_color ); ?> 100%);">
					<div class="grid-x">
						<div class="cell large-5">
							<?php
							if ( get_the_content() ) {
								?>
								<div class="headshot" role="button" data-toggle="<?php echo esc_html( $id_preface . $id );	?>">
								<?php
							} else {
								?>
									<div class="headshot">
									<?php
							}
								echo get_the_post_thumbnail( $id, 'profile-200', array(
									'loading' => 'lazy',
									'alt'     => esc_html( get_the_title() ),
									) );
							?>
									</div><!-- end of headshot? -->
								</div><!-- end of cell large-5 -->
								<div class="text cell large-7">
									<?php
									if ( get_the_content() ) {
										?>
										<a class="name" role="button" data-toggle="<?php echo esc_html(  $id_preface . $id ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
										<a class="title" role="button" data-toggle="<?php echo esc_html( $id_preface . $id ); ?>"><?php echo get_post_meta( $id, 'lfes_speaker_title', true ); ?></a>
										<?php
									} else {
										?>
										<span class="name"><?php echo esc_html( get_the_title() ); ?></span>
										<span class="title"><?php echo get_post_meta( $id, 'lfes_speaker_title', true ); ?></span>
										<?php
									}
									?>
									<ul class="social-media-links">
										<?php
										if ( $twitter ) {
											?>
											<li><a title="<?php echo esc_html( get_the_title() ); ?> on Twitter" href="<?php echo esc_url( $twitter ); ?>"><svg class="social-icon--twitter" style="fill:<?php echo esc_html( $text_value ); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
														<path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z" />
													</svg></a></li>
											<?php
										}
										if ( $linkedin ) {
											?>
											<li><a title="<?php echo esc_html( get_the_title() ); ?> on LinkedIn" href="<?php echo esc_url( $linkedin ); ?>"><svg class="social-icon--linkedin" style="fill:<?php echo esc_html( $text_value ); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
														<path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z" />
													</svg></a></li>
											<?php
										}
										if ( $website ) {
											?>
											<li><a title="<?php echo esc_html( get_the_title() ); ?> website" href="<?php echo esc_url( $website ); ?>"><svg class="social-icon--website" style="fill:<?php echo esc_html( $text_value ); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
														<path d="M448 80v352c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V80c0-26.51 21.49-48 48-48h352c26.51 0 48 21.49 48 48zm-88 16H248.029c-21.313 0-32.08 25.861-16.971 40.971l31.984 31.987L67.515 364.485c-4.686 4.686-4.686 12.284 0 16.971l31.029 31.029c4.687 4.686 12.285 4.686 16.971 0l195.526-195.526 31.988 31.991C358.058 263.977 384 253.425 384 231.979V120c0-13.255-10.745-24-24-24z" />
													</svg></a></li>
											<?php
										}
										?>
									</ul>
								</div>
								<div class="bio">
									<?php echo get_the_content(); ?>
								</div>
						</div>
				</li>
				<?php
			}
			?>
		</ul>
	</section>
	<?php
	$block_content = ob_get_clean();
	/* Restore original Post Data */
	wp_reset_postdata();
	return $block_content;
}
