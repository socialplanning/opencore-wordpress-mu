<?php
require_once('admin.php');
$title = __('Site Options');
$parent_file = 'wpmu-admin.php';

include('admin-header.php');

if( is_site_admin() == false ) {
    die( __('<p>You do not have permission to access this page.</p>') );
}

if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}

?>
<div class="wrap"> 
	<h2><?php _e('Site Options') ?></h2> 
	<form name="form1" method="POST" action="wpmu-edit.php"> 
	<input type='hidden' name='action' value='siteoptions'>
	<?php wp_nonce_field( "siteoptions" ); ?>
	<fieldset class="options">
		<legend><?php _e('Operational Settings <em>(These settings cannot be modified by blog owners)</em>') ?></legend> 
		<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Site Name:') ?></th> 
		<td><input name="site_name" type="text" id="site_name" style="width: 95%" value="<?php echo $current_site->site_name ?>" size="45" />
		<br />
		<?php _e('What you would like to call this website.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Site Admin Email:') ?></th> 
		<td><input name="admin_email" type="text" id="admin_email" style="width: 95%" value="<?php echo stripslashes( get_site_option('admin_email') ) ?>" size="45" />
		<br />
		<?php printf( __( 'Registration and support mails will come from this address. Make it generic like "support@%s"' ), $current_site->domain ); ?></td>
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Welcome Email:') ?></th> 
		<td><textarea name="welcome_email" id="welcome_email" rows='5' cols='45' style="width: 95%"><?php echo stripslashes( get_site_option('welcome_email') ) ?></textarea>
		<br />
		<?php _e('The welcome email sent to new blog owners.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('First Post:') ?></th> 
		<td><textarea name="first_post" id="first_post" rows='5' cols='45' style="width: 95%"><?php echo stripslashes( get_site_option('first_post') ) ?></textarea>
		<br />
		<?php _e('First post on a new blog.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Banned Names:') ?></th> 
		<td><input name="illegal_names" type="text" id="illegal_names" style="width: 95%" value="<?php echo implode( " ", get_site_option('illegal_names') ); ?>" size="45" />
		<br />
		<?php _e('Users are not allowed to register these blogs. Separate names by spaces.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Limited Email Registrations:') ?></th> 
		<td><input name="limited_email_domains" type="text" id="limited_email_domains" style="width: 95%" value="<?php echo get_site_option('limited_email_domains') == '' ? '' : @implode( " ", get_site_option('limited_email_domains') ); ?>" size="45" />
		<br />
		<?php _e('If you want to limit blog registrations to certain domains. Separate domains by spaces.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Banned Email Domains:') ?></th> 
		<td><textarea name="banned_email_domains" id="banned_email_domains" cols='40' rows='5'><?php echo get_site_option('banned_email_domains') == '' ? '' : @implode( "\n", get_site_option('banned_email_domains') ); ?></textarea>
		<br />
		<?php _e('If you want to ban certain email domains from blog registrations. One domain per line.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Blog upload space:') ?></th> 
		<td><input name="blog_upload_space" type="text" id="blog_upload_space" value="<?php echo get_site_option('blog_upload_space', 10) ?>" size="3" /> MB
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Upload File Types:') ?></th> 
		<td><input name="upload_filetypes" type="text" id="upload_filetypes" value="<?php echo get_site_option('upload_filetypes', 'jpg jpeg png gif') ?>" size="45" />
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Max upload file size:') ?></th> 
		<td><input name="fileupload_maxk" type="text" id="fileupload_maxk" value="<?php echo get_site_option('fileupload_maxk', 300) ?>" size="5" /> KB
		</tr> 
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Administration Settings') ?></legend> 
		<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Site Admins:') ?></th> 
		<td><input name="site_admins" type="text" id="site_admins" style="width: 95%" value="<?php echo implode( " ", get_site_option( 'site_admins', array( 'admin' ) ) ) ?>" size="45" />
		<br />
		<?php _e('These users may login to the main blog and administer the site. Space separated list of usernames.') ?></td> 
		</tr> 
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Site Wide Settings <em>(These settings may be overridden by blog owners)</em>') ?></legend> 
		<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<?php
		$lang_files = glob( ABSPATH . WPINC . "/languages/*.mo" );
		$lang = get_site_option( "WPLANG" );
		if( is_array( $lang_files ) ) {
			?>
			<tr valign="top"> 
			<th width="33%" scope="row"><?php _e('Default Language:') ?></th> 
			<td><select name="WPLANG" id="WPLANG">
			<?php
			echo "<option value=''>".__('Default')."</option>";
			while( list( $key, $val ) = each( $lang_files ) ) { 
				$l = basename( $val, ".mo" );
				echo "<option value='$l'";
				echo $lang == $l ? " selected" : "";
				echo "> $l</option>";
			}
			?>
				</select></td>
				</tr> 
				<?php
		} // languages
		?>
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Menus <em>(Enable or disable WP Backend Menus)</em>') ?></legend> 
		<table cellspacing="2" cellpadding="5" class="editform"> 
		<tr><th scope='row'><?php _e("Menu"); ?></th><th scope='row'><?php _e("Enabled"); ?></th></tr>
		<?php
		$menu_perms = get_site_option( "menu_items" );
		$menu_items = array( "plugins" );
		while( list( $key, $val ) = each( $menu_items ) ) 
		{ 
			if( $menu_perms[ $val ] == '1' ) {
				$checked = ' checked';
			} else {
				$checked = '';
			}
			print "<tr><th scope='row'>" . ucfirst( $val ) . "</th><td><input type='checkbox' name='menu_items[" . $val . "]' value='1'" . $checked . "></tr>"; 
		}
		?>
		</table>
	</fieldset>
	<fieldset class="options">
	</fieldset>
	<p class="submit"> 
	<input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /> 
	</p> 
	</form> 
</div>
<?php include('./admin-footer.php'); ?>
