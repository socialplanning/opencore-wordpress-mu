<?
if (! $_ENV['TOPP_SECRET_FILENAME']) {
    die('$TOPP_SECRET_FILENAME must be set');
}

function get_cookie($name) {
    $pattern = '/'.$name.'="(.*?)"|'.$name.'=([^ ;]*)/';
    $matches = array();
    $header = $_SERVER['HTTP_COOKIE'];
    if (get_magic_quotes_gpc()) {
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
    $c = get_cookie('__ac');
    if (! $c) {
        return false;
    }
    $c = base64_decode($c);
    list($username, $auth) = explode("\0", $c, 2);
    # FIXME: failure?
    $secret = get_openplans_secret();
    $expect = hash_hmac("sha1", $username, $secret, false);
    if ($auth != $expect) {
        return false;
    }
    return $username;
}

function get_openplans_secret() {
    $filename = $_ENV['TOPP_SECRET_FILENAME'];
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
        $dest = "http://$domain/login?came_from=" . urlencode($current_url);
        wp_redirect($dest);
        exit();
    }
}

class Openplans_User {
    function Openplans_User($user_login) {
        $this->user_login = $user_login;
        $this->ID = 1;
        $this->id = $this->ID;
    }
}

function get_userdatabylogin($user_login) {
    $user_login = sanitize_user($user_login);
    if (empty($user_login)) {
        return false;
    }
    $user = wp_cache_get($user_login, 'userlogins');
    if ($user && is_site_admin($user_login)) {
        // I'm not sure why we do this fixup after the cache?
        $user->user_level = 10;
        $cap_key = $wpdb->prefix . "capabilities";
        $user->{$cap_key} = array('administrator' => '1');
        return $user;
    } elseif ($user) {
        return $user;
    }
    
    $user = new Openplans_User($user_login);

    if( is_site_admin( $user_login ) == true ) {
        $user->user_level = 10;
        $cap_key = $wpdb->prefix . 'capabilities';
        $user->{$cap_key} = array( 'administrator' => '1' );
    }                                                           
    
    wp_cache_add($user->user_login, $user, 'userlogins');
    
    return $user;
}
?>
