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

$checkUser = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$username'");
$checkEmail = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$email' AND user_login != '$username'");

if (!$checkUser)
{
  status_header(400);
  echo "User with name $username does not exist! :";
  exit(0);
}

if ($checkEmail)
{
  status_header(400);
  echo "User with email $email already exists! :";
  exit(0);
}

if ($checkUser && !$checkEmail)
{
  status_header(200);
  echo "Changing email for user : $username";
  $email = $wpdb->escape($email);
  $oldEmail = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_login = '$username' ");
  $wpdb->query("UPDATE $wpdb->users SET user_email='$email' WHERE ID= $checkUser->ID;");
  $blogs = get_blogs_of_user ( $checkUser->ID );
  foreach ($blogs as $blog)
    {
      $query = "UPDATE wp_".$blog->userblog_id."_options SET option_value='$email' WHERE option_name='admin_email' AND option_value='$oldEmail->user_email' ;";
      $wpdb->query($query);
    }

}
