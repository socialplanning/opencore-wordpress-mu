<?
function get_cookie($name) {
    $pattern = '/'.$name.'="(.*?)"|'.$name.'=([^ ;]*)/';
    $matches = array();
    $header = $_SERVER['HTTP_COOKIE'];
    if (get_magic_quotes_gpc()) {
      //echo "stripping<br><br> ";
        $header = stripslashes($header);
    }
    if (! preg_match($pattern, $header, $matches)) {
        return NULL;
    }
    if ($matches[1]) {
        return $matches[1];
    } else {
        return $matches[2];
    }
}

function check_openplans_cookie() {

  $debug = false;
  
    $c = get_cookie('__ac');
    if (! $c) {
        return false;
    }
    if ($debug)
      {
	echo $c;
	echo "<br>";echo "<br>";
      }
    $index = strpos($c, "%3D");
    
    $numToStrip = 0;
    if ($index)
      $numToStrip = (strlen($c) - $index)/3;
    
    if ($debug)
      {
	echo $numToStrip;
	echo "<br>";echo "<br>";
	echo "index $index";
	echo "<br>";echo "<br>";
      }
    $c = base64_decode($c);
    
    if ($debug)
      {
	print_r($c);
	echo "<br>";
	echo "<br>";
      }

    list($username, $auth) = explode("\0", $c, 2);
# FIXME: failure?
    $auth = substr($auth, 0, strlen($auth)-$numToStrip);
    $auth = chop($auth);
    $secret = get_openplans_secret();
    $expect = hash_hmac("sha1", $username, $secret, false);

    if ($debug)
      {
	echo ":$expect:";
	echo "<br>";
	echo "<br>";
	echo ":$auth:";
	echo "<br>";echo "<br>";
	echo strlen($auth);
	echo "<br>";echo "<br>";
	echo strlen($expect);
	echo "<br>";echo "<br>";
	die();
      }
    
    if ($auth != $expect) {
      //echo ("not authenticated");
      //die();
      return false;
    }

    //die();
    return $username;
}

function get_openplans_secret() {
    $filename = TOPP_SECRET_FILENAME;
    return trim(file_get_contents($filename));
}

function get_currentuserinfo() {
    global $current_user;
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        return false;
    }
    if (! empty($current_user)) {
        return;
    }
    $user = check_openplans_cookie();
    //die(print_r($user));
    if ($user) {
        wp_set_current_user($user);
    } else {
        wp_set_current_user(0);
    }
}

/* FIXME: should implement:
function get_userdata($user_id) {
}
*/

function auth_redirect() {
    if (! check_openplans_cookie()) {
        nocache_headers();
        // FIXME: do we need this?:
        wp_clearcookie();
        if ($current_blog->domain) {
            $domain = $current_blog->domain;
        } else {
            $domain = $_SERVER['HTTP_HOST'];
        }
        $current_url = "http://$domain{$_SERVER['REQUEST_URI']}";
        $dest = "http://$domain/login?came_from=" . urlencode($current_url) . "&portal_status_message=Please%20sign%20in%20to%20access%20this%20page";
        wp_redirect($dest);
        exit();
    }
}

function check_ajax_referer() {

  $cookie = explode('; ', urldecode(empty($_POST['cookie']) ? $_GET['cookie'] : $_POST['cookie']));
  $opCookie = $cookie[1];
  $opCookie = explode('=', $opCookie);
  $c = $opCookie[1];
  $ctemp = explode('"', $c);
  $c = $ctemp[1];
 
    if (! $c) {
      die('-1');
    }
    if ($debug)
      {
	echo $c;
	echo "<br>";echo "<br>";
      }
    $index = strpos($c, "%3D");
    
    $numToStrip = 0;
    if ($index)
      $numToStrip = (strlen($c) - $index)/3;
    
    if ($debug)
      {
	echo $numToStrip;
	echo "<br>";echo "<br>";
	echo "index $index";
	echo "<br>";echo "<br>";
      }
    $c = base64_decode($c);

    list($username, $auth) = explode("\0", $c, 2);
# FIXME: failure?
    $auth = substr($auth, 0, strlen($auth)-$numToStrip);
    $auth = chop($auth);
    $secret = get_openplans_secret();
    $expect = hash_hmac("sha1", $username, $secret, false);

    if ($auth != $expect) {
      //echo ("not authenticated");
      //die();
      die('-1');
    }

    do_action('check_ajax_referer');
  return true;

}

?>
