<?php
/*
Plugin Name: OC Commenting
Plugin URI: www.openplans.org/project/occoment
Description: Allows tracing of comments to your OpenCore profile
Version: 0.1
Author: Douglas Mayle
Author URI: douglas.mayle.org


*/

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
?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('siteurl'); ?>/wp-content/mu-plugins/occomment/oc-styles.css" />
<script src="<?php bloginfo('siteurl'); ?>/wp-content/mu-plugins/occomment/Jugl.js" type="text/javascript"></script>
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
        // Set up namespace for Jugl.
        var uri = "http://jugl.tschaub.net/trunk/lib/Jugl.js";
        OpenCore.Jugl = window[uri];
        var template = new this.Jugl.Async.loadTemplate(
                        '<?php bloginfo('siteurl'); ?>' + '/wp-content/mu-plugins/occomment/oc-login-template.xml',
                        function (template) {
                            var env = OpenCore.memberInfo;
                            env.wp_url = OpenCore.wp_url;
                            var data = template.process(OpenCore.memberInfo);
                            var output = Ext.get('openplans_info');
                            output.appendChild(data);
                        });
        Ext.get("commentform").addListener("submit", OpenCore.relaysubmit);
    },
    wp_url: '<?php echo get_option('siteurl'); ?>',
    oc_url: 'http://cezan.openplans.org:2000/',
    wp_blogname: '<?php bloginfo('name'); ?>',
    memberInfo: {loggedin: false},
    /* Simple shortcut for adding a script tag to the page. */
    addscript: function(scriptSource) {
        if( document.createElement && document.childNodes ) {
            var scriptElem = document.createElement('script');
            scriptElem.setAttribute('type','text/javascript');
            scriptElem.setAttribute('src',scriptSource);
            document.getElementsByTagName('head')[0].appendChild(scriptElem);
        }
    },
    /* Handler for comment form submission. */
    relaysubmit: function(e) {
        var logCheck = Ext.get('oc-log-comment');
        if ((logCheck == undefined) || (!logCheck.dom.checked)) {
            return true;
        }
        OpenCore.unhide('oc-progress-spinner');
        e.preventDefault();
        // Post comment to WP first, because we care about the comment ID.
        Ext.Ajax.request({
            form: Ext.get('commentform').dom,
            success: OpenCore.wpcomment_onsuccess,
            failure: OpenCore.wpcomment_onfailure
            });
        return true;
    },
    wpcomment_onsuccess: function(transport, options) {
        // Get the URL to the new comment and include that in our form.
        var commentids = transport.responseText.match(/<li id="comment-(\d+)"/g);
        if (! commentids) {
            OpenCore.wpcomment_onfailure(transport);
            return;
        }
        var commentid = commentids.slice(-1)[0].match(/<li id="comment-(\d+)"/)[1];
        OpenCore.commenturl = location.href.split('#')[0] + '#comment-' + commentid;
        var commentform = Ext.get('commentform');
        var commentElem = document.createElement('input');
        commentElem.setAttribute('type', 'hidden');
        commentElem.setAttribute('name', 'commenturl');
        commentElem.setAttribute('value', OpenCore.commenturl);
        commentform.appendChild(commentElem);
        var blognameElem = document.createElement('input');
        blognameElem.setAttribute('type', 'hidden');
        blognameElem.setAttribute('name', 'blog_name');
        blognameElem.setAttribute('value', OpenCore.wp_blogname);
        commentform.appendChild(blognameElem);
        // Add a script tag that posts the trackback to openplans,
        // but persevere eventually if the script source fails to load.
        OpenCore._submittimer = setTimeout(function() {
            OpenCore.submitstatus(false,  "Timed out while sending comment to openplans.");}, 10000);
        OpenCore.addscript('/people/' + Ext.get('user_id').dom.value + '/trackback?' + Ext.Ajax.serializeForm(commentform.dom));
        return true;
    },
    wpcomment_onfailure: function(transport, options) {
        alert('Posting comment to wordpress failed. Try again later.');
    },
    /* Callback that runs after the trackback is posted to openplans. */
    submitstatus: function(status, msg) {
        clearTimeout(OpenCore._submittimer);
        if (! status) {
            if (msg == undefined) {
                msg = "unspecified error";
            }
            alert(msg);
        };
        OpenCore.hide('oc-progress-spinner');
        location.href = OpenCore.commenturl;
        // Since that's the same url with a different anchor,
        // we have to explicitly tell the browser to reload.
        location.reload();
    },
    hide: function(name) {
        Ext.get(name).addClass('oc-hidden');
    },
    unhide: function(name) {
        Ext.get(name).removeClass('oc-hidden');
    },
}

Ext.onReady(function() {

  /* Short and Sweet */
  OpenCore.init();

});
</script>
<?php
}
