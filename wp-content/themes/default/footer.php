<!-- begin footer -->
</div>

<?php get_sidebar(); ?>

<div id="oc-blog-footer">

<p class="oc-blog-credit"><!--<?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds. --> <cite><?php echo sprintf(__("Powered by <a href='http://wordpress.org/' title='%s'><strong>WordPress</strong></a>"), __("Powered by WordPress, state-of-the-art semantic personal publishing platform.")); ?></cite></p>

</div><!-- end #oc-blog-footer -->

</div><!-- end #oc-blog-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
