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
require_once('Snoopy.class.php');

$sig = $_POST['signature'];
$username = $_POST['username'];

$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $username, $secret, true);
$expect = trim(base64_encode($expect));
if ($sig != $expect)
{
  die("Signature '$sig' invalid for domain '$username'");
}


$email = $_POST['email'];

$checkUser = $wpdb->get_row("SELECT user_login FROM $wpdb->users WHERE user_login = '$username'");
$checkEmail = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$email'");

if ($checkUser)
{
  header("Status: 400 Bad Request");
  echo "User with name $username already exists! :";
  exit(0);
}

if ($checkEmail)
{
  header("Status: 400 Bad Request");
  echo "User with email $email already exists! :";
  exit(0);
}

if (!$checkUser && !$checkEmail)
{
  echo "Creating user $username: $username";
  wpmu_create_user($username, '', $email);
}



