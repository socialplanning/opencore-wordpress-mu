<?php

if (! isset($wp_did_header)):

/* TOPP change -- force setup when a file exists, since we write our
   own wp-config.php */

$config_filename = dirname(__FILE__) . '/wp-config.php';
$db_filename = dirname(__FILE__) . '/../wp-setup-database.txt';

if (!file_exists($config_filename) || file_exists($db_filename) {
    if (strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false) {
        $path = '';
    } else {
        $path = 'wp-admin/';
    }
    if (file_exists($db_filename)) {
        $_POST['action'] = 'step3';
    }
    include( "index-install.php" ); // install WPMU!
    if (file_exists($db_filename)) {
        unlink($db_filename);
    }
    die();
}
/* End TOPP change */

$wp_did_header = true;

require_once($config_filename);

wp();
gzip_compression();

require_once(ABSPATH . WPINC . '/template-loader.php');

endif;

?>
