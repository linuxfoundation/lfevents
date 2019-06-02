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
					style="background: linear-gradient(90deg, <?php echo esc_html( $menu_color ) ?> 0%, <?php echo esc_html( $menu_color_2 ) ?> 100%); <?php echo esc_html( $text_style ); ?>">
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
								<li id="speaker-<?php echo $value ?>"
										class="speaker cell small-6 medium-4 xxlarge-3"
										style="background: linear-gradient(<?php echo $rotation ?>, <?php echo $menu_color ?> 0%, <?php echo $menu_color_2 ?> 100%);"
										data-toggler=".open"
									>
									<div class="grid-x">
										<div class="cell large-5">
											<div class="headshot" style="background-image:url(https://i.pravatar.cc/600?img=<?php echo $value ?>);" data-toggle="speaker-<?php echo $value ?>"></div>
										</div>
										<div class="text cell large-7">
											<a class="name" data-toggle="speaker-<?php echo $value ?>">Firstname Lastname</a>
											<a class="title" data-toggle="speaker-<?php echo $value ?>">Impressive Title</a>
											<div class="bio">
												<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sint, vero magni nostrum aliquam rem odio, neque sequi iusto repudiandae quidem quam blanditiis distinctio magnam doloribus sapiente ad, ipsa velit! Harum?</p>
											</div>
											<ul class="social-media-links">
												<li><a><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="fill:<?php echo $menu_text_color; ?>;"><path d="M400 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-48.9 158.8c.2 2.8.2 5.7.2 8.5 0 86.7-66 186.6-186.6 186.6-37.2 0-71.7-10.8-100.7-29.4 5.3.6 10.4.8 15.8.8 30.7 0 58.9-10.4 81.4-28-28.8-.6-53-19.5-61.3-45.5 10.1 1.5 19.2 1.5 29.6-1.2-30-6.1-52.5-32.5-52.5-64.4v-.8c8.7 4.9 18.9 7.9 29.6 8.3a65.447 65.447 0 0 1-29.2-54.6c0-12.2 3.2-23.4 8.9-33.1 32.3 39.8 80.8 65.8 135.2 68.6-9.3-44.5 24-80.6 64-80.6 18.9 0 35.9 7.9 47.9 20.7 14.8-2.8 29-8.3 41.6-15.8-4.9 15.2-15.2 28-28.8 36.1 13.2-1.4 26-5.1 37.8-10.2-8.9 13.1-20.1 24.7-32.9 34z"/></svg></a></li>
												<li><a><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="fill:<?php echo $menu_text_color; ?>;"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/></svg></a></li>
												<li><a><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="fill:<?php echo $menu_text_color; ?>;"><path d="M448 80v352c0 26.51-21.49 48-48 48H48c-26.51 0-48-21.49-48-48V80c0-26.51 21.49-48 48-48h352c26.51 0 48 21.49 48 48zm-88 16H248.029c-21.313 0-32.08 25.861-16.971 40.971l31.984 31.987L67.515 364.485c-4.686 4.686-4.686 12.284 0 16.971l31.029 31.029c4.687 4.686 12.285 4.686 16.971 0l195.526-195.526 31.988 31.991C358.058 263.977 384 253.425 384 231.979V120c0-13.255-10.745-24-24-24z"/></svg></a></li>
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
