<?php
if ( function_exists('register_sidebar') )
	register_sidebar(array(
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</div></li>',
        'before_title' => '<div class="widget-header"><h2>',
        'after_title' => '</h2></div><div class="widget-content">',
    ));

?>
