<?php
@header('Content-type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
if (!isset($_GET["page"])) require_once('admin.php');
if ( $editing ) {
	wp_enqueue_script( array('dbx-admin-key?pagenow=' . attribute_escape($pagenow),'admin-custom-fields') );
	if ( current_user_can('manage_categories') )
		wp_enqueue_script( 'ajaxcat' );
	if ( user_can_richedit() )
		wp_enqueue_script( 'wp_tiny_mce' );
}

get_admin_page_title();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>

<!-- TOPP -->
<head id="oc-wp-admin-header">
<!-- END TOPP -->

<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php echo wp_specialchars( strip_tags( $title ) ); ?> &#8212; WordPress</title>
<link rel="stylesheet" href="<?php bloginfo('home') ?>/wp-admin/wp-admin.css?version=<?php bloginfo('version'); ?>" type="text/css" />
<?php if ( ('rtl' == $wp_locale->text_direction) ) : ?>
<link rel="stylesheet" href="<?php bloginfo('home'); ?>/wp-admin/rtl.css?version=<?php bloginfo('version'); ?>" type="text/css" />
<?php endif; ?> 
<script type="text/javascript">
//<![CDATA[
function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
//]]>
</script>
<?php if ( ($parent_file != 'link-manager.php') && ($parent_file != 'options-general.php') ) : ?>
<style type="text/css">* html { overflow-x: hidden; }</style>
<?php endif;
if ( isset($page_hook) )
	do_action('admin_print_scripts-' . $page_hook);
else if ( isset($plugin_page) )
	do_action('admin_print_scripts-' . $plugin_page);
do_action('admin_print_scripts');

if ( isset($page_hook) )
	do_action('admin_head-' . $page_hook);
else if ( isset($plugin_page) )
	do_action('admin_head-' . $plugin_page);
do_action('admin_head');
?>
<style type="text/css">
a {
  border-bottom: none !important;
}
h2 {
  font-weight: bold !important;
  border-bottom: 1px dotted #ccc !important;
  padding-bottom: 0.25em !important;
}
legend {
  font-family: Arial, Helvetica, Verdana, sans-serif !important;
}
#oc-content-container {
  padding-top: 0 !important;
}
#wphead, #user_info, #minisub, iframe#uploading {
  display: none;
}

.wrap {
  margin: 0.5em 0 !important;
}
#poststuff {
  margin-right: 1em !important;
}
#poststuff #moremeta {
  float: right !important;
  margin: 0 0 0 2em !important;
  position: relative !important;
  right: 0 !important;
}

ul#adminmenu {
  margin: 1em 0 0 0;
}
ul#adminmenu li {
  line-height: 175%;
}

#oc-content-container #moremeta .dbx-handle {
  background: #4D4945 url('../wp-content/themes/default/images/box-head.gif');
}
#oc-content-container #advancedstuff h3.dbx-handle {
  background: #fff url('../wp-content/themes/default/images/box-head-right.gif');
}
#oc-content-container #advancedstuff div.dbx-h-andle-wrapper {
  background: #fff url('../wp-content/themes/default/images/box-head-left.gif');
}
#oc-content-container  #postcustom,
#oc-content-container #wp-bookmarklet {
  display: none;
  visibility: hidden;
}
#oc-content-container .wrap {
  border: none;
}
</style>
</head>
<!-- TOPP -->
<body id="oc-wp-admin-body">
<!-- TOPP -->
<div id="wphead">
<h1><?php bloginfo('name'); ?> <span>(<a href="<?php echo get_option('home') . '/'; ?>"><?php _e('View site &raquo;') ?></a>)</span></h1>
</div>
<div id="user_info"><p><?php printf(__('Howdy, <strong>%s</strong>.'), $user_identity) ?> [<a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>"><?php _e('Sign Out'); ?></a>, <a href="profile.php"><?php _e('My Profile'); ?></a>] </p></div>

<?php
require(ABSPATH . '/wp-admin/menu-header.php');

if ( $parent_file == 'options-general.php' ) {
	require(ABSPATH . '/wp-admin/options-head.php');
}
?>
