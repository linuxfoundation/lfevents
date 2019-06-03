<?php
/**
 * @package   Deep_Duplicate_Page
 *
 * Plugin Name:       Deep Duplicate Page
 * Plugin URI:        https://github.com/LF-Engineering/lfevents/tree/master/web/wp-content/plugins/deep-duplicate-page
 * Description:       Adds Deep Duplicate action to the page row actions.  A deep duplicate of a page duplicates the page and all children creating a new copy of the full page tree.
 * Version:           0.1.0
 * Author:            cjyabraham
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * Function creates page deep duplicate 
 */
function ddp_deep_duplicate_page(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'ddp_deep_duplicate_page' == $_REQUEST['action'] ) ) ) {
		wp_die('No page to duplicate has been supplied!');
	}
 
	/*
	 * Nonce verification
	 */
	if ( !isset( $_GET['deep_duplicate_nonce'] ) || !wp_verify_nonce( $_GET['deep_duplicate_nonce'], basename( __FILE__ ) ) )
		return;
 
	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );
 
	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {
 
		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );
 
		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		/*
		 * duplicate all post meta just in two SQL queries
		 */
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				if( $meta_key == '_wp_old_slug' ) continue;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
 
 
		/*
		 * finally, redirect to the edit post screen for the new draft
		 */
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die('Page creation failed, could not find original page: ' . $post_id);
	}
}
add_action( 'admin_action_ddp_deep_duplicate_page', 'ddp_deep_duplicate_page' );
 
/*
 * Add the duplicate link to action list for page_row_actions
 */
function ddp_duplicate_page_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		$actions['deep_duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=ddp_deep_duplicate_page&post=' . $post->ID, basename(__FILE__), 'deep_duplicate_nonce' ) . '" title="Deep Duplicate this item" rel="permalink">Deep Duplicate</a>';
	}
	return $actions;
}
 
add_filter( 'page_row_actions', 'ddp_duplicate_page_link', 10, 2 );
