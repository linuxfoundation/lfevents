<?php
/**
 * Search & Filter Pro Results Template
 * This file determines which results template file to load.
 *
 * @package   Search_Filter
 * @author    Ross Morsali
 * @link      https://searchandfilter.com
 * @copyright 2018 Search & Filter
 *
 * Note: these templates are not full page templates, rather
 * just an encaspulation of the your results loop which should
 * be inserted in to other pages by using a shortcode - think
 * of it as a template part
 *
 * This template is an absolute base example showing you what
 * you can do, for more customisation see the WordPress docs
 * and using template tags -
 *
 * http://codex.wordpress.org/Template_Tags
 */

if ( is_page_template( 'page-templates/events-calendar.php' ) ) {
	require get_template_directory() . '/search-filter/events-calendar.php';
} elseif ( is_page_template( 'page-templates/community-events.php' ) ) {
	require get_template_directory() . '/search-filter/community-events.php';
}
