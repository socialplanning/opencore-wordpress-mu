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
$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $domain, $secret, true);
$expect = trim(base64_encode($expect));

$username = $_POST['username'];

if ($sig != $expect)
{
  die("Signature '$sig' invalid for domain '$domain'");
}


$checkUser = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$username'");
$checkDomain = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain'");

if (!$checkUser)
{
  header("Status: 400 Bad Request");
  echo "User with name $username does not exist! :";
  exit(0);
}

if (!$checkDomain)
{
  header("Status: 400 Bad Request");
  echo "Blog with domain $domain does not exist! :";
  exit(0);
}

if ($checkUser && $checkDomain)
{
  //echo "Remove user $username from blog $domain";
  //echo "user ID $checkUser->ID";
  //echo "domain ID $checkDomain->blog_id";
  remove_user_from_blog($checkUser->ID, $checkDomain->blog_id);
}