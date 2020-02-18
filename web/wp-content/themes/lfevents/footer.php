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

<footer class="site-footer">
	<section id="text-2" class="widget widget_text"><div class="textwidget">
		<p>Copyright &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> The Linux Foundation®. All rights reserved. The Linux Foundation has registered trademarks and uses trademarks. For a list of trademarks of The Linux Foundation, please see our <a href="https://www.linuxfoundation.org/trademark-usage/" target="_blank" rel="noopener">Trademark Usage</a> page. Linux is a registered trademark of Linus Torvalds.</p>
		<p>Forms on this site are protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.</p>
	</div></section>
	<?php dynamic_sidebar( 'footer-widgets' ); ?>
</footer>

<?php wp_footer(); ?>
</div> <!-- end .site-container -->
</body>
</html>
