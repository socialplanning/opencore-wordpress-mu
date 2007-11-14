<?php
/*
Plugin Name: WordPress in OpenPlans
Plugin URI: http://www.openplans.org
Description: Modification for WordPress running inside of OpenPlans
Author: Nick Grossman
Version: 0.1
Author URI: http://www.openplans.org/people/nickyg
*/

#
# WP Plugins can act via action and filter hooks in the WP core, 
# or they can be used directly by templates
#  
# Info on the Plugin API is here: http://codex.wordpress.org/Plugin_API
#  
# A list of available Action hooks is here: http://codex.wordpress.org/Plugin_API/Action_Reference
# A list of available Filters hooks is here: http://codex.wordpress.org/Plugin_API/Filter_Reference
#

#
# Define your functions
#

  /* REMOVE ADMIN MENUS
  instructions at http://weeklytips.wordpress.com/2006/03/04/extra-tip-removing-admin-menus/
  Find array of admin menus at /wp-admin/menu.php
  */
  add_action('admin_head', 'oc_remove_admin_menu_items');
  function oc_remove_admin_menu_items() {
	global $menu, $submenu;
	
	// Presentation - change this to go straight to Widgets
  	//unset($menu[25]);
  	$menu[25] = array(__('Sidebar Widgets'), 'switch_themes', 'widgets.php');

  	
  	// Users / Profile
  	unset($menu[35]);
  	
  	// Plugins
  	unset($menu[30]);
  	
  	
  	// write -> page
  	unset($submenu['post-new.php'][10]);
  	
  	// manage -> pages
  	unset($submenu['edit.php'][10]);
  	
  	// manage -> uploads
  	unset($submenu['edit.php'][12]);
  	
  	// options -> General
  	unset($submenu['options-general.php'][10]);
  	
  	// redirect options -> general to options -> writing
  	$menu[40] = array(__('Options'), 'manage_options', 'options-writing.php');
  	
  	// options -> reading -> front page
  	// can't do this with a plugin -- must edit file directly
  	
  	// options -> permalinks
  	unset($submenu['options-general.php'][35]);
  	
  	// options -> delete blog
  	// can't figure out how to do this -- doesn't seem to be in wp-admin/menu.php

  }

  add_action('admin_head', 'oc_wp_admin_css');

  function oc_wp_admin_css() {
    ?>
      <style type="text/css">
        #titlediv input,
        textarea#content,
        textarea#excerpt
         {
          width: 450px;
         }
        
        #message p 
        {
         margin: 0;
         padding: 3px 0px;
        }
      </style>   
    <?php
  }

//add_action('admin_head', 'oc_xinha_head');
//add_action('wp_head', 'oc_xinha_head');
function oc_xinha_head() {
  ?>
  <!-- TOPP addition -->
    <link type="text/css" rel="stylesheet" title="blue-look" href="/++resource++xinha/skins/blue-look/skin.css" />
    <link type="text/css" rel="alternate stylesheet" title="green-look" href="/++resource++xinha/skins/green-look/skin.css" />
    <link type="text/css" rel="alternate stylesheet" title="xp-blue" href="/++resource++xinha/skins/xp-blue/skin.css" />
    <link type="text/css" rel="alternate stylesheet" title="xp-green" href="/++resource++xinha/skins/xp-green/skin.css" />
    <link type="text/css" rel="alternate stylesheet" title="inditreuse" href="/++resource++xinha/skins/inditreuse/skin.css" />
    <link type="text/css" rel="alternate stylesheet" title="blue-metallic" href="/++resource++xinha/skins/blue-metallic/skin.css" />
    
    <style>
    table.htmlarea {
    
        width: 100% !important;
        height: 300px;
    }
    </style>
    
    <script type="text/javascript">
    var _editor_url = "/++resource++xinha/";
    var _editor_lang = "en";
    var xinha_editor = "content";
    </script>
    <!-- END TOPP addition-->
    
    <!-- Load up the actual editor core -->
    <script type="text/javascript" src="/++resource++xinha/XinhaCore.js"></script>
    <script type="text/javascript" src="/++resource++xinha/xinhaconfig.js"></script>
  <?php 
}

//require_once("Snoopy.class.php");
add_action('template_redirect', 'check_blog_status');
function check_blog_status()
{
  global $wpdb;
  global $current_user;
  $url = TOPP_ZOPE_URI.$_SERVER['REQUEST_URI'];
  $url = preg_replace('/blog.*/','info.xml',$url);
  $adminInfo = trim(file_get_contents(TOPP_ADMIN_INFO_FILENAME));
  list($usr, $pass) = split(":", $adminInfo);
  $file = _fetch_remote_file1($url, $usr, $pass);

  if (!strchr($file->response_code, "200"))
    {
      die("Blog communication failure with opencore.");
    }
  
  $project_policy = $file->results;

  if (! ($xmlparser = xml_parser_create()) )
    { 
      die ("Cannot create parser");
    }
  
  $isMember = is_user_member_of_blog($current_user->id, $wpdb->blogid);
  if (!$isMember)
    {
      xml_parse_into_struct($xmlparser, $project_policy, $vals, $index);      

      foreach ($vals as $val)
	{
	  if ($val["tag"] == "POLICY")
	    {
	      $policy = $val["value"];
	    }
	}

      if ( !(($policy == "medium_policy") || ($policy == "open_policy")) )
	{
	  include(TEMPLATEPATH . '/index-unauthorized.php');
	  exit;
	}
    }

  if (get_blog_option($wpdb->blogid,"activated") == "false")
    {
      include(TEMPLATEPATH . '/index-deactivated.php');
      exit;
    }


}

function _fetch_remote_file1 ($url, $username, $password,  $headers = "" )
	{
	  // Snoopy is an HTTP client in PHP
	  require_once("Snoopy.class.php");
	  $client = new Snoopy();
	  $client->user = $username;
	  $client->pass = $password;
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

?>
