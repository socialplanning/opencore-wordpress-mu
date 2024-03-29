<?php
require_once ('admin.php');
$title = __('Import');
$parent_file = 'edit.php';
require_once ('admin-header.php');
?>

<div class="wrap">
<h2><?php _e('Import'); ?></h2>
<p><?php _e('If you have posts or comments in another system, WordPress can import those into this blog. To get started, choose a system to import from below:'); ?></p>

<?php

// Load all importers so that they can register.
$import_loc = 'wp-admin/import';
$import_root = ABSPATH.$import_loc;
$imports_dir = @ dir($import_root);
if ($imports_dir) {
	while (($file = $imports_dir->read()) !== false) {
		if ($file{0} == '.') {
			continue;
		} elseif (substr($file, -4) == '.php') {
			require_once($import_root . '/' . $file);
		}
	}
}

$importers = get_importers();

if (empty ($importers)) {
	echo '<p>'.__('No importers are available.').'</p>'; // TODO: make more helpful
} else {
?>
<table class="widefat">

<?php
	$style = '';
	foreach ($importers as $id => $data) {
		$style = ('class="alternate"' == $style || 'class="alternate active"' == $style) ? '' : 'alternate';
		$action = "<a href='admin.php?import=$id' title='".wptexturize(strip_tags($data[1]))."'>{$data[0]}</a>";

		if ($style != '')
			$style = 'class="'.$style.'"';
		echo "
			<tr $style>
				<td class='import-system'>$action</td>
				<td class='desc'>{$data[1]}</td>
			</tr>";
	}
?>

</table>
<?php
}
?>

</div>

<?php

include ('admin-footer.php');
?>

