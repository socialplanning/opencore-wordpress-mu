<?php
/* This is an HTTP API for creating blogs, internal to TOPP
 * Usage:
 * POST /openplans-create-user.php
 * Body:
 *   username = name of the user to add
 *   email = email address of the user that needs to be added
 *   signature = hmac of domain, plus secret key, to validate request
 * Response:
 *   'ok'
 */
define('WP_INSTALLING', true);
require_once('openplans-auth.php');
require_once('wp-config.php');
require_once(ABSPATH . WPINC . '/wpmu-functions.php');
require_once(ABSPATH . WPINC . '/registration.php');
require_once(ABSPATH . 'wp-admin/admin-db.php');
require_once('Snoopy.class.php');

$sig = $_POST['signature'];
$domain = $_POST['domain'];
$username = $_POST['username'];
$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $username, $secret, true);
$expect = trim(base64_encode($expect));


if ($sig != $expect)
{
  die("Signature '$sig' invalid for domain '$domain'");
}


$checkUser = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$username'");
$checkDomain = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain'");

if (!$checkUser)
{
  status_header(400);
  echo "User with name $username does not exist! :";
  exit(0);
}

if (!$checkDomain)
{
  status_header(400);
  echo "Blog with domain $domain does not exist! :";
  exit(0);
}

if ($checkUser && $checkDomain)
{
  status_header(200);
  //echo "Remove user $username from blog $domain";
  //echo "user ID $checkUser->ID";
  //echo "domain ID $checkDomain->blog_id";
  remove_user_from_blog($checkUser->ID, $checkDomain->blog_id);
}
