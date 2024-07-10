<?php
/**
 * FoundationPress Comments
 *
 * @package FoundationPress
 */

if ( ! class_exists( 'Foundationpress_Comments' ) ) :
	/**
	 * Comment
	 */
	class Foundationpress_Comments extends Walker_Comment {

		/**
		 * Init classwide variables.
		 *
		 * @var string $tree_type
		 */
		public $tree_type = 'comment';

		/**
		 * Comment ID.
		 *
		 * @var array $db_fields
		 */
		public $db_fields = array(
			'parent' => 'comment_parent',
			'id'     => 'comment_ID',
		);

		/** CONSTRUCTOR
		 * You'll have to use this if you plan to get to the top of the comments list, as
		 * start_lvl() only goes as high as 1 deep nested comments */
		function __construct() { //phpcs:ignore ?>

			<h3><?php comments_number( __( 'No Responses to', 'foundationpress' ), __( 'One Response to', 'foundationpress' ), __( '% Responses to', 'foundationpress' ) ); ?> &#8220;<?php the_title(); ?>&#8221;</h3>
			<ol class="comment-list">

			<?php
		}

		/** START_LVL
		 * Starts the list before the CHILD elements are added.
		 *
		 * @param string $output Comment.
		 * @param int    $depth Comment.
		 * @param array  $args Comment.
		 */
		function start_lvl( &$output, $depth = 0, $args = array() ) { //phpcs:ignore
			$GLOBALS['comment_depth'] = $depth + 1; //phpcs:ignore
			?>

					<ul class="children">
			<?php
		}

		/** END_LVL
		 * Ends the children list of after the elements are added.
		 *
		 * @param string $output Comment.
		 * @param int    $depth Comment.
		 * @param array  $args Comment.
		 */
		function end_lvl( &$output, $depth = 0, $args = array() ) { //phpcs:ignore
			$GLOBALS['comment_depth'] = $depth + 1; //phpcs:ignore
			?>

			</ul><!-- /.children -->

			<?php
		}

		/**
		 * START_EL.
		 *
		 * @param string $output Comment.
		 * @param string $comment Comment.
		 * @param int    $depth Comment.
		 * @param array  $args Comment.
		 * @param int    $id Comment.
		 */
		function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) { //phpcs:ignore
			++$depth;
			$GLOBALS['comment_depth'] = $depth; //phpcs:ignore
			$GLOBALS['comment']       = $comment; //phpcs:ignore
			$parent_class             = ( empty( $args['has_children'] ) ? '' : 'parent' );
			?>

			<li <?php comment_class( $parent_class ); ?> id="comment-<?php comment_ID(); ?>">
				<article id="comment-body-<?php comment_ID(); ?>" class="comment-body">



			<header class="comment-author">

				<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>

				<div class="author-meta vcard author">

				<?php
				/* translators: %s: comment author link */
				printf(
					__( '<cite class="fn">%s</cite>', 'foundationpress' ), //phpcs:ignore
					get_comment_author_link()
				);
				?>
				<time datetime="<?php echo comment_date( 'c' ); //phpcs:ignore ?>"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><?php printf( get_comment_date(), get_comment_time() ); ?></a></time>

			</div><!-- /.comment-author -->

			</header>

				<section id="comment-content-<?php comment_ID(); ?>" class="comment">
					<?php if ( ! $comment->comment_approved ) : ?>
							<div class="notice">
					<p class="bottom"><?php _e( 'Your comment is awaiting moderation.', 'foundationpress' ); //phpcs:ignore?></p>
				</div>
						<?php
					else :
						comment_text();
						?>
					<?php endif; ?>
				</section><!-- /.comment-content -->

				<div class="comment-meta comment-meta-data hide">
					<a href="<?php echo htmlspecialchars( get_comment_link( get_comment_ID() ) ); //phpcs:ignore?>"><?php comment_date(); ?> at <?php comment_time(); ?></a> <?php edit_comment_link( '(Edit)' ); ?>
				</div><!-- /.comment-meta -->

				<div class="reply">
					<?php
					$reply_args = array(
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
					);

						comment_reply_link( array_merge( $args, $reply_args ) );
					?>
					</div><!-- /.reply -->
				</article><!-- /.comment-body -->

			<?php
		}

		/**
		 * Comment
		 *
		 * @param string $output Comment.
		 * @param string $comment Comment.
		 * @param int    $depth Comment.
		 * @param array  $args Comment.
		 */
		function end_el( & $output, $comment, $depth = 0, $args = array() ) { //phpcs:ignore
			?>

			</li><!-- /#comment-' . get_comment_ID() . ' -->

			<?php
		}

		/** DESTRUCTOR */
		function __destruct() { //phpcs:ignore
			?>

		</ol><!-- /#comment-list -->

			<?php
		}
	}
endif;
