<?php
/**
 * 404 page
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/global-header' );
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
			<li>Return to the <a href="<?php echo esc_url( home_url() ); ?>">home
					page</a></li>
			<li>Click the <a href="javascript:history.back()">Back</a> button
			</li>
			<li>Search the site:</li>
		</ul>


		<form role="search" method="get"
			action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<div>
				<label>
					<span class="search-text screen-reader-text">Search the
						site</span>
					<input type="search" class="search-field margin-y"
						placeholder="Enter search term"
						value="<?php echo get_search_query(); ?>" name="s"
						title="Search for" autocomplete="off" autocorrect="off"
						autocapitalize="off" spellcheck="false" />
				</label>
			</div>
			<div>
				<input type="submit" class="button" value="Search" />
			</div>
		</form>
	</div>
	</article>
</main>
<?php
get_footer();
