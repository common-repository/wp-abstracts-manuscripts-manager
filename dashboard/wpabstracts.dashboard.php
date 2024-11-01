<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
include_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'abstracts/abstracts.manage.php'));
global $post;
update_option('wpabstracts_event_id', $event_id); // set event_id for use in functions (redirection etc)
update_option('wpabstracts_dashboard_id', $post->ID); // set dashboard_id for use in functions (redirection etc)
$task = isset($_GET["task"]) ? sanitize_text_field($_GET["task"]) : '';
$userTasks = array('register', 'activate', 'lostpassword', 'resetpassword');

if(in_array($task, $userTasks)){
	do_action('wpabstracts_dashboard_init');
	$html = wpabstracts_user_getview($task, false);
	echo $html;
	return;
}

if(is_user_logged_in()){
	$user = wp_get_current_user();
	$task = isset($_GET["task"]) ? sanitize_text_field($_GET["task"]) : '';
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if($task && $id && !wpabstracts_user_can_manage($task, $id)) {
        wpabstracts_show_message(__("You do not have permissions to manage this resource.", "wpabstracts"), 'alert-danger');
        return;
    }

    // when on add or edit abstracts - hide new abstracts menu
    $show_nav_btns = true;
    if($task == 'add_abstract' || $task == 'edit_abstract'){
        $show_nav_btns = false;
    }
    wpabstracts_dashboard_header($user, $show_nav_btns);

    if($task == 'profile'){
        do_action('wpabstracts_dashboard_init');
        $html = wpabstracts_user_getview('profile', false);
        echo $html;
        return;
    }

    if(in_array('subscriber', $user->roles) || in_array('administrator', $user->roles)){
        if( $task == "add_abstract" ){
            wpabstracts_add_abstract($event_id);
        }
        else if ($task == "edit_abstract" ){
            wpabstracts_edit_abstract($id);
        }
        else if ($task == "view_abstract" ){
            wpabstracts_view_abstract($id);
        }
        else if ($task == "delete_abstract"){
            wpabstracts_delete_abstracts();
            wpabstracts_show_author_dashboard($user, $event_id);
        }else{
            wpabstracts_show_author_dashboard($user, $event_id);
        }
    }
} else{
	wpabstracts_get_login();
}

function wpabstracts_dashboard_header($user, $show_nav_btns) { 
    $header = apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'dashboard/html/dashboard.header.php');
    ob_start();
    include_once($header);
    $html = ob_get_contents();
    ob_end_clean();
    echo $html;
}

function wpabstracts_show_author_dashboard($user, $event_id) {
	$statuses = wpabstracts_get_statuses();
    if(isset($event_id) && intval($event_id) > 0) { // this means the shortcode has an event_id, else it [wpabstracts] - all events
        $filters = array('submit_by' => $user->ID, 'event' => $event_id);
    } else {
        $filters = array('submit_by' => $user->ID);
    }
    $abstracts = wpabstracts_get_abstract_where($filters); 
    $author_dashbaord = apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'dashboard/html/dashboard.author.php');
    ob_start();
    include_once($author_dashbaord);
    $html = ob_get_contents();
    ob_end_clean();
    echo $html;
}
