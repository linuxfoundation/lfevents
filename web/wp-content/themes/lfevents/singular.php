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
				$children = lfe_remove_parent_links( 'title_li=&child_of=' . $parent_id . '&echo=0&sort_column=menu_order&post_type=' . $post->post_type, $background_style_solid );
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
				?>
				<?php get_template_part( 'template-parts/content', 'page' ); ?>

				<?php lfe_get_sponsors( $parent_id ); ?>

				<?php /* comments_template(); */ ?>
			<?php endwhile; ?>

		</main>
		<?php /* get_sidebar(); */ ?>
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
			echo '';
		}
		if ( $linkedin ) {
			echo '';
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
