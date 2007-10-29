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
//define(TOPP_GLOBAL_SCRIPT, true);
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
  status_header(400);
  die("Signature '$sig' invalid for domain '$username'");
}


$email = $_POST['email'];
$home_page = $_POST['home_page'];
$checkUser = $wpdb->get_row("SELECT user_login FROM $wpdb->users WHERE user_login = '$username'");
$checkEmail = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$email'");

if ($checkUser)
{
  status_header(400);
  echo "User with name $username already exists! :";
  exit(0);
}

if ($checkEmail)
{
  status_header(400);
  echo "User with email $email already exists! :";
  exit(0);
}

if (!$checkUser && !$checkEmail)
{
  status_header(200);
  echo "Creating user: $username";
  wpmu_create_user($username, '', $email);
  //  echo "UPDATE wpdb->users SET user_url='bling' WHERE user_login = '$username' ";
  $wpdb->query("UPDATE $wpdb->users SET user_url='$home_page' WHERE user_login = '$username' "); 
}
