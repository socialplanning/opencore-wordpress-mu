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
require_once(ABSPATH . '/wp-admin/admin-db.php');

$sig = $_POST['signature'];
$domain = $_POST['domain'];
$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $domain, $secret, true);
$expect = trim(base64_encode($expect));


$blogs = $wpdb->get_row( "SELECT blog_id FROM $wpdb->blogs WHERE domain = '$domain'");
if ($blogs->blog_id)
{
  status_header(200);
  echo "The Blog has been deleted.";
  wpmu_delete_blog($blogs->blog_id, true);
}
else
{
  status_header(400);
  echo "Blog has already been deleted or was never created in the first place.";
}

?>
