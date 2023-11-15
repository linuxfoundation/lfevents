<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

$parent_id = lfe_get_event_parent_id( $post );

$splash_page = get_post_meta( get_the_ID(), 'lfes_splash_page', true );

if ( show_non_event_menu() && ! $splash_page && is_lfeventsci() ) {
	$footer_classes = 'lf-footer has-lf-primary-700-background-color has-white-color';
} else {
	$footer_classes = 'event-footer--legal';
}
?>
<footer
	class="<?php echo esc_html( $footer_classes ); ?>">

<?php
	// Show newsletter on non-event pages.
if ( show_non_event_menu() && ! $splash_page && is_lfeventsci() ) :
	?>
	<section class="lf-footer__newsletter container wrap">

		<h4 class="lf-footer__title">Join the Linux Foundation mailing list to
			hear about the latest events, news &amp; more</h4>

		<?php
			echo do_shortcode( '[hubspot type=form portal=8112310 id=3fd88e30-9f70-4257-a44d-72643403281d]' );
		?>

		<p class="lf-footer__privacy">
		By submitting this form you are consenting to receive marketing emails about news, events, and training from The Linux Foundation. You can unsubscribe at any time by following the "Subscription Center" link included within such communications. For information on our privacy practices and commitment to protecting your privacy, please review our <a target="_blank" rel="noopener" href="https://www.linuxfoundation.org/privacy/">Privacy Policy</a>. We do not sell your contact information to third parties.
		</p>
	</section>
<?php endif; ?>

	<section class="lf-copyright container wrap">

		<?php if ( is_lfeventsci() ) : ?>
		<p>Copyright © <?php echo esc_html( gmdate( 'Y' ) ); ?> The Linux Foundation®. All rights reserved. The
			Linux Foundation has registered trademarks and uses trademarks. For
			a list of trademarks of The Linux Foundation, please see our <a target="_blank" rel="noopener"
				href="https://www.linuxfoundation.org/trademark-usage">Trademark Usage</a> page. Linux is a
			registered trademark of Linus Torvalds. <a target="_blank" rel="noopener"
				href="https://www.linuxfoundation.org/terms/">Terms of Use</a> |
			<a target="_blank" rel="noopener"  href="https://www.linuxfoundation.org/privacy/">Privacy
				Policy</a> | <a target="_blank" rel="noopener"
				href="https://www.linuxfoundation.org/bylaws/">Bylaws</a> | <a target="_blank" rel="noopener"
				href="https://www.linuxfoundation.org/antitrust-policy/">Antitrust
				Policy</a> | <a target="_blank" rel="noopener"
				href="https://www.linuxfoundation.org/good-standing-policy/">Good
				Standing Policy</a>.</p>
		<?php else : ?>
		<p>Copyright <?php echo esc_html( gmdate( 'Y' ) ); ?> &copy; LF Asia, LLC. | info@lfasiallc.com, icp license, no. <a href="http://beian.miit.gov.cn/" target="_blank" rel="noopener">京ICP备17074266号-6</a></p>
		<?php endif; ?>
	</section>

	<?php
	// Show social on non-event pages.
	if ( show_non_event_menu() && ! $splash_page && is_lfeventsci() ) :
		?>
<section class="lf-footer__social container wrap">

		<?php
		$twitter   = 'https://twitter.com/linuxfoundation';
		$linkedin  = 'https://www.linkedin.com/company/the-linux-foundation/';
		$youtube   = 'https://www.youtube.com/user/TheLinuxFoundation';
		$facebook  = 'https://www.facebook.com/TheLinuxFoundation/';
		$instagram = 'https://www.instagram.com/linux_foundation';

		echo '<ul class="lf-footer__icons">';
		if ( $twitter ) {
			echo '<li class="s-tw"><a rel="noopener" title="X" target="_blank" href="' . esc_html( $twitter ) . '">';
			get_template_part( 'template-parts/svg/twitter' );
			echo '</a></li>';
		}
		if ( $linkedin ) {
			echo '<li class="s-li"><a rel="noopener" title="Linkedin" target="_blank" href="' . esc_html( $linkedin ) . '">';
			get_template_part( 'template-parts/svg/linkedin' );
			echo '</a></li>';
		}
		if ( $youtube ) {
			echo '<li class="s-yt"><a rel="noopener" title="YouTube" target="_blank" href="' . esc_html( $youtube ) . '">';
			get_template_part( 'template-parts/svg/youtube' );
			echo '</a></li>';
		}
		if ( $facebook ) {
			echo '<li class="s-fb"><a rel="noopener" title="Facebook" target="_blank" href="' . esc_html( $facebook ) . '">';
			get_template_part( 'template-parts/svg/facebook' );
			echo '</a></li>';
		}
		if ( $instagram ) {
			echo '<li class="s-in"><a rel="noopener" title="Instagram" target="_blank" href="' . esc_html( $instagram ) . '">';
			get_template_part( 'template-parts/svg/instagram' );
			echo '</a></li>';
		}
		echo '</ul>';
		?>
</section>
	<?php endif; ?>
</footer>
</div> <!-- end .site-container -->
<?php wp_footer(); ?>
</body>

</html>
