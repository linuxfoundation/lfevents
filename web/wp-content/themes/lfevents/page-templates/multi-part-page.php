<?php
/**
 * Template Name: Multi-part Page with Menu
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-nav' );

/**
 * Adds the menu section to the bottom of the content of each post.
 *
 * @param string $content Content of the post.
 */
function tcb_content_filter( $content ) {
	// get all div tags of class "wp-block-cgb-block-tab-container-block".
	$tag_regex = '/<[^>]*class="[^"]*\bwp-block-cgb-block-tab-container-block\b[^"]*"[^>]*>/i';
	preg_match_all( $tag_regex, $content, $matches );

	if ( ! $matches ) {
		return $content;
	}

	$menu = '<div class="tab-container-block-menu"><ul>';

	// grab the data-menu-title and id from each tag to construct the menu.
	foreach ( $matches[0] as $match ) {
		preg_match( '/id="([^"]*)"/i', $match, $id );
		preg_match( '/data-menu-title="([^"]*)"/i', $match, $menu_title );

		$menu .= '<li><a href="#' . $id[1] . '">' . $menu_title[1] . '</a></li>';
	}

	$menu .= '</ul></div>';

	// add the menu markup to the end of $content.
	return $content . $menu;
}
add_filter( 'the_content', 'tcb_content_filter' );

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
						<header id="event-calendar-header" class="alignwide about-page-header">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</header>
						<div class="alignwide">
							<div class="event-calendar-container">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
