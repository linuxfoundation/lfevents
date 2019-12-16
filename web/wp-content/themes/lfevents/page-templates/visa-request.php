<?php
/**
 * Template Name: Visa Request Form
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-nav' );
?>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<header class="about-page-header">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</header>
						<?php the_content(); ?>
						<label>
							Select the event you would like a visa form for:
							<select>
								<?php
								$args = array(
									'post_type'   => 'page',
									'post_parent' => 0,
									'no_found_rows' => true,  // used to improve performance.
									'meta_query' => array(
										array(
											'key'     => 'lfes_event_has_passed',
											'compare' => '!=',
											'value' => '1',
										),
									),
									'orderby'   => 'title',
									'order'     => 'ASC',
									'posts_per_page' => 100,
								);
								$the_query = new WP_Query( $args );

								if ( $the_query->have_posts() ) {
									while ( $the_query->have_posts() ) {
										$the_query->the_post();
										echo '<option value="' . lfe_get_event_url( $post->ID ) . '" >' . get_the_title() . '</option>';
									}
								}
								wp_reset_postdata(); // Restore original Post Data.
								?>
							</select>
						</label>
						
						<?php edit_post_link( __( '(Edit)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
					</div>
				</article>
				<?php comments_template(); ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
