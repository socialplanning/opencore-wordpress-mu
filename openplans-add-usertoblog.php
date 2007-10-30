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
define('TOPP_GLOBAL_SCRIPT',true);
require_once('openplans-auth.php');
require_once('wp-config.php');
require_once(ABSPATH . WPINC . '/wpmu-functions.php');
require_once(ABSPATH . WPINC . '/registration.php');
require_once('Snoopy.class.php');


$username = $_POST['username'];
$role = $_POST['role'];
$sig = $_POST['signature'];
$domain = $_POST['domain'];
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

if ( !(($role == 'ProjectAdmin') || ($role == 'ProjectMember')) )
{
  status_header(400);
  echo "The only allowed roles are ProjectAdmin and ProjectMember";
  exit(0);
}

if ($checkUser && $checkDomain)
{
  echo "Adding user $username (user ID is $checkUser->ID)  to blog $domain (domain ID $checkDomain->blog_id) with role $role ";

  $wp_role = '';

  if ($role === "ProjectMember")
    {
      $wp_role = 'contributor';
    }
  if ($role === "ProjectAdmin")
    {
      $wp_role = 'administrator';
    }

  status_header(200);
  add_user_to_blog($checkDomain->blog_id,$checkUser->ID, $wp_role);
}
