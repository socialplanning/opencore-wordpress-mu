<?php

if ( !function_exists('wp_login') ) :
function wp_login($username, $password, $already_md5 = false) {
	global $wpdb, $error, $current_user;

	$username = strtolower($username);

	if ( !$username )
		return false;

	if ( !$password ) {
		$error = __('<strong>Error</strong>: The password field is empty.');
		return false;
	}

	if ($current_user->data->user_login == $username)
		return true;

	$login = get_userdatabylogin($username);
	//$login = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $wpdb->users WHERE user_login = '$username'");

	if (!$login) {
		if( is_site_admin( $username ) ) {
			unset( $login );
			$userdetails = get_userdatabylogin( $username );
			$login->user_login = $username;
			$login->user_pass = $userdetails->user_pass;
		} else {
			$admins = get_admin_users_for_domain();
			reset( $admins );
			while( list( $key, $val ) = each( $admins ) ) 
			{ 
				if( $val[ 'user_login' ] == $username ) {
					unset( $login );
					$login->user_login = $username;
					$login->user_pass  = $val[ 'user_pass' ];
				}
			}
		}
	}
	if (!$login) {
		$error = __('<strong>Error</strong>: Wrong username.');
		return false;
	} else {
		if( is_site_admin( $username ) == false && ( $primary_blog = get_usermeta( $login->ID, "primary_blog" ) ) ) {
			$details = get_blog_details( $primary_blog );
			if( is_object( $details ) && $details->archived == 1 || $details->spam == 1 || $details->deleted == 1 ) {
				$error = __('<strong>Error</strong>: Blog suspended.');
				return false;
			}
		}
		// If the password is already_md5, it has been double hashed.
		// Otherwise, it is plain text.
		if ( ($already_md5 && $login->user_login == $username && md5($login->user_pass) == $password) || ($login->user_login == $username && $login->user_pass == md5($password)) ) {
			return true;
		} else {
			$error = __('<strong>Error</strong>: Incorrect password.');
			$pwd = '';
			return false;
		}
	}
}
endif;

if ( !function_exists('get_userdata') ) :
function get_userdata( $user_id ) {
	global $wpdb, $cache_userdata, $wpmuBaseTablePrefix;
	$user_id = (int) $user_id;
	if ( $user_id == 0 )
		return false;

	$user = wp_cache_get($user_id, 'users');
	$user_level = $wpmuBaseTablePrefix . $wpdb->blogid . '_user_level';
	if ( $user && is_site_admin( $user->user_login ) ) {
		$user->$user_level = 10;
		$user->user_level = 10;
		$cap_key = $wpdb->prefix . 'capabilities';
		$user->{$cap_key} = array( 'administrator' => '1' );
		return $user;
	} elseif ( $user ) {
		return $user;
	}

	if ( !$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = '$user_id'") )
		return false;

	$metavalues = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = '$user_id' /* pluggable get_userdata */");

	if ($metavalues) {
		foreach ( $metavalues as $meta ) {
			@ $value = unserialize($meta->meta_value);
			if ($value === FALSE)
				$value = $meta->meta_value;
			$user->{$meta->meta_key} = $value;

			// We need to set user_level from meta, not row
			if ( $wpdb->prefix . 'user_level' == $meta->meta_key )
				$user->user_level = $meta->meta_value;
		} // end foreach
	} //end if

	if( is_site_admin( $user->user_login ) == true ) {
	    $user->user_level = 10;
	    $cap_key = $wpdb->prefix . 'capabilities';
	    $user->{$cap_key} = array( 'administrator' => '1' );
	}

	wp_cache_add($user_id, $user, 'users');
	wp_cache_add($user->user_login, $user, 'userlogins');

	return $user;
}
endif;

if ( !function_exists('get_userdatabylogin') ) :
function get_userdatabylogin($user_login) {
	global $wpdb;
	$user_login = sanitize_user( $user_login );

	if ( empty( $user_login ) )
		return false;
		
	$userdata = wp_cache_get($user_login, 'userlogins');
	if( $userdata && is_site_admin( $user_login ) == true ) {
		$userdata->user_level = 10;
		$cap_key = $wpdb->prefix . 'capabilities';
		$userdata->{$cap_key} = array( 'administrator' => '1' );
		return $userdata;
	} elseif( $userdata )
		return $userdata;

	if ( !$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$user_login'") )
		return false;

	$metavalues = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = '$user->ID'");

	if ($metavalues) {
		foreach ( $metavalues as $meta ) {
			@ $value = unserialize($meta->meta_value);
			if ($value === FALSE)
				$value = $meta->meta_value;
			$user->{$meta->meta_key} = $value;

			// We need to set user_level from meta, not row
			if ( $wpdb->prefix . 'user_level' == $meta->meta_key )
				$user->user_level = $meta->meta_value;
		}
	}
	if( is_site_admin( $user_login ) == true ) {
		$user->user_level = 10;
		$cap_key = $wpdb->prefix . 'capabilities';
		$user->{$cap_key} = array( 'administrator' => '1' );
	}

	wp_cache_add($user->ID, $user, 'users');
	wp_cache_add($user->user_login, $user, 'userlogins');

	return $user;

}
endif;

?>
