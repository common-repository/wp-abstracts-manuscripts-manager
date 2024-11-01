<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if ($_POST) {
	// save / update options
	
	foreach ($_POST['options'] as $option => $value) {
		wpabstracts_save_option($option);
    }
	// these are for options that are not in the POST['option'] implementation
	wpabstracts_update_admin_columns();
	wpabstracts_update_shortcode_options();
	wpabstracts_update_pdf_options();
	wpabstracts_update_edit_statuses();
	
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
	// save / update admin notification recipients
    if(isset($_POST['wpabstracts_admin_enabled'])){
        $admins_enabled = $_POST['wpabstracts_admin_enabled'];
        foreach($admins_enabled as $id => $enabled){
            update_user_meta($id, 'wpabstracts_enable_notification', $enabled);
        }
    }
	do_action('wpabstracts_save_settings');
	
	wpabstracts_show_message('Awesome! Your user settings are locked away.', 'alert-success');
}

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

function wpabstracts_update_admin_columns() {
	$abstracts_columns = get_option('wpabstracts_abstracts_columns');
	$columns_update = isset($_POST['admin_columns']) ? $_POST['admin_columns'] : [];
	foreach($abstracts_columns as $key => $column) {
		if(in_array($key, $columns_update)) {
			$abstracts_columns[$key]['enabled'] = true;
		} else {
			$abstracts_columns[$key]['enabled'] = false;
		}
	}
	update_option('wpabstracts_abstracts_columns', $abstracts_columns);
}

function wpabstracts_update_shortcode_options() {
	$shortcode_options = get_option('wpabstracts_accepted_shortcode');
	$options_update = isset($_POST['accepted_shortcode']) ? $_POST['accepted_shortcode'] : [];
	foreach($shortcode_options as $key => $option) {
		if(in_array($key, $options_update)) {
			$shortcode_options[$key]['enabled'] = true;
		} else {
			$shortcode_options[$key]['enabled'] = false;
		}
	}
	update_option('wpabstracts_accepted_shortcode', $shortcode_options);
}

function wpabstracts_update_pdf_options() {
	$pdf_options = get_option('wpabstracts_pdf_options');
	$options_update = isset($_POST['pdf_options']) ? $_POST['pdf_options'] : [];
	foreach($pdf_options as $key => $option) {
		if(in_array($key, $options_update)) {
			$pdf_options[$key]['enabled'] = true;
		} else {
			$pdf_options[$key]['enabled'] = false;
		}
	}
	update_option('wpabstracts_pdf_options', $pdf_options);
}

function wpabstracts_update_edit_statuses() {
	$edit_statuses = isset($_POST['edit_statuses']) ? $_POST['edit_statuses'] : [];
	update_option('wpabstracts_edit_status', $edit_statuses);
}

function wpabstracts_is_column_selected($column_key, $columns){
	if(is_array($columns) && property_exists((object)$columns, $column_key) && $columns[$column_key]['enabled']){
		return 'checked="checked"';
	}
}

function wpabstracts_is_status_selected($statuses, $status){
	if(is_array($statuses) && in_array($status, $statuses)){
		return 'checked="checked"';
	}
}

$statuses = wpabstracts_get_statuses();
$abstracts_columns = get_option('wpabstracts_abstracts_columns');
$accept_shortcode = get_option('wpabstracts_accepted_shortcode');
$pdf_options = get_option('wpabstracts_pdf_options');
// since v2.6
$edit_statuses = wpabstracts_get_edit_statuses();

?>

<div class="wpabstracts container-fluid wpabstracts-admin-container">
	<form method="post" id="abstracts_settings" action="?page=wpabstracts&tab=abstracts&subtab=settings">
		<h3>
			<?php _e('Settings', 'wpabstracts'); ?> <input type="submit" name="Submit" class="wpabstracts btn btn-primary" value="<?php _e('Save Changes', 'wpabstracts'); ?>" />		
		</h3>
		<div class="wpabstracts row">
			<div class="wpabstracts col-xs-12 col-sm-4">
				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title"><?php _e('Abstracts Configuration', 'wpabstracts'); ?></h6>
					</div>

					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Show Abstract Description', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to hide the decription area completely from the submission page.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>                        </span>
							<select name="options[wpabstracts_show_description]" class="wpabstracts pull-right">
								<option value="1" <?php selected(get_option('wpabstracts_show_description'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0" <?php selected(get_option('wpabstracts_show_description'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Allow Editor Media', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to hide or show the Add Media button on the submission text editor.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_editor_media]" class="wpabstracts pull-right">
									<option value="1" <?php selected(get_option('wpabstracts_editor_media'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
									<option value="0" <?php selected(get_option('wpabstracts_editor_media'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Show Author Fields', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to hide the author area completely from the submission page.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_show_author]" class="wpabstracts pull-right">
								<option value="1" <?php selected(get_option('wpabstracts_show_author'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0" <?php selected(get_option('wpabstracts_show_author'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Show Attachment Uploads', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to hide the attachment area completely from the submission page.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_show_attachments]" class="wpabstracts pull-right">
								<option value="1" <?php selected(get_option('wpabstracts_show_attachments'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0" <?php selected(get_option('wpabstracts_show_attachments'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Make Attachment Required', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to make attachments required or optional.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_attachment_pref]" class="wpabstracts pull-right">
								<option value="required" <?php selected(get_option('wpabstracts_attachment_pref'), 'required'); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="optional" <?php selected(get_option('wpabstracts_attachment_pref'), 'optional'); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Show Presenter Fields', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to hide the presenter area completely from the submission and edit pages.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_show_presenter]" class="wpabstracts pull-right">
								<option value='1' <?php selected(get_option('wpabstracts_show_presenter'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value='0' <?php selected(get_option('wpabstracts_show_presenter'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Show Abstract Keywords', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Enable this to display the keywords input on the submission page. Enabling this makes keywords required.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_show_keywords]" class="wpabstracts pull-right">
								<option value="1" <?php selected(get_option('wpabstracts_show_keywords'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0" <?php selected(get_option('wpabstracts_show_keywords'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Show Terms & Conditions', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Enable this to display your terms and conditions on the submission page. Enabling this makes the checkbox required.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="options[wpabstracts_show_conditions]" class="wpabstracts pull-right">
								<option value="1" <?php selected(get_option('wpabstracts_show_conditions'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0" <?php selected(get_option('wpabstracts_show_conditions'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Maximum User Submissions', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Set the maximum submissions allowed per user.', 'wpabstracts'); ?>">
									<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
								</span>
							<input name="options[wpabstracts_submit_limit]" type="text" value="<?php echo get_option('wpabstracts_submit_limit'); ?>" class="wpabstracts form-control" />
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Maximum Word Count', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Maximum character count allowed in a submission.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input name="options[wpabstracts_chars_count]" type="text" id="charscount" value="<?php echo get_option('wpabstracts_chars_count'); ?>" class="wpabstracts form-control" />
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Maximum Attachments', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Set the maximum attachment upload allowed per submission.', 'wpabstracts'); ?>">
									<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
								</span>
							<input name="options[wpabstracts_upload_limit]" type="text" value="<?php echo get_option('wpabstracts_upload_limit'); ?>" class="wpabstracts form-control" />
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Maximum Attachment Size', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Maxmium size allowed for attachments (in bytes).', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input name="options[wpabstracts_max_attach_size]" type="text" value="<?php echo get_option('wpabstracts_max_attach_size'); ?>" class="wpabstracts form-control"/>
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Permitted Attachments', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('File extentions allowed for uploading (separate extentions with a comma).', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input name="options[wpabstracts_permitted_attachments]" type="text" id="attachments_permitted" value="<?php echo get_option('wpabstracts_permitted_attachments'); ?>" class="wpabstracts form-control" />
						</div>

						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Set Presenter Preferences', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Set the types of presentation allowed (separated by commas), Eg. Poster, Panel, Round Table', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<input name="options[wpabstracts_presenter_preference]" type="text" value="<?php echo get_option('wpabstracts_presenter_preference'); ?>" class="wpabstracts form-control"/>
						</div>

					</div>

				</div>

				<div class="wpabstracts panel panel-primary">
					<div class="wpabstracts panel-heading">
						<h6 class="wpabstracts panel-title">
                            <?php _e('Admin Notification Recipients', 'wpabstracts'); ?>
                            <span class="settings_tip" data-tip="<?php _e('Enable or disable admin notications for admin users.', 'wpabstracts'); ?>">
                                <i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
                            </span>
                        </h6>
                    </div>
                    <div class="wpabstracts panel-body">
                        <div class="wpabstracts form-group col-xs-12" id="wpa_email_container">
                            <?php $super_admins = get_users( array('role'=>'administrator', 'fields' => array('ID', 'user_email', 'display_name')));?>
                            <?php foreach($super_admins as $super_admin){ ?>
                            <?php $enabled = get_user_meta($super_admin->ID, 'wpabstracts_enable_notification', true);?>
                            <div class="wpabstracts form-group col-xs-12">
                                <?php echo $super_admin->display_name . ' ['.$super_admin->user_email.']';?>
                                <select name="wpabstracts_admin_enabled[<?php echo $super_admin->ID;?>]" class="wpabstracts pull-right">>
                                    <option value="1" <?php selected(intval($enabled), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
                                    <option value="0" <?php selected(intval($enabled), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
                                </select>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
				</div>

			</div>

			<div class="wpabstracts col-xs-12 col-sm-8">

				<div class="wpabstracts row">
					<div class="wpabstracts col-xs-12 col-sm-6">
						<div class="wpabstracts panel panel-primary">
							<div class="wpabstracts panel-heading">
								<h6 class="wpabstracts panel-title"><?php _e('Abstract Status', 'wpabstracts'); ?></h6>
							</div>

							<div class="wpabstracts panel-body">

								<div class="wpabstracts form-group col-xs-12" id="wpa_status_container">
									<?php _e('Abstracts Status Description', 'wpabstracts'); ?>
									<span class="settings_tip" data-tip="<?php _e('Describe the four statuses Abstracts may be in.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
										<i class="wpabstracts glyphicon glyphicon-plus-sign add_status" onclick="wpabstracts_add_status();"></i>
									</span>
									<?php foreach($statuses as $status){ ?>
										<?php $status_id = 'wpa_status_' . $status->id;?>
										<div class="input-group wpa_status" id="<?php echo $status_id;?>" style="margin-bottom: 5px;">
											<input type="text" name="wpabstracts_status[<?php echo $status->id;?>]" value="<?php echo $status->name; ?>" class="form-control" autocomplete="off">
											<i class="wpabstracts input-group-addon glyphicon glyphicon-minus-sign delete_status" onclick="wpabstracts_delete_status(this, <?php echo $status->id;?>);"></i>
										</div>
									<?php } ?>
									<div class="input-group wpa_status_default hidden">
										<input type="text" name="wpabstracts_status[]" class="form-control" autocomplete="off">
										<i class="wpabstracts input-group-addon glyphicon glyphicon-minus-sign delete_status" onclick="wpabstracts_delete_status(this, null);"></i>
									</div>
								</div>

								<div class="wpabstracts form-group col-xs-12">
									<?php _e('Statuses that allow edits', 'wpabstracts'); ?> 
									<span class="settings_tip" data-tip="<?php _e('Select the statuses for when authors are allowed to edit their submission.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
									<?php foreach($statuses as $status) { ?>
										<div class="checkbox">
											<label>
												<input type="checkbox" name="edit_statuses[]" value="<?php echo $status->id;?>" <?php echo wpabstracts_is_status_selected($edit_statuses, $status->id);?>>
												<?php echo $status->name;?>
											</label>
										</div>
									<?php } ?>
								</div>

								<div class="wpabstracts form-group col-xs-12">
									<?php _e('Default Submission Status', 'wpabstracts'); ?> 
									<span class="settings_tip" data-tip="<?php _e('Select the default status of newly submitted abstracts.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
									<?php $edit_status = get_option('wpabstracts_default_status'); ?>
									<select name="options[wpabstracts_default_status]" class="wpabstracts form-control">
										<?php foreach ($statuses as $key => $status) { ?>
											<option value="<?php echo $status->id; ?>" <?php selected($edit_status, $status->id); ?>><?php _e($status->name, 'wpabstracts'); ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="wpabstracts form-group col-xs-12">
									<?php _e('Sync Review Status to Abstracts', 'wpabstracts'); ?>
									<span class="settings_tip" data-tip="<?php _e('Enable this to allow reviewer status selection to update the abstract status. This works best when one reviewer is assigned to the abstract. If more than one reviews are submitted the last one wins.', 'wpabstracts'); ?>">
											<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
										</span>
									<select name="options[wpabstracts_sync_status]" class="wpabstracts pull-right">
										<option value="1" <?php selected(get_option('wpabstracts_sync_status'), 1); ?>><?php _e('Yes', 'wpabstracts'); ?></option>
										<option value="0" <?php selected(get_option('wpabstracts_sync_status'), 0); ?>><?php _e('No', 'wpabstracts'); ?></option>
									</select>
								</div>
								
							</div>
						</div>

					</div>

					<div class="wpabstracts col-xs-12 col-sm-6">

						<div class="wpabstracts panel panel-primary">
							<div class="wpabstracts panel-heading">
								<h6 class="wpabstracts panel-title"><?php _e('Admin Columns', 'wpabstracts'); ?>
									<span class="settings_tip" data-tip="<?php _e('Select columns to display on manage abstracts tab.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
								</h6>
							</div>

							<div class="wpabstracts panel-body">
								<div class="wpabstracts form-group col-xs-12">
									<?php 
									foreach($abstracts_columns as $key => $column) { ?>
										<div class="checkbox">
											<label>
												<input type="checkbox" name="admin_columns[]" value="<?php echo $key;?>" <?php echo wpabstracts_is_column_selected($key, $abstracts_columns);?>>
												<?php echo $abstracts_columns[$key]['label'];?>
											</label>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="wpabstracts row">

					<div class="wpabstracts col-xs-12 col-sm-6">
						<div class="wpabstracts panel panel-primary">
							<div class="wpabstracts panel-heading">
								<h6 class="wpabstracts panel-title"><?php _e('Accepted Shortcode', 'wpabstracts'); ?>
									<span class="settings_tip" data-tip="<?php _e('Select the status and info to display on the details page when using the [wpabstracts_accepted] shortcode.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
									
								</h6>
								<small>[wpabstracts_accepted event_id="x"]</small>
							</div>

							<div class="wpabstracts panel-body">

								<div class="wpabstracts form-group col-xs-12">
									<?php 
									if(is_array($accept_shortcode)) {
										foreach($accept_shortcode as $key => $column) { ?>
											<div class="checkbox">
												<label>
													<input type="checkbox" name="accepted_shortcode[]" value="<?php echo $key;?>" <?php echo wpabstracts_is_column_selected($key, $accept_shortcode);?>>
													<?php echo $accept_shortcode[$key]['label'];?>
												</label>
											</div>
									<?php } ?>
								<?php } ?>
								</div>

								<div class="wpabstracts form-group col-xs-12">
									<?php _e('Shortcode Abstract Status', 'wpabstracts'); ?> 
									<span class="settings_tip" data-tip="<?php _e('Select the status of the items to display when using the shortcode.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
									<?php $accepted_status = get_option('wpabstracts_accepted_status'); ?>
									<select name="options[wpabstracts_accepted_status]" class="wpabstracts form-control">
										<?php foreach ($statuses as $key => $status) { ?>
											<option value="<?php echo $status->id; ?>" <?php selected($accepted_status, $status->id); ?>><?php _e($status->name, 'wpabstracts'); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="wpabstracts col-xs-12 col-sm-6">
						<div class="wpabstracts panel panel-primary">
							<div class="wpabstracts panel-heading">
								<h6 class="wpabstracts panel-title"><?php _e('PDF Export Options', 'wpabstracts'); ?>
									<span class="settings_tip" data-tip="<?php _e('Select the info include when exporting abstracts to PDF.', 'wpabstracts'); ?>">
										<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
									</span>
								</h6>
							</div>

							<div class="wpabstracts panel-body">

								<div class="wpabstracts form-group col-xs-12">
									<?php 
									if($pdf_options) {
										foreach($pdf_options as $key => $column) { ?>
											<div class="checkbox">
												<label>
													<input type="checkbox" name="pdf_options[]" value="<?php echo $key;?>" <?php echo wpabstracts_is_column_selected($key, $pdf_options);?>>
													<?php echo $pdf_options[$key]['label'];?>
												</label>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="wpabstracts col-xs-12">
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
		</div>
	</form>
</div>
