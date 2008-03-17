<?php
get_header();
?>

<div id="oc-content-main">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="post fullpost <?php if (in_category(47)) : ?>heds<?php endif; ?>" id="post-<?php the_ID(); ?>">

      <h2 class="title"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent link to &ldquo;<?php the_title(); ?>&rdquo;"><?php the_title(); ?></a></h2>
      </p>
      <p class="credit">
       by <?php the_author_posts_link(); ?> on  <abbr class="date" title="<?php the_time('c') ?>"><?php the_time('F j, Y') ?></abbr> <?php comments_popup_link(__('0'), __('1'), __('%'), __('commentcount')); ?>
</p>  
      <div class="entry">
        <?php the_content("Continue reading &ldquo;" . the_title('', '', false) . "&rdquo;&nbsp;&raquo;"); ?>
      </div>
      
      <?php if (get_post_custom_values('Actions')) : ?>
        <div class="postactions">
          <h3>Take Action:</h3>
          <div class="postactions-content">
          <?php echo get_post_meta($post->ID, 'Actions', true); ?>
          </div>
        </div><!-- end .postactions -->
      <?php endif; ?>
      <div class="postmetadata metaText clearAfter">
        <div class="postcategories">
          Categories: <?php the_category(', ', ''); ?>
        </div>
        <div class="postshare">
          <?php comments_popup_link(__('No Comments &#187;'), __('1 Comment'), __('% Comments'), __('commentcount')); ?><?php the_last_commenter(' latest by ') ?>
          <?php edit_post_link('Edit', '', ''); ?>
        </div>
      </div>
    </div>

<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile;  ?>
<?php elseif (is_home()) : /* home and no posts -- show blank slate */ ?>
  <?php if (current_user_can('edit_posts')) : ?>
  <h2>Welcome to your blog!</h2>

  <p>You haven't written any posts yet. <a href="wp-admin/post-new.php">Write a post &raquo;</a></p>
  <?php elseif (is_user_logged_in()) : ?>
  <p>There aren't any blog posts yet.  Come back soon!</p>
  <?php else :?>
  <!-- not logged in.  Should have a greyed-out new post thingy   -->
  <p>There aren't any blog posts yet.  If you're a member of this project, please <a href="wp-admin/post-new.php">log in and write some</a>.</p>
  <?php endif; ?>
<?php else : ?>
  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>

<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

</div><!-- end #oc-blog-main -->

<?php get_footer(); ?>
