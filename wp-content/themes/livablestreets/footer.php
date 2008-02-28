<!-- begin footer -->
</div>

<?php get_sidebar(); ?>

<div id="oc-blog-footer">

<p class="oc-blog-credit oc-discreetText"><!--<?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds. --> <cite><?php echo sprintf(__("Blogs on this site are powered by <a href='http://mu.wordpress.org/' title='%s'>WordPress MU</a>."), __("Powered by WordPress, state-of-the-art semantic personal publishing platform.")); ?></cite></p>

</div><!-- end #oc-blog-footer -->

</div><!-- end #oc-blog-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
