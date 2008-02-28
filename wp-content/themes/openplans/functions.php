<?php
if ( function_exists('register_sidebar') )
	register_sidebar(array(
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '',
        'after_title' => '',
    ));
function the_last_commenter($intro = "", $before = '', $after = '') { // inside the loop
	global $post, $wpdb;
	$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_post_ID = {$post->ID} AND comment_approved = '1' ORDER BY comment_ID DESC LIMIT 1");
	if ($comment) {
	  ?>
		<?php echo $before; ?><a href="<?php echo get_permalink($post->ID); ?>#comment-<?php echo $comment->comment_ID; ?>"><?php echo $intro . $comment->comment_author; ?></a><?php echo $after; ?>
	  <?php
	} else {
		return;
	}
}
?>
