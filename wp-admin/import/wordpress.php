<?php

class WP_Import {

	var $posts = array ();
	var $posts_processed = array ();
    // Array of arrays. [[0] => XML fragment, [1] => New post ID]
	var $file;
	var $id;
	var $mtnames = array ();
	var $newauthornames = array ();
	var $j = -1;

	function header() {
		echo '<div class="wrap">';
		echo '<h2>'.__('Import WordPress').'</h2>';
	}

	function footer() {
		echo '</div>';
	}

	function unhtmlentities($string) { // From php.net for < 4.3 compat
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		return strtr($string, $trans_tbl);
	}

	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__('Howdy! Upload your WordPress eXtended RSS (WXR) file and we&#8217;ll import the posts, comments, custom fields, and categories into this blog.').'</p>';
		echo '<p>'.__('Choose a WordPress WXR file to upload, then click Upload file and import.').'</p>';
		wp_import_upload_form("admin.php?import=wordpress&amp;step=1");
		echo '</div>';
	}

	function get_tag( $string, $tag ) {
		global $wpdb;
		preg_match("|<$tag.*?>(.*?)</$tag>|is", $string, $return);
		$return = preg_replace('|^<!\[CDATA\[(.*)\]\]>$|s', '$1', $return[1]);
		$return = $wpdb->escape( trim( $return ) );
		return $return;
	}

	function users_form($n) {
		global $wpdb, $testing;
		$users = get_users_of_blog($wpdb->blogid);
?><select name="userselect[<?php echo attribute_escape($n); ?>]">
	<option value="#NONE#">- Select -</option>
	<?php
		foreach ($users as $user) {
			echo '<option value="'.$user->user_login.'">'.$user->user_login.'</option>';
		}
?>
	</select>
	<?php
	}

	//function to check the authorname and do the mapping
	function checkauthor($author) {
		global $wpdb;

		$map = $_POST['userselect'];

		$user_id = username_exists($map[$author]); //use that key to get the value of the author's name from $newauthornames

		return $user_id;
	}

	function get_entries() {
		set_magic_quotes_runtime(0);
		$importdata = array_map('rtrim', file($this->file)); // Read the file into an array

		$this->posts = array();
		$this->categories = array();
		$num = 0;
		$doing_entry = false;
		foreach ($importdata as $importline) {
			if ( false !== strpos($importline, '<wp:category>') ) {
				preg_match('|<wp:category>(.*?)</wp:category>|is', $importline, $category);
				$this->categories[] = $category[1];
				continue;
			}
			if ( false !== strpos($importline, '<item>') ) {
				$this->posts[$num] = '';
				$doing_entry = true;
				continue;	
			}
			if ( false !== strpos($importline, '</item>') ) {
				$num++;
				$doing_entry = false;
				continue;	
			}
			if ( $doing_entry ) {
				$this->posts[$num] .= $importline . "\n";
			}
		}

		foreach ($this->posts as $post) {
			$post_ID = (int) $this->get_tag( $post, 'wp:post_id' );
			if ($post_ID) {
				$this->posts_processed[$post_ID][0] = &$post;
				$this->posts_processed[$post_ID][1] = 0;
			}
		}
	}

	function get_wp_authors() {
		$temp = array ();
		$i = -1;
		foreach ($this->posts as $post) {
			if ('' != trim($post)) {
				++ $i;
				$author = $this->get_tag( $post, 'dc:creator' );
				array_push($temp, "$author"); //store the extracted author names in a temporary array
			}
		}

		// We need to find unique values of author names, while preserving the order, so this function emulates the unique_value(); php function, without the sorting.
		$authors[0] = array_shift($temp);
		$y = count($temp) + 1;
		for ($x = 1; $x < $y; $x ++) {
			$next = array_shift($temp);
			if (!(in_array($next, $authors)))
				array_push($authors, "$next");
		}

		return $authors;
	}

	function wp_authors_form() {
?>
<h2><?php _e('Assign Authors'); ?></h2>
<p><?php _e('To make it easier for you to edit and save the imported posts and drafts, you may want to change the name of the author of the posts. For example, you may want to import all the entries as <code>admin</code>s entries.'); ?></p>
	<?php


		$authors = $this->get_wp_authors();
		echo '<ol id="authors">';
		echo '<form action="?import=wordpress&amp;step=2&amp;id=' . $this->id . '" method="post">';
		wp_nonce_field('import-wordpress');
		$j = -1;
		foreach ($authors as $author) {
			++ $j;
			echo '<li>'.__('Current author:').' <strong>'.$author.'</strong><br />'.'Map to existing: ';
			$this->users_form($j);
			echo '</li>';
		}

		echo '<input type="submit" value="Submit">'.'<br/>';
		echo '</form>';
		echo '</ol>';

	}

	function select_authors() {
		$file = wp_import_handle_upload();
		if ( isset($file['error']) ) {
			echo '<p>'.__('Sorry, there has been an error.').'</p>';
			echo '<p><strong>' . $file['error'] . '</strong></p>';
			return;
		}
		$this->file = $file['file'];
		$this->id = (int) $file['id'];

		$this->get_entries();
		$this->wp_authors_form();
	}

	function process_categories() {
		global $wpdb;

		$cat_names = (array) $wpdb->get_col("SELECT cat_name FROM $wpdb->categories");

		while ( $c = array_shift($this->categories) ) {
			$cat_name = trim($this->get_tag( $c, 'wp:cat_name' ));

			// If the category exists we leave it alone
			if ( in_array($cat_name, $cat_names) )
				continue;

			$category_nicename	= $this->get_tag( $c, 'wp:category_nicename' );
			$posts_private		= (int) $this->get_tag( $c, 'wp:posts_private' );
			$links_private		= (int) $this->get_tag( $c, 'wp:links_private' );

			$parent = $this->get_tag( $c, 'wp:category_parent' );

			if ( empty($parent) )
				$category_parent = '0';
			else
				$category_parent = category_exists($parent);

			$catarr = compact('category_nicename', 'category_parent', 'posts_private', 'links_private', 'posts_private', 'cat_name');

			$cat_ID = wp_insert_category($catarr);
		}
	}

	function process_posts() {
		$i = -1;
		echo '<ol>';

		foreach ($this->posts as $post)
			$this->process_post($post);

		echo '</ol>';

		wp_import_cleanup($this->id);

		echo '<h3>'.sprintf(__('All done.').' <a href="%s">'.__('Have fun!').'</a>', get_option('home')).'</h3>';
	}

	function process_post($post) {
		global $wpdb;

		$post_ID = (int) $this->get_tag( $post, 'wp:post_id' );
  		if ( $post_ID && !empty($this->posts_processed[$post_ID][1]) ) // Processed already
			return 0;

		// There are only ever one of these
		$post_title     = $this->get_tag( $post, 'title' );
		$post_date      = $this->get_tag( $post, 'wp:post_date' );
		$post_date_gmt  = $this->get_tag( $post, 'wp:post_date_gmt' );
		$comment_status = $this->get_tag( $post, 'wp:comment_status' );
		$ping_status    = $this->get_tag( $post, 'wp:ping_status' );
		$post_status    = $this->get_tag( $post, 'wp:status' );
		$post_name      = $this->get_tag( $post, 'wp:post_name' );
		$post_parent    = $this->get_tag( $post, 'wp:post_parent' );
		$menu_order     = $this->get_tag( $post, 'wp:menu_order' );
		$post_type      = $this->get_tag( $post, 'wp:post_type' );
		$guid           = $this->get_tag( $post, 'guid' );
		$post_author    = $this->get_tag( $post, 'dc:creator' );

		$post_content = $this->get_tag( $post, 'content:encoded' );
		$post_content = preg_replace('|<(/?[A-Z]+)|e', "'<' . strtolower('$1')", $post_content);
		$post_content = str_replace('<br>', '<br />', $post_content);
		$post_content = str_replace('<hr>', '<hr />', $post_content);

		preg_match_all('|<category>(.*?)</category>|is', $post, $categories);
		$categories = $categories[1];

		$cat_index = 0;
		foreach ($categories as $category) {
			$categories[$cat_index] = $wpdb->escape($this->unhtmlentities(str_replace(array ('<![CDATA[', ']]>'), '', $category)));
			$cat_index++;
		}

		if ($post_id = post_exists($post_title, '', $post_date)) {
			echo '<li>';
			printf(__('Post <i>%s</i> already exists.'), stripslashes($post_title));
		} else {

			// If it has parent, process parent first.
			$post_parent = (int) $post_parent;
			if ($parent = $this->posts_processed[$post_parent]) {
				if (!$parent[1]) $this->process_post($parent[0]); // If not yet, process the parent first.
				$post_parent = $parent[1]; // New ID of the parent;
			}

			echo '<li>';
			printf(__('Importing post <i>%s</i>...'), stripslashes($post_title));

			$post_author = $this->checkauthor($post_author); //just so that if a post already exists, new users are not created by checkauthor

			$postdata = compact('post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'post_name', 'comment_status', 'ping_status', 'post_modified', 'post_modified_gmt', 'guid', 'post_parent', 'menu_order', 'post_type');
			$comment_post_ID = $post_id = wp_insert_post($postdata);

			// Memorize old and new ID.
			if ( $post_id && $post_ID && $this->posts_processed[$post_ID] )
				$this->posts_processed[$post_ID][1] = $post_id; // New ID.

			// Add categories.
			if (count($categories) > 0) {
				$post_cats = array();
				foreach ($categories as $category) {
					$cat_ID = (int) $wpdb->get_var("SELECT cat_ID FROM $wpdb->categories WHERE cat_name = '$category'");
					if ($cat_ID == 0) {
						$cat_ID = wp_insert_category(array('cat_name' => $category));
					}
					$post_cats[] = $cat_ID;
				}
				wp_set_post_categories($post_id, $post_cats);
			}	
		}

		// Now for comments
		preg_match_all('|<wp:comment>(.*?)</wp:comment>|is', $post, $comments);
		$comments = $comments[1];
		$num_comments = 0;
		if ( $comments) { foreach ($comments as $comment) {
			$comment_author       = $this->get_tag( $comment, 'wp:comment_author');
			$comment_author_email = $this->get_tag( $comment, 'wp:comment_author_email');
			$comment_author_IP    = $this->get_tag( $comment, 'wp:comment_author_IP');
			$comment_author_url   = $this->get_tag( $comment, 'wp:comment_author_url');
			$comment_date         = $this->get_tag( $comment, 'wp:comment_date');
			$comment_date_gmt     = $this->get_tag( $comment, 'wp:comment_date_gmt');
			$comment_content      = $this->get_tag( $comment, 'wp:comment_content');
			$comment_approved     = $this->get_tag( $comment, 'wp:comment_approved');
			$comment_type         = $this->get_tag( $comment, 'wp:comment_type');
			$comment_parent       = $this->get_tag( $comment, 'wp:comment_parent');

			if ( !comment_exists($comment_author, $comment_date) ) {
				$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_url', 'comment_author_email', 'comment_author_IP', 'comment_date', 'comment_date_gmt', 'comment_content', 'comment_approved', 'comment_type', 'comment_parent');
				wp_insert_comment($commentdata);
				$num_comments++;
			}
		} }

		if ( $num_comments )
			printf(' '.__('(%s comments)'), $num_comments);

		// Now for post meta
		preg_match_all('|<wp:postmeta>(.*?)</wp:postmeta>|is', $post, $postmeta);
		$postmeta = $postmeta[1];
		if ( $postmeta) { foreach ($postmeta as $p) {
			$key   = $this->get_tag( $p, 'wp:meta_key' );
			$value = $this->get_tag( $p, 'wp:meta_value' );
			$value = stripslashes($value); // add_post_meta() will escape.
			add_post_meta( $post_id, $key, $value );
		} }
	}

	function import() {
		$this->id = (int) $_GET['id'];

		$this->file = get_attached_file($this->id);
		$this->get_entries();
		$this->process_categories();
		$this->process_posts();
	}

	function dispatch() {
		if (empty ($_GET['step']))
			$step = 0;
		else
			$step = (int) $_GET['step'];

		$this->header();
		switch ($step) {
			case 0 :
				$this->greet();
				break;
			case 1 :
				check_admin_referer('import-upload');
				$this->select_authors();
				break;
			case 2:
				check_admin_referer('import-wordpress');
				$this->import();
				break;
		}
		$this->footer();
	}

	function WP_Import() {
		// Nothing.
	}
}

$wp_import = new WP_Import();

register_importer('wordpress', 'WordPress', __('Import <strong>posts, comments, custom fields, pages, and categories</strong> from a WordPress export file'), array ($wp_import, 'dispatch'));

?>
