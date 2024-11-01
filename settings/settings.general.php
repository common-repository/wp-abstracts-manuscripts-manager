<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

function wpabstracts_save_option($option) {
	switch($option){
		case 'wpabstracts_permitted_attachments':
		$_POST['options'][$option] = str_replace(" ", "", $_POST['options'][$option]);
		break;
		case 'wpabstracts_author_instructions':
		$_POST['options'][$option] = wp_kses_post($_POST['options'][$option]);
		break;
		case 'wpabstracts_terms_conditions':
		$_POST['options'][$option] = wp_kses_post($_POST['options'][$option]);
		break;
    }
	update_option($option, $_POST['options'][$option]);
}

if ($_POST) {
    // save / update options
	foreach ($_POST['options'] as $option => $value) {
		wpabstracts_save_option($option);
    }
    // save / update statuses
    if(isset($_POST['wpabstracts_status'])){
        $statuses = $_POST['wpabstracts_status'];
        foreach($statuses as $id => $status){
            if($status){
                $statusData = array('name' => $status);
                wpabstracts_upsert_status($id, $statusData);
            }
        }
    }
    // remove statuses is necessary
    if(isset($_POST['wpabstracts_delete_status'])){
        $ids = (array) $_POST["wpabstracts_delete_status"];
        wpabstracts_delete_statuses($ids);
    }
    do_action('wpabstracts_save_settings');
	wpabstracts_show_message(__('Awesome! Your settings updated.', 'wpabstracts'), 'alert-success');
}
?>
<?php $statuses = wpabstracts_get_statuses(); ?>
<?php do_action('wpabstracts_render_settings_before'); ?>
<div class="wpabstracts container-fluid wpabstracts-admin-container">
    <form method="post" id="wpabstracts_settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <h3><?php _e('General Settings', 'wpabstracts'); ?> <input type="submit" name="Submit" class="wpabstracts btn btn-primary" value="<?php _e('Save Changes', 'wpabstracts'); ?>" /></h3>
    <div class="wpabstracts row">

        <div class="wpabstracts col-xs-12 col-md-4">
            <div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
                    <h6 class="wpabstracts panel-title"><?php _e('Frontend Dashboard Configuration', 'wpabstracts'); ?></h6>
                </div>

                <div class="wpabstracts panel-body">

                    <div class="wpabstracts form-group col-xs-12">
                        <?php _e('Enable WP Abstracts Registration', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Use WP Abstracts User registration module instead of WordPress\'s default registration.', 'wpabstracts'); ?>">
                                <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                            </span>
                        <select name="options[wpabstracts_enable_register]" class="wpabstracts pull-right">
                            <option value="1" <?php selected(get_option('wpabstracts_enable_register'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
                            <option value="0" <?php selected(get_option('wpabstracts_enable_register'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
                        </select>
                    </div>

                    <div class="wpabstracts form-group col-xs-12">
                        <?php _e('Allow WordPress Admin Access', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Disables users from accessing Wordpress Admin dashboard. Enable this if you want to allow frontend access only.', 'wpabstracts'); ?>">
                                <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                            </span>
                        <select name="options[wpabstracts_frontend_dashboard]" class="wpabstracts pull-right">
                            <option value="1" <?php selected(get_option('wpabstracts_frontend_dashboard'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
                            <option value="0" <?php selected(get_option('wpabstracts_frontend_dashboard'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
                        </select>
                    </div>

                    <div class="wpabstracts form-group col-xs-12">
                        <?php _e('Show WordPress Admin Bar', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Disables users from seeing the Wordpress Admin Bar after sign in.', 'wpabstracts'); ?>">
                            <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                        </span>
                        <select name="options[wpabstracts_show_adminbar]" class="wpabstracts pull-right">
                            <option value="1" <?php selected(get_option('wpabstracts_show_adminbar'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
                            <option value="0" <?php selected(get_option('wpabstracts_show_adminbar'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
                        </select>
                    </div>

                    <div class="wpabstracts form-group col-xs-12">
                        <?php _e('Enable Captcha (recommended)', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Enable this to require users to enter a captcha code at login.', 'wpabstracts'); ?>">
                                    <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                                </span>
                        <select name="options[wpabstracts_captcha]" class="wpabstracts pull-right">
                                <option value="1" <?php selected(get_option('wpabstracts_captcha'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
                                <option value="0" <?php selected(get_option('wpabstracts_captcha'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
                        </select>
                    </div>

                    <div class="wpabstracts form-group col-xs-12">
                        <?php _e('Redirect on Login', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Enable this to redirect users to the dashboard on login from anywhere.', 'wpabstracts'); ?>">
                            <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                        </span>
                        <select name="options[wpabstracts_login_redirect]" class="wpabstracts pull-right">
                                <option value="1" <?php selected(get_option('wpabstracts_login_redirect'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
                                <option value="0" <?php selected(get_option('wpabstracts_login_redirect'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
                        </select>
                    </div>

                    <div class="wpabstracts form-group col-xs-12">
                        <?php _e('Refresh Captcha Secret (Optional)', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('As as entra layer of security reset your captcha secret.', 'wpabstracts'); ?>">
                                <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                        </span>
                        <div class="input-group refresh_btn">
                        <span class="input-group-addon" onclick="javascript:refreshSecret();"><i class="wpabstracts text-info glyphicon glyphicon-refresh"></i></span>
                            <input name="options[wpabstracts_captcha_secret]" type="text" id="wpabstracts_captcha_secret" value="<?php echo get_option('wpabstracts_captcha_secret'); ?>" class="wpabstracts form-control"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wpabstracts col-xs-12 col-md-8">
            <div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
                    <h6 class="wpabstracts panel-title">
                        <?php _e('Author Instructions', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Enter specific instructions for authors to follow for submissions', 'wpabstracts'); ?>">
                            <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                        </span>
                    </h6>
                </div>
                <div>
                    <?php
                        $settings = array( 'media_buttons' => false, 'textarea_name' => 'options[wpabstracts_author_instructions]', 'wpautop'=>true, 'dfw' => true, 'editor_height' => 100, 'quicktags' => false);
                        wp_editor(stripslashes(get_option('wpabstracts_author_instructions')), 'wpabstracts_author_instructions', $settings);
                    ?>
                </div>
            </div>

             <div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
                    <h6 class="wpabstracts panel-title">
                        <?php _e('Terms and Conditions', 'wpabstracts'); ?>
                        <span class="settings_tip" data-tip="<?php _e('Enter your terms and contitions for authors to agree.', 'wpabstracts'); ?>">
                            <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                        </span>
                    </h6>
                </div>
                <div>
                    <?php
                        $settings = array( 'media_buttons' => false, 'textarea_name' => 'options[wpabstracts_terms_conditions]', 'wpautop'=>true, 'dfw' => true, 'editor_height' => 100, 'quicktags' => false);
                        wp_editor(stripslashes(get_option('wpabstracts_terms_conditions')), 'wpabstracts_terms_conditions', $settings);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php apply_filters('wpabstracts_html_filter', $html = null, 'after_settings'); ?>
    </form>
</div>
<?php do_action('wpabstracts_render_settings_after'); ?>

<script>
function refreshSecret(){
  var chars = '0ABCDE1abcde2FGHIJK3fghijk4LMNOPQ5lmnopq6RSTU7rstu8VWXYZ9vwxyz';
  var secret = '';
  for(var i=0; i < 21; i++) {
    secret += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  jQuery('#wpabstracts_captcha_secret').val(secret);
}
</script>
