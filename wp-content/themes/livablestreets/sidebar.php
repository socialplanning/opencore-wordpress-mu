<ul id="sidebar">
 <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar()) { ?>
    <!--<li id="searchform-area" class="widget">
	<div class="widget-content unbordered">		
	<form id="searchform" action="<?php bloginfo('home'); ?>" method="get">	
    <input type="text" name="s" class="text" value="<?php _e('Search this blog'); ?>" id="s" size="25"  onblur="if(this.value=='') this.value='Search this blog';" onfocus="if(this.value=='Search this blog') this.value='';"	/><input id="search-submit" class="image" type="image" alt="Search" src="/++resource++woonerf/img/search.png" />	
</form>	
	</div>
  </li>-->
  <?php if (current_user_can('publish_posts')) : ?>
  <li class="widget unbordered">
    <div class="oc-getstarted selfclear">
    <a href="<?php bloginfo('home'); ?>/wp-admin/post-new.php" class="oc-banana">Write a post</a>
    <p style="text-align: center">
      <a href="<?php bloginfo('home'); ?>/wp-admin/">Administer your blog</a>
    </p>
    </div>
  </li>
  <?php elseif (current_user_can('edit_posts')) : ?>
  <li class="widget unbordered">
    <div class="oc-getstarted selfclear">
    <a href="<?php bloginfo('home'); ?>/wp-admin/post-new.php" class="oc-banana">Submit a post</a>
    <p style="text-align: center">
      <a href="<?php bloginfo('home'); ?>/wp-admin/">Administer your blog</a>
    </p>
    </div>
  </li>
  <?php endif; ?>
  <li class="widget">
    <div class="widget-header">
    <h2>Categories</h2>
    </div>
    <div class="widget-content selfclear">
    <ul class="oc-plainList">
      <?php wp_list_categories('title_li='); ?>
    </ul>
    </div>
  </li>
  <li class="widget">
    <div class="widget-header">
      <h2><?php _e('Archives'); ?></h2>
    </div>
    <div class="widget-content selfclear">
      <ul class="oc-plainList">
        <?php wp_get_archives('type=monthly'); ?>
      </ul>
    </div>
  </li>

<!--
  <?php wp_list_bookmarks('title_li=&title_before=<div class=widget-header><h2>&title_after=</h2></div><div class=widget-content><ul>&class=widget'); 
  
  # [TODO] wp_list_bookmarks appears to generate unclosed markup - explore ways to not require these external closing div and li tags
  ?>
  </div>
  </li>
-->

  <li class="widget">
    <div class="widget-header">
      <h2><?php _e('Syndicate'); ?></h2>
          </div>
    <div class="widget-content selfclear">
    <ul class="oc-plainList">
      <li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php _e('Syndicate this site using RSS'); ?>"><?php _e('Articles <abbr title="Really Simple Syndication">RSS</abbr> Feed'); ?></a></li>
      <li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php _e('The latest comments to all posts in RSS'); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr> Feed'); ?></a></li>
      <?php wp_meta(); ?>
    </ul>
  </div>
  </li>
<?php } ?>
</ul><!-- /#sidebar -->

