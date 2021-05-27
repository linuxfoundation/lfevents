<?php // phpcs:ignoreFile // due to wrong CPT naming.
/**
 * The archive listing page for weekly snaps
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>
<main role="main" id="main" class="main-container-body">
<?php get_template_part( 'template-parts/non-event-hero' ); ?>
<div class="container wrap">
This is a description of the Weekly Snaps in general.
</div>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="container wrap">
			<h2 class="large-margin-top"><?php echo get_the_date(); ?></h2>
			<?php the_content(); ?>
			<?php get_template_part( 'template-parts/edit-link' ); ?>
			</div>
		</article>
		<?php
	endwhile;

	$prev_text = sprintf(
		'%s <span class="nav-prev-text">%s</span>',
		'<span aria-hidden="true">&larr;</span>',
		__( 'Prev', 'lf-theme' )
	);
	$next_text = sprintf(
		'<span class="nav-next-text">%s</span> %s',
		__( 'Next', 'lf-theme' ),
		'<span aria-hidden="true">&rarr;</span>'
	);
	
	$posts_pagination = get_the_posts_pagination(
		array(
			'mid_size'  => 2,
			'end_size'  => 1,
			'prev_text' => $prev_text,
			'next_text' => $next_text,
		)
	);
	
	if ( $posts_pagination ) :
		echo $posts_pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped during generation.
	endif;
	
	?>
</main>
<?php
get_footer();
