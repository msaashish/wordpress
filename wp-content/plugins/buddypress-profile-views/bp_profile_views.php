<?php

/**

 * Plugin Name: Bp Profile Views

 * Author: Venu Gopal Chaladi

 * Author URI:http://dhrusya.com

 * Version:1.1

 * Plugin URI:http://dhrusya.com/products

 * Description: Show the profile views count

 * License: GPL

 * Tested with Buddypress 1.5+

 * Date: 18th July 2012

 */

$bp_profilevisits_db_version = "1.1";

function bp_profile_views_init() {
	require( dirname( __FILE__ ) . '/includes/bp_views_core.php' );
}
add_action( 'bp_init', 'bp_profile_views_init' );



	add_action('bp_init', 'bp_countvisits');
	function bp_countvisits() {
		session_start();					
		if ( !isset($_SESSION['profile_visit']) or $_SESSION['profile_visit']!=bp_displayed_user_id()) {
			global $bp, $wpdb;
			$table_name = $wpdb->prefix . "bp_profilevisits";
			
			$user_id = bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id();
			//set_newuser_cookie($user_id);
			setcookie('profile_visit', bp_displayed_user_id(), strtotime('+1 day'));
			
			$_SESSION['profile_visit']= bp_displayed_user_id();
			//insert user data
			$sql="select count(id) from $table_name where userid=$user_id";
			$user_count = $wpdb->get_var( $wpdb->prepare($sql));
			 
			if($user_count<1){
				$sqli="insert into ".$wpdb->prefix . "bp_profilevisits values(NULL, $user_id, 1)";
				$wpdb->query( $sqli);
				//$wpdb->insert( $table_name, array( $lastid+1, 'userid' => $user_id, 'visits' => 1 ) );
			}else{
				//update			
				$wpdb->query( $wpdb->prepare("update $table_name set `views`=views+1 where userid=%d",$user_id ));
			}
		}
		
	}
	
	
function bp_profile_views_install() {
   global $wpdb;
   global $bp_profilevisits_db_version;

   $table_name = $wpdb->prefix . "bp_profilevisits";
      

	$sql="CREATE TABLE IF NOT EXISTS $table_name (
  	`id` int(10) NOT NULL AUTO_INCREMENT,
  	`userid` int(10) NOT NULL,
  	`views` float NOT NULL DEFAULT '0',
  	PRIMARY KEY (`id`)
	) ;";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);
 
   add_option("bp_profilevisits_db_version", $bp_profilevisits_db_version);
}


register_activation_hook(__FILE__,'bp_profile_views_install');
