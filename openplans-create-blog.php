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

$sig = $_POST['signature'];
$domain = $_POST['domain'];
$path = $_POST['path'];
$title = $_POST['title'];
$membersXML = $_POST['members'];
$secret = get_openplans_secret();
$expect = hash_hmac("sha1", $domain, $secret, true);
$expect = trim(base64_encode($expect));



//die (print_r($_POST));

if ($sig != $expect)
{
  die("Signature '$sig' invalid for domain '$domain'");
}


if (!$path) 
{
  $path = '/blog';
}



//This makes sure that a blog doesn't already exist with the name that
//is requested
$blogs = $wpdb->get_row( "SELECT blog_id FROM $wpdb->blogs WHERE domain = '$domain'");

if ($blogs)
{

  if (get_blog_option($blogs->blog_id, "activated") == "false")
    {
      status_header(200);
      update_blog_option($blogs->blog_id, "activated", "true");
      exit(0);
    }
  else
    {
      status_header(400);
      echo "Blog with domain '$domain' already exists and is activated;";
      exit(0);
    }
}


############CLASS DEFINITION############
class member_profile
{
  var $username = "";
  var $teams = array(); 
  # key is team name, value is an array of roles
}
###########END CLASS DEF################

$domain_pieces = split("\.", $domain);
$project_name = $domain_pieces[0];

// XXX must point to an opencore instance running off https://svn.openplans.org/svn/opencore/branches/wordpress-sandbox
// FIXME make configurable
//$url_team = "http://localhost:4570/openplans/projects/".$project_name."/members.xml";
//echo $url_team;
//echo "The project that was selected from openplans is $project_name: ";

$team = array();
//$resp = _fetch_remote_file( $url_team, "admin", "admin" );
//$team = _parse_team_file($resp->results);
$team = _parse_team_file($membersXML);

//die($team);

//check to see if all the users are in the wp table and find the first
//administrator

$firstAdmin = '';
$firstAdminUserID;

$currentUserNum = 0;


foreach ($team as $user)
{
  $userID = $wpdb->get_row( "SELECT ID FROM $wpdb->users WHERE user_login = '$user->username'");
  if (! $userID)
    {
      status_header(400);
      echo "User with name $user->username does not have a blog username! :";
      exit(0);
    }
  
  if ($firstAdmin == '' && ($user->teams[$project_name][0] == "ProjectAdmin") )
    {
      $firstAdmin = $user->username;
      $firstAdminUserID = $wpdb->get_row( "SELECT ID FROM $wpdb->users WHERE user_login = '$user->username'");
      unset($team[$currentUserNum]);
      echo "The first admin found is $firstAdmin :";
    }
  $currentUserNum = $currentUserNum + 1;
}

echo "Creating the blog with just this admin user for now $firstAdmin with id $firstAdminUserID->ID: ";
$blog_id = wpmu_create_blog($domain, $path, $title, $firstAdminUserID->ID);
add_blog_option($blog_id, "activated", "true");

/* Set "openplans" as the default theme */
update_blog_option($blog_id, "template", "openplans");
update_blog_option($blog_id, "stylesheet", "openplans");

if (!$blog_id) 
{
  status_header(500);
  echo("Error creating blog");
}
else 
{
  echo("Created blog ID $blog_id : ");
}

echo "Now adding the rest of the team to the blog :";

//add each user to allow to work on the blog
foreach ($team as $user)
{
  $userID = $wpdb->get_row( "SELECT ID FROM $wpdb->users WHERE user_login = '$user->username'");
  $op_role =  $user->teams[$project_name][0];
  $wp_role = '';
  
  if ($op_role === "ProjectMember")
    {
      $wp_role = 'author';
    }
  if ($op_role === "ProjectAdmin")
    {
      $wp_role = 'administrator';
    }
  
  echo "Adding the user $user->username to the blog :";
  add_user_to_blog($blog_id, $userID->ID, $wp_role);  
}

$titleForFirstPost = 'Example Post';

$contentForFirstComment = '
This comment was created automatically.
If you are a project administrator, you can edit or delete this comment by clicking on the <b>Edit</b> link below.
';
$authorForFirstComment = 'Site Admin';
$urlForFirstComment = '';

$contentForFirstPost = '
Welcome to the blog of project '.$project_name.'.  This post was automatically generated to help you get started.

To change this post, click the <b>Edit</b> link in the upper left corner of this post.  From there, you can edit this post or delete it entirely.

To write a new post, click the big orange <b>Write a post</b> button. As an adminstrator, you will be able to both write draft posts and publish them to the blog.  Members of this project who are not administrators can write posts, but not publish them.

Check out the sidebar to the right.  Here you can find:<ul>
<li> a list of the <b>Categories</b> used by this blog to tag and organize its posts</li>
<li>the <b>Archives</b> where you can navigate to older posts</li>
<li>the <b>Blogroll</b>, which can contain links to other blogs and sites related to your project</li>
<li>
<b>Feeds</b> that can keep you updated on any new posts or comments to the blog</li>
</ul>
<br>  To manage your blog posts and comments, add blogroll links, or see other advanced options, click the <b>Administer your blog</b> link just below the Write a post button.

<p align="right">Click the <b>Comments</b> link below to see comments on this post.</p>
';

//edit fist blog post to be an openplans style welcome
$wpdb->query("UPDATE $wpdb->posts SET post_content='$contentForFirstPost' WHERE ID=1");
$wpdb->query("UPDATE $wpdb->posts SET post_title='$titleForFirstPost' WHERE ID=1");



$wpdb->query("UPDATE $wpdb->comments SET comment_content='$contentForFirstComment' WHERE comment_ID=1");
$wpdb->query("UPDATE $wpdb->comments SET comment_author='$authorForFirstComment' WHERE comment_ID=1");
$wpdb->query("UPDATE $wpdb->comments SET comment_author_url='$urlForFirstComment' WHERE comment_ID=1");

//echo ("SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl' ");
//global $current_blog;
//echo ":".$current_blog->domain.":";
//echo ":".$current_blog->path.":";

	/* Lets add some default options if they don't exist
			If an option with the specified name already exists, no changes are made to its value
			or to the database as a whole. add_option() can only add options, not alter them.*/

	$tmp_noanswer =	'<p align="center">
<strong>Error:</strong> Please press the back button and fill the required field for spam protection.
</p>';
	$tmp_wronganswer = '<p align="center">
<strong>Error:</strong> You have entered the wrong sum in the spam protection field.
<br />Press the back button and try again.
</p>';
	$optionarray_def = array(
		'mcsp_opt_numbers'				=> '1~1, 2~2, 3~3, 4~4, 5~5, 6~6, 7~7, 8~8, 9~9, 10~10',
		'mcsp_opt_msg_no_answer' 		=> $tmp_noanswer,
		'mcsp_opt_msg_wrong_answer' 	=> $tmp_wronganswer,
		'mcsp_opt_fieldname_useranswer' => 'mcspvalue',
		'mcsp_opt_fieldname_mathresult' => 'mcspinfo',
		);
	add_option('plugin_mathcommentspamprotection', $optionarray_def, 'Math Comment Spam Protection Plugin Options');

###############FUNCTIONS DEFS#####################

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



function _parse_team_file ($data)
{
  global $project_name;
  
  $return_team = array();
  if (! ($xmlparser = xml_parser_create()) )
    { 
      die ("Cannot create parser");
    }
  else
    {
    }		
  
  
  $data=eregi_replace(">"."[[:space:]]+"."<","><",$data);
  
  xml_parse_into_struct($xmlparser, $data, $vals, $index);
  //die(print_r($vals));
  
  foreach ($vals as $val)
    {
      //die ($val[tag]);
      
      if ($val[type] == "open" || $val[type] == "complete")
	{
	  
	  $currentMemberNumber = sizeof ($return_team) - 1;
	  $currentTeamNumber = sizeof ( $return_team[$currentMemberNumber] ) - 1;
	  switch ($val[tag])
	    {
	    case ("MEMBER"):
	      //die ("got a member");
	      array_push($return_team, new member_profile());
	      break;
	    case ("ID"):
	      //die ("got an id");
	      $return_team[$currentMemberNumber]->username = $val[value];
	      $return_team[$currentMemberNumber]->teams[$project_name] = array();
	      //die(print_r($return_team));
	      break;
	    case ("ROLE"):
	      array_push($return_team[$currentMemberNumber]->teams[$project_name], $val[value]);
	      break; 
	      
	    }
	}
      
    }

  //die(print_r($return_team));
  
  xml_parser_free($xmlparser);	
  
  return $return_team;
}
###############END FUNCTIONS DEFS#####################

?>
