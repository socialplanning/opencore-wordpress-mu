<?php
get_header();
?>

<div id="oc-content-main">


<h1>
Sorry!
</h1>

<?php if (!current_user_can('publish_posts')) :?>
  <p>The blog for this project has been disabled.  You can enable it by updating your <a href="../preferences">project preferences.</a></p>
<?php else:  ?>
  <p>There is no blog associated with this project.  Return to the <a href="../">project homepage</a>.</p>
<?php endif ?>



<div class="oc-blog-post oc-clearAfter" id="post-<?php the_ID(); ?>">

</div>

</div><!-- end #oc-blog-main -->
