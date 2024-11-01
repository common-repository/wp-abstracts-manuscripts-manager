<script type="text/javascript">
jQuery(function() {
	jQuery( "#abs_event_start" ).datepicker({ dateFormat: "yy-mm-dd" });
	jQuery( "#abs_event_end" ).datepicker({ dateFormat: "yy-mm-dd" });
	jQuery( "#abs_event_deadline" ).datepicker({ dateFormat: "yy-mm-dd" });
});
</script>
<div class="wpabstracts container-fluid">
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('New Event','wpabstracts'), 'new_event');?>
		<button type="button" onclick="wpabstracts_validateEvent();" class="wpabstracts btn btn-primary"><?php _e('Submit','wpabstracts');?></button>
	</h3>
	<form method="post" enctype="multipart/form-data" id="abs_event_form">
		<div class="wpabstracts row">

			<div class="wpabstracts col-xs-12 col-sm-12 col-md-8">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Event Information','wpabstracts'), 'event_information');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_name" id="abs_event_name" placeholder="<?php echo apply_filters('wpabstracts_title_filter', __('Event Title','wpabstracts'), 'event_title');?>" />
						<br>
						<?php wp_editor('', 'abs_event_desc', true, true); ?>
					</div>
				</div>
			</div>

			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Event Details','wpabstracts'), 'event_details');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label" for="abs_event_status"><?php _e('Status','wpabstracts');?></label>
							<select name="abs_event_status" id="abs_event_status" class="wpabstracts form-control wpa_event_input">
								<option value="1"><?php _e('Active','wpabstracts');?></option>
							</select>
								
							<label class="wpabstracts control-label" for="abs_event_host"><?php _e('Host','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_host" id="abs_event_host"/>

							<label class="wpabstracts control-label" for="abs_event_address"><?php _e('Location','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_address" id="abs_event_address" />

							<label class="wpabstracts control-label" for="abs_event_start"><?php _e('Start Date','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_start" id="abs_event_start" autocomplete="off"/>

							<label class="wpabstracts control-label" for="abs_event_end"><?php _e('End Date','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_end" id="abs_event_end" autocomplete="off"/>

							<label class="wpabstracts control-label" for="abs_event_deadline"><?php _e('Deadline','wpabstracts');?></label>
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_event_deadline" id="abs_event_deadline" autocomplete="off"/>
						</div>
					</div>
				</div>
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Topics','wpabstracts'), 'topics');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group" id="topics_table">
							<p><input class="wpabstracts form-control wpa_event_input" type="text" name="topics[]" id="topics[]" value="" /></p>
						</div>
						<div class="inner_btns">
							<a class="button-secondary" href="#add-topic" onclick="wpabstracts_add_topic();" style="float: left;"><?php echo apply_filters('wpabstracts_title_filter', __('add topic','wpabstracts'), 'add_topic');?></a>
							<a class="button-secondary" href="#delete-topic" onclick="wpabstracts_delete_topic();" style="float: right;"><?php echo apply_filters('wpabstracts_title_filter', __('delete topic','wpabstracts'), 'delete_topic');?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
