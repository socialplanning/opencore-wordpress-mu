<?php

$url_members = "http://anil:4570/openplans/people/all.xml";

class user_profile
{
  var $username = '';
  var $name = '';
  var $password = '';
  var $emailaddress = '';
  var $home_page = '';
}

define('WP_INSTALLING', true);
require_once('openplans-auth.php');
require_once('wp-config.php');
require_once(ABSPATH . WPINC . '/wpmu-functions.php');
require_once(ABSPATH . WPINC . '/registration.php');
require_once('Snoopy.class.php');

$secret = get_openplans_secret();
$expect = hash_hmac("sha1", "admin", $secret, true);
$expect = trim(base64_encode($expect));

$all_members = $_POST['members'];
$sig = $_POST['signature'];

if ($sig != $expect)
{
  die("Signature '$sig' invalid for domain '$domain'");
}

$resp = _fetch_remote_file( $url_members, "admin", "admin" );
//$users = _parse_user_file($resp->results);
$users = _parse_user_file($all_members);
update_wp_users_table($users);
status_header(200);

function update_wp_users_table($users)
{
  global $wpdb;

  foreach ($users as $user)
    {      
      wpmu_create_user($user->username, $user->password, $user->emailaddress);
      $wpdb->query("UPDATE $wpdb->users SET user_url='$user->home_page' WHERE user_login = '$user->username' ");
      //echo "added user: <br>";
      //print_user($user);
    }
  echo "There were ".sizeof($users)." users added";
}

function _parse_user_file ($data)
{
  if (! ($xmlparser = xml_parser_create() ) )
    {
      die ("Cannot create parser");
    }

  $data=eregi_replace(">"."[[:space:]]+"."<","><",$data);
  xml_parse_into_struct($xmlparser, $data, $vals, $index);

  $users = array();

  $currentSize = 0;

  foreach ($vals as $val)
    {
      if ($val[type] == "complete")
	{
	  if ($val[tag] == "USERNAME")
	    {
	      array_push($users, new user_profile() );
	      $users[$currentSize]->username = $val[value];
	    }
	  if ($val[tag] == "NAME")
	    {
	      $users[$currentSize]->name = $val[value];
	    }
	  if ($val[tag] == "EMAIL")
	    {
	      $users[$currentSize]->emailaddress = $val[value];	      
	    }
	  if ($val[tag] == "HOME_PAGE")
	    {
	      $users[$currentSize]->home_page = $val[value];	      
	      $currentSize = $currentSize+1;
	    }
	}
    }

  return $users;
}

function _fetch_remote_file ($url, $username, $password,  $headers = "" )
{
  // Snoopy is an HTTP client in PHP
  $client = new Snoopy();
  $client->user = "admin";
  $client->pass = "admin";
  $client->agent = MAGPIE_USER_AGENT;
  $client->read_timeout = MAGPIE_FETCH_TIME_OUT;
  $client->use_gzip = MAGPIE_USE_GZIP;
  if (is_array($headers) )
    {
      $client->rawheaders = $headers;
    }
  
  @$client->fetch($url);
  return $client;
}

function print_user($user)
{
  echo "<br>-----------------------<br>";
  echo "username: ".$user->username.'<br>';
  echo "name: ".$user->name.'<br>';
  echo "email: ".$user->emailaddress.'<br>';
  echo "password: ".$user->password.'<br>';
  echo "home_page: ".$user->home_page.'<br>';
  echo "-----------------------<br>";
}



?>
