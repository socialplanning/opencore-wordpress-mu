<?php
require_once('admin.php');

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if( is_site_admin() == false ) {
    die( __('<p>You do not have permission to access this page.</p>') );
}
if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}
$themes = get_themes();
$allowed_themes = get_site_allowed_themes();
?>
<div class="wrap">

<form action='wpmu-edit.php?action=updatethemes' method='POST'>
<h3><?php _e('Site Themes') ?></h3>
<table border="0" cellspacing="5" cellpadding="5">
<caption><?php _e('Disable themes site-wide. You can enable themes on a blog by blog basis.') ?></caption>
<tr><th width="100"><?php _e('Active') ?></th><th><?php _e('Theme') ?></th><th><?php _e('Description') ?></th></tr>
<?php
foreach( $themes as $key => $theme ) {
	$theme_key = wp_specialchars( $theme[ 'Stylesheet' ] );
	$i++;
	$enabled = '';
	$disabled = '';
	if( isset( $allowed_themes[ $theme_key ] ) == true ) {
		$enabled = 'checked ';
	} else {
		$disabled = 'checked ';
	}
?>

<tr valign="top" style="<?php if ($i%2) echo 'background: #eee'; ?>">
<td>
<label><input name="theme[<?php echo $theme_key ?>]" type="radio" id="<?php echo $theme_key ?>" value="disabled" <?php echo $disabled ?>/><?php _e('No') ?></label>
&nbsp;&nbsp;&nbsp; 
<label><input name="theme[<?php echo $theme_key ?>]" type="radio" id="<?php echo $theme_key ?>" value="enabled" <?php echo $enabled ?>/><?php _e('Yes') ?></label>
</td>
<th scope="row" align="left"><?php echo $key ?></th> 
<td><?php echo $theme[ 'Description' ] ?></td>
</tr> 
<?php
}
?>
</table>
<p class="submit">
<input type='submit' value='<?php _e('Update Themes &raquo;') ?>' />
</p>
</form>

</div>
<?php include('admin-footer.php'); ?>
