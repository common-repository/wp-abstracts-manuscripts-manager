<?php

$key = isset($_GET["key"]) ? sanitize_text_field($_GET["key"]) : false;
$user_login = isset($_GET["user"]) ? sanitize_text_field($_GET["user"]) : false;

if(is_user_logged_in()){
	wpabstracts_show_message(__('Oops, you are already logged in. Activation is only for new accounts.', 'wpabstracts'), 'alert-danger');
	return;
}

if(!$key || !$user_login){
	wpabstracts_show_message(__("Oops, you're got an invalid link. Please check your email and copy and paste the full link.", "wpabstracts"), 'alert-danger');
	return;
}

// activate user
$wp_user = get_user_by('login', $user_login);

if($wp_user){

	$wpa_user = wpabstracts_get_user($wp_user->ID);

	if($wpa_user->status){
		wpabstracts_show_message(__("Your account was activated already. Please login.", "wpabstracts"), 'alert-info');
		wpabstracts_get_login();
		return;
	}

	if($key == $wpa_user->activation_key){
		wpabstracts_activate_user($wp_user->ID, $message = false);
		wpabstracts_show_message(__('Your account was successfully activated.', 'wpabstracts'), 'alert-success');
		wpabstracts_get_login();
	}else{
		wpabstracts_show_message(__('Oops, your activation key do not match our records for you. Please try again or contact support.', 'wpabstracts'), 'alert-danger');
	}

}else{
	wpabstracts_show_message(__("Oops, we could not find this user. Please try again or contact support", "wpabstracts"), 'alert-danger');
}
