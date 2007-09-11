<?php
/* This is an HTTP API for creating blogs, internal to TOPP
 * Usage:
 * POST /openplans-create-blog.php
 * Body:
 *   domain = domain to create
 *   path = path to create (e.g., '/blog')
 *   title = title of blog
 *   user_id = user id of first admin of site (?)
 *   signature = hmac of domain, plus secret key, to validate request
 * Response:
 *   'ok'
 */
define('WP_INSTALLING', true);
require_once('openplans-auth.php');
require_once('wp-config.php');
require_once(ABSPATH . WPINC . '/wpmu-functions.php');

$sig = $_POST['signature'];

$domain = $_POST['domain'];
$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $domain, $secret, true);
$expect = trim(base64_encode($expect));
if ($sig != $expect) {
    die("Signature '$sig' invalid for domain '$domain'");
}

$path = $_POST['path'];
if (! $path) {
    $path = '/blog';
}

$title = $_POST['title'];
/* FIXME: not sure what user_id should even be, or if we really need it
   in this case */
$user_id = $_POST['user_id'];

wpmu_create_blog($domain, $path, $title, $user_id);

echo "ok";

?>
