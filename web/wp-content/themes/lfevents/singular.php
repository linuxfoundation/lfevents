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

$featured_image = get_the_post_thumbnail( $parent_id, 'full' );
if ( $featured_image ) {
	$event_link_content = $featured_image;
} else {
	$event_link_content = get_the_title( $parent_id );
}

?>

<?php /* get_template_part( 'template-parts/featured-image' ); */ ?>

<?php
// menu background color.
$menu_color = get_post_meta( $parent_id, 'lfes_menu_color', true );
$menu_color_2 = get_post_meta( $parent_id, 'lfes_menu_color_2', true );
$menu_text_color = get_post_meta( $parent_id, 'lfes_menu_text_color', true );
$background_style = 'background-color: ' . $menu_color . ';';
if ( $menu_color_2 ) {
	$background_style = 'background: linear-gradient(90deg, ' . $menu_color . ' 0%, ' . $menu_color_2 . ' 100%);';
}
$text_style = 'color: ' . $menu_text_color . ';';
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
				<li class="page_item event-home-link"><a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>"><?php echo $event_link_content; //phpcs:ignore ?></a></li>
				<?php
				$children = lfe_remove_parent_links( 'title_li=&child_of=' . $parent_id . '&echo=0&sort_column=menu_order&post_type=' . $post->post_type, $background_style );
				if ( $children ) {
					echo $children; //phpcs:ignore
				}
				lfe_get_other_events( $parent_id, $background_style );
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

			<!-- hardcoded speakers list -->
			<div class="entry-content">
				<section class="speakers-section wp-block-ugb-container alignfull"
					style="background: linear-gradient(90deg, <?php echo esc_html( $menu_color ); ?> 0%, <?php echo esc_html( $menu_color_2 ); ?> 100%); <?php echo esc_html( $text_style ); ?>">
					<h3 class="speakers-section-title">Featured Speakers</h3>
					<ul class="speaker-list grid-x">
						<?php
						$speaker_array = array( 16, 11, 5, 17, 25, 64, 38, 68, 22, 55, 41 );
						foreach ( $speaker_array as &$value ) {
							if ( array_search( $value, $speaker_array ) % 2 == 0 ) {
								$rotation = '-45deg';
							} else {
								$rotation = '135deg';
							}
							?>
								<li id="speaker-<?php echo $value; ?>"
										class="speaker cell small-6 medium-4 xxlarge-3"
										style="background: linear-gradient(<?php echo $rotation; ?>, <?php echo $menu_color; ?> 0%, <?php echo $menu_color_2; ?> 100%);"
										data-toggler=".open"
									>
									<div class="grid-x">
										<div class="cell large-5">
											<div class="headshot" style="background-image:url(https://i.pravatar.cc/600?img=<?php echo $value; ?>);" data-toggle="speaker-<?php echo $value; ?>"></div>
										</div>
										<div class="text cell large-7">
											<a class="name" data-toggle="speaker-<?php echo $value; ?>">Firstname Lastname</a>
											<a class="title" data-toggle="speaker-<?php echo $value; ?>">Impressive Title</a>
											<div class="bio">
												<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint, vero magni nostrum aliquam rem odio, neque sequi iusto repudiandae quidem quam blanditiis distinctio magnam doloribus sapiente ad, ipsa velit! Harum?</p>
											</div>
											<ul class="social-media-links">
												<li><a><svg class="social-icon--twitter" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></a></li>
												<li><a><svg class="social-icon--linkedin" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/></svg></a></li>
												<li><a><svg class="social-icon--website" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="fill:<?php echo $menu_text_color; ?>;"><path d="M448 80v352c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V80c0-26.51 21.49-48 48-48h352c26.51 0 48 21.49 48 48zm-88 16H248.029c-21.313 0-32.08 25.861-16.971 40.971l31.984 31.987L67.515 364.485c-4.686 4.686-4.686 12.284 0 16.971l31.029 31.029c4.687 4.686 12.285 4.686 16.971 0l195.526-195.526 31.988 31.991C358.058 263.977 384 253.425 384 231.979V120c0-13.255-10.745-24-24-24z"/></svg></a></li>
												<li><a><svg class="social-icon--instagram" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a></li>
												<!-- <li><a><svg class="social-icon--medium" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M71.5 142.3c.6-5.9-1.7-11.8-6.1-15.8L20.3 72.1V64h140.2l108.4 237.7L364.2 64h133.7v8.1l-38.6 37c-3.3 2.5-5 6.7-4.3 10.8v272c-.7 4.1 1 8.3 4.3 10.8l37.7 37v8.1H307.3v-8.1l39.1-37.9c3.8-3.8 3.8-5 3.8-10.8V171.2L241.5 447.1h-14.7L100.4 171.2v184.9c-1.1 7.8 1.5 15.6 7 21.2l50.8 61.6v8.1h-144v-8L65 377.3c5.4-5.6 7.9-13.5 6.5-21.2V142.3z"/></svg></a></li> -->
												<!-- <li><a><svg class="social-icon--qq" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M433.754 420.445c-11.526 1.393-44.86-52.741-44.86-52.741 0 31.345-16.136 72.247-51.051 101.786 16.842 5.192 54.843 19.167 45.803 34.421-7.316 12.343-125.51 7.881-159.632 4.037-34.122 3.844-152.316 8.306-159.632-4.037-9.045-15.25 28.918-29.214 45.783-34.415-34.92-29.539-51.059-70.445-51.059-101.792 0 0-33.334 54.134-44.859 52.741-5.37-.65-12.424-29.644 9.347-99.704 10.261-33.024 21.995-60.478 40.144-105.779C60.683 98.063 108.982.006 224 0c113.737.006 163.156 96.133 160.264 214.963 18.118 45.223 29.912 72.85 40.144 105.778 21.768 70.06 14.716 99.053 9.346 99.704z"/></svg></a></li> -->
												<!-- <li><a><svg class="social-icon--xing" style="fill:<?php echo $menu_text_color; ?>;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M162.7 210c-1.8 3.3-25.2 44.4-70.1 123.5-4.9 8.3-10.8 12.5-17.7 12.5H9.8c-7.7 0-12.1-7.5-8.5-14.4l69-121.3c.2 0 .2-.1 0-.3l-43.9-75.6c-4.3-7.8.3-14.1 8.5-14.1H100c7.3 0 13.3 4.1 18 12.2l44.7 77.5zM382.6 46.1l-144 253v.3L330.2 466c3.9 7.1.2 14.1-8.5 14.1h-65.2c-7.6 0-13.6-4-18-12.2l-92.4-168.5c3.3-5.8 51.5-90.8 144.8-255.2 4.6-8.1 10.4-12.2 17.5-12.2h65.7c8 0 12.3 6.7 8.5 14.1z"/></svg></a></li> -->
											</ul>
										</div>
									</div>
								</li>
						<?php } ?>
					</ul>
				</section>
			</div>

		</main>
		<?php /* get_sidebar(); */ ?>
	</div>
</div>
<?php
get_footer();
