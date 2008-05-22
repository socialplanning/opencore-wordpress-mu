<?php
get_header();
?>

<div id="content">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<?php 
# Determine whether we should be showing the full post or not.
# 
$use_full_post = ($counter < 10 && $wp_query->query_vars['paged'] < 2) ? true : false;
?>
<div id="post-<?php the_ID(); ?>" class="post fullpost <?php if (in_category(47)) : ?> headlines<?php endif; ?>">
  <div class="post-header selfclear">
    <abbr class="post-date" title="<?php the_time('c') ?>"><?php the_time('F j, Y') ?></abbr> <?php comments_popup_link(__('No Comments Yet'), __('1 Comment'), __('% Comments'), __('post-comment-count')); ?>
  </div><!-- /.post-header -->
  <div class="post-content">
    <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="Permalink to &ldquo;<?php the_title(); ?>&rdquo;" rel="bookmark"><?php the_title(); ?></a><?php edit_post_link('Edit', ' (', ')'); ?></h2>
    <p class="post-author">by <?php the_author_posts_link(); ?></p>
    <div class="post-entry">
      <?php ($use_full_post) ? the_content("Continue reading &raquo;") : the_excerpt($excerpt_length=120, $allowedtags='<p><ul><li><img><span><div><a><br><br />', $filter_type='none', $use_more_link=true, $more_link_text="(more...)", $force_more=true, $fakeit=1, $fix_tags=true, $no_more=false, $more_tag='div', $more_link_title='Continue reading this entry', $showdots=true); ?>
    </div><!-- /.post-entry -->
  </div><!-- /.post-content -->
  <?php if (!is_single()) : ?>
  <div class="post-footer">
    <div class="selfclear">
      <?php comments_popup_link(__('No Comments'), __('1 Comment'), __('% Comments'), __('post-comment-count')); ?>
    </div>
    <div class="selfclear even">
      <span class="post-categories">Categorized as: <?php the_category(', ', ''); ?></span>
    </div>
  </div><!-- /.post-footer -->
  <?php endif; ?>
</div><!-- /.post -->

<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile;  ?>
<?php elseif (is_home()) : /* home and no posts -- show blank slate */ ?>
  <?php if (current_user_can('edit_posts')) : ?>
  <h2>Welcome to your blog!</h2>
  <p>You haven't written any posts yet. <a href="wp-admin/post-new.php">Write a post &raquo;</a></p>
  <?php else :?>
  <p>There aren't any blog posts yet.  Come back soon!</p>
  <?php endif; ?>
<?php else : ?>
  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>

<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

</div><!-- end #oc-blog-main -->

<?php get_footer(); ?>
