<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(!class_exists('WPAbstract_Abstracts_Table')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_classes.php'));
}

if(is_admin() && isset($_GET['tab']) && ($_GET["tab"]=="emails")){
	wpabstracts_emails_tab_header();
	$subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'templates';
	wpabstracts_email_tab_handler($subtab);
}

function wpabstracts_emails_tab_header(){

	$current = isset($_GET['subtab']) ? $_GET['subtab']  : 'templates';

	$sections = array(
		'templates' => __('Templates', 'wpabstracts'),
		'settings' => __('Settings', 'wpabstracts')
	);

	$sub_menu = '<div class="wpabstracts container-fluid">';
	$sub_menu .= '<ul class="submenu-container">';
	foreach( $sections as $slug => $name ){
		$class = ( $slug == $current ) ? "active" : "";
		$sub_menu .= "<li role='presentation' class='submenu-item ".$class."'><a href='?page=wpabstracts&tab=emails&subtab=$slug'><strong>$name</strong></a></li>";
	}
	$sub_menu .= '</ul>';
	$sub_menu .= '</div>';
	echo $sub_menu;
}

function wpabstracts_email_tab_handler($subtab) {
    $admin_page = WPABSTRACTS_PLUGIN_DIR . "emails/admin." . $subtab . ".php";
	if(is_file($admin_page)){
		ob_start();
		include($admin_page);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	}else{
		wpabstracts_show_message(__('Sorry, the tab you selected does not exist.', 'wpabstracts'), 'alert-danger');
	}
}

