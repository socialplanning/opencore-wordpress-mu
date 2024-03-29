<?php
// This array constructs the admin menu bar.
//
// Menu item name
// The minimum level the user needs to access the item: between 0 and 10
// The URL of the item's file
$menu[0] = array(__('Dashboard'), 'read', 'index.php');

if (strpos($_SERVER['REQUEST_URI'], 'edit-pages.php') !== false)
	$menu[5] = array(__('Write'), 'edit_pages', 'page-new.php');
else
	$menu[5] = array(__('Write'), 'edit_posts', 'post-new.php');
if (strpos($_SERVER['REQUEST_URI'], 'page-new.php') !== false)
	$menu[10] = array(__('Manage'), 'edit_pages', 'edit-pages.php');
else
	$menu[10] = array(__('Manage'), 'edit_posts', 'edit.php');
$menu_perms = get_site_option( "menu_items" );
if( is_array( $menu_perms ) == false )
	$menu_perms = array();
	
$menu[15] = array(__('Comments'), 'edit_posts', 'edit-comments.php');
$menu[20] = array(__('Blogroll'), 'manage_links', 'link-manager.php');
$menu[25] = array(__('Presentation'), 'switch_themes', 'themes.php');
if( $menu_perms[ 'plugins' ] == 1 )
	$menu[30] = array(__('Plugins'), 'activate_plugins', 'plugins.php');
if ( current_user_can('edit_users') )
	$menu[35] = array(__('Users'), 'edit_users', 'users.php');
else
	$menu[35] = array(__('Profile'), 'read', 'profile.php');
$menu[40] = array(__('Options'), 'manage_options', 'options-general.php');


$_wp_real_parent_file['post.php'] = 'post-new.php'; // Back-compat
$submenu['post-new.php'][5] = array(__('Write Post'), 'edit_posts', 'post-new.php');
$submenu['post-new.php'][10] = array(__('Write Page'), 'edit_pages', 'page-new.php');

$submenu['edit-comments.php'][5] = array(__('Comments'), 'edit_posts', 'edit-comments.php');
$awaiting_mod = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '0'");
$submenu['edit-comments.php'][25] = array(sprintf(__("Awaiting Moderation (%s)"), "<span id='awaitmod'>$awaiting_mod</span>"), 'edit_posts', 'moderation.php');


$submenu['edit.php'][5] = array(__('Posts'), 'edit_posts', 'edit.php');
$submenu['edit.php'][10] = array(__('Pages'), 'edit_pages', 'edit-pages.php');
//$submenu['edit.php'][12] = array(__('Uploads'), 'upload_files', 'upload.php');
$submenu['edit.php'][15] = array(__('Categories'), 'manage_categories', 'categories.php');
//$submenu['edit.php'][30] = array(__('Files'), 'edit_files', 'templates.php');
$submenu['edit.php'][35] = array(__('Import'), 'import', 'import.php');
$submenu['edit.php'][40] = array(__('Export'), 'import', 'export.php');

$submenu['link-manager.php'][5] = array(__('Manage Blogroll'), 'manage_links', 'link-manager.php');
$submenu['link-manager.php'][10] = array(__('Add Link'), 'manage_links', 'link-add.php');
$submenu['link-manager.php'][20] = array(__('Import Links'), 'manage_links', 'link-import.php');

if ( current_user_can('edit_users') ) {
	$_wp_real_parent_file['profile.php'] = 'users.php'; // Back-compat for plugins adding submenus to profile.php.
	$submenu['users.php'][5] = array(__('Authors &amp; Users'), 'edit_users', 'users.php');
	$submenu['users.php'][10] = array(__('Your Profile'), 'read', 'profile.php');
} else {
	$submenu['profile.php'][5] = array(__('Your Profile'), 'read', 'profile.php');
}

$submenu['options-general.php'][10] = array(__('General'), 'manage_options', 'options-general.php');
$submenu['options-general.php'][15] = array(__('Writing'), 'manage_options', 'options-writing.php');
$submenu['options-general.php'][20] = array(__('Reading'), 'manage_options', 'options-reading.php');
$submenu['options-general.php'][25] = array(__('Discussion'), 'manage_options', 'options-discussion.php');
$submenu['options-general.php'][30] = array(__('Privacy'), 'manage_options', 'options-privacy.php');
$submenu['options-general.php'][35] = array(__('Permalinks'), 'manage_options', 'options-permalink.php');
//$submenu['options-general.php'][40] = array(__('Miscellaneous'), 'manage_options', 'options-misc.php');

//$submenu['plugins.php'][5] = array(__('Plugins'), 'activate_plugins', 'plugins.php');
//$submenu['plugins.php'][10] = array(__('Plugin Editor'), 'edit_plugins', 'plugin-editor.php');

$submenu['themes.php'][5] = array(__('Themes'), 'switch_themes', 'themes.php');
//$submenu['themes.php'][10] = array(__('Theme Editor'), 'edit_themes', 'theme-editor.php');

get_currentuserinfo();
if( is_site_admin() ) {
	$menu[1] = array(__('Site Admin'), '10', 'wpmu-admin.php' );
	$submenu[ 'wpmu-admin.php' ][1] = array( __('Admin'), '10', 'wpmu-admin.php' );
	$submenu[ 'wpmu-admin.php' ][5] = array( __('Blogs'), '10', 'wpmu-blogs.php' );
	$submenu[ 'wpmu-admin.php' ][10] = array( __('Users'), '10', 'wpmu-users.php' );
	$submenu[ 'wpmu-admin.php' ][20] = array( __('Themes'), '10', 'wpmu-themes.php' );
	$submenu[ 'wpmu-admin.php' ][25] = array( __('Options'), '10', 'wpmu-options.php' );
	$submenu[ 'wpmu-admin.php' ][30] = array( __('Upgrade'), '10', 'wpmu-upgrade-site.php' );
}
do_action('_admin_menu');

// Create list of page plugin hook names.
foreach ($menu as $menu_page) {
	$admin_page_hooks[$menu_page[2]] = sanitize_title($menu_page[0]);
}

$_wp_submenu_nopriv = array();
$_wp_menu_nopriv = array();
// Loop over submenus and remove pages for which the user does not have privs.
foreach ($submenu as $parent => $sub) {
	foreach ($sub as $index => $data) {
		if ( ! current_user_can($data[1]) ) {
			unset($submenu[$parent][$index]);
			$_wp_submenu_nopriv[$parent][$data[2]] = true;
		}
	}

	if ( empty($submenu[$parent]) )
		unset($submenu[$parent]);
}

// Loop over the top-level menu.
// Menus for which the original parent is not acessible due to lack of privs will have the next
// submenu in line be assigned as the new menu parent.
foreach ( $menu as $id => $data ) {
	if ( empty($submenu[$data[2]]) ) 
		continue;
	$subs = $submenu[$data[2]];
	$first_sub = array_shift($subs);
	$old_parent = $data[2];
	$new_parent = $first_sub[2];
	// If the first submenu is not the same as the assigned parent,
	// make the first submenu the new parent.
	if ( $new_parent != $old_parent ) {
		$_wp_real_parent_file[$old_parent] = $new_parent;
		$menu[$id][2] = $new_parent;

		foreach ($submenu[$old_parent] as $index => $data) {
			$submenu[$new_parent][$index] = $submenu[$old_parent][$index];
			unset($submenu[$old_parent][$index]);
		}
		unset($submenu[$old_parent]);
		$_wp_submenu_nopriv[$new_parent] = $_wp_submenu_nopriv[$old_parent];
	}
}

do_action('admin_menu', '');

// Remove menus that have no accessible submenus and require privs that the user does not have.
// Run re-parent loop again.
foreach ( $menu as $id => $data ) {
	// If submenu is empty...
	if ( empty($submenu[$data[2]]) ) {
		// And user doesn't have privs, remove menu.
		if ( ! current_user_can($data[1]) ) {
			$_wp_menu_nopriv[$data[2]] = true;
			unset($menu[$id]);
		}
	}
}

unset($id);

uksort($menu, "strnatcasecmp"); // make it all pretty

if (! user_can_access_admin_page()) {
	global $wpdb;
	/* TOPP: force login if they access this without a login: */
	auth_redirect();
	/* TOPP redirect: */
	header("Location: http://{$current_blog->domain}/?portal_status_message=You%20do%20not%20have%20access%20to%20this%20blog");
	/* TOPP commenting out this:
	// find the blog of this user first
	$primary_blog = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = '$user_ID' AND meta_key = 'primary_blog'" );
	if( $primary_blog ) {
		$newblog = $wpdb->get_row( "SELECT * FROM {$wpdb->blogs} WHERE blog_id = '{$primary_blog}'" );
		if( $newblog != null ) {
			header( "Location: http://" . $newblog->domain . $newblog->path . "wp-admin/" );
			exit;
		}
	}
	*/
	wp_die( __('You do not have sufficient permissions to access this page.') );
}

?>
