<?php
require_once('admin.php');

$title = __('Options');
$this_file = 'options.php';
$parent_file = 'options-general.php';

wp_reset_vars(array('action'));

if ( !current_user_can('manage_options') )
	wp_die(__('Cheatin&#8217; uh?'));

if( $_GET[ 'adminhash' ] ) {
	$new_admin_details = get_option( 'adminhash' );
	if( is_array( $new_admin_details ) && $new_admin_details[ 'hash' ] == $_GET[ 'adminhash' ] && $new_admin_details[ 'newemail' ] != '' ) {
		update_option( "admin_email", $new_admin_details[ 'newemail' ] );
		delete_option( "adminhash" );
		delete_option( "new_admin_email" );
		wp_redirect( get_option( "siteurl" ) . "/wp-admin/options-general.php?updated=true" );
		exit;
	} else {
		wp_redirect( get_option( "siteurl" ) . "/wp-admin/options-general.php?updated=false" );
		exit;
	}
}
switch($action) {

case 'update':
	$any_changed = 0;

	check_admin_referer('update-options');

	if ( !$_POST['page_options'] ) {
		foreach ( (array) $_POST as $key => $value) {
			if ( !in_array($key, array('_wpnonce', '_wp_http_referer')) )
				$options[] = $key;
		}
	} else {
		$options = explode(',', stripslashes($_POST['page_options']));
	}

	if ($options) {
		foreach ($options as $option) {
			$option = trim($option);
			$value = trim($_POST[$option]);
			$value = sanitize_option($option, $value); // This does stripslashes on those that need it
			update_option($option, $value);
		}
	}
    
	$referred = remove_query_arg('updated' , wp_get_referer());
	$goback = add_query_arg('updated', 'true', wp_get_referer());
	$goback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $goback);
	wp_redirect($goback);
    break;

default:
if (!is_site_admin())
	die('Not admin');

	include('admin-header.php'); ?>

<div class="wrap">
  <h2><?php _e('All Options'); ?></h2>
  <form name="form" action="options.php" method="post" id="all-options">
  <?php wp_nonce_field('update-options') ?>
  <input type="hidden" name="action" value="update" />
	<p class="submit"><input type="submit" name="Update" value="<?php _e('Update Options &raquo;') ?>" /></p>
  <table width="98%">
<?php
$options = $wpdb->get_results("SELECT * FROM $wpdb->options ORDER BY option_name");

foreach ( (array) $options as $option) :
	$disabled = '';
	$option->option_name = attribute_escape($option->option_name);
	if ( is_serialized($option->option_value) ) {
		if ( is_serialized_string($option->option_value) ) {
			// this is a serialized string, so we should display it
			$value = maybe_unserialize($option->option_value);
			$options_to_update[] = $option->option_name;
			$class = 'all-options';
		} else {
			$value = 'SERIALIZED DATA';
			$disabled = ' disabled="disabled"';
			$class = 'all-options disabled';
		}
	} else {
		$value = $option->option_value;
		$options_to_update[] = $option->option_name;
		$class = 'all-options';
	}
	echo "
<tr>
	<th scope='row'><label for='$option->option_name'>$option->option_name</label></th>
<td>";

	if (strpos($value, "\n") !== false) echo "<textarea class='$class' name='$option->option_name' id='$option->option_name' cols='30' rows='5'>" . wp_specialchars($value) . "</textarea>";
	else echo "<input class='$class' type='text' name='$option->option_name' id='$option->option_name' size='30' value='" . attribute_escape($value) . "'$disabled />";

	echo "</td>
	<td>$option->option_description</td>
</tr>";
endforeach;
?>
  </table>
<?php $options_to_update = implode(',', $options_to_update); ?>
<p class="submit"><input type="hidden" name="page_options" value="<?php echo $options_to_update; ?>" /><input type="submit" name="Update" value="<?php _e('Update Options &raquo;') ?>" /></p>
  </form>
</div>


<?php
break;
} // end switch

include('admin-footer.php');
?>
