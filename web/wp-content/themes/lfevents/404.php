<?php
/**
 * 404 page
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/header-global' );
?>

<main role="main" id="main" class="main-container-body">
	<?php get_template_part( 'template-parts/non-event-hero' ); ?>
	<div class="entry-content container wrap">
		<div class="callout warning">
			<p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
		</div>

		<p>Please try the following:</p>
		<ul>
			<li>Check your spelling
			</li>
			<li>Return to the <a href="<?php echo esc_url( home_url() ); ?>">homepage</a></li>
			<li>Click the <a href="javascript:history.back()">Back</a> button
			</li>
		</ul>
	</div>
	</article>
</main>
<?php
get_footer();
