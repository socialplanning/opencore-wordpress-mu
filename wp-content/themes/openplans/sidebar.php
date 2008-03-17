
<!-- begin sidebar -->
<div id="oc-content-sidebar">
<?php   /* Widgetized sidebar, if you have the plugin installed. */
    if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
    
  <?php if (current_user_can('publish_posts')) : ?>
  <div class="oc-getstarted">
    <a href="<?php bloginfo('home'); ?>/wp-admin/post-new.php" class="oc-banana">Write a post</a>
    <p style="text-align: center">
      <a href="<?php bloginfo('home'); ?>/wp-admin/">Administer your blog</a>
    </p>
  </div>
  <?php elseif (current_user_can('edit_posts')) : ?>
  <div class="oc-getstarted">
    <a href="<?php bloginfo('home'); ?>/wp-admin/post-new.php" class="oc-banana">Submit a post</a>
    <p style="text-align: center">
      <a href="<?php bloginfo('home'); ?>/wp-admin/">Administer your blog</a>
    </p>
  </div>
  <?php elseif (is_user_logged_in()) : ?>
  <div class="oc-getstarted">
    Only members of this project can write posts.  <a href="../request-membership">Request membership to this project.</a>
  </div>
  <?php else : ?>
  <!-- actually, what should this say? "Write a post"?  "Submit a post"?  "Administer this blog"? -->
  <?php endif; ?>

  <div id="search" class="oc-boxy">
   <form id="searchform" method="get" action="<?php bloginfo('home'); ?>">
   <h2><label for="s""><?php _e('Search this blog'); ?></label></h2>
    <span class="oc-form-fieldBlock">
    <input type="text" name="s" id="s" size="33" />&nbsp;
    <input type="submit" value="<?php _e('Search'); ?>" />
    </span>
  </form>
  </div>
    
  <div class="oc-boxy">
    <h2>Categories</h2>
    <ul class="oc-plainList">
      <?php wp_list_categories('title_li='); ?>
    </ul>
    <div id="archives">
      <h2><?php _e('Archives'); ?></h2>
      <ul class="oc-plainList">
        <?php wp_get_archives('type=monthly'); ?>
      </ul>
    </div>
  </div>

  <div class="oc-boxy oc-plainList">
 	  <?php wp_list_bookmarks('title_li='); ?>
  </div>
  
  <div id="meta" class="oc-boxy">
    <h2><?php _e('Feeds'); ?></h2>
    <ul class="oc-plainList">
      <li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php _e('Syndicate this site using RSS'); ?>"><?php _e('Articles <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
      <li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php _e('The latest comments to all posts in RSS'); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
      <?php wp_meta(); ?>
    </ul>
  </div>
<?php endif; ?>


</div>
<!-- end sidebar -->
