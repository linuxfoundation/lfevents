<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

$splash_page = get_post_meta( get_the_ID(), 'lfes_splash_page', true );

if ( false && show_non_event_menu() && ! $splash_page ) :
	// show the homepage hub footer.
	// TODO: Remove false in if statement to show on non-event pages.
	// TODO: Diverge style from event-footer class structure.
	?>
<section class="event-footer xlarge-padding-y is-style-lf-blue-gradient"
	role="footer">
	<div class="event-footer-newsletter">
		<p
			class="event-footer-newsletter__title">Join our mailing list to hear all the latest about events, news and more</p>
		<?php
			echo do_shortcode( '[hubspot type=form portal=8112310 id=be35e462-1b9f-4499-9437-17f4d5c31ae5]' );
		?>
		<p class="event-footer-newsletter__privacy">
		The Linux Foundation uses the information you provide to us to contact you about upcoming events. You may unsubscribe from these communications at any time. For more information, please see our <a target="_blank" rel="noopener" href="https://www.linuxfoundation.org/privacy/">Privacy Policy</a>.
		</p>
	</div>

	<div class="event-footer-logo-social__wrapper white">
		<?php
		$twitter   = 'https://twitter.com/linuxfoundation';
		$linkedin  = 'https://www.linkedin.com/company/the-linux-foundation/';
		$youtube   = 'https://www.youtube.com/user/TheLinuxFoundation';
		$facebook  = 'https://www.facebook.com/TheLinuxFoundation/';
		$instagram = 'https://www.instagram.com/linux_foundation';

		echo '<ul class="event-footer-logo-social__icons white">';
		if ( $twitter ) {
			echo '<li><a rel="noopener" title="Twitter" target="_blank" href="' . esc_html( $twitter ) . '">';
			get_template_part( 'template-parts/svg/twitter' );
			echo '</a></li>';
		}
		if ( $linkedin ) {
			echo '<li><a rel="noopener" title="Linkedin" target="_blank" href="' . esc_html( $linkedin ) . '">';
			get_template_part( 'template-parts/svg/linkedin' );
			echo '</a></li>';
		}
		if ( $youtube ) {
			echo '<li><a rel="noopener" title="YouTube" target="_blank" href="' . esc_html( $youtube ) . '">';
			get_template_part( 'template-parts/svg/youtube' );
			echo '</a></li>';
		}
		if ( $facebook ) {
			echo '<li><a rel="noopener" title="Facebook" target="_blank" href="' . esc_html( $facebook ) . '">';
			get_template_part( 'template-parts/svg/facebook' );
			echo '</a></li>';
		}
		if ( $instagram ) {
			echo '<li><a rel="noopener" title="Instagram" target="_blank" href="' . esc_html( $instagram ) . '">';
			get_template_part( 'template-parts/svg/instagram' );
			echo '</a></li>';
		}
		echo '</ul>';
		?>
	</div>
</section>
	<?php
endif;
?>
<footer class="site-footer">
	<section id="text-2" class="widget widget_text">
		<div class="textwidget">
			<?php if ( is_lfeventsci() ) { ?>
			<p>Copyright &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> The Linux Foundation®. All rights reserved. The Linux Foundation has registered trademarks and uses trademarks. For a list of trademarks of The Linux Foundation, please see our <a href="https://www.linuxfoundation.org/trademark-usage/" target="_blank" rel="noopener">Trademark Usage</a> page. Linux is a registered trademark of Linus Torvalds.</p>
			<?php } else { ?>
			<p>Copyright <?php echo esc_html( gmdate( 'Y' ) ); ?> &copy; LF Asia, LLC. | info@lfasiallc.com, icp license, no. <a href="http://beian.miit.gov.cn/" target="_blank" rel="noopener">京ICP备17074266号-6</a></p>
			<?php } ?>
			<p>Forms on this site are protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
		</div>
	</section>
	<?php dynamic_sidebar( 'footer-widgets' ); ?>
</footer>

<?php get_template_part( 'template-parts/cookie-banner' ); ?>

<?php wp_footer(); ?>
</div> <!-- end .site-container -->
</body>

</html>
