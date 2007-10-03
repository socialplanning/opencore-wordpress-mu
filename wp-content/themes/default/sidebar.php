
<!-- begin sidebar -->
<div id="oc-blog-sidebar">

<?php 	/* Widgetized sidebar, if you have the plugin installed. */
		if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
		
  <h3>Pages</h3>
  <ul>
	<?php wp_list_pages('title_li='); ?>
	</ul>
	
	<h3>Blogroll</h3>
	<ul>
	<?php wp_list_bookmarks('title_li='); ?>
	</ul>
	
	<h3>Categories</h3>
	<ul>
	<?php wp_list_categories('title_li='); ?>
  </ul>
  
 <div id="search">
   <label for="s"><?php _e('Search:'); ?></label>
   <form id="searchform" method="get" action="<?php bloginfo('home'); ?>">
	<div>
		<input type="text" name="s" id="s" size="15" /><br />
		<input type="submit" value="<?php _e('Search'); ?>" />
	</div>
	</form>
 </div>
 <div id="archives"><?php _e('Archives:'); ?>
	<ul>
	 <?php wp_get_archives('type=monthly'); ?>
	</ul>
 </div>
 <div id="meta"><?php _e('Meta:'); ?>
	<ul>
		<?php wp_register(); ?>
		<li><?php wp_loginout(); ?></li>
		<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php _e('Syndicate this site using RSS'); ?>"><?php _e('<abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
		<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php _e('The latest comments to all posts in RSS'); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
		<li><a href="http://validator.w3.org/check/referer" title="<?php _e('This page validates as XHTML 1.0 Transitional'); ?>"><?php _e('Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr>'); ?></a></li>
		<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
		<li><a href="http://wordpress.org/" title="<?php _e('Powered by WordPress, state-of-the-art semantic personal publishing platform.'); ?>"><abbr title="WordPress">WP</abbr></a></li>
		<?php wp_meta(); ?>
	</ul>
 </div>
<?php endif; ?>


</div>
<!-- end sidebar -->
