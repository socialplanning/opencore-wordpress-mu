<?php
/* This is an HTTP API for creating blogs, internal to TOPP
 * Usage:
 * POST /openplans-create-blog.php
 * Body:
 *   domain = domain to create
 *   path = path to create (e.g., '/blog')
 *   title = title of blog
 *   signature = hmac of domain, plus secret key, to validate request
 * Response:
 *   'ok'
 */
define('TOPP_GLOBAL_SCRIPT', true);

define('WP_INSTALLING', true);
require_once('openplans-auth.php');

//require_once('wpmu-settings.php');
require_once('wp-config.php');
require_once(ABSPATH . WPINC . '/wpmu-functions.php');
require_once('Snoopy.class.php');

$sig = $_POST['signature'];
$domain = $_POST['domain'];
$membersXML = $_POST['members'];
$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $domain, $secret, true);
$expect = trim(base64_encode($expect));


$blogs = $wpdb->get_row( "SELECT blog_id FROM $wpdb->blogs WHERE domain = '$domain'");

if (get_blog_option($blogs->blog_id, "activated") == "true")
{
  status_header(200);
  echo "Blog deleted successfully";
  update_blog_option($blogs->blog_id, "activated", "false");
}
else
{
  status_header(400);
  echo "Blog has already been deleted";
}

//status_header(400);
//echo get_blog_option($blogs->blog_id, "activated");
?>
