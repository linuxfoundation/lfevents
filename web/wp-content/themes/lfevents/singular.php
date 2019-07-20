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

get_header();

if ( $post->post_parent ) {
	$ancestors = get_post_ancestors( $post->ID );
	$parent_id = $ancestors[ count( $ancestors ) - 1 ];
} else {
	$parent_id = $post->ID;
}

// menu background color.
$menu_color = get_post_meta( $parent_id, 'lfes_menu_color', true );
$menu_color_2 = get_post_meta( $parent_id, 'lfes_menu_color_2', true );
$menu_color_3 = get_post_meta( $parent_id, 'lfes_menu_color_3', true );
$menu_text_color = get_post_meta( $parent_id, 'lfes_menu_text_color', true );
$background_style = 'background-color: ' . $menu_color . ';';
if ( $menu_color_2 ) {
	$background_style = 'background: linear-gradient(90deg, ' . $menu_color . ' 0%, ' . $menu_color_2 . ' 100%);';
}
$text_style = 'color: ' . $menu_text_color . ';';

$logo = get_post_meta( $parent_id, 'lfes_' . $menu_text_color . '_logo', true );
if ( $logo ) {
	$event_link_content = '<img src="' . wp_get_attachment_url( $logo ) . '" alt="' . get_the_title( $parent_id ) . '">';
} else {
	$event_link_content = get_the_title( $parent_id );
}

?>

<div data-sticky-container>
	<header class="event-header sticky" data-sticky data-sticky-on="large" data-options="marginTop:0;" style="<?php echo esc_html( $background_style . $text_style ); ?>">

		<div class="pre-nav">
			<?php
			echo '<a class="event-home-link" href="' . get_permalink( $parent_id ) . '">' . $event_link_content . '</a>'; //phpcs:ignore
			?>

			<button class="menu-toggler button alignright" data-toggle="event-menu">
				<span class="hamburger-icon"></span>
			</button>
		</div>

		<nav id="event-menu" class="event-menu show-for-large" data-toggler="show-for-large">
			<ul class="event-menu-list">
				<li class="page_item event-home-link" id="popout-header-link"><a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>" style="background-color:<?php echo $menu_color; ?>;"><?php echo $event_link_content; //phpcs:ignore ?></a></li>
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

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();

				if ( has_post_thumbnail() && $post->post_parent ) {
					if ( 'white' == $menu_text_color ) {
						$subpage_header_text_color = 'black';
						$subpage_header_background_color = 'white';
					} else {
						$subpage_header_text_color = 'white';
						$subpage_header_background_color = 'black';
					}
					$subpage_header_style = 'color:' . $subpage_header_text_color . '; background-color: ' . $subpage_header_background_color . '; -webkit-text-fill-color: transparent; background: -webkit-gradient(linear,left top,right bottom,from(' . $menu_color_2 . '),to(' . $menu_color . ')); background: -o-gradient(linear,left top,right bottom,from(' . $menu_color_2 . '),to(' . $menu_color . ')); -webkit-background-clip: text;';
					?>
					<header class="event-subpage-header" style="background-image:url(<?php the_post_thumbnail_url(); ?>); <?php echo esc_html( $text_style ); ?>">
						<div class="overlay" style="background: linear-gradient(90deg, <?php echo esc_html( $menu_color_2 ); ?> 0%, <?php echo esc_html( $menu_color ); ?> 100%); <?php echo esc_html( $text_style ); ?>"></div>
						<h1 style="background-color: <?php echo esc_html( $subpage_header_background_color ); ?>;">
							<span style="<?php echo esc_html( $subpage_header_style ); ?>">
								<?php the_title(); ?>
							</span>
						</h1>
					</header>
					<?php
				}

				get_template_part( 'template-parts/content', 'page' );
				lfe_get_sponsors( $parent_id );
			endwhile;
			?>
		</main>
	</div>
</div>

<div class="event-footer"
	style="background: linear-gradient(90deg, <?php echo esc_html( $menu_color_2 ); ?> 0%, <?php echo esc_html( $menu_color ); ?> 100%); <?php echo esc_html( $text_style ); ?>">
	<h3 class="event-social-links-header">Follow Us</h3>
	<ul class="event-social-links">

		<?php
		$wechat = get_post_meta( $parent_id, 'lfes_wechat', true );
		$linkedin = get_post_meta( $parent_id, 'lfes_linkedin', true );
		$qq = get_post_meta( $parent_id, 'lfes_qq', true );
		$youtube = get_post_meta( $parent_id, 'lfes_youtube', true );
		$facebook = get_post_meta( $parent_id, 'lfes_facebook', true );
		$twitter = get_post_meta( $parent_id, 'lfes_twitter', true );

		if ( $wechat ) {
			echo '<li>
				<a data-toggle="wechat-dropdown">
					<svg class="social-icon--wechat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M385.2 167.6c6.4 0 12.6.3 18.8 1.1C387.4 90.3 303.3 32 207.7 32 100.5 32 13 104.8 13 197.4c0 53.4 29.3 97.5 77.9 131.6l-19.3 58.6 68-34.1c24.4 4.8 43.8 9.7 68.2 9.7 6.2 0 12.1-.3 18.3-.8-4-12.9-6.2-26.6-6.2-40.8-.1-84.9 72.9-154 165.3-154zm-104.5-52.9c14.5 0 24.2 9.7 24.2 24.4 0 14.5-9.7 24.2-24.2 24.2-14.8 0-29.3-9.7-29.3-24.2.1-14.7 14.6-24.4 29.3-24.4zm-136.4 48.6c-14.5 0-29.3-9.7-29.3-24.2 0-14.8 14.8-24.4 29.3-24.4 14.8 0 24.4 9.7 24.4 24.4 0 14.6-9.6 24.2-24.4 24.2zM563 319.4c0-77.9-77.9-141.3-165.4-141.3-92.7 0-165.4 63.4-165.4 141.3S305 460.7 397.6 460.7c19.3 0 38.9-5.1 58.6-9.9l53.4 29.3-14.8-48.6C534 402.1 563 363.2 563 319.4zm-219.1-24.5c-9.7 0-19.3-9.7-19.3-19.6 0-9.7 9.7-19.3 19.3-19.3 14.8 0 24.4 9.7 24.4 19.3 0 10-9.7 19.6-24.4 19.6zm107.1 0c-9.7 0-19.3-9.7-19.3-19.6 0-9.7 9.7-19.3 19.3-19.3 14.5 0 24.4 9.7 24.4 19.3.1 10-9.9 19.6-24.4 19.6z"/></svg>
				</a>
				<div class="dropdown-pane" id="wechat-dropdown" data-dropdown data-hover="true" data-hover-pane="true" data-hover-delay="0" data-position="top" data-alignment="center">' . wp_get_attachment_image( esc_html( $wechat ) ) . '</div>
			</li>';
		}
		if ( $linkedin ) {
			echo '';
			echo '<li><a target="_blank" href="' . esc_html( $linkedin ) . '"><svg class="social-icon--linkedin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/></svg></a></li>';
		}
		if ( $qq ) {
			echo '<li><a target="_blank" href="' . esc_html( $qq ) . '"><svg class="social-icon--qq" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M433.754 420.445c-11.526 1.393-44.86-52.741-44.86-52.741 0 31.345-16.136 72.247-51.051 101.786 16.842 5.192 54.843 19.167 45.803 34.421-7.316 12.343-125.51 7.881-159.632 4.037-34.122 3.844-152.316 8.306-159.632-4.037-9.045-15.25 28.918-29.214 45.783-34.415-34.92-29.539-51.059-70.445-51.059-101.792 0 0-33.334 54.134-44.859 52.741-5.37-.65-12.424-29.644 9.347-99.704 10.261-33.024 21.995-60.478 40.144-105.779C60.683 98.063 108.982.006 224 0c113.737.006 163.156 96.133 160.264 214.963 18.118 45.223 29.912 72.85 40.144 105.778 21.768 70.06 14.716 99.053 9.346 99.704z"/></svg></a></li>';
		}
		if ( $youtube ) {
			echo '<li><a target="_blank" href="' . esc_html( $youtube ) . '"><svg class="social-icon--youtube" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg></a></li>';
		}
		if ( $facebook ) {
			echo '<li><a target="_blank" href="' . esc_html( $facebook ) . '"><svg class="social-icon--facebook" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg></a></li>';
		}
		if ( $twitter ) {
			echo '<li><a target="_blank" href="' . esc_html( $twitter ) . '"><svg class="social-icon--twitter" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></a></li>';
		}
		?>
	</ul>
</div>

<?php
get_footer();
