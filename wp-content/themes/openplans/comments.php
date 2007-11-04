<?php if ( !empty($post->post_password) && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) : ?>
<p><?php _e('Enter your password to view comments.'); ?></p>
<?php return; endif; ?>

<div class="oc-blog-comments">
  <div class="oc-blog-headingBlock oc-blog-comments oc-clearAfter">
    <?php if ( comments_open() ) : ?>
    <div style="float: right;"><a href="#postcomment" title="<?php _e("Leave a comment"); ?>">Leave a comment &darr;</a></div>
    <?php endif; ?>
    <h3 id="comments"><?php comments_number(__('No Comments'), __('1 Comment'), __('% Comments')); ?></h3>
    <span class="oc-headingContext oc-discreetText"><?php comments_rss_link(__('<abbr class="oc-button-rss" title="Really Simple Syndication">RSS</abbr>')); ?></span>
  </div>
  
  <?php if ( $comments ) : ?>
  <ol id="commentlist" class="oc-feed-list">
  
  <?php foreach ($comments as $comment) : ?>
  
  <li id="comment-<?php comment_ID() ?>" class="oc-feed-item">
    <?php comment_text() ?>
    <p><cite class="oc-discreetText"><?php comment_type(__('Comment'), __('Trackback'), __('Pingback')); ?> <?php _e('by'); ?> <?php comment_author_link() ?> on <?php comment_date() ?> at <a href="#comment-<?php comment_ID() ?>"><?php comment_time() ?></a></cite> <?php edit_comment_link(__("Edit"), ' | '); ?>
    </p>
    <?php if ($comment->comment_approved == '0') : ?>
      <span class="oc-statusMessage">Your comment is awaiting moderation.</span>
    <?php endif; ?>
    </li>
  
  <?php endforeach; ?>
  
  </ol>
</div>
  
<?php else : // If there are no comments yet ?>
	<p><?php _e('No comments yet.'); ?></p>
<?php endif; ?>

<div class="oc-boxy">
<?php if ( comments_open() ) : ?>
<h3 id="postcomment"><?php _e('Leave a comment'); ?></h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be logged in to post a comment.</p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<?php else : ?>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"> <span class="oc-discreetText">Name <?php if ($req) _e('(required)'); ?></span></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"> <span class="oc-discreetText">Mail (will not be published) <?php if ($req) _e('(required)'); ?></span></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"> <span class="oc-discreetText">Website</span></label></p>

<?php endif; ?>

<?php 
/****** Math Comment Spam Protection Plugin ******/
if ( function_exists('math_comment_spam_protection') && (!is_user_logged_in())) { 
	$mcsp_info = math_comment_spam_protection();
?> 	<p><input type="text" name="mcspvalue" id="mcspvalue" value="" size="22" tabindex="4" />
	<label for="mcspvalue" style="font-size: 1.3em; font-weight: bold;"><small>Spam protection: Sum of <?php echo $mcsp_info['operand1'] . ' + ' . $mcsp_info['operand2'] . ' ?' ?></small></label>
	<input type="hidden" name="mcspinfo" value="<?php echo $mcsp_info['result']; ?>" />
</p>
<?php } // if function_exists... 
?>

<!--<p><span class="oc-discreetText"><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></span></p>-->

<p><textarea name="comment" id="comment" cols="80" rows="10" tabindex="4" style="width: 500px;"></textarea></p>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit comment" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php else : // Comments are closed ?>
<p><?php _e('Sorry, the comment form is closed at this time.'); ?></p>
<?php endif; ?>

</div>

<?php if ( pings_open() ) : ?>
  <div class="oc-boxy">
    <label for="trackback">Trackback:</label> <input name="trackback" type="text" size="60" value="<?php trackback_url() ?>" />
  </div>
<?php endif; ?>
