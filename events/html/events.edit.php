<script type="text/javascript">
jQuery(function() {
	jQuery("#abs_event_start").datepicker({ dateFormat: "yy-mm-dd" });
	jQuery("#abs_event_end").datepicker({ dateFormat: "yy-mm-dd" });
	jQuery("#abs_event_deadline").datepicker({ dateFormat: "yy-mm-dd" });
});
</script>
<div class="wpabstracts container-fluid">
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('Edit Event','wpabstracts'), 'edit_event');?>
		<button type="button" id="event_submit_btn" onclick="wpabstracts_validateEvent();" class="wpabstracts btn btn-primary"><?php _e('Submit','wpabstracts');?></button>
	</h3>
	<form method="post" enctype="multipart/form-data" id="abs_event_form">
		<div class="wpabstracts row">
			<div class="wpabstracts col-xs-12 col-sm-12 col-md-8">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Event Information','wpabstracts'), 'event_information');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_name" id="abs_event_name" placeholder="<?php _e('Event title','wpabstracts');?>" value="<?php echo esc_attr($abs_event->name);?>" />
						<br>
						<?php $evt_settings = array( 'media_buttons' => true, 'wpautop'=>true, 'dfw' => true, 'editor_height' => 360, 'quicktags' => true); ?>
						<?php wp_editor(stripslashes($abs_event->description), 'abs_event_desc', $evt_settings); ?>
					</div>
				</div>
			</div>

			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h4><?php echo apply_filters('wpabstracts_title_filter', __('Event Information','wpabstracts'), 'event_information');?></h4>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label" for="abs_event_status"><?php _e('Status','wpabstracts');?></label>
							<select name="abs_event_status" id="abs_event_status" class="wpabstracts form-control wpa_event_input">
								<option value="1" <?php selected("1", $abs_event->status);?>><?php _e('Active','wpabstracts');?></option>
								<option value="-1" <?php selected("-1", $abs_event->status);?>><?php _e('Archived','wpabstracts');?></option>
							</select>

							<label class="wpabstracts control-label" for="abs_event_host"><?php _e('Host','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_host" id="abs_event_host" value="<?php echo stripslashes($abs_event->host);?>" />

							<label class="wpabstracts control-label" for="abs_event_address"><?php _e('Location','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_address" id="abs_event_address" value="<?php echo stripslashes($abs_event->address); ?>" />

							<label class="wpabstracts control-label" for="abs_event_start"><?php _e('Start Date','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_start" id="abs_event_start" value="<?php echo stripslashes($abs_event->start_date); ?>" autocomplete="off" />

							<label class="wpabstracts control-label" for="abs_event_end"><?php _e('End Date','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_end" id="abs_event_end" value="<?php echo stripslashes($abs_event->end_date); ?>" autocomplete="off" />

							<label class="wpabstracts control-label" for="abs_event_deadline"><?php _e('Deadline','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_deadline" id="abs_event_deadline" value="<?php echo stripslashes($abs_event->deadline); ?>" autocomplete="off" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
