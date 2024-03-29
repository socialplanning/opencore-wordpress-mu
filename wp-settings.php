<?php
if( defined( 'ABSPATH' ) == false )
	die();
include('openplans-exceptions.php');

// Turn register globals off
function wp_unregister_GLOBALS() {
	if ( !ini_get('register_globals') )
		return;

	if ( isset($_REQUEST['GLOBALS']) )
		die('GLOBALS overwrite attempt detected');

	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');

	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ( $input as $k => $v ) 
		if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
			$GLOBALS[$k] = NULL;
			unset($GLOBALS[$k]);
		}
}

wp_unregister_GLOBALS(); 

if( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
	$HTTP_USER_AGENT = $_SERVER[ 'HTTP_USER_AGENT' ];
} else {
	$HTTP_USER_AGENT = '';
}
unset( $wp_filter, $cache_userdata, $cache_lastcommentmodified, $cache_lastpostdate, $cache_settings, $category_cache, $cache_categories );

if ( ! isset($blog_id) )
	$blog_id = 0;

// Fix for IIS, which doesn't set REQUEST_URI
if ( empty( $_SERVER['REQUEST_URI'] ) ) {
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME']; // Does this work under CGI?

	// Append the query string if it exists and isn't null
	if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
	$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

// Fix for Dreamhost and other PHP as CGI hosts
if (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false)
	unset($_SERVER['PATH_INFO']);

// Fix empty PHP_SELF
$PHP_SELF = $_SERVER['PHP_SELF'];
if ( empty($PHP_SELF) )
	$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);

if ( !(phpversion() >= '4.1') )
	die( 'Your server is running PHP version ' . phpversion() . ' but WordPress requires at least 4.1' );

if ( !extension_loaded('mysql') && !file_exists(ABSPATH . 'wp-content/db.php') )
	die( 'Your PHP installation appears to be missing the MySQL which is required for WordPress.' );

function timer_start() {
	global $timestart;
	$mtime = explode(' ', microtime() );
	$mtime = $mtime[1] + $mtime[0];
	$timestart = $mtime;
	return true;
}

function timer_stop($display = 0, $precision = 3) { //if called like timer_stop(1), will echo $timetotal
	global $timestart, $timeend;
	$mtime = microtime();
	$mtime = explode(' ',$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$timeend = $mtime;
	$timetotal = $timeend-$timestart;
	$r = number_format($timetotal, $precision);
	if ( $display )
		echo $r;
	return $r;
}
timer_start();

// Change to E_ALL for development/debugging
error_reporting(E_ALL ^ E_NOTICE);

// For an advanced caching plugin to use, static because you would only want one
if ( defined('WP_CACHE') )
	@include ABSPATH . 'wp-content/advanced-cache.php';

define('WPINC', 'wp-includes');

if ( !defined('LANGDIR') ) {
	if ( file_exists(ABSPATH . 'wp-content/languages') && @is_dir(ABSPATH . 'wp-content/languages') )
		define('LANGDIR', 'wp-content/languages'); // no leading slash, no trailing slash
	else
		define('LANGDIR', WPINC . '/languages'); // no leading slash, no trailing slash
}

if ( file_exists(ABSPATH . 'wp-content/db.php') )
	require (ABSPATH . 'wp-content/db.php');
else
	require_once (ABSPATH . WPINC . '/wp-db.php');

// $table_prefix is deprecated as of 2.1
$wpdb->prefix = $table_prefix;

if ( preg_match('|[^a-z0-9_]|i', $wpdb->prefix) && !file_exists(ABSPATH . 'wp-content/db.php') )
	die("<strong>ERROR</strong>: <code>$table_prefix</code> in <code>wp-config.php</code> can only contain numbers, letters, and underscores.");

// Table names

$wpdb->blogs		= $wpdb->prefix . 'blogs';
$wpdb->site		= $wpdb->prefix . 'site';
$wpdb->sitemeta		= $wpdb->prefix . 'sitemeta';
$wpdb->sitecategories	= $wpdb->prefix . 'sitecategories';
$wpdb->signups		= $wpdb->prefix . 'signups';
$wpdb->registration_log	= $wpdb->prefix . 'registration_log';
$wpdb->blog_versions	= $wpdb->prefix . 'blog_versions';
$wpdb->users		= $wpdb->prefix . 'users';
$wpdb->usermeta		= $wpdb->prefix . 'usermeta';

if( defined( 'SUNRISE' ) )
	include_once( ABSPATH . 'wp-content/sunrise.php' );

require_once ( ABSPATH . 'wpmu-settings.php' );
$wpdb->prefix           = $table_prefix;
$wpdb->posts            = $wpdb->prefix . 'posts';
$wpdb->categories       = $wpdb->prefix . 'categories';
$wpdb->post2cat         = $wpdb->prefix . 'post2cat';
$wpdb->comments         = $wpdb->prefix . 'comments';
$wpdb->link2cat         = $wpdb->prefix . 'link2cat';
$wpdb->links            = $wpdb->prefix . 'links';
$wpdb->linkcategories   = $wpdb->prefix . 'linkcategories';
$wpdb->options          = $wpdb->prefix . 'options';
$wpdb->postmeta         = $wpdb->prefix . 'postmeta';
$wpdb->siteid           = $current_blog->site_id;
$wpdb->blogid           = $current_blog->blog_id;

if ( defined('CUSTOM_USER_TABLE') )
	$wpdb->users = CUSTOM_USER_TABLE;
if ( defined('CUSTOM_USER_META_TABLE') )
	$wpdb->usermeta = CUSTOM_USER_META_TABLE;

// To be removed in 2.2
$tableposts = $tableusers = $tablecategories = $tablepost2cat = $tablecomments = $tablelink2cat = $tablelinks = $tablelinkcategories = $tableoptions = $tablepostmeta = '';

if ( file_exists(ABSPATH . 'wp-content/object-cache.php') )
	require_once (ABSPATH . 'wp-content/object-cache.php');
else
	require_once (ABSPATH . WPINC . '/cache.php');

// To disable persistant caching, add the below line to your wp-config.php file, uncommented of course.
// define('DISABLE_CACHE', true);

wp_cache_init();

if( !defined( "UPLOADS" ) )
	define( "UPLOADS", "wp-content/blogs.dir/{$wpdb->blogid}/files/" );
if( defined( "SHORTINIT" ) && constant( "SHORTINIT" ) == true ) // stop most of WP being loaded, we just want the basics
	return;


require (ABSPATH . WPINC . '/functions.php');
require (ABSPATH . WPINC . '/classes.php');
require (ABSPATH . WPINC . '/plugin.php');
require (ABSPATH . WPINC . '/default-filters.php');
include_once(ABSPATH . WPINC . '/streams.php');
include_once(ABSPATH . WPINC . '/gettext.php');
require_once (ABSPATH . WPINC . '/l10n.php');

if ( !is_blog_installed() && (strpos($_SERVER['PHP_SELF'], 'install.php') === false && !defined('WP_INSTALLING')) ) {
	if (strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false)
		$link = 'install.php';
	else
		$link = 'wp-admin/install.php';
	wp_die(sprintf("It doesn't look like you've installed WP yet. Try running <a href='%s'>install.php</a>.", $link));
}

require (ABSPATH . WPINC . '/formatting.php');
require (ABSPATH . WPINC . '/capabilities.php');
require (ABSPATH . WPINC . '/query.php');
require (ABSPATH . WPINC . '/theme.php');
require (ABSPATH . WPINC . '/user.php');
require (ABSPATH . WPINC . '/general-template.php');
require (ABSPATH . WPINC . '/link-template.php');
require (ABSPATH . WPINC . '/author-template.php');
require (ABSPATH . WPINC . '/post.php');
require (ABSPATH . WPINC . '/post-template.php');
require (ABSPATH . WPINC . '/category.php');
require (ABSPATH . WPINC . '/category-template.php');
require (ABSPATH . WPINC . '/comment.php');
require (ABSPATH . WPINC . '/comment-template.php');
require (ABSPATH . WPINC . '/rewrite.php');
require (ABSPATH . WPINC . '/feed.php');
require (ABSPATH . WPINC . '/bookmark.php');
require (ABSPATH . WPINC . '/bookmark-template.php');
require (ABSPATH . WPINC . '/kses.php');
require (ABSPATH . WPINC . '/cron.php');
require (ABSPATH . WPINC . '/version.php');
require (ABSPATH . WPINC . '/deprecated.php');
require (ABSPATH . WPINC . '/script-loader.php');

require_once( ABSPATH . WPINC . '/wpmu-functions.php' );

if( defined( "WP_INSTALLING" ) == false ) {
	$current_site->site_name = get_site_option('site_name');
}

if( $current_site->site_name == false ) {
	$current_site->site_name = ucfirst( $current_site->domain );
}

if( defined( "WP_INSTALLING" ) == false ) {
	$locale = get_option( "WPLANG" );
	if( $locale == false )
		$locale = get_site_option( "WPLANG" );
}

$wpdb->hide_errors();
if( defined( 'MUPLUGINDIR' ) == false ) 
	define( 'MUPLUGINDIR', 'wp-content/mu-plugins' );

$plugins = glob( ABSPATH . MUPLUGINDIR . '/*.php' );
if( is_array( $plugins ) ) {
	foreach ( $plugins as $plugin ) {
		if( is_file( $plugin ) )
			include_once( $plugin );
	}
}
$wpdb->show_errors();

if ( '1' == $current_blog->deleted )
	graceful_fail(__('This user has elected to delete their account and the content is no longer available.'));

if ( '2' == $current_blog->deleted )
		graceful_fail(sprintf(__('This blog has not been activated yet. If you are having problems activating your blog, please contact <a href="mailto:support@{%1$s}">support@{%1$s}</a>.'), $current_site->domain));

if( $current_blog->archived == '1' )
    graceful_fail(__('This blog has been archived or suspended.'));

if( $current_blog->spam == '1' )
    graceful_fail(__('This blog has been archived or suspended.'));

if (!strstr($_SERVER['PHP_SELF'], 'install.php') && !strstr($_SERVER['PHP_SELF'], 'wp-admin/import')) :
    // Used to guarantee unique hash cookies
    $cookiehash = ''; // Remove in 1.4
	define('COOKIEHASH', ''); 
endif;

if ( !defined('USER_COOKIE') )
	define('USER_COOKIE', 'wordpressuser');
if ( !defined('PASS_COOKIE') )
	define('PASS_COOKIE', 'wordpresspass');
if ( !defined('COOKIEPATH') )
	define('COOKIEPATH', $current_site->path );
if ( !defined('SITECOOKIEPATH') )
	define('SITECOOKIEPATH', $current_site->path );
if ( !defined('COOKIE_DOMAIN') )
	define('COOKIE_DOMAIN', '.' . $current_site->domain);

require (ABSPATH . WPINC . '/vars.php');

if ( !defined('PLUGINDIR') )
	define('PLUGINDIR', 'wp-content/plugins'); // no leading slash, no trailing slash
if ( get_option('active_plugins') ) {
	$current_plugins = get_option('active_plugins');
	if ( is_array($current_plugins) ) {
		foreach ($current_plugins as $plugin) {
			if ('' != $plugin && file_exists(ABSPATH . PLUGINDIR . '/' . $plugin))
				include_once(ABSPATH . PLUGINDIR . '/' . $plugin);
		}
	}
}

require (ABSPATH . WPINC . '/pluggable.php');

if ( defined('WP_CACHE') && function_exists('wp_cache_postload') )
	wp_cache_postload();

do_action('plugins_loaded');

// If already slashed, strip.
if ( get_magic_quotes_gpc() ) {
	$_GET    = stripslashes_deep($_GET   );
	$_POST   = stripslashes_deep($_POST  );
	$_COOKIE = stripslashes_deep($_COOKIE);
}

// Escape with wpdb.
$_GET    = add_magic_quotes($_GET   );
$_POST   = add_magic_quotes($_POST  );
$_COOKIE = add_magic_quotes($_COOKIE);
$_SERVER = add_magic_quotes($_SERVER);

do_action('sanitize_comment_cookies');

$wp_the_query =& new WP_Query();
$wp_query     =& $wp_the_query;
$wp_rewrite   =& new WP_Rewrite();
$wp           =& new WP();

if( defined( "WP_INSTALLING" ) == false )
	validate_current_theme();
define('TEMPLATEPATH', get_template_directory());
define('STYLESHEETPATH', get_stylesheet_directory());

// Load the default text localization domain.
load_default_textdomain();

$locale = get_locale();
$locale_file = ABSPATH . LANGDIR . "/$locale.php";
if ( is_readable($locale_file) )
	require_once($locale_file);

// Pull in locale data after loading text domain.
require_once(ABSPATH . WPINC . '/locale.php');

$wp_locale =& new WP_Locale();

// Load functions for active theme.
if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists(STYLESHEETPATH . '/functions.php') )
	include(STYLESHEETPATH . '/functions.php');
if ( file_exists(TEMPLATEPATH . '/functions.php') )
	include(TEMPLATEPATH . '/functions.php');

function shutdown_action_hook() {
	do_action('shutdown');
	wp_cache_close();
}
register_shutdown_function('shutdown_action_hook');

// Everything is loaded and initialized.
do_action('init');

?>
