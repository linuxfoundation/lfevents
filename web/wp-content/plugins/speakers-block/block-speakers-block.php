<?php
/**
 * Plugin Name:       Speakers Block
 * Description:       Allows for insertion of a Speakers showcase in a page/post. It requires an existing Speakers CPT already setup <a href="https://github.com/linuxfoundation/lfevents/blob/main/web/wp-content/mu-plugins/custom/lfevents/admin/partials/cpts.php#L56">like this</a>.
 * Plugin URI: https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/speakers-block
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
	$color_mode = $attributes['colorMode'] ?? '';

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
		$bg_color_2 ? $bg_color_2 : ( $bg_color_1 ? $bg_color_1 : 'transparent' );

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
				$github   = get_post_meta( $id, 'lfes_speaker_github', true );
				$website  = get_post_meta( $id, 'lfes_speaker_website', true );
				?>
				<li id="
				<?php
				echo esc_html( $id_preface . $id );
				?>
				"
				class="speaker cell small-6 medium-4 xxlarge-3"
				data-toggler=".open"
				style="background: linear-gradient(-45deg, transparent 30%, <?php echo esc_html( $gradient_color ); ?> 100%);">
					<div class="grid-x">
						<div class="cell large-5">
							<?php
							if ( get_the_content() ) {
								?>
								<div class="headshot" role="button" data-toggle="<?php echo esc_html( $id_preface . $id ); ?>">
								<?php
							} else {
								?>
									<div class="headshot">
									<?php
							}
								echo get_the_post_thumbnail(
									$id,
									'profile-200',
									array(
										'loading' => 'lazy',
										'alt'     => esc_html( get_the_title() . ' headshot' ),
									)
								);
				?>
									</div><!-- end of headshot? -->
								</div><!-- end of cell large-5 -->
								<div class="text cell large-7">
									<?php
									if ( get_the_content() ) {
										?>
										<a class="name" role="button" data-toggle="<?php echo esc_html( $id_preface . $id ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
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
											<li><a title="<?php echo esc_html( get_the_title() ); ?> on X" href="<?php echo esc_url( $twitter ); ?>"><svg class="social-icon--twitter" style="fill:<?php echo esc_html( $text_value ); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="-24.52 -24.52 1248.04 1275.04">
											<path d="M714.163 519.284L1160.89 0h-105.86L667.137 450.887 357.328 0H0l468.492 681.821L0 1226.37h105.866l409.625-476.152 327.181 476.152H1200L714.137 519.284h.026zM569.165 687.828l-47.468-67.894L144.011 79.6944h162.604L611.412 515.685l47.468 67.894 396.2 566.721H892.476L569.165 687.854v-.026z"/>
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
										if ( $github ) {
											?>
											<li><a title="<?php echo esc_html( get_the_title() ); ?> on GitHub" href="<?php echo esc_url( $github ); ?>"><svg class="social-icon--github" style="fill:<?php echo esc_html( $text_value ); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="-1.95 -1.95 101.40 99.78">
														<path fill-rule="evenodd" d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z" clip-rule="evenodd"/>
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
