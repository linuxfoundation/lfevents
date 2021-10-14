<?php
/**
 * The template for displaying pages and all lfevents post types
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

$parent_id = lfe_get_event_parent_id( $post );

get_header();

$splash_page = get_post_meta( get_the_ID(), 'lfes_splash_page', true );
$hide_header = get_post_meta( get_the_ID(), 'lfes_hide_header', true );
$event_has_passed = false;

if ( ! $splash_page ) {
	// menu background color.
	$menu_color       = get_post_meta( $parent_id, 'lfes_menu_color', true );
	$menu_color_2     = get_post_meta( $parent_id, 'lfes_menu_color_2', true );
	$menu_color_3     = get_post_meta( $parent_id, 'lfes_menu_color_3', true );
	$menu_text_color  = get_post_meta( $parent_id, 'lfes_menu_text_color', true );

	$background_style = 'background-color: ' . $menu_color . ';';

	$event_has_passed = get_post_meta( $parent_id, 'lfes_event_has_passed', true );

	if ( $menu_color_2 ) {
		$background_style = 'background: linear-gradient(90deg, ' . $menu_color . ' 0%, ' . $menu_color_2 . ' 100%);';
	}
	$text_style = 'color: ' . $menu_text_color . ';';

	$overlay_strength = get_post_meta( $parent_id, 'lfes_overlay_strength', true );

	$overlay_style = '';

	if ( $overlay_strength || '0' === $overlay_strength ) {
		$overlay_strength = (int) $overlay_strength * 0.8 * 0.01;
		$overlay_style = 'opacity: ' . $overlay_strength . ';';
	}

	// set hamburger and list elements color (via class name).
	if ( 'white' == $menu_text_color ) {
		$subpage_header_elements_class = 'is-white';
	} else {
		$subpage_header_elements_class = 'is-black';
	}

	$logo = get_post_meta( $parent_id, 'lfes_' . $menu_text_color . '_logo', true );
	if ( $logo ) {
		$event_link_content = '<img loading="lazy" src="' . wp_get_attachment_url( $logo ) . '" alt="' . get_the_title( $parent_id ) . '">';
	} else {
		$event_link_content = get_the_title( $parent_id );
	}
	?>

<div data-sticky-container>
	<header class="event-header sticky" data-sticky data-sticky-on="large"
		data-options="marginTop:0;"
		style="<?php echo esc_html( $background_style . $text_style ); ?>">
		<?php lfe_event_alert_bar( $parent_id ); ?>

		<div class="pre-nav">
			<?php
echo '<a class="event-home-link" href="' . get_permalink( $parent_id ) . '">' . $event_link_content . '</a>'; //phpcs:ignore
			?>

			<button class="menu-toggler button alignright"
				data-toggle="event-menu">
				<span class="hamburger-icon <?php echo esc_html( $subpage_header_elements_class ); ?>"></span>
			</button>
		</div>

		<nav id="event-menu" class="event-menu show-for-large"
			data-toggler="show-for-large">
			<ul class="event-menu-list <?php echo esc_html( $subpage_header_elements_class ); ?>">
				<li class="page_item event-home-link" id="popout-header-link"><a
						href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>"
						style="background-color:<?php echo $menu_color; ?>;"><?php echo $event_link_content; //phpcs:ignore ?></a>
				</li>
				<?php
				if ( $menu_color_3 ) {
					$background_style_solid = 'background: ' . $menu_color_3 . ';';
				} else {
					$background_style_solid = $background_style;
				}
				$children = lfe_get_event_menu( $parent_id, $post->post_type, $background_style_solid );
				if ( $children ) {
				echo $children; //phpcs:ignore
				}
				lfe_get_other_events( $parent_id, $background_style_solid, $menu_text_color );
				?>
			</ul>
		</nav>

	</header>
</div>

	<?php
} else {
	get_template_part( 'template-parts/event-splash-header' );
}
?>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">

			<?php if ( $event_has_passed ) { ?>
			<div class="entry-content">
				<div class="alignfull text-center xlarge-padding-top large-padding-bottom"
					style="background: <?php echo esc_html( $menu_color_3 ); ?>; color: <?php echo esc_html( $menu_text_color ); ?>; box-shadow: inset 0 -0.125rem 0 0 rgba(0,0,0,.2);">
					<h4 class="no-margin" style="">
						<svg aria-hidden="true" focusable="false" role="img"
							xmlns="http://www.w3.org/2000/svg"
							viewBox="0 0 448 512"
							class="icon--inline small-margin-right">
							<path fill="currentColor"
								d="M436 160H12c-6.627 0-12-5.373-12-12v-36c0-26.51 21.49-48 48-48h48V12c0-6.627 5.373-12 12-12h40c6.627 0 12 5.373 12 12v52h128V12c0-6.627 5.373-12 12-12h40c6.627 0 12 5.373 12 12v52h48c26.51 0 48 21.49 48 48v36c0 6.627-5.373 12-12 12zM12 192h424c6.627 0 12 5.373 12 12v260c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V204c0-6.627 5.373-12 12-12zm333.296 95.947l-28.169-28.398c-4.667-4.705-12.265-4.736-16.97-.068L194.12 364.665l-45.98-46.352c-4.667-4.705-12.266-4.736-16.971-.068l-28.397 28.17c-4.705 4.667-4.736 12.265-.068 16.97l82.601 83.269c4.667 4.705 12.265 4.736 16.97.068l142.953-141.805c4.705-4.667 4.736-12.265.068-16.97z">
							</path>
						</svg>
						<?php lfe_passed_event_banner( $parent_id ); ?>
					</h4>
				</div>
			</div>
			<?php } ?>

			<?php
			while ( have_posts() ) :
				the_post();
				if ( $post->post_parent && ! $hide_header ) {
					if ( 'white' == $menu_text_color ) {
						$subpage_header_text_color       = 'black';
						$subpage_header_background_color = 'white';
					} else {
						$subpage_header_text_color       = 'white';
						$subpage_header_background_color = 'black';
					}
					$subpage_header_style = 'color:' . $subpage_header_text_color . '; background-color: ' . $subpage_header_background_color . '; -webkit-text-fill-color: transparent; background: -webkit-gradient(linear,left top,right bottom,from(' . $menu_color_2 . '),to(' . $menu_color . ')); background: -o-gradient(linear,left top,right bottom,from(' . $menu_color_2 . '),to(' . $menu_color . ')); -webkit-background-clip: text;';
					?>
			<header class="event-subpage-header background-image-wrapper"
				style="<?php echo esc_html( $text_style ); ?>">
				<div class="overlay"
					style="background: linear-gradient(90deg, <?php echo esc_html( $menu_color_2 ); ?> 0%, <?php echo esc_html( $menu_color ); ?> 100%); <?php echo esc_html( $text_style . $overlay_style ); ?>">
				</div>
				<figure class="figure-container">
					<?php
					if ( has_post_thumbnail() ) {
						echo wp_get_attachment_image(
							get_post_thumbnail_id( get_the_ID() ),
							'fp-xlarge',
							false,
							array(
								'class' => '',
								'loading' => 'eager',
							)
						);
					} else {
						echo wp_get_attachment_image(
							get_post_thumbnail_id( $parent_id ),
							'fp-xlarge',
							false,
							array(
								'class' => '',
								'loading' => 'eager',
							)
						);
					}
					?>
				</figure>

				<h1 class="content-wrapper"
					style="background-color: <?php echo esc_html( $subpage_header_background_color ); ?>;">
					<span
						style="<?php echo esc_html( $subpage_header_style ); ?>">
						<?php echo strip_tags( get_the_title(), '<br>' ); //phpcs:ignore ?>
					</span>
				</h1>
			</header>
					<?php
				} else {
					?>
			<h1 class="show-for-sr"><?php echo strip_tags( get_the_title(), '<br>' ); //phpcs:ignore ?></h1>
					<?php
				}

				if ( is_page_template( 'page-templates/multi-part-page.php' ) ) {
					get_template_part( 'template-parts/content', 'multi-part-page' );

				} else {
					get_template_part( 'template-parts/content', 'page' );
				}
				lfe_get_sponsors( $parent_id );
endwhile;
			?>
		</main>
	</div>
</div>

<?php
// Only show footer if not a splash screen.
if ( ! $splash_page ) :
	?>

<section class="event-footer xlarge-padding-y" role="footer"
	style="background: linear-gradient(90deg, <?php echo esc_html( $menu_color_2 ); ?> 0%, <?php echo esc_html( $menu_color ); ?> 100%); <?php echo esc_html( $text_style ); ?>">

	<?php

	// setup the form defaults.
	$form_title      = 'Join our mailing list to hear all the latest about events, news and more';
	$form_privacy    = 'The Linux Foundation uses the information you provide to us to contact you about upcoming events. You may unsubscribe from these communications at any time. For more information, please see our <a target="_blank" rel="noopener" href="https://www.linuxfoundation.org/privacy/">Privacy Policy</a>.';

	if ( get_post_meta( $parent_id, 'lfes_form_title', true ) ) {
		$form_title = get_post_meta( $parent_id, 'lfes_form_title', true );
		$form_title = str_replace( "\n", '<br>', $form_title );
	}

	if ( get_post_meta( $parent_id, 'lfes_form_privacy', true ) ) {
		$form_privacy = get_post_meta( $parent_id, 'lfes_form_privacy', true );
		$form_privacy = str_replace( "\n", '<br>', $form_privacy );
		$form_privacy = preg_replace( '/\[(.*?)]\((https?.*?)\)/', '<a target="_blank" rel="noopener" href="$2">$1</a>', $form_privacy );
	}

	$allowed_elements = array(
		'href'   => true,
		'class'  => true,
		'alt'    => true,
		'rel'    => true,
		'target' => true,
	);
	?>

<div class="event-footer-newsletter <?php echo esc_html( $menu_text_color ); ?>">
	<p class="event-footer-newsletter__title"><?php echo wp_kses( $form_title, array( 'br' => array() ) ); ?></p>
	<?php
		echo do_shortcode( '[hubspot type=form portal=8112310 id=be35e462-1b9f-4499-9437-17f4d5c31ae5]' );
	?>
	<p class="event-footer-newsletter__privacy">
	<?php
	echo wp_kses(
		$form_privacy,
		array(
			'a' => $allowed_elements,
			'br' => array(),
		)
	);
	?>
	</p>
</div>

	<?php
	// Only display this section if there is a logo set.
	if ( $logo ) :
		?>
	<div class="event-footer-alignment">

		<div class="event-footer-logo-social">

			<div class="event-footer-logo-social__logo">
				<?php
				echo '<a href="' . esc_url( get_permalink( $parent_id ) ) . '">' . wp_kses_post( $event_link_content ) . '</a>';
				?>
			</div>

			<?php
			// setup social media icons if present.
			$wechat    = get_post_meta( $parent_id, 'lfes_wechat', true );
			$linkedin  = get_post_meta( $parent_id, 'lfes_linkedin', true );
			$qq        = get_post_meta( $parent_id, 'lfes_qq', true );
			$youtube   = get_post_meta( $parent_id, 'lfes_youtube', true );
			$facebook  = get_post_meta( $parent_id, 'lfes_facebook', true );
			$twitter   = get_post_meta( $parent_id, 'lfes_twitter', true );
			$instagram = get_post_meta( $parent_id, 'lfes_instagram', true );
			$twitch    = get_post_meta( $parent_id, 'lfes_twitch', true );
			$slack     = get_post_meta( $parent_id, 'lfes_slack', true );

			// hashtag.
			$hashtag = get_post_meta( $parent_id, 'lfes_hashtag', true );

			// if no custom social links are set, fallback to defaults for LF.
			if ( ! $wechat && ! $linkedin && ! $qq && ! $youtube && ! $facebook && ! $twitter && ! $instagram ) {

				$twitter   = 'https://twitter.com/linuxfoundation';
				$linkedin  = 'https://www.linkedin.com/company/the-linux-foundation/';
				$youtube   = 'https://www.youtube.com/user/TheLinuxFoundation';
				$facebook  = 'https://www.facebook.com/TheLinuxFoundation/';
				$instagram = 'https://www.instagram.com/linux_foundation';

			}
			?>

			<div
				class="event-footer-logo-social__wrapper <?php echo esc_html( $menu_text_color ); ?>">
				<?php
				echo '<ul class="event-footer-logo-social__icons ' . esc_html( $menu_text_color ) . '">';
				if ( $wechat ) {
					echo '<li>';
					echo '<a data-toggle="wechat-dropdown">';
					get_template_part( 'template-parts/svg/wechat' );
					echo '</a>';
					echo '<div class="dropdown-pane" id="wechat-dropdown" data-dropdown data-hover="true" data-hover-pane="true" data-hover-delay="0" data-position="top" data-alignment="center">' . wp_get_attachment_image( esc_html( $wechat ) ) . '</div>';
					echo '</li>';
				}
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
				if ( $qq ) {
					echo '<li><a rel="noopener" title="QQ" target="_blank" href="' . esc_html( $qq ) . '">';
					get_template_part( 'template-parts/svg/qq' );
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
				if ( $twitch ) {
					echo '<li><a rel="noopener" title="Twitch" target="_blank" href="' . esc_html( $twitch ) . '">';
					get_template_part( 'template-parts/svg/twitch' );
					echo '</a></li>';
				}
				if ( $slack ) {
					echo '<li><a rel="noopener" title="Slack" target="_blank" href="' . esc_html( $slack ) . '">';
					get_template_part( 'template-parts/svg/slack' );
					echo '</a></li>';
				}
				echo '</ul>';

				if ( $hashtag ) {
					?>
				<div class="event-footer-logo-social__hashtag">
					<p><?php echo esc_html( $hashtag ); ?></p>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php endif; ?>

		<?php
		$children = lfe_get_event_menu( $parent_id, $post->post_type, null, true );
		if ( $children ) :
			?>
		<ul
			class="event-footer-menu <?php echo esc_html( $menu_text_color ); ?>">
			<?php
			echo wp_kses_post( $children );
			?>
		</ul>
			<?php
endif;
		?>
	</div>
</section>
<?php endif; ?>
<?php
get_footer();
