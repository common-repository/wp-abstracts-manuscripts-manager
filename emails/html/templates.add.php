<div class="wpabstracts container-fluid">
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('New Template','wpabstracts'), 'new_template'); ?>
		<button type="button" onclick="wpabstracts_validateTemplate();" class="wpabstracts btn btn-primary"><?php _e('Submit','wpabstracts');?></button>
	</h3>
	<form method="post" enctype="multipart/form-data" id="emailtemplate">
		<div class="wpabstracts row">
			<div class="wpabstracts col-xs-12 col-sm-12 col-md-8">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Template Information','wpabstracts'), 'template_information');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label col-sm-2" for="template_name"><?php echo apply_filters('wpabstracts_title_filter', __('Template Name', 'wpabstracts'), 'template_name');?></label>
							<div class="col-sm-10">
								<input type="text" name="template_name" id="template_name" class="wpabstracts form-control wpa_event_input">
							</div>
						</div>
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label col-sm-2" for="from_name"><?php echo apply_filters('wpabstracts_title_filter', __('From Name', 'wpabstracts'), 'from_name');?></label>
							<div class="col-sm-10">
								<input type="text" name="from_name" id="from_name" class="wpabstracts form-control wpa_event_input">
							</div>
						</div>
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label col-sm-2" for="from_email"><?php echo apply_filters('wpabstracts_title_filter', __('From Email', 'wpabstracts'), 'from_email');?></label>
							<div class="col-sm-10">
								<input type="text" name="from_email" id="from_email" class="wpabstracts form-control wpa_event_input">
							</div>
						</div>
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label col-sm-2" for="email_subject"><?php echo apply_filters('wpabstracts_title_filter', __('Email Subject', 'wpabstracts'), 'email_subject');?></label>
							<div class="col-sm-10">
								<input type="text" name="email_subject" id="email_subject" class="wpabstracts form-control wpa_event_input">
							</div>
						</div>
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label" for="email_body"><?php echo apply_filters('wpabstracts_title_filter', __('Email Body', 'wpabstracts'), 'email_body');?></label>
								<?php
								$default_message = 'Hi {DISPLAY_NAME},
								
								This is a sample notification about Abstract # {ABSTRACT_ID}.
								
								Your Abstracts Title is: {ABSTRACT_TITLE} for event {EVENT_NAME} starting on the {EVENT_START}.
				
								To view the comments and manage your submission please visit {SITE_NAME} and sign in.
			
								Regards,

								WP Abstracts Team
								{SITE_NAME}
								{SITE_URL}';
								$editor_media = get_option('wpabstracts_editor_media');
								$editor_settings = array( 'media_buttons' => $editor_media, 'wpautop'=>true, 'dfw' => true, 'editor_height' => 300, 'quicktags' => true);
								wp_editor(stripslashes($default_message), 'email_body', $editor_settings);
								?>
						</div>
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label" for="include_submission"><?php echo apply_filters('wpabstracts_title_filter', __('Include Submission as Attachment', 'wpabstracts'), 'include_submission');?></label>
							<input type="checkbox" name="include_submission" id="include_submission" class="wpabstracts--checkbox">
						</div>
					</div>
				</div>
			</div>
			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Template Settings','wpabstracts'), 'template_settings');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Active', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Use this setting to enable or disable this email template.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="template_status" class="wpabstracts pull-right">
								<option value="1"><?php _e('Yes', 'wpabstracts'); ?></option>
								<option value="0"><?php _e('No', 'wpabstracts'); ?></option>
							</select>
						</div>	
						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Receiver', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Select the receiver for this email notification (Author or Admin).', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="template_receiver" class="wpabstracts pull-right">
								<option value="author"><?php _e('Author', 'wpabstracts'); ?></option>
								<option value="admin"><?php _e('Admin', 'wpabstracts'); ?></option>
							</select>
						</div>	
						<div class="wpabstracts form-group col-xs-12">
							<?php _e('Trigger', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Selection the abstract action that triggers this email notification.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="template_trigger" class="wpabstracts pull-right" id="template_trigger">
								<option value="submission"><?php _e('Submission', 'wpabstracts'); ?></option>
								<option value="revision"><?php _e('Revision', 'wpabstracts'); ?></option>
								<option value="status"><?php _e('Status Change', 'wpabstracts'); ?></option>
							</select>
						</div>	
						<div class="wpabstracts form-group col-xs-12" id="abs_status_selection" style="display:none">
							<?php _e('Status', 'wpabstracts'); ?>
							<span class="settings_tip" data-tip="<?php _e('Select the abstract status that triggers this email notification.', 'wpabstracts'); ?>">
								<i class="wpabstracts text-info glyphicon glyphicon-question-sign"></i>
							</span>
							<select name="template_abs_status" class="wpabstracts pull-right">
								<?php print_r($statuses);?>
								<?php foreach($statuses as $status) { ?>
									<option value="<?php echo $status->id;?>"><?php echo $status->name;?></option>
								<?php } ?>
							</select>
						</div>		
				
					</div>
				</div>

				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Available Shortcodes','wpabstracts'), 'available_shortcodes');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<?php 
							$shortcodes = wpabstracts_template_shortcodes();
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
	</form>
</div>
<script>
    jQuery(function(){
        jQuery('#template_trigger').on('change', function(v){
			var trigger = jQuery(this).val();
			if(trigger == 'status') {
                jQuery('#abs_status_selection').show();
			} else {
				jQuery('#abs_status_selection').hide();
			}
		});
	});
</script>