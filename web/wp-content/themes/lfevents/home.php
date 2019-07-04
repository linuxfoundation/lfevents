<?php
/**
 * The template for the homepage
 *
 * @package FoundationPress
 */

get_header(); ?>

<div data-sticky-container>
	<header class="main-header sticky" data-sticky data-sticky-on="large" data-options="marginTop:0;">

		<button class="menu-toggler button alignright hide-for-large" data-toggle="main-menu">
			<span class="hamburger-icon"></span>
		</button>

		<a class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'lfevents-horizontal-white-blue.svg' ); //phpcs:ignore ?>"></a>

		<nav id="main-menu" class="main-menu show-for-large" data-toggler="show-for-large" role="navigation">
			<?php foundationpress_about_pages_nav(); ?>
		</nav>
	</header>
</div>

<div class="main-container about-page">
	<div class="main-grid grid-container">
		<main class="main-content-full-width">

			<?php
			// Top homepage content.
			$query = new WP_Query(
				array(
					'post_type' => 'lfe_about_page',
					'name' => 'homepage',
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					the_content();
				}
				wp_reset_postdata();
			}
			?>

			<hr>

			<?php
			// Upcoming Events.
			$query = new WP_Query(
				array(
					'post_type' => 'page',
					'post_parent' => 0,
					'no_found_rows' => true,
					'meta_key'   => 'lfes_date_start',
					'orderby'    => 'meta_value',
					'order'      => 'ASC',
					'post_status' => array( 'draft', 'publish' ),
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_date_start', true ) );
					$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
					?>
					<article id="post-<?php the_ID(); ?>" class="cell medium-6 large-4">
						<div class="callout">
							<h4 class="h5 no-margin"><strong>
							<?php
							if ( 'publish' == $post->post_status ) {
								echo '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>'; //phpcs:ignore
							} else {
								the_title();
							}
							?>
							</strong></h4>
							<p>
								<?php
								echo '<small>';
								if ( $dt_date_start != $dt_date_end ) {
									echo esc_html( $dt_date_start->format( 'm/j/Y' ) . ' - ' . $dt_date_end->format( 'm/j/Y' ) );
								} else {
									echo esc_html( $dt_date_start->format( 'm/j/Y' ) );
								}
								$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
								if ( $country ) {
									$country = $country[0]->name;
									echo ' | ' . esc_html( get_post_meta( $post->ID, 'lfes_city', true ) ) . ', ' . esc_html( $country );
								}
								echo '</small>';
								?>
							</p>
						</div>
					</article>
					<?php
				}
				wp_reset_postdata();
			}
			?>
			<a href="<?php echo esc_url( home_url( '/about/events-calendar' ) ); ?>">Full Calls for Proposals Calendar</a>

			<h2>Latest News</h2>
			<?php
			// Latest News.
			$query = new WP_Query(
				array(
					'post_type' => 'post',
					'no_found_rows' => true,
					'numberposts' => 5,
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					echo '<p><a href="' . get_permalink() . '">' . get_the_title() .'</a></p>';
					echo '<p>' . get_the_date() . '</p>';
					echo '<p>' . get_the_excerpt() . '</p>';
				}
			}
			wp_reset_postdata();
			?>
			<a href="<?php echo esc_url( home_url( '/about/news' ) ); ?>">Read More News</a>
		</main>
	</div>
</div>
<?php
get_footer();
