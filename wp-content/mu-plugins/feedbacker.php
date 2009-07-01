<?php
/*
Plugin Name: Feedbacker
Plugin URI: http://www.openplans.org 
Description: Plugin to help with opencore-feedbacker integration 
Author: Anil Makhijani
Version: 0.1
Author URI: http://anilmakhijani.com
*/

function feedbacker_send_new_comment($comment_id) {
    $policy = feedbacker_get_policy();
    if ($policy != "closed_policy") {
        $policy = 0;
    }
    else {
        $policy = 1;
    }
    $comment = get_comment($comment_id);
    if (!$comment->comment_approved) {
       return;
    }
   $post = get_post($comment->comment_post_ID);
   $categories = get_the_category($comment->comment_post_ID);
   $cats = Array();
   foreach ($categories as $category) {
     array_push($cats, $category->category_nicename);
   }
   $author_map = Array ('name'=> $comment->comment_author, 'email'=>$comment->comment_author_email,
                        'uri'=>$comment->comment_author_url);
   // set the target url
   $updated = strtotime($comment->comment_date)-date('Z');
   $published = $updated;
   $postdata = Array ('title'=>json_encode($post->post_title), 
                      'updated'=>json_encode("@".$updated."@"),
                      'object_type'=>json_encode('comment'), 
                      'action'=>json_encode('comment posted'),
                      'author'=>json_encode($author_map),
                      'summary'=>json_encode(''),
                      'content'=>json_encode($comment->comment_content),
                      'link'=>json_encode($post->guid."#comment-".$comment_id), 
                      'published'=>json_encode("@".$published."@"),
                      'rights'=>json_encode(''),
                      'project'=>json_encode($_SERVER['HTTP_X_OPENPLANS_PROJECT']), 
                      'closed'=>json_encode($policy),
                      'categories'=>json_encode($cats));

//   $myFile = "/usr/local/topp/phpdebug.txt";
//   $fh = fopen($myFile, 'w') or die("can't open file");
//
//   foreach ($postdata as $key=>$value) {
//         $stringData = "$key => $value \n";
//           fwrite($fh, $stringData);
//   }
//
//   fclose($fh);

   $postdata_str = urlencode_array($postdata, "", "&");

   $url = TOPP_FEEDBACKER_URI;
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_POST, sizeof($postdata));
   curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata_str);
   curl_setopt($ch, CURLOPT_URL,$url);

   $result= curl_exec ($ch);
   curl_close ($ch);
}


function feedbacker_send_new_post($post_id) {
    $policy = feedbacker_get_policy();
    if ($policy != "closed_policy") {
        $policy = 0;
    }
    else {
        $policy = 1;
    }
    $post = get_post($post_id);
   $author = get_userdata($post->post_author);

   $categories = get_the_category($post_id);
   $cats = Array();
   foreach ($categories as $category) {
     array_push($cats, $category->category_nicename);
   }
 
   $author_map = Array ('name'=> $author->display_name, 'email'=>$author->user_email,
                        'uri'=>"http://www.livablestreets.com/people/".$author->user_login);
   // set the target url
   $updated = strtotime($post->post_modified)-date('Z');
   $published = strtotime($post->post_date)-date('Z');
   $postdata = Array ('title'=>json_encode($post->post_title), 
                      'updated'=>json_encode("@".$updated."@"),
                      'object_type'=>json_encode('blog_post'), 
                      'action'=>json_encode('posted'),
                      'author'=>json_encode($author_map),
                      'summary'=>json_encode($post->post_excerpt),
                      'content'=>json_encode($post->post_content),
                      'link'=>json_encode(get_permalink($post_id)), 
                      'published'=>json_encode("@".$published."@"),
                      'rights'=>json_encode(''),
                      'project'=>json_encode($_SERVER['HTTP_X_OPENPLANS_PROJECT']), 
                      'closed'=>json_encode($policy),
                      'categories'=>json_encode($cats));

   $postdata_str = urlencode_array($postdata, "", "&");

   $url = TOPP_FEEDBACKER_URI;
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_POST, sizeof($postdata));
   curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata_str);
   curl_setopt($ch, CURLOPT_URL,$url);

   $result= curl_exec ($ch);
   curl_close ($ch);
}

function urlencode_array(
    $var,                // the array value
    $varName,            // variable name to be used in the query string
    $separator = '&'    // what separating character to use in the query string
) {
    $toImplode = array();
    foreach ($var as $key => $value) {
        if (is_array($value)) {
            $toImplode[] = urlencode_array($value, "{$varName}[{$key}]", $separator);
        } else {
            $toImplode[] = "{$varName}{$key}=".urlencode($value);
        }
    }
    return implode($separator, $toImplode);
}

function feedbacker_get_policy() {
  global $wpdb;
  global $current_user;
  $url = TOPP_ZOPE_URI . "/projects/" . $_SERVER['HTTP_X_OPENPLANS_PROJECT'] . '/info.xml';
  $adminInfo = trim(file_get_contents(TOPP_ADMIN_INFO_FILENAME));
  list($usr, $pass) = split(":", $adminInfo);
  $file = _fetch_remote_file1($url, $usr, $pass);
  global $policy;

  if (!strchr($file->response_code, "200"))
    {
      die("Blog communication failure with opencore; url='$url'");
    }

  $project_policy = $file->results;

  if (! ($xmlparser = xml_parser_create()) )
    {
      die ("Cannot create parser");
    }

  $isMember = is_user_member_of_blog($current_user->id, $wpdb->blogid);
      xml_parse_into_struct($xmlparser, $project_policy, $vals, $index);

      foreach ($vals as $val)
    {
      if ($val["tag"] == "POLICY")
        {
          $policy = $val["value"];
        }
    }
   return $policy;

}

add_action('publish_post', 'feedbacker_send_new_post');
add_action('comment_post', 'feedbacker_send_new_comment');

?>
