<?php
/**
 * Staff Listing Shortcode
 *
 * @package WordPress
 * @subpackage lf-theme
 * @since 1.0.0
 */

/**
 * Add shortcode.
 *
 * @param array $atts Attributes.
 */
function add_staff_shortcode( $atts ) {
	ob_start();
	?>
<!-- wp:columns {"className":"is-style-feature-grid"} -->
<div class="wp-block-columns is-style-feature-grid"><!-- wp:column {"backgroundColor":"light-gray"} -->
<div class="wp-block-column has-light-gray-background-color has-background"><!-- wp:image {"id":127302,"width":200,"height":210,"sizeSlug":"full","linkDestination":"none","className":"is-style-rounded"} -->
<figure class="wp-block-image size-full is-resized is-style-rounded"><img src="https://lfeventsci.lndo.site/wp-content/uploads/2021/09/Nick-Cook.jpg" alt="" class="wp-image-127302" width="200" height="210"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3>Nick Cook</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Head of Operations</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"light-gray"} -->
<div class="wp-block-column has-light-gray-background-color has-background"><!-- wp:image {"id":127320,"sizeSlug":"full","linkDestination":"none","className":"is-style-rounded"} -->
<figure class="wp-block-image size-full is-style-rounded"><img src="https://lfeventsci.lndo.site/wp-content/uploads/2021/09/Screen-Shot-2021-09-15-at-11.43.06-AM.jpg" alt="" class="wp-image-127320"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3>Bob Jones</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Director of Marketing</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"light-gray"} -->
<div class="wp-block-column has-light-gray-background-color has-background"><!-- wp:image {"id":126822,"sizeSlug":"large","linkDestination":"none","className":"is-style-rounded"} -->
<figure class="wp-block-image size-large is-style-rounded"><img src="https://lfeventsci.lndo.site/wp-content/uploads/2021/09/Patty-headshot-1024x969.jpg" alt="" class="wp-image-126822"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3>Patty</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>CTO</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"light-gray"} -->
<div class="wp-block-column has-light-gray-background-color has-background"><!-- wp:image {"id":126799,"sizeSlug":"full","linkDestination":"none","className":"is-style-rounded"} -->
<figure class="wp-block-image size-full is-style-rounded"><img src="https://lfeventsci.lndo.site/wp-content/uploads/2021/09/kim-mcmahon.jpeg" alt="" class="wp-image-126799"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3>Kim MacMahon</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Director of Anaytics</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->


	<?php
	$block_content = ob_get_clean();
	return $block_content;
}
add_shortcode( 'staff', 'add_staff_shortcode' );
