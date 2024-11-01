<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(is_admin() && isset($_GET['tab']) && ($_GET["tab"]=="users")) {
	wpabstracts_user_header();
	$subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'manage';
	wpabstracts_user_tabs($subtab);
}

function wpabstracts_user_tabs($subtab) {
	$admin_page = WPABSTRACTS_PLUGIN_DIR . "users/users." . $subtab . ".php";
	if(is_file($admin_page)) {
		ob_start();
		include($admin_page);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	} else {
		wpabstracts_show_message(__('Sorry, the tab you selected does not exist.', 'wpabstracts'), 'alert-danger');
	}
}

function wpabstracts_user_header(){
	$current = isset($_GET['subtab']) ? $_GET['subtab']  : 'manage';
	$sections = array(
		'manage' => __('Manage Users', 'wpabstracts'),
		'formbuilder' => __('Form Builder', 'wpabstracts'),
		'settings' => __('Settings', 'wpabstracts')
	);
	$sub_menu = '<div class="wpabstracts container-fluid">';
	$sub_menu .= '<ul class="submenu-container">';
	foreach( $sections as $slug => $name ){
		$class = ( $slug == $current ) ? "active" : "";
		$sub_menu .= "<li role='presentation' class='submenu-item ".$class."'><a href='?page=wpabstracts&tab=users&subtab=$slug'><strong>$name</strong></a></li>";
	}
	$sub_menu .= '</ul>';
	$sub_menu .= '</div>';
	echo $sub_menu;
}
