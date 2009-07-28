<?php
/*
Plugin Name: OC Commenting
Plugin URI: www.openplans.org/project/occoment
Description: Allows tracing of comments to your OpenCore profile
Version: 0.1
Author: Douglas Mayle
Author URI: douglas.mayle.org


*/
$Opencore_remote_url = substr(get_option('siteurl'),0,strlen(get_option('siteurl')) - 4);
add_action("OC_ProgressSpinner", "oc_progress_spinner");
function oc_progress_spinner()
{
?>
<img id="oc-progress-spinner" class="oc-hidden" src="<?php bloginfo('siteurl') ?>/wp-content/mu-plugins/occomment/images/spinning_wheel_throbber.gif" />
<?php
}

add_action("OC_OpenPlansLink", "oc_comment_link");
function oc_comment_link()
{
?>
    <a class="oc-hidden" id="oc_login" href="http://www.openplans.org/login">Use my OpenPlans Login</a>
<?php
}

add_action("wp_head", "oc_js");
function oc_js()
{
    global $Opencore_remote_url;
?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('siteurl'); ?>/wp-content/mu-plugins/occomment/oc-styles.css" />
<script type="text/javascript">
var OpenCore = {
    /* Comment Page Initialization.  (i.e. Logged in User query and response handling,
     * as well as javascriptification of the page.
     * */
    init: function() {
        var logged_in = Ext.get('author_info');
        if (!logged_in) {
            // The user is not a logged in user, so there is no need to continue.
            return;
        }
        var commentbox = new Ext.Template('');
        commentbox.append('openplans_info', {id: OpenCore.memberInfo.id,
                         profileurl: OpenCore.memberInfo.profileurl,
                         logouturl:OpenCore.oc_url + 'logout'});
    },
    wp_url: '<?php echo get_option('siteurl'); ?>',
    oc_url: '<?php echo "$Opencore_remote_url"; ?>',
    wp_blogname: '<?php bloginfo('name'); ?>',
    memberInfo: {loggedin: false},
    hide: function(name) {
        var hide = Ext.get(name);
        if (hide) hide.addClass('oc-hidden');

    },
    unhide: function(name) {
        var unhide = Ext.get(name);
        if (unhide) unhide.removeClass('oc-hidden');

    },
}

Ext.onReady(function() {

  /* Short and Sweet */
  OpenCore.init();

});
</script>
<?php
}
