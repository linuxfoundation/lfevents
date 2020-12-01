<?php
/**
 * About Page Header.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<header class="about-page-header" style="border: 10px solid black;">
	<h1 class="entry-title">TEST <?php the_title(); ?></h1>

	<?php
if ( is_singular( 'post' ) ) {
	echo '<time class="updated" datetime="' . esc_html( get_the_time( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
}
?>

</header>
