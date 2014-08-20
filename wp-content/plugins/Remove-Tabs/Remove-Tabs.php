<?php
/*
Plugin Name: Remove Tabs
Plugin URI: http://www.ashish.com
Description: Remove Help Tab and Screen Options Tab
Author: aashish
Author URI: http://www.exe.ie
*/

/* It will remove the tabs, not hide them with CSS */

add_filter( 'contextual_help', 'mytheme_remove_help_tabs', 999, 3 );
function mytheme_remove_help_tabs($old_help, $screen_id, $screen){
    $screen->remove_help_tabs();
    return $old_help;
}

add_filter('screen_options_show_screen', '__return_false');
?>