<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<footer class="site-footer" role="footer">
	<section id="text-2" class="widget widget_text"><div class="textwidget">
		<?php if ( is_lfeventsci() ) { ?>
			<p>Copyright &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> The Linux Foundation®. All rights reserved. The Linux Foundation has registered trademarks and uses trademarks. For a list of trademarks of The Linux Foundation, please see our <a href="https://www.linuxfoundation.org/trademark-usage/" target="_blank" rel="noopener">Trademark Usage</a> page. Linux is a registered trademark of Linus Torvalds.</p>
		<?php } else { ?>
			<p>Copyright <?php echo esc_html( gmdate( 'Y' ) ); ?> &copy; LF Asia, LLC. | info@lfasiallc.com, icp license, no. 京ICP备17074266号-6</p>
		<?php } ?>
		<p>Forms on this site are protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
	</div></section>
	<?php dynamic_sidebar( 'footer-widgets' ); ?>
</footer>

<?php get_template_part( 'template-parts/cookie-banner' ); ?>

<?php wp_footer(); ?>
</div> <!-- end .site-container -->
</body>
</html>
