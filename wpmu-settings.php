<?php
if( $current_site && $current_blog )
	return;

$wpmuBaseTablePrefix = $table_prefix;

/* TOPP section:
   This takes the environmental variable PROXY_BASE, which should be like
     http://original-host/SCRIPT_NAME
   and applies it to the current request information.  That is, it overwrites
   HTTP_HOST with original-host, and fixes up REQUEST_URI to use SCRIPT_NAME.
   The remaining PATH_INFO is determined by looking for VirtualPathRoot in
   the URL.  This typically goes with httpd.conf like:

    RewriteEngine On
    # %1 = scheme
    # %2 = host
    # %3 = SCRIPT_NAME
    # %4 = PATH_INFO
    RewriteCond %{REQUEST_URI} ^/VirtualHostBase/([^/]+)/([^/]+)/VirtualHostRoot/(.*)/VirtualPathRoot/(.*)
    RewriteRule .* /%4 [PT,E=PROXY_BASE:%1://%2/%3]

   The server may be proxied to like:

    RewriteRule ^/blog(.*) http://localhost:8090/VirtualHostBase/http/%{HTTP_HOST}/VirtualHostRoot/blog/VirtualPathRoot$1 [P]

   This saves all the request path and host information, except for
   the scheme, for which there is no clear standard for expressing it
   in the CGI environment.  (FIXME: further inspection might find a way)
*/


if ($_SERVER['HTTP_X_FORWARDED_SERVER']) {
  $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_SERVER'];
  $_ENV['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
  $openplans_base_path = $_SERVER['HTTP_X_FORWARDED_PATH'];
  $_SERVER['REQUEST_URI'] = $openplans_base_path . $_SERVER['REQUEST_URI'];
  $_ENV['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
  /* echo "Parse headers X-Forwarded-Server='{$_SERVER['HTTP_X_FORWARDED_SERVER']}' X-Forwarded-Path='{$_SERVER['HTTP_X_FORWARDED_PATH']} HOST={$_SERVER['HTTP_HOST']} base_path='$openplans_base_path' REQUEST_URI={$_SERVER['REQUEST_URI']}<br>"; */
} else {
  if (defined(TOPP_GLOBAL_SCRIPT))
    {
      die('No X-Forwarded-Server header exists; are you accessing WordPress directly instead of through Deliverance?');
    }
}
/* end TOPP section */


$domain = addslashes( $_SERVER['HTTP_HOST'] );
/* TOPP: Use a normalized form of the domain name, based on the 
   X-Openplans-Project header */
if ($_SERVER['HTTP_X_OPENPLANS_PROJECT']) {
        $openplans_base_domain = $domain;
	$domain = $_SERVER['HTTP_X_OPENPLANS_PROJECT'] . '.openplans.org';
}
/* End TOPP customization */

if( substr( $domain, 0, 4 ) == 'www.' )
	$domain = substr( $domain, 4 );
if( strpos( $domain, ':' ) ) {
        /* TOPP addition ("|| true") -- we don't care about the port */
	if( substr( $domain, -3 ) == ':80'  || true) {
		$domain = substr( $domain, 0, -3 );
		$_SERVER['HTTP_HOST'] = substr( $_SERVER['HTTP_HOST'], 0, -3 );
	} else {
		die( 'WPMU only works without the port number in the URL: '
                    . $_SERVER['HTTP_HOST'] . '.' );
	}
}
$domain = preg_replace('/:.*$/', '', $domain); // Strip ports
if( substr( $domain, -1 ) == '.' )
	$domain = substr( $domain, 0, -1 );


/* TOPP: use our path calculation when available */
if ($openplans_base_path) {
	$path = $openplans_base_path;
} else {
	/* Old code: */
	$path = preg_replace( '|([a-z0-9-]+.php.*)|', '', $_SERVER['REQUEST_URI'] );
	$path = str_replace ( '/wp-admin/', '/', $path );
	$path = preg_replace( '|(/[a-z0-9-]+?/).*|', '$1', $path );
}
/* end TOPP section */

function wpmu_current_site() {
	global $wpdb, $current_site, $domain, $path, $sites;
	$path = substr( $_SERVER[ 'REQUEST_URI' ], 0, 1 + strpos( $_SERVER[ 'REQUEST_URI' ], '/', 1 ) );
	if( constant( 'VHOST' ) == 'yes' ) {
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='$path'" );
		if( $current_site != null )
			return $current_site;
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='/'" );
		if( $current_site != null ) {
			$path = '/';
			return $current_site;
		}
		$sitedomain = substr( $domain, 1 + strpos( $domain, '.' ) );
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$sitedomain' AND path='$path'" );
		if( $current_site != null )
			return $current_site;
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$sitedomain' AND path='/'" );
		if( $current_site == null && defined( "WP_INSTALLING" ) == false ) {
			if( count( $sites ) == 1 ) {
				$current_site = $sites[0];
				die( "That blog does not exist. Please try <a href='http://{$current_site->domain}{$current_site->path}'>http://{$current_site->domain}{$current_site->path}</a>" );
			} else {
				die( "No WPMU site defined on this host ('$sitedomain' from '$domain'). If you are the owner of this site, please check <a href='http://trac.mu.wordpress.org/wiki/DebuggingWpmu'>Debugging WPMU</a> for further assistance." );
			}
		} else {
			$path = '/';
		}
	} else {
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='$path'" );
		if( $current_site != null )
			return $current_site;
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='/'" );
		if( $current_site == null && defined( "WP_INSTALLING" ) == false ) {
			if( count( $sites ) == 1 ) {
				$current_site = $sites[0];
				die( "That blog does not exist. Please try <a href='http://{$current_site->domain}{$current_site->path}'>http://{$current_site->domain}{$current_site->path}</a>" );
			} else {
				die( "No WPMU site defined on this host. If you are the owner of this site, please check <a href='http://trac.mu.wordpress.org/wiki/DebuggingWpmu'>Debugging WPMU</a> for further assistance." );
			}
		} else {
			$path = '/';
		}
	}
	return $current_site;
}

$wpdb->hide_errors();
$sites = $wpdb->get_results( "SELECT * FROM $wpdb->site" ); // usually only one site
if( count( $sites ) == 1 ) {
	$current_site = $sites[0];
	$path = $current_site->path;
} else {
	$current_site = wpmu_current_site();
}


if( constant( 'VHOST' ) == 'yes' ) {
	$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain'");
	if( $current_blog != null ) {
		$current_site = $wpdb->get_row("SELECT * FROM $wpdb->site WHERE id='{$current_blog->site_id}'");
	} else {
		$blogname = substr( $domain, 0, strpos( $domain, '.' ) );
	}
} else {
	$blogname = htmlspecialchars( substr( $_SERVER[ 'REQUEST_URI' ], strlen( $path ) ) );
	if( strpos( $blogname, '/' ) )
		$blogname = substr( $blogname, 0, strpos( $blogname, '/' ) );
	if( strpos( " ".$blogname, '?' ) )
		$blogname = substr( $blogname, 0, strpos( $blogname, '?' ) );
	$blognames = array( 'page', 'comments', 'blog', 'wp-admin', 'wp-includes', 'wp-content', 'files', 'feed' );
	if( $blogname == '' || in_array( $blogname, $blognames ) || is_file( $blogname ) || is_blogname_page( $blogname ) ) {
		$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$path'");
	} else {
		$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '{$path}{$blogname}/'");
	}
}

/* TOPP: put the $current_blog->domain back to what it should be */
/* echo "reset current_blog; base_domain='$openplans_base_domain', path='$openplans_base_path', HOST='{$_SERVER['HTTP_HOST']}', REQUEST_URI='{$_SERVER['REQUEST_URI']}' <br>\n"; */
if ($openplans_base_domain) {
	$current_blog->domain = $openplans_base_domain;
	$current_blog->path = $openplans_base_path;
}
/* End TOPP customization */

if( defined( "WP_INSTALLING" ) == false ) {
	if( $current_site && $current_blog == null ) {
		/* TOPP change: because we don't have self-signup, we don't redirect to that address */
		header("Location: http://{$_SERVER[HTTP_HOST]}/?portal_status_message=The%20blog%20for%20this%20site%20has%20not%20been%20set%20up");
		// TOPP FIXME: should we disable wp-signup.php?
		// header( "Location: http://{$current_site->domain}{$current_site->path}wp-signup.php?new=" . urlencode( $blogname ) );
		/* End TOPP change */
	}
	if( $current_blog == false || $current_site == false )
		is_installed();
}

function is_blogname_page( $blogname ) {
	global $wpdb, $table_prefix, $domain, $path;

	$blog_id = $wpdb->get_var("SELECT blog_id FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$path'");

	// is the request for a page of the main blog? We need to cache this information somewhere to save a request
	$pages = $wpdb->get_col( "SELECT LOWER(post_name) FROM {$table_prefix}{$blog_id}_posts WHERE post_type='page'" ); 

	if( is_array( $pages ) == false ) 
		return false;

	if( in_array( strtolower( $blogname ), $pages ) ) {
		return true;
	} else {
		return false;
	}
}

$blog_id = $current_blog->blog_id;
$public  = $current_blog->public;
$site_id = $current_blog->site_id;

if( $site_id == 0 )
	$site_id = 1;

$current_site->site_name = $wpdb->get_var( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = '$site_id' AND meta_key = 'site_name'" );
if( $current_site->site_name == null )
	$current_site->site_name = ucfirst( $current_site->domain );

if( $blog_id == false ) {
    // no blog found, are we installing? Check if the table exists.
    if ( defined('WP_INSTALLING') ) {
	$query = "SELECT blog_id FROM ".$wpdb->blogs." limit 0,1";
	$blog_id = $wpdb->get_var( $query );
	if( $blog_id == false ) {
	    // table doesn't exist. This is the first blog
	    $blog_id = 1;
	} else {
	    // table exists
	    // don't create record at this stage. we're obviously installing so it doesn't matter what the table vars below are like.
	    // default to using the "main" blog.
	    $blog_id = 1;
	}
    } else {
	$check = $wpdb->get_results( "SELECT * FROM $wpdb->site" );
	if( $check == false ) {
	    $msg = ': DB Tables Missing';
	} else {
	    $msg = '';
	}
	die( "No Blog by that name ('$blogname') on this system." . $msg );
    }
}

$wpdb->show_errors();

if( '0' == $current_blog->public ) {
	// This just means the blog shouldn't show up in google, etc. Only to registered members
}

function is_installed() {
	global $wpdb, $domain, $path;
	$base = stripslashes( $base );
	if( defined( "WP_INSTALLING" ) == false ) {
		$check = $wpdb->get_results( "SELECT * FROM $wpdb->site" );
		$msg = "If your blog does not display, please contact the owner of this site.<br /><br />If you are the owner of this site please check that MySQL is running properly and all tables are error free.<br /><br />";
		if( $check == false ) {
			$msg .= "<strong>Database Tables Missing.</strong><br />Database tables are missing. This means that MySQL is either not running, WPMU was not installed properly, or someone deleted {$wpdb->site}. You really <em>should</em> look at your database now.<br />";
		} else {
			$msg .= '<strong>Could Not Find Blog!</strong><br />';
			$msg .= "Searched for <em>" . $domain . $path . "</em> in " . DB_NAME . "::" . $wpdb->blogs . " table. Is that right?<br />";
		}
		$msg .= "<br />\n<h1>What do I do now?</h1>";
		$msg .= "Read the <a target='_blank' href='http://trac.mu.wordpress.org/wiki/DebuggingWpmu'>bug report</a> page. Some of the guidelines there may help you figure out what went wrong.<br />";
		$msg .= "If you're still stuck with this message, then check that your database contains the following tables:<ul>
			<li> $wpdb->blogs </li>
			<li> $wpdb->users </li>
			<li> $wpdb->usermeta </li>
			<li> $wpdb->site </li>
			<li> $wpdb->sitemeta </li>
			<li> $wpdb->sitecategories </li>
			</ul>";
		$msg .= "If you suspect a problem please report it to the support forums but you must include the information asked for in the <a href='http://trac.mu.wordpress.org/wiki/DebuggingWpmu'>WPMU bug reporting guidelines</a>!<br /><br />";
		if( is_file( 'release-info.txt' ) ) {
			$msg .= 'Your bug report must include the following text: "';
			$info = file( 'release-info.txt' );
			$msg .= $info[ 4 ] . '"';
		}

		die( "<h1>Fatal Error</h1> " . $msg );
	}
}

$table_prefix = $table_prefix . $blog_id . '_';

?>
