<?php

if($_POST){
	$user_settings = get_option('wpabstracts_user_settings');
	if($user_settings->sync_fields){
		wpabstracts_sync_wpfields($_POST, $user_id);
	}
	wpabstracts_save_profile($user_id);
}else{
	wpabstracts_edit_profile($user_id);
}

function wpabstracts_save_profile($user_id){
	global $wpdb;
	$sanitized_data = wpabstracts_sanitize_custom_form_fields($_POST);
	$data = array('data' => serialize($sanitized_data));
	$where = array('user_id' => $user_id);
	$wpdb->show_errors();
	$wpdb->update($wpdb->prefix."wpabstracts_users", $data, $where);
	wpabstracts_show_message(__('Profile updated successfully.', 'wpabstracts'), 'alert-success');
	$redirect = (is_admin()) ? '?page=wpabstracts&tab=users&subtab=manage' : '?dashboard';
	wpabstracts_redirect($redirect);
}

function wpabstracts_edit_profile($user_id) {
	$wpa_user = wpabstracts_get_user($user_id);
	$wp_user = get_user_by('ID', $user_id);
	$form_fields = get_option('wpabstracts_registration_form');
	$user_data = $wpa_user->data ? unserialize($wpa_user->data) : null;
	$user_login = new stdClass();
	$user_login->ID = $wp_user->ID;
	$user_login->username = $wp_user->user_login;
	$user_login->email = $wp_user->user_email;

	?>

	<div class="wpabstracts container-fluid">

		<h3>
			<?php echo apply_filters('wpabstracts_title_filter', __('Edit Profile','wpabstracts'), 'edit_profile'); ?>
			<button type="button" onclick="wpabstracts_update_profile();" class="wpabstracts btn btn-primary"><?php _e('Submit','wpabstracts');?></button>
		</h3>

		<form method="post" enctype="multipart/form-data" id="wpabstracts_register_form">

			<div class="wpabstracts panel panel-default">
				<div class="wpabstracts panel-heading">
					<h3 class="wpabstracts panel-title"><?php echo apply_filters('wpabstracts_title_filter', __('Profile Information','wpabstracts'), 'profile_information'); ?></h3>
				</div>
				<div class="wpabstracts panel panel-body">
					<div id="profile_form_container"></div>
				</div>
			</div>
		</form>

		<script>
		jQuery(function(){
			var user_data = JSON.parse(JSON.stringify(<?php echo json_encode($user_data); ?>));
			var user_login = JSON.parse(JSON.stringify(<?php echo json_encode($user_login); ?>));

			var _formData = JSON.stringify(<?php echo $form_fields; ?>);

			var _layoutTemplates = {
				default: function(field, label, help, data) {
					return jQuery('<div/>')
					.addClass('wpabstracts ' + data.layout)
					.attr('id', 'row-' + data.id)
					.append(label, help, field);
				}
			};
			console.log('_formData', _formData);
			jQuery('#profile_form_container').formRender({
				formData: _formData,
				layoutTemplates: _layoutTemplates
			});

			wpabstracts_populate_form(user_data, user_login);

		});

		function wpabstracts_update_profile(){
			
			var errors = false;

			jQuery("#wpabstracts_register_form input, #wpabstracts_register_form select, #wpabstracts_register_form textarea").each(function(){
				if(jQuery(this).attr('required')){
					errors = wp_field_validate(jQuery(this).attr('id'), errors);
				}
			});

			if(errors) {
				alertify.error(wpabstracts.required_fields);
			} else {
				jQuery('#wpabstracts_register_form').submit();
			}
		}

		function wpabstracts_populate_form(user_data, user_login){
			// when $_POST is saved, php removed the [] from the input name, so we have format the user_data and append the [] to the attr name
			var formatted = [];
			for (let prop in user_data) {
				if(Array.isArray(user_data[prop])) {
					var key = prop + '[]';
					formatted[key] = user_data[prop];
				} else {
					formatted[prop] = user_data[prop];
				}
			}
			if(formatted) {
				jQuery("#wpabstracts_register_form :input").each(function(){
					jQuery(this).val(formatted[jQuery(this).attr('name')]);
				});
			}
			jQuery("#username").val(user_login.username);
			jQuery("#wpabstracts_register_form").append('<input type="hidden" value="' + user_login.ID + '">');
			if(jQuery('select[multiple]')) {
				jQuery('select[multiple]').multiselect();
			}
		}

		</script>

	</div>
	<?php
}
