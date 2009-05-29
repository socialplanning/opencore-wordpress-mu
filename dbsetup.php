<?php
/* TOPP file */
define('WP_INSTALLING', true);
require_once(dirname(__FILE__) . '/wp-config.php');
require_once(dirname(__FILE__) . '/openplans-auth.php');
require_once(dirname(__FILE__) . '/wp-includes/capabilities.php');
if ($_SERVER['argv']) {
    if ($_SERVER['argv'][1] != get_openplans_secret()) {
        header('Status: 400 Bad Request');
        die("Invalid secret: '".$_SERVER['argv'][1]."'\n");
        return 255;
    }
} else if ($_POST['secret'] != get_openplans_secret()) {
    header('Status: 400 Bad Request');
    die("Invalid secret: '".$_POST['secret']."'\n");
}
$_POST['action'] = 'step3';
include('index-install.php');
?>
