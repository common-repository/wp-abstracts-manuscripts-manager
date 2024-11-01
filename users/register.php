<?php

if ($_POST) {
    wpabstracts_users_register();
} else {
    if (get_option('users_can_register')) {
        wpabstracts_users_loadform();
    } else {
        wpabstracts_show_message(__("Registration is currently not allowed for this site. Please contact your site admin.", "wpabstracts"), 'alert-danger');
    }
}

function wpabstracts_users_register()
{
    global $wpdb;

    $userdata = array(
        'user_login'  => wp_unslash($_POST['email']),
        'user_email' => wp_unslash($_POST['email']),
        'user_pass' => $_POST['password']
    );

    // check captcha manually since registration_errors filter is out of scope for wp_insert_user
    if (get_option('wpabstracts_captcha')) {
        $captcha_input = (isset($_POST['captcha_input'])) ? strtolower(sanitize_text_field($_POST['captcha_input'])) : false;
        $captcha_hash = (isset($_POST['captcha_hash'])) ? $_POST['captcha_hash'] : 'zero';
        if (!$captcha_input || 0 != strcmp($captcha_hash, wpabstracts_generate_hash(str_replace(' ', '', $captcha_input)))) {
            wpabstracts_show_message(__("You have entered an invalid captcha code. Please try again.", "wpabstracts"), "alert-danger");
            unset($_POST['captcha_input']);
            unset($_POST['captcha_hash']);
            wpabstracts_users_loadform();
            return;
        }
    }

    $user_id = wp_insert_user($userdata);
    
    if (!is_wp_error($user_id)) {
        $user_settings = get_option('wpabstracts_user_settings');

        $activation_key = sha1(mt_rand(10000, 99999).time().$_POST['email']);

        unset($_POST['password']);
        unset($_POST['password_repeat']);

        $data = array(
            'data' => serialize($_POST),
            'user_id' => $user_id,
            'activation_key' => $activation_key,
            'status' => intval($user_settings->auto_activate_on)
        );
        $wpdb->show_errors();
        $wpa_user_id = $wpdb->insert($wpdb->prefix."wpabstracts_users", $data);

        if ($user_settings->sync_fields) {
            wpabstracts_sync_wpfields($_POST, $user_id);
        }
        $user = get_user_by('ID', $user_id); // get user after sync for update fields
        if ($user_settings->reg_message_on) {
            $success_message = wpabstracts_filter_shortcodes($user, $activation_key, $user_settings->reg_message);
            wpabstracts_show_message($success_message, 'alert-success');
        }
        if ($user_settings->reg_email_on) {
            wpabstracts_users_activation_email($user, $activation_key);
        }
        if ($user_settings->auto_activate_on) { // TODO implement auto redirect to dashboard
            //wpabstacts_user_after_registration($user_id);
        }
    } else {
        wpabstracts_show_message($user_id->get_error_message(), 'alert-danger');
        wpabstracts_users_loadform();
    }
}

function wpabstracts_users_loadform()
{
    if (is_user_logged_in()) {
        wpabstracts_show_message(__("You're currently logged in. Please sign out to register for an account.", "wpabstracts"), 'alert-danger');
        return;
    }
    $form_data = get_option('wpabstracts_registration_form');
    $settings = get_option('wpabstracts_user_settings');
    ob_start();
    include_once(WPABSTRACTS_PLUGIN_DIR . 'users/register.html.php');
    $html = ob_get_contents();
    ob_end_clean();
    echo $html;
}

function wpabstracts_users_activation_email($user, $act_key)
{
    $settings = get_option('wpabstracts_user_settings');
    
    $_subject = wpabstracts_filter_shortcodes($user, $act_key, $settings->email_subject);
    $_message = wpabstracts_filter_shortcodes($user, $act_key, wpautop($settings->reg_email));

    $to = apply_filters('wpabstracts_reg_email_to', $user->user_email, $user);
    $subject = apply_filters('wpabstracts_reg_email_subject', $_subject, $user);
    $message = apply_filters('wpabstracts_reg_email_message', $_message, $user);
    $headers = apply_filters('wpabstracts_reg_email_headers', __('From:', 'wpabstracts') . $settings->admin_name . " <" . $settings->admin_email . "> \r\n", $user);

    add_filter('wp_mail_content_type', 'wpabstracts_set_html_content_type');
    $success = wp_mail($to, $subject, $message, $headers);
    remove_filter('wp_mail_content_type', 'wpabstracts_set_html_content_type');
}

function wpabstracts_filter_shortcodes($user, $act_key, $content)
{
    $keys = array(
        '{DISPLAY_NAME}',
        '{USERNAME}',
        '{SITE_NAME}',
        '{SITE_URL}',
        '{DASHBOARD_URL}',
        '{ACTIVATE_LINK}'
    );

    $display_name = $user->display_name;
    $username = $user->user_login;
    $site_name = get_option('blogname');
    $site_url = home_url();
    $dashboard_url = wpabstracts_get_dashboard();
    $activation_link = $dashboard_url . "?task=activate&user=".$username."&key=".$act_key;

    $values = array(
        $display_name,
        $username,
        $site_name,
        $site_url,
        $dashboard_url,
        $activation_link
    );

    return str_replace($keys, $values, stripslashes($content));
}

function wpabstracts_redirect_loader($url, $msg, $timeout)
{
    echo "<br><div class='wpabstracts containter-fluid'>"
    . "<div class='wpabstracts alert alert-success' role='alert'>"
    . "<strong>" . $msg . "</strong>"
    . "</div>"
    . "</div>"
    . "<script>setTimeout(function(){ window.location = '" . $url . "'}, " . $timeout . ");</script>";
}
