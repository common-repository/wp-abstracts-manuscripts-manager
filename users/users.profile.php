<?php

if($_POST){
	$user_settings = get_option('wpabstracts_user_settings');
	if($user_settings->sync_fields) {
		wpabstracts_sync_wpfields($_POST, $user_id);
	}
	wpabstracts_save_profile($user_id);
	wpabstracts_edit_profile($user_id);
}else{
	wpabstracts_edit_profile($user_id);
}

function wpabstracts_save_profile($user_id){
	global $wpdb;
	$data = array('data' => serialize($_POST));
	$where = array('user_id' => $user_id);
	$wpdb->show_errors();
	$wpdb->update($wpdb->prefix."wpabstracts_users", $data, $where);
	wpabstracts_show_message(__('Profile updated successfully.', 'wpabstracts'), 'alert-success');
}

function wpabstracts_edit_profile($user_id) {
	$wpa_user = wpabstracts_get_user($user_id);
	$wp_user = get_user_by('ID', $user_id);
	$form_fields = get_option('wpabstracts_registration_form');
	$user_data = unserialize($wpa_user->data);
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
			if(user_data) {
				jQuery("#wpabstracts_register_form :input").each(function(){
					jQuery(this).val(user_data[jQuery(this).attr('name')]);
				});
			}
			jQuery("#username").val(user_login.username);
			jQuery("#wpabstracts_register_form").append('<input type="hidden" value="' + user_login.ID + '">');
		}

		</script>

	</div>
	<?php
}
