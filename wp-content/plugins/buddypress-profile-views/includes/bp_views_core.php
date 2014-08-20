<?

add_action("bp_after_member_header","bp_profile_views_count");


function bp_profile_views_count(){
	global $bp, $wpdb;
	$user_id = bp_displayed_user_id() ? bp_displayed_user_id() : bp_loggedin_user_id();
	


	
	$visit_count  = $wpdb->get_var( $wpdb->prepare("SELECT `views` FROM wp_bp_profilevisits where userid=$user_id" ));
    $visit_count = $visit_count ? $visit_count : 0;

	$duserid=$bp->displayed_user->id;
	
	echo"<div style=\"clear:both\"><strong>Total Profile Visits:</strong> 
			<span style=\"color:#339\"><strong>$visit_count</strong></span></div>";	
}



?>