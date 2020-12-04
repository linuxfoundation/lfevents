<?php
/**
 * The default template for displaying page content
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<?php the_content(); ?>
	<?php get_template_part( 'template-parts/edit-link' ); ?>
	</div>
</article>
