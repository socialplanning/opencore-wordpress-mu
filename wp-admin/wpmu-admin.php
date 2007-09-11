<?php
require_once('admin.php');

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if( is_site_admin() == false ) {
    die( __('<p>You do not have permission to access this page.</p>') );
}
if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}
?>
<div class="wrap">
<?php

do_action( "wpmuadminresult", "" );

switch( $_GET[ 'action' ] ) {
    default:
    ?>
<form name="searchform" action="wpmu-users.php" method="get">
<p>
<input name="action" value="users" type="hidden" />
<input name="s" value="" size="17" type="text" /> 
<input name="submit" value="<?php _e("Search Users &raquo;"); ?>" type="submit" />
</p> 
</form>

<form name="searchform" action="wpmu-blogs.php" method="get">
<p>
<input type='hidden' name='action' value='blogs' />
<input type="text" name="s" value="" size="17" />
<input type="submit" name="submit" value="<?php _e("Search Blogs &raquo;"); ?>" />
</p>
</form>
<?php
    break;
}

?>
</div>
<?php include('admin-footer.php'); ?>
