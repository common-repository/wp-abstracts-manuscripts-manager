<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(is_admin() && isset($_GET['tab']) && ($_GET["tab"]=="abstracts")){
	wpabstracts_abstracts_header();
	$subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'manage';
	wpabstracts_abstracts_tabs($subtab);
}

function wpabstracts_abstracts_tabs($subtab) {
	$page = apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'abstracts/abstracts.' . $subtab . '.php');
	if(is_file($page)){
		ob_start();
		include($page);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	}else{
		wpabstracts_show_message(__('Sorry, the tab you selected does not exist.', 'wpabstracts'), 'alert-danger');
	}
}

function wpabstracts_abstracts_header(){

	$current = isset($_GET['subtab']) ? $_GET['subtab']  : 'manage';

	$sections = array(
		'manage' => __('Manage Abstracts', 'wpabstracts'),
		//'formbuilder' => __('Form Builder', 'wpabstracts'),
		'settings' => __('Settings', 'wpabstracts')
	);

	$sub_menu = '<div class="wpabstracts container-fluid">';
	$sub_menu .= '<ul class="submenu-container">';
	foreach( $sections as $slug => $name ){
		$class = ( $slug == $current ) ? "active" : "";
		$sub_menu .= "<li role='presentation' class='submenu-item ".$class."'><a href='?page=wpabstracts&tab=abstracts&subtab=$slug'><strong>$name</strong></a></li>";
	}
	$sub_menu .= '</ul>';
	$sub_menu .= '</div>';
	echo $sub_menu;
}
