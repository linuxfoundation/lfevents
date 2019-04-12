<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<?php /* get_template_part( 'template-parts/featured-image' ); */ ?>

<div data-sticky-container>
	<header class="event-header sticky" data-sticky data-options="marginTop:0;" >
		<button class="menu-toggler button float-right hide-for-large" data-toggle="event-menu">
			<span class="hamburger-icon"></span>
		</button>

		<nav id="event-menu" class="event-menu show-for-large" data-toggler="show-for-large">
			<div class="button-group expanded stacked-for-medium">
				<a href="#" class="button" style="min-width:16rem;">
					<img style="max-height:3rem;" src="https://events.linuxfoundation.org/wp-content/uploads/2018/12/OSSNA_Logo_800x161-01.png" />
				</a>
				<a class="button dropdown" href="#" data-toggle="example-dropdown-1">Attend</a>
				<div class="dropdown-pane" id="example-dropdown-1" data-dropdown data-hover="true" data-hover-pane="true">
					<ul class="menu vertical">
						<li><a href="#">Diversity and Inclusion</a></li>
						<li><a href="#">Child Care</a></li>
						<li><a href="#">Diversity Scholarships</a></li>
						<li><a href="#">Convince Your Boss</a></li>
						<li><a href="#">Code of Conduct</a></li>
					</ul>
				</div>
				<a class="button" href="#">Sponsors</a>
				<a class="button" href="#">Venue + Travel</a>
				<a class="button dropdown" href="#" data-toggle="dropdown-2">Program</a>
				<div class="dropdown-pane" id="dropdown-2" data-dropdown data-hover="true" data-hover-pane="true">
					<ul class="menu vertical">
						<li><a href="#">Schedule</a></li>
						<li><a href="#">Pre-Conference</a></li>
						<li><a href="#">Co-Located Events</a></li>
						<li><a href="#">Program Co-Chairs</a></li>
						<li><a href="#">Training and Exams</a></li>
					</ul>
				</div>
				<a class="button" href="#">Register</a>
				<a class="button" href="#">Other LF Events</a>
			</div>
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
				<?php comments_template(); ?>
			<?php endwhile; ?>
		</main>
		<?php /* get_sidebar(); */ ?>
	</div>
</div>
<?php
get_footer();
