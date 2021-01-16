<?php
/**
 * Home banner
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

	// Grab the content for the top of the home page.
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

			<div class="home-hero background-image-wrapper">
			<figure class="figure-container">
			<?php
			if ( has_post_thumbnail() ) {
				echo wp_get_attachment_image( get_post_thumbnail_id(), 'fp-medium', false, array( 'class' => '', 'loading' => 'eager', 'decoding' => 'async' ) );
			}
			?>
			</figure>
				<div class="content-wrapper container wrap">
					<?php
					the_content();
					?>
				</div>
			</div>
			<?php
		}
		wp_reset_postdata();
	}
