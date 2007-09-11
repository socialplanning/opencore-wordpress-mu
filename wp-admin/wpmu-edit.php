<?php
require_once('admin.php');

do_action( "wpmuadminedit", "" );

$id = intval( $_REQUEST[ 'id' ] );
if( isset( $_POST[ 'ref' ] ) == false && empty( $_SERVER[ 'HTTP_REFERER' ] ) == false )
	$_POST[ 'ref' ] = $_SERVER[ 'HTTP_REFERER' ];

switch( $_REQUEST[ 'action' ] ) {
	case "siteoptions":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('siteoptions');

		update_site_option( "WPLANG", $_POST[ 'WPLANG' ] );
		if( is_email( $_POST[ 'admin_email' ] ) )
			update_site_option( "admin_email", $_POST[ 'admin_email' ] );
		$illegal_names = split( ' ', $_POST[ 'illegal_names' ] );
		foreach( $illegal_names as $name ) {
			$name = trim( $name );
			if( $name != '' )
				$names[] = trim( $name );
		}
		update_site_option( "illegal_names", $names );
		if( $_POST[ 'limited_email_domains' ] != '' ) {
			update_site_option( "limited_email_domains", split( ' ', $_POST[ 'limited_email_domains' ] ) );
		} else {
			update_site_option( "limited_email_domains", '' );
		}
		if( $_POST[ 'banned_email_domains' ] != '' ) {
			$banned_email_domains = split( "\n", stripslashes($_POST[ 'banned_email_domains' ]) );
			foreach( $banned_email_domains as $domain ) {
				$banned[] = trim( $domain );
			}
			update_site_option( "banned_email_domains", $banned );
		} else {
			update_site_option( "banned_email_domains", '' );
		}
		update_site_option( "menu_items", $_POST[ 'menu_items' ] );
		update_site_option( "blog_upload_space", $_POST[ 'blog_upload_space' ] );
		update_site_option( "upload_filetypes", $_POST[ 'upload_filetypes' ] );
		update_site_option( "site_name", $_POST[ 'site_name' ] );
		update_site_option( "first_post", $_POST[ 'first_post' ] );
		update_site_option( "welcome_email", $_POST[ 'welcome_email' ] );
		update_site_option( "fileupload_maxk", $_POST[ 'fileupload_maxk' ] );
		$site_admins = explode( ' ', str_replace( ",", " ", $_POST['site_admins'] ) );
		if ( is_array( $site_admins ) ) {
			$mainblog_id = $wpdb->get_var( "SELECT blog_id FROM {$wpdb->blogs} WHERE domain='{$current_site->domain}' AND path='{$current_site->path}'" );
			if( $mainblog_id ) {
				reset( $site_admins );
				foreach( $site_admins as $site_admin ) {
					$uid = $wpdb->get_var( "SELECT ID FROM {$wpdb->users} WHERE user_login='{$site_admin}'" );
					if( $uid )
						add_user_to_blog( $mainblog_id, $uid, 'Administrator' );
				}
			}
			update_site_option( 'site_admins' , $site_admins );
		}
		wp_redirect( add_query_arg( "updated", "true", $_SERVER[ 'HTTP_REFERER' ] ) );
		die();
	break;
	case "searchcategories":
		$search = wp_specialchars( $_POST[ 'search' ] );
		$query = "SELECT cat_name FROM " . $wpdb->sitecategories . " WHERE cat_name LIKE '%" . $search . "%' limit 0,10";
		$cats = $wpdb->get_results( $query );
		if( is_array( $cats ) ) {
			print "<ul>";
			while( list( $key, $val ) = each( $cats ) ) 
			{ 
				print "<li>{$val->cat_name}</li>";
			}
			print "</ul>";
		}
		exit;
	break;
	case "searchusers":
		$search = wp_specialchars( $_POST[ 'search' ] );
		$query = "SELECT " . $wpdb->users . ".ID, " . $wpdb->users . ".user_login FROM " . $wpdb->users . " WHERE user_login LIKE '" . $search . "%' limit 0,10";
		$users = $wpdb->get_results( $query );
		if( is_array( $users ) ) {
			print "<ul>";
			while( list( $key, $val ) = each( $users ) ) 
			{ 
				print "<li>{$val->user_login}</li>";
			}
			print "</ul>";
		} else {
			_e('No Users Found');
		}
		exit;
	break;
	case "adduser":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('add-user');
		
		if( is_array( $_POST[ 'user' ] ) == true ) {
			$user = $_POST['user'];
			$password = generate_random_password();
			$user_id = wpmu_create_user(wp_specialchars( strtolower( $user['username'] ) ), $password, wp_specialchars( $user['email'] ) );
			if(false == $user_id) {
				die( __("<p>There was an error creating the user</p>") );
			} else {
				wp_new_user_notification($user_id, $password);
			}
			wp_redirect( add_query_arg( "updated", "useradded", $_SERVER[ 'HTTP_REFERER' ] ) );
			die();
		}
	
	break;
	case "addblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		
		check_admin_referer('add-blog');
		
		if( is_array( $_POST[ 'blog' ] ) == true ) {
			$blog = $_POST['blog'];
			$domain = strtolower( wp_specialchars( $blog['domain'] ) );
			$email = wp_specialchars( $blog['email'] );
			if( constant( "VHOST" ) == 'yes' ) {
				$newdomain = $domain.".".$current_site->domain;
				$path = $base;
			} else {
				$newdomain = $current_site->domain;
				$path = $base.$domain.'/';
			}
			
			$user_id = email_exists($email);
			if( !$user_id ) { // I'm not sure what this check should be.
				$password = generate_random_password();
				$user_id = wpmu_create_user( $domain, $password, $email );
				if(false == $user_id) {
					die( __("<p>There was an error creating the user</p>") );
				} else {
					wp_new_user_notification($user_id, $password);
				}
			}

			$wpdb->hide_errors();
			$blog_id = wpmu_create_blog($newdomain, $path, wp_specialchars( $blog['title'] ), $user_id ,'', $current_site->id);
			$wpdb->show_errors();
			if( !is_wp_error($blog_id) ) {
				$content_mail = sprintf(__("New blog created by %1s\n\nAddress: http://%2s\nName: %3s"), $current_user->user_login , $newdomain.$path, wp_specialchars($blog['title']) );
				@wp_mail( get_site_option('admin_email'),  sprintf(__('[%s] New Blog Created'), $current_site->site_name), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );
				wp_redirect( add_query_arg( "updated", "blogadded", $_SERVER[ 'HTTP_REFERER' ] ) );
				die();
			} else {
				die( $blog_id->get_error_message() );
			}
		}
			
	break;
	case "updateblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('editblog');
		$options_table_name = $wpmuBaseTablePrefix . $id ."_options";

		// themes
		if( is_array( $_POST[ 'theme' ] ) ) {
			$allowed_themes = $_POST[ 'theme' ];
			$_POST[ 'option' ][ 'allowedthemes' ] = $_POST[ 'theme' ];
		} else {
			$_POST[ 'option' ][ 'allowedthemes' ] = '';
		}
		if( is_array( $_POST[ 'option' ] ) ) {
			$c = 1;
			$count = count( $_POST[ 'option' ] );
			while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
				if( $c == $count ) {
					update_blog_option( $id, $key, $val );
				} else {
					update_blog_option( $id, $key, $val, false ); // no need to refresh blog details yet
				}
				$c++;
			}
		}
		// update blogs table
		$query = "UPDATE $wpdb->blogs SET
				domain       = '".$_POST[ 'blog' ][ 'domain' ]."',
				path         = '".$_POST[ 'blog' ][ 'path' ]."',
				registered   = '".$_POST[ 'blog' ][ 'registered' ]."',
				public       = '".$_POST[ 'blog' ][ 'public' ]."',
				archived     = '".$_POST[ 'blog' ][ 'archived' ]."',
				mature       = '".$_POST[ 'blog' ][ 'mature' ]."',
				deleted      = '".$_POST[ 'blog' ][ 'deleted' ]."',
				spam         = '".$_POST[ 'blog' ][ 'spam' ]."' 
			WHERE  blog_id = '$id'";
		$result = $wpdb->query( $query );
		update_blog_status( $id, 'spam', $_POST[ 'blog' ][ 'spam' ] );
		// user roles
		if( is_array( $_POST[ 'role' ] ) == true ) {
			$newroles = $_POST[ 'role' ];
			reset( $newroles );
			while( list( $userid, $role ) = each( $newroles ) ) { 
				$role_len = strlen( $role );
				$existing_role = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$userid'  AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities'" );
				if( false == $existing_role ) {
					$wpdb->query( "INSERT INTO " . $wpdb->usermeta . "( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '$userid', '" . $wpmuBaseTablePrefix . $id . "_capabilities', 'a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}')" );
				} elseif( $existing_role != "a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}" ) {
					$wpdb->query( "UPDATE $wpdb->usermeta SET meta_value = 'a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}' WHERE user_id = '$userid'  AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities'" );
				}

			}
		}

		// remove user
		if( is_array( $_POST[ 'blogusers' ] ) ) {
			reset( $_POST[ 'blogusers' ] );
			while( list( $key, $val ) = each( $_POST[ 'blogusers' ] ) ) { 
				$wpdb->query( "DELETE FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities' AND user_id = '" . $key . "'" );
			}
		}

		// change password
		if( is_array( $_POST[ 'user_password' ] ) ) {
			reset( $_POST[ 'user_password' ] );
			$newroles = $_POST[ 'role' ];
			while( list( $userid, $pass ) = each( $_POST[ 'user_password' ] ) ) { 
				unset( $_POST[ 'role' ] );
				$_POST[ 'role' ] = $newroles[ $userid ];
				if( $pass != '' ) {
					$cap = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = '{$userid}' AND meta_key = '{$wpmuBaseTablePrefix}{$wpdb->blogid}_capabilities' AND meta_value = 'a:0:{}'" );
					$userdata = get_userdata($userid);
					$_POST[ 'pass1' ] = $_POST[ 'pass2' ] = $pass;
					$_POST[ 'email' ] = $userdata->user_email;
					$_POST[ 'rich_editing' ] = $userdata->rich_editing;
					edit_user( $userid );
					if( $cap == null )
						$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE user_id = '{$userid}' AND meta_key = '{$wpmuBaseTablePrefix}{$wpdb->blogid}_capabilities' AND meta_value = 'a:0:{}'" );
				}
			}
			unset( $_POST[ 'role' ] );
			$_POST[ 'role' ] = $newroles;
		}

		// add user?
		if( $_POST[ 'newuser' ] != '' ) {
			$newuser = $_POST[ 'newuser' ];
			$userid = $wpdb->get_var( "SELECT ID FROM " . $wpdb->users . " WHERE user_login = '$newuser'" );
			if( $userid ) {
				$user = $wpdb->get_var( "SELECT user_id FROM " . $wpdb->usermeta . " WHERE user_id='$userid' AND meta_key='wp_" . $id . "_capabilities'" );
				if( $user == false )
					$wpdb->query( "INSERT INTO " . $wpdb->usermeta . "( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '$userid', '" . $wpmuBaseTablePrefix . $id . "_capabilities', 'a:1:{s:" . strlen( $_POST[ 'new_role' ] ) . ":\"" . $_POST[ 'new_role' ] . "\";b:1;}')" );
			}
		}
		wpmu_admin_do_redirect( "wpmu-blogs.php?action=editblog&id=".$id );
	break;
	case "deleteblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('deleteblog');
		if( $id != '0' && $id != '1' )
			wpmu_delete_blog( $id, true );
		if( $_POST[ 'ref' ] ) {
			wp_redirect( add_query_arg( "updated", "blogdeleted", $_POST[ 'ref' ] ) );
		} else {
			wp_redirect( add_query_arg( "updated", "blogdeleted", $_SERVER[ 'HTTP_REFERER' ] ) );
		}
		die();
	break;
	case "allblogs":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('allblogs');
		if( is_array( $_POST[ 'allblogs' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'allblogs' ] ) ) {
				if( $val != '0' && $val != '1' ) {
					if( $_POST[ 'blogfunction' ] == 'delete' ) {
						wpmu_delete_blog( $val, true );
					} elseif( $_POST[ 'blogfunction' ] == 'spam' ) {
						update_blog_status( $val, "spam", '1', 0 );
						set_time_limit(60); 
					}
				}
			}
		}

		wp_redirect( add_query_arg( "updated", "blogsupdated", $_SERVER[ 'HTTP_REFERER' ] ) );
		die();
	break;
	case "archiveblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('archiveblog');
		update_blog_status( $id, "archived", '1' );
		do_action( "archive_blog", $id );
		wp_redirect( add_query_arg( "updated", "blogarchived", $_POST[ 'ref' ] ) );
		die();
	break;
	case "unarchiveblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('unarchiveblog');
		do_action( "unarchive_blog", $id );
		update_blog_status( $id, "archived", '0' );
		wp_redirect( add_query_arg( "updated", "blogunarchived", $_POST[ 'ref' ] ) );
		die();
	break;
	case "activateblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('activateblog');
		update_blog_status( $id, "deleted", '0' );
		do_action( "activate_blog", $id );
		wp_redirect( add_query_arg( "updated", "blogactivated", $_POST[ 'ref' ] ) );
		die();
	break;
	case "deactivateblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('deactivateblog');
		do_action( "deactivate_blog", $id );
		update_blog_status( $id, "deleted", '1' );
		wp_redirect( add_query_arg( "updated", "blogdeactivated", $_POST[ 'ref' ] ) );
		die();
	break;
	case "unspamblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('unspamblog');
		update_blog_status( $id, "spam", '0' );
		do_action( "unspam_blog", $id );
		wp_redirect( add_query_arg( "updated", "blogunspam", $_POST[ 'ref' ] ) );
		die();
	break;
	case "spamblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		check_admin_referer('spamblog');
		do_action( "make_spam_blog", $id );
		update_blog_status( $id, "spam", '1' );
		wp_redirect( add_query_arg( "updated", "blogspam", $_POST[ 'ref' ] ) );
		die();
	break;
	case "mature":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		update_blog_status( $id, 'mature', '1' );
		do_action( 'mature_blog', $id );
		wp_redirect( add_query_arg( "updated", "blogmature", $_POST[ 'ref' ] ) );
		die();
	break;
	case "unmature":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		update_blog_status( $id, 'mature', '0' );
		do_action( 'unmature_blog', $id );
		wp_redirect( add_query_arg( "updated", "blogunmature", $_POST[ 'ref' ] ) );
		die();
	break;
    	case "updateuser":
		check_admin_referer('edituser');
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		unset( $_POST[ 'option' ][ 'ID' ] );
		if( is_array( $_POST[ 'option' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
				$query = "UPDATE ".$wpdb->users." SET ".$key." = '".$val."' WHERE  ID  = '".$id."'";
				$wpdb->query( $query );
			}
		}
		if( is_array( $_POST[ 'meta' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'meta' ] ) ) { 
				$query = "UPDATE ".$wpdb->usermeta." SET meta_key = '".$_POST[ 'metaname' ][ $key ]."', meta_value = '".$val."' WHERE  umeta_id  = '".$key."'";
				$wpdb->query( $query );
			}
		}
		if( is_array( $_POST[ 'metadelete' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'metadelete' ] ) ) { 
				$query = "DELETE FROM ".$wpdb->usermeta." WHERE  umeta_id  = '".$key."'";
				$wpdb->query( $query );
			}
		}
		wp_redirect( add_query_arg( "updated", "userupdated", $_SERVER[ 'HTTP_REFERER' ] ) );
		die();
	break;
    	case "updatethemes":
		if( is_site_admin() == false )
			die( __('<p>You do not have permission to access this page.</p>') );

    		if( is_array( $_POST[ 'theme' ] ) ) {
			$themes = get_themes();
			reset( $themes );
			foreach( $themes as $key => $theme ) {
				if( $_POST[ 'theme' ][ wp_specialchars( $theme[ 'Stylesheet' ] ) ] == 'enabled' )
					$allowed_themes[ wp_specialchars( $theme[ 'Stylesheet' ] ) ] = true;
			}
			update_site_option( 'allowedthemes', $allowed_themes );
		}
		wp_redirect( add_query_arg( "updated", "themesupdated", $_SERVER[ 'HTTP_REFERER' ] ) );
		die();
	break;
	case "confirm":
	?>
		<html><head><title><?php _e("Please confirm your action"); ?></title></head><body><h1><?php _e("Please Confirm"); ?></h1><form action='wpmu-edit.php' method='POST'><input type='hidden' name='action' value='<?php echo wp_specialchars( $_GET[ 'action2' ] ) ?>'><input type='hidden' name='id' value='<?php echo wp_specialchars( $_GET[ 'id' ] ); ?>'><input type='hidden' name='ref' value='<?php if( isset( $_GET[ 'ref' ] ) ) {echo wp_specialchars( $_GET[ 'ref' ] ); } else { echo $_SERVER[ 'HTTP_REFERER' ]; } ?>'><?php wp_nonce_field( $_GET[ 'action2' ] ) ?><p><?php echo wp_specialchars( $_GET[ 'msg' ] ) ?></p><input type='submit' value='<?php _e("Confirm"); ?>'></form></body></html>
	<?php
	break;
	default:
		wpmu_admin_do_redirect( "wpmu-admin.php" );
	break;
}
?>
