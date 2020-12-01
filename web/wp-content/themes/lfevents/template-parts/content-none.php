<?php
/**
 * The template part for displaying a message that posts cannot be found
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<header class="page-header">
	<h1 class="page-title"><?php _e( 'Nothing Found', 'foundationpress' ); //phpcs:ignore ?></h1>
</header>

<div class="page-content">
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

		<?php if ( is_search() ) : ?>

	<p>Sorry, but nothing matched your search terms. Please try again with some different keywords.</p>
			<?php get_search_form(); ?>

	<?php else : ?>

	<p>It seems we can't find what you're looking for. Perhaps searching can help.</p>
		<?php get_search_form(); ?>

	<?php endif; ?>
	<?php endif; ?>
</div>
