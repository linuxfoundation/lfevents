<?php
/**
 * Sidebar content for the lfeventsci site
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<h4 class="medium-margin-bottom">Latest News</h4>
<?php
$query = new WP_Query(
	array(
		'post_type' => 'post',
		'no_found_rows' => true,
		'posts_per_page' => 5,
	)
);
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		echo '<h5 class="text-medium no-margin"><a href="' . esc_html( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h5>';
		echo '<p class="text-tiny medium-margin-bottom">' . get_the_date() . '</p>';
	}
}
wp_reset_postdata();
?>
<p class="xlarge-margin-bottom"><a href="<?php echo esc_url( home_url( '/about/news' ) ); ?>"><strong>More News&hellip;</strong></a></p>

<h4 class="medium-margin-bottom">Newsletter Sign-up</h4>
<p>Sign up for the LF Events newsletter to learn about upcoming events!</p>
<div id="message"></div>
<form id="sfmc-form" class="xlarge-margin-bottom" action="https://cloud.email.thelinuxfoundation.org/Submission---Dynamic-Newsletter-Form-Events">
  <label for="FirstName">
	<input type="text" name="FirstName" placeholder="First name" required="">
  </label>
  <label for="LastName">
	<input type="text" name="LastName" placeholder="Last name" required="">
  </label>
  <label for="EmailAddress">
	<input type="email" name="EmailAddress" placeholder="Email address" required="">
  </label>

  <input type="hidden" name="ownerid" value="00541000002w50ZAAQ">
  <input type="hidden" id="txtUrl" name="txtUrl" value="" readonly="">
  <div data-callback="onSubmit" data-sitekey="6LdoJscUAAAAAGb5QCtNsaaHwkZBPE3-R0d388KZ" class="g-recaptcha" data-size="invisible"></div>

  <script>
	document.getElementById('txtUrl').value = window.location.href;
  </script>

<input class="button expanded" type="submit" value="SIGN UP!" id="submitbtn">
<p class="text-tiny">You may unsubscribe at any time. For more information please see our <a target="_blank" href="https://www.linuxfoundation.org/privacy/">Privacy Policy <?php echo esc_html( get_template_part( 'template-parts/svg/external-link' ) ); ?></a>.</p>
</form>
<script src="https://www.recaptcha.net/recaptcha/api.js" async="" defer=""></script> <?php //phpcs:ignore ?>
<script defer="" src="https://events.linuxfoundation.org/wp-content/themes/lfevents/dist/assets/js/sfmc-forms.js?ver=1578152844"></script> <?php //phpcs:ignore ?>

<h4 class="medium-margin-bottom large-margin-top">Community Events</h4>
<?php
$query = new WP_Query(
	array(
		'post_type' => 'lfe_community_event',
		'no_found_rows' => true,
		'posts_per_page' => 10,
		'meta_key'   => 'lfes_community_date_start',
		'orderby'    => 'meta_value',
		'order'      => 'ASC',
	)
);
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_start', true ) );
		$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_end', true ) );

		echo '<h5 class="text-medium no-margin">';
		echo '<a class="prevent-orphaned-icon" target="_blank" href="' . esc_html( get_post_meta( $post->ID, 'lfes_community_external_url', true ) ) . '">';
		echo esc_html( get_the_title() );
		echo '&nbsp;';
		echo esc_html( get_template_part( 'template-parts/svg/external-link' ) );
		echo '</a>';
		echo '</h5>';

		echo '<p class="text-tiny medium-margin-bottom">';
		echo esc_html( jb_verbose_date_range( $dt_date_start, $dt_date_end ) );
		$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
		if ( $country ) {
			$country = $country[0]->name;
			$city = get_post_meta( $post->ID, 'lfes_community_city', true );
			if ( $city ) {
				$city .= ', ';
			}
			echo ' | ' . esc_html( $city ) . esc_html( $country );
		}
		echo '</p>';
	}
}
wp_reset_postdata();
?>
<p class="xlarge-margin-bottom"><a href="<?php echo esc_url( home_url( '/about/community' ) ); ?>"><strong>More Community Events&hellip;</strong></a></p>
