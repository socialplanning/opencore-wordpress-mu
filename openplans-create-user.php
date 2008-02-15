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
$checkUser = $wpdb->get_row("SELECT ID FROM $wpdb->users WHERE user_login = '$username'");
$checkEmail = $wpdb->get_row("SELECT ID FROM $wpdb->users WHERE user_email = '$email'");

if ($checkUser)
{
  $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE user_id = '$checkUser->ID'" );
  $wpdb->query( "DELETE FROM {$wpdb->users} WHERE ID = '$checkUser->ID'" );
}

if ($checkEmail)
{
  $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE user_id = '$checkEmail->ID'" );
  $wpdb->query( "DELETE FROM {$wpdb->users} WHERE ID = '$checkEmail->ID'" );
}

status_header(200);
echo "Creating user: $username";
wpmu_create_user($username, '', $email);
$wpdb->query("UPDATE $wpdb->users SET user_url='$home_page' WHERE user_login = '$username' "); 
