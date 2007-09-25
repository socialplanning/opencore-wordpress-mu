<?
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

?>
