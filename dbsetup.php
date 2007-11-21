<?php
/* TOPP file */
define('WP_INSTALLING', true);
require_once(dirname(__FILE__) . '/wp-config.php');
require_once(dirname(__FILE__) . '/openplans-auth.php');
if ($_POST['secret'] != get_openplans_secret()) {
    header('Status: 400 Bad Request');
    die("Invalid secret: '".$_POST['secret']."'\n");
}
$_POST['action'] = 'step3';
include('index-install.php');
?>
