<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(is_user_logged_in() && is_super_admin()){
     if (isset($_GET['task']) && ($_GET['task']) == 'download') {
        if(isset($_GET['type']) && ($_GET['type'])){
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $type = $_GET['type'];
            switch($type){
                case 'users':
                    wpabstracts_useraddon_export_users();
                break;
            }
        }
    }
}else{
    defined('ABSPATH') or die("ERROR: You do not have permission to access this resource.");
}


function wpabstracts_useraddon_export_users() {

    global $wpdb;
    $wpa_users = $wpdb->prefix."wpabstracts_users";
    $wp_users = $wpdb->prefix."users";
    $query = "SELECT wpusers.ID, wpusers.user_email, wpausers.data, wpausers.status FROM $wpa_users AS wpausers LEFT JOIN $wp_users AS wpusers ON wpausers.user_id = wpusers.ID";
    $users = $wpdb->get_results($query);

    $form_fields = json_decode(get_option('wpabstracts_registration_form'));

    $header[] = "User ID";
    $header[] = "Email/Username";

    foreach($form_fields as $cid => $field){
        $header[$field->cid] = $field->label;
    }

    foreach($users as $user){
        $user_data = unserialize($user->data);
        $user_row = array();

        $user_row[] = $user->ID;
        $user_row[] = $user->user_email;

        if(is_array($user_data)) {
            foreach($user_data as $cid => $field){
                if(array_key_exists($cid, $header)){
                    $user_row[] = $field;
                }
            }
        }
        $finals[] = $user_row;
    }

    $exportName = "wpabstracts_users.csv";
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$exportName\"");
    ob_start();
    $file_report = fopen('php://output', 'w');
    fputcsv($file_report, array_values($header), ",");
    foreach ($finals AS $data) {
        fputcsv($file_report, array_values(stripslashes_deep($data)), ",");
    }
    fclose($file_report);
    $report = ob_get_contents();
    ob_end_clean();
    echo $report;
    exit(0);
}
