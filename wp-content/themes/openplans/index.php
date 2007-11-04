<?php
get_header();
?>

<div id="oc-content-main">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="oc-blog-post oc-clearAfter" id="post-<?php the_ID(); ?>">
   <div class="oc-blog-headingBlock oc-blog-postTitle" style="position:relative; padding-right: 30px;">
     <div style="position:absolute;top 15px; right: 10px;"><?php edit_post_link(__('Edit')); ?></div>
     <h3 class="oc-blog-storytitle oc-biggestText"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
     <span class="oc-headingContext oc-discreetText">by <?php the_author_link() ?><!-- at <?php the_time() ?>--></span>
  </div>

  <div class="oc-blog-storycontent">
    <?php the_content(__('(more...)')); ?>
  </div>
  <div class="oc-blog-meta">
  <div class="oc-blog-categories oc-discreetText">
    <?php _e('Filed'); ?> <?php the_time('F jS, Y'); ?> <?php _e("under"); ?> <?php the_category(',') ?>
  </div>
  <div class="oc-blog-feedback oc-discreetText">
    <?php wp_link_pages(); ?>
    <?php comments_popup_link(__('Comments (0)'), __('Comments (1)'), __('Comments (%)')); ?>
  </div>
  </div>
</div>

<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile; else: ?>
  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>

<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

</div><!-- end #oc-blog-main -->

<?php get_footer(); ?>
