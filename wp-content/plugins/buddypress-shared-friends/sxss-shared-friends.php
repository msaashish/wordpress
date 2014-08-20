<?php
/*
Plugin Name: sxss Buddypress Shared Friends
Plugin URI: http://sxss.info
Description: Plugin displays the firneds two users share on there profile page
Author: sxss
Version: 1.0
*/


// I18n
load_plugin_textdomain('sxss_sf', false, basename( dirname( __FILE__ ) ) . '/languages' );

function sxss_sf_friendlist_array($userid = false)
{
	// check for errors
	if($userid == false || false == is_user_logged_in() ) return false;
	
	global $wpdb;
	global $table_prefix;
	
	// check for valid userid
	$userid = (int) mysql_real_escape_string($userid);
	
	// query db
	$mysql = $wpdb->get_results("SELECT * FROM " . $table_prefix . "bp_friends WHERE initiator_user_id = '$userid' OR friend_user_id = '$userid' AND is_confirmed = 1", ARRAY_A);

	// save userids in array
	foreach($mysql as $data)
	{
		if($data["initiator_user_id"] == $userid) $return[] = $data["friend_user_id"];
		else $return[] = $data["initiator_user_id"];
	}
	
	// if no friends
	if( false == isset($return) ) return false;
	
	// return array with the friends userids
	return $return;
}

function sxss_sf_same_friends()
{
	// check for errors
	if( false == is_user_logged_in() ) return false; 
	
	global $bp;
	
	// get friendlists	
	$user_displayed = sxss_sf_friendlist_array($bp->displayed_user->id);
	$user_loggedin = sxss_sf_friendlist_array($bp->loggedin_user->id);

	// check for errors
	if( $user_displayed == false || $user_loggedin == false|| $user_loggedin == $user_displayed ) return false;
	
	// search for matches
	$gemeinsam = array_intersect($user_displayed, $user_loggedin);
	
	// return them
	return $gemeinsam;
}

function sxss_sf_gravatar_list()
{	
	// get array of same friends
	$gemeinsam = sxss_sf_same_friends();
	
	// get number of shared friends
	$count = sizeof( $gemeinsam );
	
	if( false == is_user_logged_in() || $gemeinsam == false || $count < 1 ) return false;
	
	// get gravatar size from db
	$size = get_option('sxss_sf_size');
	
	$return["list"] = "";
	$return["count"] = $count;
	
	// array to string (html)
	foreach($gemeinsam as $data)
	{
		$user_info = get_userdata($data);
			
		$return["list"] .= '<a style="padding-right: 5px !important;" title="' . $user_info->display_name . '" href="' . bp_core_get_user_domain($data) . '">' . get_avatar( $user_info->user_email, $size ) . '</a> ';		
	}
	
	return $return;
		
}

function sxss_sf_print_profile()
{	
	// check for errors
	if( false == sxss_sf_gravatar_list() || get_option('sxss_sf_display_profile') != 1 ) return false;
	
	// get list and number of gravatars
	$list = sxss_sf_gravatar_list();
	
	// save number
	$count = $list["count"];
	
	echo '<div id="sxss-same-friends">';
	
	// headline	
	echo "<h4>"; printf(_n('Shared friend: %d', 'Shared friends: %d', $count), $count); echo "</h4>";
	
	// list
	echo $list["list"];
		
	echo '</div><br style="clear: both;" />';

}

add_action('bp_after_profile_content', 'sxss_sf_print_profile');


function sxss_sf_widget($args) 
{
	// check for errors
	if( false != sxss_sf_gravatar_list() )
	{
		// get list and number of gravatars
		$list = sxss_sf_gravatar_list();

		extract( $args );

		echo $before_widget; 
		
		echo $before_title; 
		
		$count = $list["count"];
		
		printf(_n('%d shared friend', '%d shared friends', $count), $count);
		
		echo $after_title;
		
			echo '<div class="avatar-block">';
			
				echo $list["list"];
			
			echo '</div>';
			
		echo $after_widget;		

	}
		
}

register_sidebar_widget(__('Shared Friends', 'sxss_sf'), 'sxss_sf_widget');

// settingspage
function sxss_sf_settings() 
{
	// save settings
	if ($_POST['action'] == 'update')
	{
		// only save int
		$postsize = (int) $_POST["sxss_sf_size"];
		
		// save gravatar size
		update_option('sxss_sf_size', $postsize);
		
		// save checkbox
		if( true == isset($_POST["sxss_sf_display_profile"]) && $_POST["sxss_sf_display_profile"] == 1 ) update_option('sxss_sf_display_profile', 1);
		else update_option('sxss_sf_display_profile', 0);

		$message = '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.', 'sxss_sf') . '</strong></p></div>'; 
	} 

	// get size from db
	$size = get_option('sxss_sf_size');
	if( get_option('sxss_sf_display_profile') == 1 ) $sxss_sf_display_profile = "checked ";

	echo '

	<div class="wrap">

		'.$message.'

		<div id="icon-options-general" class="icon32"><br /></div>

		<h2>' . __('Buddypress: Shared Friends', 'sxss_sf') . '</h2>

		<form method="post" action="">

		<input type="hidden" name="action" value="update" />
		
		<p style="width: 500px;line-height: 20px;">' . __('The Buddypress Shared Friends plugin displays a list of shared friends on user profiles and in a <a href="widgets.php">widget</a>. The list of shared friends is only displayed, if the loggedin user is actualy on a profile page of another user and they have common friends!', 'sxss_sf') . '</p>

		<br /><p><input type="checkbox" name="sxss_sf_display_profile" value="1" ' . $sxss_sf_display_profile . '/> ' . __('Display the list of shared friends at the bottom of the profile page', 'sxss_sf') . '</p>
		
		<p><input type="text" style="width: 50px;" name="sxss_sf_size" value="'.$size.'"> ' . __('Size of your friends gravatars', 'sxss_sf') . '</p>

		<input type="submit" class="button-primary" value="' . __('Save settings', 'sxss_sf') . '" />

		</form>

	</div>';
}

// register settings page
function sxss_sf_admin_menu()
{  
	add_options_page(__('sxss Shared Friends', 'sxss_sf'), __('sxss Shared Friends', 'sxss_sf'), 9, 'sxss_sf', 'sxss_sf_settings');  
}  

add_action("admin_menu", "sxss_sf_admin_menu"); 

?>