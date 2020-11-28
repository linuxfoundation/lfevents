<?php
/**
 * Home banner
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

	// Top homepage content.
	$query = new WP_Query(
		array(
			'post_type' => 'lfe_about_page',
			'name'      => 'homepage',
		)
	);
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			?>
			<div class="home-hero">
				<?php
				$args   = array(
					'post_parent'    => $post->ID,
					'post_type'      => 'attachment',
					'numberposts'    => -1, // show all.
					'post_status'    => 'any',
					'post_mime_type' => 'image',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				);
				$images = get_posts( $args );
				$i      = 0;
				if ( $images ) {
					?>
					<div class="bg-images">
						<?php
						foreach ( $images as $key => $image ) {
							$image_url = wp_get_attachment_image_src( $image->ID, 'fp-medium' )[0];
							?>
							<div class="bg-image" style="background-image: url(
							<?php echo esc_html( $image_url ); ?>
							);"><img src="<?php echo esc_html( $image_url ); ?>" alt="" style="display: none;"></div>
							<?php
							$i++;
						}
						?>
					</div>
					<?php
				}
				?>

				<div class="bg-animation"></div>
				<div class="grid-container">
					<?php
					the_content();
					?>
				</div>
			</div>
			<?php
		}
		wp_reset_postdata();
	}
