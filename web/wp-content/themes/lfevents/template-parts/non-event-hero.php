<?php
/**
 * Non-Event Hero.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<header class="non-event-hero is-style-lf-blue-gradient">
	<div class="container wrap">


		<?php
		if ( function_exists( 'is_tag' ) && is_tag() || is_category() || is_tax() ) :
			?>
		<h1 class="blog-title"><?php single_cat_title(); ?></h1>
			<?php
			the_archive_description( '<div class="taxonomy-description">', '</div>' );
			?>
		<?php elseif ( is_author() ) : ?>
		<h1 class="blog-title">All posts by <?php the_author(); ?></h1>
		<?php elseif ( is_post_type_archive( 'lfe_weekly_snap' ) ) : ?>
			<h1 class="page-title" itemprop="headline">Weekly Snaps
		</h1>
			<?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
		<?php elseif ( is_archive() ) : ?>
			<h1 class="page-title" itemprop="headline"><?php the_title(); ?>
		</h1>
			<?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
		<?php elseif ( is_search() ) : ?>
		<h2 class="page-title"><span>Search results for: </span>
			<?php echo esc_attr( get_search_query() ); ?></h2>
		<?php elseif ( ! ( is_404() ) && ( is_page() ) ) : ?>
		<h1 class="page-title" itemprop="headline"><?php the_title(); ?>
		</h1>
		<?php elseif ( ! ( is_404() ) && ( is_single() ) ) : ?>
			<?php
			if ( is_singular( 'post' ) ) {
				?>
		<div class="eyebrow white"><time datetime="<?php echo esc_html( get_the_time( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></div>
				<?php
			}
			?>
		<h1 class="post-title" itemprop="headline"><?php the_title(); ?>
		</h1>

		<?php elseif ( is_404() ) : ?>
		<h2 class="post-title" itemprop="headline">Sorry that page wasn't
			found</h2>
		<?php elseif ( is_home() ) : ?>
		<h2 class="blog-title"><?php single_post_title(); ?></h2>
		<?php else : ?>
		<h1 class="page-title" itemprop="headline"><?php the_title(); ?>
		</h1>
		<?php endif; ?>
	</div>
</header>
