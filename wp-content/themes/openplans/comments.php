<?php // Do not delete these lines
  if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
    die ('Please do not load this page directly. Thanks!');

  if (!empty($post->post_password)) { // if there's a password
    if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
      ?>

      <p class="nocomments">This post is password protected. Enter the password to view comments.</p>

      <?php
      return;
    }
  }
?>

<!-- You can start editing here. -->

<h3 id="comments"><?php comments_number('No Comments', 'One Comment', '% Comments' );?>
<?php if ( comments_open() ) : ?>
	(<a href="#respond" title="<?php _e("Leave a comment"); ?>">leave a comment</a>)
<?php endif; ?>
</h3>
<?php if ($comments) : ?>
  <?php
  /* get the author email for this article and assign to a variable to prevent multiple evaluations */
  $author_url = get_the_author_url();
  ?>
  <ol class="commentlist">
  <?php foreach ($comments as $key => $comment) : ?>
  <?php
    /* Assigns 'author' as a class if the poster's url matches the author's url. 
      [FIXME] This seems naive, as users could mischievously match a known url to appear to be the author.
    */
    $authorcommentclass = ($comment->comment_author_url == $author_url) ? ' authorcomment' : '';
    /* Changes every other comment to a different class */
    $oddcommentclass = ( empty( $oddcommentclass ) ) ? ' odd' : '';
  ?>
    <li class="comment clearAfter<?php echo $authorcommentclass; ?><?php echo $oddcommentclass; ?>" id="comment-<?php comment_ID() ?>">
      
      <div class="credit">
        <strong class="commentnumber" title="Comment #<?php echo $key+1 ?>"><?php echo $key+1 ?></strong>
        <!-- FIXME make the number above unique to avoid misnumbering when comments are deleted -->
	<?php global $Opencore_remote_url ?>
	<?php if (substr($comment->comment_author_url,0,strlen($Opencore_remote_url) + 7) == $Opencore_remote_url . 'people/') { ?>
        <img class="thumbnail" src="<?php echo substr($comment->comment_author_url,0,strlen($comment->comment_author_url) - 7) . 'portrait' ?>" width="80" height="80" alt="Post Thumbnail" />
	<?php } else { ?>
        <img class="thumbnail" src="<?php bloginfo('template_directory'); ?>/images/default-portrait-thumb.gif" width="80" height="80" alt="Post Thumbnail" />
	<?php } ?>
        <cite><?php comment_author_link() ?></cite>
      </div>
      <div class="commententry"><?php comment_text() ?>
        <div class="commentmetadata">
            <?php comment_date('F j, Y') ?> <?php comment_time() ?> <small><?php edit_comment_link('EDIT','',''); ?></small> <small><?php do_action("crown_link", $comment->comment_ID); ?></small>
        </div>
      </div>
      <?php if ($comment->comment_approved == '0') : ?>
      <br />
      <em>Your comment is awaiting moderation.</em>
      <?php endif; ?>
    </li>

  <?php endforeach; /* end for each comment */ ?>

  </ol>

 <?php else : // this is displayed if there are no comments so far ?>

  <?php if ('open' == $post->comment_status) : ?>
    <!-- If comments are open, but there are no comments. -->

   <?php else : // comments are closed ?>
    <!-- If comments are closed. -->
    <p class="nocomments">Comments are closed.</p>

  <?php endif; ?>
<?php endif; ?>


<?php if ('open' == $post->comment_status) : ?>

<h3 id="respond">Leave a Comment</h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>

<?php else : ?>

<div id="author_info">
<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"><small>Name <?php if ($req) echo "(required)"; ?><?php do_action("OC_OpenPlansLink")?></small></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><small>Mail (will not be published) <?php if ($req) echo "(required)"; ?></small></label></p>

<p class="hide"><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><small>Website</small></label></p>

</div>
<div id="openplans_info">
</div>

<?php endif; ?>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <code><?php echo allowed_tags(); ?></code></small></p>-->

<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
<?php do_action("OC_ProgressSpinner") ?>
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php endif; // if you delete this the sky will fall on your head ?>
