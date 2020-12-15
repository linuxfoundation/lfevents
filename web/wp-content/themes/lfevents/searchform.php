<?php
/**
 * The template for displaying search form
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<form role="search" method="get" class="error-search-form"
			action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<div>
				<label>
					<span class="search-text">Search the site</span><br/>

				<input type="search" class=""
						placeholder="Enter search term"
						value="<?php echo get_search_query(); ?>" name="s"
						title="Search for" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />
				</label>
			</div>
			<div>
				<input id="searchsubmit" type="submit" class="button"
					value="Search" />
			</div>
		</form>
