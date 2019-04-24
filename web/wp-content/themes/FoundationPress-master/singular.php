<?php
/**
 * The template for displaying pages and all lfevents post types
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<?php /* get_template_part( 'template-parts/featured-image' ); */ ?>

<div data-sticky-container>
	<header class="event-header sticky" data-sticky data-options="marginTop:0;" >

		<div class="pre-nav">
			<?php
			if ( $post->post_parent ) {
				$ancestors = get_post_ancestors( $post->ID );
				$parent_id    = $ancestors[ count( $ancestors ) - 1 ];
			} else {
				$parent_id = $post->ID;
			}
			echo '<a href="' . post_permalink( $parent_id ) . '">' . get_the_title( $parent_id ) . '</a>'; //phpcs:ignore
			?>

			<button class="menu-toggler button alignright" data-toggle="event-menu">
				<span class="hamburger-icon"></span>
			</button>
		</div>

		<nav id="event-menu" class="event-menu show-for-large" data-toggler="show-for-large">
			<ul class="event-menu-list">
				<?php
				wp_list_pages( 'title_li=&include=' . $parent_id . '&post_type=' . $post->post_type );

				$children = lfe_remove_parent_links( 'title_li=&child_of=' . $parent_id . '&echo=0&sort_column=menu_order&post_type=' . $post->post_type );
				if ( $children ) {
					echo $children; //phpcs:ignore
				}
				lfe_get_other_events( $parent_id );
				?>
			</ul>
		</nav>

	</header>
</div>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<?php get_template_part( 'template-parts/content', 'page' ); ?>
				<?php comments_template(); ?>
			<?php endwhile; ?>
		</main>
		<?php /* get_sidebar(); */ ?>
	</div>
</div>
<?php
get_footer();
