<?php
require_once('admin.php');
$title = __('WPMU Admin: Users');
$parent_file = 'wpmu-admin.php';

$id = intval( $_REQUEST[ 'id' ] );
if( is_site_admin() == false ) {
	die( __('<p>You do not have permission to access this page.</p>') );
}

switch( $_REQUEST[ 'action' ] ) {
	case "confirm":
	?>
		<form action='wpmu-users.php' method='POST'><input type='hidden' name='action' value='<?php echo wp_specialchars( $_GET[ 'action2' ] ) ?>'><input type='hidden' name='id' value='<?php echo wp_specialchars( $_GET[ 'id' ] ) ?>'><input type='hidden' name='ref' value='<?php if( isset( $_GET[ 'ref' ] ) ) {echo wp_specialchars( $_GET[ 'ref' ] ); } else { echo $_SERVER[ 'HTTP_REFERER' ]; } ?>'><?php wp_nonce_field( $_GET[ 'action2' ] ) ?><p><?php echo wp_specialchars( $_GET[ 'msg' ] ) ?></p><input type='submit' value='Confirm'></form>
	<?php
		die();
	break;
	case "deleteuser":
		check_admin_referer('deleteuser');
		if( $id != '0' && $id != '1' )
			wpmu_delete_user($id);

		wp_redirect( add_query_arg( "update", "userdeleted", $_POST[ 'ref' ] ) );
		die();
	break;
	case "allusers":
		check_admin_referer('allusers');
		if( is_array( $_POST[ 'allusers' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'allusers' ] ) ) {
				if( $val != '' && $val != '0' && $val != '1' ) {
					$user_details = get_userdata( $val );
					if( $_POST[ 'userfunction' ] == 'delete' ) {
						wpmu_delete_user($val);
					} elseif( $_POST[ 'userfunction' ] == 'spam' ) {
						$blogs = get_blogs_of_user( $val, true );
						if( is_array( $blogs ) ) {
							while( list( $key, $details ) = each( $blogs ) ) { 
								update_blog_status( $details->userblog_id, "spam", '1' );
								do_action( "make_spam_blog", $details->userblog_id );
							}
						}
					}
				}
			}
		}
		wp_redirect( add_query_arg( "updated", "true", $_SERVER[ 'HTTP_REFERER' ] ) );
		die();
	break;
}

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}

print '<div class="wrap">';
switch( $_GET[ 'action' ] ) {
    case "edit":
    print "<h2>".__('Edit User')."</h2>";
    $options_table_name = $wpmuBaseTablePrefix . $_GET[ 'id' ] ."_options";
    $query = "SELECT *
              FROM   ".$wpdb->users."
	      WHERE  ID = '".$_GET[ 'id' ]."'";
    $userdetails = $wpdb->get_results( $query, ARRAY_A );
    $query = "SELECT *
              FROM   ".$wpdb->usermeta."
	      WHERE  user_id = '".$_GET[ 'id' ]."'";
    $usermetadetails= $wpdb->get_results( $query, ARRAY_A );
    ?>

    <table><td valign='top'>
    <form name="form1" method="post" action="wpmu-edit.php?action=updateuser"> 
    <input type="hidden" name="action" value="updateuser" /> 
    <input type="hidden" name="id" value="<?php echo intval( $_GET[ 'id' ] ) ?>" /> 
    <?php wp_nonce_field( "edituser" ); ?>
    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
    <?php
    unset( $userdetails[0][ 'ID' ] );
    while( list( $key, $val ) = each( $userdetails[0] ) ) { 
    	?>
	<tr valign="top"> 
	<th width="33%" scope="row"><?php echo ucwords( str_replace( "_", " ", $key ) ) ?></th> 
	<td><input name="option[<?php echo $key ?>]" type="text" id="<?php echo $val ?>" value="<?php echo $val ?>" size="40" /></td> 
	</tr> 
    	<?php
    }
    ?>
    </table>
    </td><td valign='top'>
    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
	<tr><th style='text-align: left'><?php _e('Name') ?></th><th style='text-align: left'><?php _e('Value') ?></th><th style='text-align: left'><?php _e('Delete') ?></th></tr>
    <?php
    while( list( $key, $val ) = each( $usermetadetails ) ) { 
	if( substr( $val[ 'meta_key' ], -12 ) == 'capabilities' )
	    return;
    	?>
	<tr valign="top"> 
	<th width="33%" scope="row"><input name="metaname[<?php echo $val[ 'umeta_id' ] ?>]" type="text" id="<?php echo $val[ 'meta_key' ] ?>" value="<?php echo $val[ 'meta_key' ] ?>"></th> 
	<td><input name="meta[<?php echo $val[ 'umeta_id' ] ?>]" type="text" id="<?php echo $val[ 'meta_value' ] ?>" value="<?php echo addslashes( $val[ 'meta_value' ] ) ?>" size="40" /></td> 
	<td><input type='checkbox' name='metadelete[<?php echo $val[ 'umeta_id' ] ?>]'></td>
	</tr> 
    	<?php
    }
    ?>
    </table>
    </td></table>

    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update User') ?> &raquo;" />
    </p>
    <?php
    break;
    default:
		if( isset( $_GET[ 'start' ] ) == false ) {
			$start = 0;
		} else {
			$start = intval( $_GET[ 'start' ] );
		}
		if( isset( $_GET[ 'num' ] ) == false ) {
			$num = 30;
		} else {
			$num = intval( $_GET[ 'num' ] );
		}

		$query = "SELECT * FROM ".$wpdb->users;
		if( $_GET[ 's' ] != '' ) {
			$search = '%' . addslashes( $_GET['s'] ) . '%';
			$query .= " WHERE user_login LIKE '$search' OR user_email LIKE '$search'";
		}
		if( isset( $_GET[ 'sortby' ] ) == false ) {
			$_GET[ 'sortby' ] = 'id';
		}
		if( $_GET[ 'sortby' ] == 'email' ) {
			$query .= ' ORDER BY user_email ';
		} elseif( $_GET[ 'sortby' ] == 'id' ) {
			$query .= ' ORDER BY ID ';
		} elseif( $_GET[ 'sortby' ] == 'login' ) {
			$query .= ' ORDER BY user_login ';
		} elseif( $_GET[ 'sortby' ] == 'name' ) {
			$query .= ' ORDER BY display_name ';
		} elseif( $_GET[ 'sortby' ] == 'registered' ) {
			$query .= ' ORDER BY user_registered ';
		}
		if( $_GET[ 'order' ] == 'DESC' ) {
			$query .= "DESC";
		} else {
			$query .= "ASC";
		}
		$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
		$user_list = $wpdb->get_results( $query, ARRAY_A );
		if( count( $user_list ) < $num ) {
			$next = false;
		} else {
			$next = true;
		}
?>
<h2><?php _e("Users"); ?></h2>
<form name="searchform" action="wpmu-users.php" method="get" style="float: left; width: 16em; margin-right: 3em;"> 
  <table><tr><td>
  <fieldset> 
  <legend><?php _e('Search Users&hellip;') ?></legend> 
  <input type='hidden' name='action' value='users' />
  <input type="text" name="s" value="<?php if (isset($_GET[ 's' ])) echo wp_specialchars($_GET[ 's' ], 1); ?>" size="17" /> 
  <input type="submit" name="submit" value="<?php _e('Search') ?>"  /> 
  </fieldset>
  <?php
  if( isset($_GET[ 's' ]) && $_GET[ 's' ] != '' ) {
	  ?><a href="/wp-admin/wpmu-blogs.php?action=blogs&s=<?php echo wp_specialchars($_GET[ 's' ], 1) ?>"><?php _e('Search Blogs:') ?> <?php echo wp_specialchars($_GET[ 's' ], 1) ?></a><?php
  }
  ?>
  </td><td>
  <fieldset> 
  <legend><?php _e('User Navigation') ?></legend> 
  <?php 

  $url2 = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ] . "&s=" .$_GET[ 's' ];

  if( $start == 0 ) { 
	  _e('Previous&nbsp;Users');
  } elseif( $start <= 30 ) { 
	  echo '<a href="wpmu-users.php?start=0' . $url2 . '">'.__('Previous&nbsp;Users').'</a>';
  } else {
	  echo '<a href="wpmu-users.php?start=' . ( $start - $num ) . '&' . $url2 . '">'.__('Previous&nbsp;Users').'</a>';
  } 
  if ( $next ) {
	  echo '&nbsp;||&nbsp;<a href="wpmu-users.php?start=' . ( $start + $num ) . '&' . $url2 . '">'.__('Next&nbsp;Users').'</a>';
  } else {
	  echo '&nbsp;||&nbsp;'.__('Next&nbsp;Users');
  }
  ?>
  </fieldset>
  </td></tr></table>
</form>

<br style="clear:both;" />

<?php

// define the columns to display, the syntax is 'internal name' => 'display name'
$posts_columns = array(
  'id'         => __('ID'),
  'login'      => __('Login'),
  'email'     => __('Email'),
  'name'       => __('Name'),
  'registered' => __('Registered'),
  'blogs'      => __('Blogs')
);
$posts_columns = apply_filters('manage_posts_columns', $posts_columns);

// you can not edit these at the moment
$posts_columns['control_edit']   = '';
$posts_columns['control_delete'] = '';

?>
<script language="javascript">
<!--
var checkflag = "false";
function check_all_rows() {
	field = document.formlist;
	if (checkflag == "false") {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allusers[]' )
				field[i].checked = true;}
		checkflag = "true";
		return "<?php _e('Uncheck All') ?>"; 
	} else {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allusers[]' )
				field[i].checked = false; }
		checkflag = "false";
		return "<?php _e('Check All') ?>"; 
	}
}
//  -->
</script>

<form name='formlist' action='wpmu-users.php' method='POST'>
<table width="100%" cellpadding="3" cellspacing="3"> 
	<tr>

<?php foreach($posts_columns as $column_id => $column_display_name) { ?>
	<th scope="col"><?php if( $column_id == 'blogs' ) { _e( "Blogs" ); } else { ?><a href="wpmu-users.php?sortby=<?php echo $column_id ?>&<?php if( $_GET[ 'sortby' ] == $column_id ) { if( $_GET[ 'order' ] == 'DESC' ) { echo "order=ASC&" ; } else { echo "order=DESC&"; } } ?>start=<?php echo $start ?>"><?php echo $column_display_name; ?></a></th><?php } ?>
<?php } ?>

	</tr>
<?php
if ($user_list) {
$bgcolor = '';
foreach ($user_list as $user) { 
$class = ('alternate' == $class) ? '' : 'alternate';
?> 
	<tr class='<?php echo $class; ?>'>

<?php

foreach($posts_columns as $column_name=>$column_display_name) {

	switch($column_name) {
	
	case 'id':
		?>
		<th scope="row"><input type='checkbox' id='<?php echo $user[ 'ID' ] ?>' name='allusers[]' value='<?php echo $user[ 'ID' ] ?>' /> <label for='<?php echo $user[ 'ID' ] ?>'><?php echo $user[ 'ID' ] ?></label></th>
		<?php
		break;

	case 'login':
		?>
		<td><label for='<?php echo $user[ 'ID' ] ?>'><?php echo $user[ 'user_login' ] ?></label>
		</td>
		<?php
		break;

	case 'name':
		?>
		<td><?php echo $user[ 'display_name' ] ?></td>
		<?php
		break;

	case 'email':
		?>
		<td><?php echo $user[ 'user_email' ] ?></td>
		<?php
		break;

	case 'registered':
		?>
		<td><?php echo $user[ 'user_registered' ] ?></td>
		<?php
		break;

	case 'blogs':
		$blogs = get_blogs_of_user( $user[ 'ID' ], true );
		?>
		<td><?php if( is_array( $blogs ) ) 
				while( list( $key, $val ) = each( $blogs ) ) { 
					print '<a href="wpmu-blogs.php?action=editblog&id=' . $val->userblog_id . '">' . str_replace( '.' . $current_site->domain, '', $val->domain . $val->path ) . '</a> (<a '; 
					if( get_blog_status( $val->userblog_id, 'spam' ) == 1 )
						print 'style="background-color: #f66" ';
					print 'target="_new" href="http://'.$val->domain . $val->path.'">' . __('View') . '</a>)<BR>'; 
				} ?></td>
		<?php
		break;

	case 'control_edit':
		?>
		<td><?php echo "<a href='user-edit.php?user_id=".$user[ 'ID' ]."' class='edit'>" . __('Edit') . "</a>"; ?></td>
		<?php
		break;

	case 'control_delete':
		?>
		<td><?php echo "<a href='wpmu-users.php?action=confirm&action2=deleteuser&amp;msg=" . urlencode( __("You are about to delete this user.") ) . "&amp;id=".$user[ 'ID' ]."&amp;redirect=".wpmu_admin_redirect_url()."' class='delete'\">" . __('Delete') . "</a>"; ?></td>
		<?php
		break;

	default:
		?>
		<td><?php do_action('manage_users_custom_column', $column_name, $id); ?></td>
		<?php
		break;
	}
}
?>
	</tr> 
<?php
}
} else {
?>
  <tr style='background-color: <?php echo $bgcolor; ?>'> 
    <td colspan="8"><?php _e('No users found.') ?></td> 
  </tr> 
<?php
} // end if ($users)
?> 
</table> 
<p><input type=button value="<?php _e('Check All') ?>" onClick="this.value=check_all_rows()" /> </p>
<p><?php _e('Selected Users:') ?></p>
<ul>
    <?php wp_nonce_field( "allusers" ); ?>
<li><input type='radio' name='userfunction' id='delete' value='delete' /> <label for='delete'><?php _e('Delete') ?></label></li>
<li><input type='radio' name='userfunction' id='spam' value='spam' /> <label for='spam'><?php _e('Mark as Spammers') ?></label></li>
</ul>
<input type='hidden' name='action' value='allusers'>
<p><input type='submit' value='<?php _e('Apply Changes') ?>'></p>
</form>

<?php
}
?>
</div>
<form name="addform" action="wpmu-edit.php?action=adduser" method="post">
<div class="wrap">
<h2><?php _e('Add User') ?></h2>
<?php wp_nonce_field('add-user') ?>
<table>
<tr><th scope='row'><?php _e('Username') ?></th><td><input type="text" name="user[username]" /></td></tr>
<tr><th scope='row'><?php _e('Email') ?></th><td><input type="text" name="user[email]" /></td></tr>
</table>
<input type="submit" name="Add user" value="<?php _e('Add user') ?>" />
</form>
</div>
<?php include('admin-footer.php'); ?>
