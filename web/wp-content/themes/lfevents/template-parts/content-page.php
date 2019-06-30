<?php
/**
 * The default template for displaying page content
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php

	/*
	<header>
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>
	*/
	?>
	<div class="entry-content">
		<?php the_content(); ?>

		<div class="alignfull lfe-image-and-text pull-right" style="background-image:url(https://dev-lfeventsci.pantheonsite.io/wp-content/uploads/2019/05/124.jpg);">
			<img src="https://dev-lfeventsci.pantheonsite.io/wp-content/uploads/2019/05/124.jpg" />
			<div class="text">
				<blockquote>
					This was my first time at KubeCon, a conference dedicated to Kubernetes and other cloud native technologies. There were three times the number of attendees in Copenhagen as there had been last year in Berlin and it feels as though these technologies are ‘crossing the chasm’ to reach the early adopters.
					<cite>Sarah Wells, Technical Director for Operations and Reliability, Financial Times</cite>
				</blockquote>
			</div>
		</div>

		<?php edit_post_link( __( '(Edit)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
	</div>
	<?php

	/*
	<footer>
		<?php
			wp_link_pages(
				array(
					'before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ),
					'after'  => '</p></nav>',
				)
			);
			?>
		<?php
		$tag = get_the_tags(); if ( $tag ) {
			?>
			<p><?php the_tags(); ?></p><?php } ?>
	</footer>
	*/
	?>
</article>
