<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

function wpabstracts_save_option() {
	$formOptions = new stdClass();
	$formOptions->admin_name = sanitize_text_field($_POST['admin_name']);
	$formOptions->admin_email = strtolower(sanitize_email($_POST['admin_email']));
	$formOptions->email_subject = sanitize_text_field($_POST['email_subject']);
	$formOptions->auto_activate_on = intval($_POST['auto_activate_on']);
	$formOptions->ignore_activation = intval($_POST['ignore_activation']);
	$formOptions->sync_fields = intval($_POST['sync_fields']); 
	$formOptions->display_name = sanitize_text_field($_POST['display_name']); 
	$formOptions->reg_message_on = intval($_POST['reg_message_on']);
	$formOptions->reg_message = wp_kses_post($_POST['reg_message']);
	$formOptions->reg_email_on = intval($_POST['reg_email_on']);
	$formOptions->reg_email = wp_kses_post($_POST['reg_email']);

	$pw_rules = new stdClass();
	$pw_rules->min_pwd = intval($_POST['min_pwd']);
	$pw_rules->max_pwd = intval($_POST['max_pwd']);
	$pw_rules->number = intval($_POST['incl_number']);
	$pw_rules->uppercase = intval($_POST['incl_uppercase']);
	$pw_rules->lowercase = intval($_POST['incl_lowercase']);
	$pw_rules->special = intval($_POST['incl_special']);
	$formOptions->password_rules  = $pw_rules;

	$form_fields = json_decode(get_option('wpabstracts_registration_form'));
	$userlist_columns = $_POST['userlist_columns'];

	$columns = array();

	foreach($userlist_columns as $field_name){
		$field = wpabstracts_get_user_field_record($form_fields, $field_name);
		$columns[$field_name] = $field->label;
	}
	$formOptions->admin_columns = $columns;

	update_option('wpabstracts_user_settings', $formOptions);
}

if ($_POST) {
	wpabstracts_save_option();
	wpabstracts_show_message('Awesome! Your user settings are locked away.', 'alert-success');
}

if(is_admin() && isset($_GET['task']) && $_GET['task'] == 'sync') {
	if(wp_verify_nonce($_GET['_wpnonce'], "sync-user-meta")) {
		$users = wpabstracts_get_users();
		if($users){
			foreach ($users as $key => $user) {
				$user_data = unserialize($user->data);
				if($user_data){
					wpabstracts_sync_wpfields($user_data, $user->user_id);
				}
			}
			wpabstracts_show_message('Awesome! Your WP Abstracts user data was successfully synced to Wordpress user profiles.', 'alert-success');
		}
	}else {
		wpabstracts_show_message("Action terminated due to failed security checks.", 'alert-danger');
	}

}

$settings = get_option('wpabstracts_user_settings');

$pwrules = $settings->password_rules;

function wpabstracts_is_pwrule_selected($ruleid, $rules){
	if(is_array($rules) && in_array($ruleid, $rules)){
		return 'selected="selected"';
	}
}

function wpabstracts_is_column_selected($field_name, $columns){
	if(is_array($columns) && array_key_exists($field_name, $columns)){
		return 'checked="checked"';
	}
}
?>

<div class="wpabstracts container-fluid wpabstracts-admin-container">
	<form method="post" id="wpabstracts_settings" action="?page=wpabstracts&tab=users&subtab=settings">
		<h3>
			<?php _e('Settings', 'wpabstracts'); ?> <input type="submit" name="Submit" class="wpabstracts btn btn-primary" value="<?php _e('Save Changes', 'wpabstracts'); ?>" />		
			<a href="javascript:wpabstracts_sync_user_meta(`<?php echo wp_create_nonce("sync-user-meta");?>`);" class="wpabstracts btn btn-info">
				<?php _e('Sync User Meta', 'wpabstracts');?>
				<i class="wpabstracts glyphicon glyphicon-random"></i> 
			</a>
		</h3>
		<div class="wpabstracts row">
			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title"><?php _e('General Settings', 'wpabstracts'); ?></h6>
					</div>

					<div class="wpabstracts panel-body">

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Show Registration Thank You', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Set whether to show a thank you message or not after registration.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="reg_message_on" class="wpabstracts form-control">
								<option value="1" <?php selected($settings->reg_message_on, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0" <?php selected($settings->reg_message_on, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Send Registration Confirmation', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Set whether to send a confirmation email to the user with next steps or not after registration', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="reg_email_on" class="wpabstracts form-control">
								<option value="1" <?php selected($settings->reg_email_on, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($settings->reg_email_on, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Activate Users Automatically', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Set whether to activate users on registration or not (not recommended).', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="auto_activate_on" class="wpabstracts form-control">
								<option value="1" <?php selected($settings->auto_activate_on, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($settings->auto_activate_on, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Ignore User Activation at Login', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Whether or not to check for user activation at login. Used maninly when users are registered outside of WP Abstracts.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="ignore_activation" class="wpabstracts form-control">
								<option value="1" <?php selected($settings->ignore_activation, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($settings->ignore_activation, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<div class="wpabstracts row">
								<div class="wpabstracts form-group col-xs-6">
									<label class="wpabstracts control-label"><?php _e('Sync WP Fields', 'wpabstracts'); ?></label>
									<span class="settings_tip" data-tip="<?php _e('Set whether to sync WP Abstracts Profile fields to Wordpress meta data.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
									<select class="wpabstracts form-control" name="sync_fields">
										<option value="1" <?php selected($settings->sync_fields, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
										<option value="0"  <?php selected($settings->sync_fields, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
									</select>
								</div>
								
							</div>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Display Name', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Select the field or fields you would like to use as display name for all users.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<?php $form_fields = json_decode(get_option('wpabstracts_registration_form')); ?>
							<select name="display_name" class="wpabstracts form-control">
								<?php 
								foreach($form_fields as $field) {
									if($field->type !== 'header' && $field->type !== 'paragraph') { ?>
										<option value="<?php echo $field->name;?>"<?php selected($settings->display_name, $field->name); ?>><?php echo $field->label;?></option>
									<?php } ?>
								<?php } ?>
								<option value="first_last"<?php selected($settings->display_name, "first_last"); ?>><?php _e('First & Last Name');?></option>
								<option value="last_first"<?php selected($settings->display_name, "last_first"); ?>><?php _e('Last & First Name');?></option>
							</select>
						</div>

					</div>
				</div>
			</div>

			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title"><?php _e('Password Rules', 'wpabstracts'); ?></h6>
					</div>

					<div class="wpabstracts panel-body">

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Minimum Password Length', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Enter the minimum amount of character for a password', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="min_pwd" class="wpabstracts pull-right">
								<option value="5" <?php selected($pwrules->min_pwd, 5); ?>><?php _e('5', 'wpabstracts'); ?></option>
								<option value="6"  <?php selected($pwrules->min_pwd, 6); ?>><?php _e('6', 'wpabstracts'); ?></option>
								<option value="7"  <?php selected($pwrules->min_pwd, 7); ?>><?php _e('7', 'wpabstracts'); ?></option>
								<option value="8"  <?php selected($pwrules->min_pwd, 8); ?>><?php _e('8', 'wpabstracts'); ?></option>
								<option value="9"  <?php selected($pwrules->min_pwd, 9); ?>><?php _e('9', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('Maximum Password Length', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Enter the maximum amount of character for a password', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="max_pwd" class="wpabstracts pull-right">
								<option value="10" <?php selected($pwrules->max_pwd, 10); ?>><?php _e('10', 'wpabstracts'); ?></option>
								<option value="11"  <?php selected($pwrules->max_pwd, 11); ?>><?php _e('11', 'wpabstracts'); ?></option>
								<option value="12"  <?php selected($pwrules->max_pwd, 12); ?>><?php _e('12', 'wpabstracts'); ?></option>
								<option value="13"  <?php selected($pwrules->max_pwd, 13); ?>><?php _e('13', 'wpabstracts'); ?></option>
								<option value="14"  <?php selected($pwrules->max_pwd, 14); ?>><?php _e('14', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('At least one number', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Require passwords to have at least one number', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="incl_number" class="wpabstracts pull-right">
								<option value="1" <?php selected($pwrules->number, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($pwrules->number, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('At least one uppercase', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Require passwords to have at least one uppercase', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="incl_uppercase" class="wpabstracts pull-right">
								<option value="1" <?php selected($pwrules->uppercase, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($pwrules->uppercase, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('At least one lowercase', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Require passwords to have at least one lowercase', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="incl_lowercase" class="wpabstracts pull-right">
								<option value="1" <?php selected($pwrules->lowercase, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($pwrules->lowercase, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<label class="wpabstracts control-label"><?php _e('At least one special character', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Require passwords to have at least one special character', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="incl_special" class="wpabstracts pull-right">
								<option value="1" <?php selected($pwrules->special, 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"  <?php selected($pwrules->special, 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title"><?php _e('Admin Columns', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Select up to six (6) column fields to display when managing users.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
						</h6>
					</div>

					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group col-xs-12">
							<?php
							$form_fields = json_decode(get_option('wpabstracts_registration_form'));
							foreach($form_fields as $field) {
								if($field->type !== 'header' && $field->type !== 'paragraph') { ?>
									<div class="checkbox">
										<label>
											<input type="checkbox" name="userlist_columns[]" value="<?php echo $field->name;?>" <?php echo wpabstracts_is_column_selected($field->name, $settings->admin_columns);?>>
											<?php echo $field->label;?>
										</label>
									</div>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="wpabstracts row">

			<div class="wpabstracts col-xs-12">
				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title"><?php _e('Registration Thank you Message', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Enter a thank you message that shows after the registration form has been submitted', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
						</h6>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group col-xs-12">
							<?php
							$tuSettings = array( 'media_buttons' => false, 'textarea_name' => 'reg_message', 'wpautop'=>true, 'dfw' => true, 'editor_height' => 180, 'quicktags' => true);
							wp_editor($settings->reg_message, 'reg_message', $tuSettings);
							?>
						</div>
					</div>
				</div>
			</div>

			<div class="wpabstracts col-xs-12">
				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title"><?php _e('Registration Confirmation Email', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Create an email template used for sending the confirmation message to the user.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
						</h6>
					</div>
					<div class="wpabstracts panel-body">

						<div class="wpabstracts form-group col-xs-12 col-md-4">
							<label class="wpabstracts control-label"><?php _e('From Name', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Enter the email of the administrator to be notified when a user registers.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input class="wpabstracts form-control" name="admin_name" type="text" value="<?php echo $settings->admin_name; ?>" />
						</div>

						<div class="wpabstracts form-group col-xs-12 col-md-4">
							<label class="wpabstracts control-label"><?php _e('From Email', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Enter the email of the administrator to be notified when a user registers.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input class="wpabstracts form-control" name="admin_email" type="text" value="<?php echo $settings->admin_email; ?>" />
						</div>

						<div class="wpabstracts form-group col-xs-12 col-md-4">
							<label class="wpabstracts control-label"><?php _e('Email Subject', 'wpabstracts'); ?></label>
							<span class="settings_tip" data-tip="<?php _e('Enter the subject for the email sent when a user registers.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input class="wpabstracts form-control" name="email_subject" type="text" value="<?php echo $settings->email_subject; ?>" />
						</div>

						<div class="wpabstracts form-group col-xs-12 col-md-8">
							<?php
							$cmSettings = array( 'media_buttons' => false, 'textarea_name' => 'reg_email', 'wpautop'=>true, 'dfw' => true, 'editor_height' => 180, 'quicktags' => true);
							wp_editor($settings->reg_email, 'reg_email', $cmSettings);
							?>
						</div>

						<div class="wpabstracts col-xs-12 col-md-4">
							<div class="wpabstracts panel panel-default">
								<div class="wpabstracts panel-heading">
									<h5><?php echo apply_filters('wpabstracts_title_filter', __('Available Shortcodes','wpabstracts'), 'available_shortcodes');?></h5>
								</div>
								<div class="wpabstracts panel-body">
									<?php 
										$shortcodes = wpabstracts_reg_email_shortcodes();
										foreach ($shortcodes as $shortcode) { ?>
											<p>
												<?php echo $shortcode['name'] . ': ' . $shortcode['code']; ?>
												<span class="settings_tip" data-tip="<?php echo $shortcode['help']; ?>">
													<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
												</span>
											</p>
									<?php } ?>						
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
