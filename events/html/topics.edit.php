<div class="wpabstracts container-fluid">
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('Edit Topic','wpabstracts'), 'edit_topic');?>
		<button type="button" id="topic_submit_btn" onclick="wpabstracts_validateGenericForm('abs_topic_form')" class="wpabstracts btn btn-primary"><?php _e('Submit','wpabstracts');?></button>
	</h3>
	<form method="post" enctype="multipart/form-data" id="abs_topic_form">
		<div class="wpabstracts row">
			<?php $events = wpabstracts_get_events(); ?>
			<div class="wpabstracts col-xs-12 col-sm-4">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Topic Information','wpabstracts'), 'topic_info');?></h5>
					</div>
					<div class="wpabstracts panel-body row">
						<div class="col-xs-12">
							<div class="wpabstracts form-group">
								<label class="wpabstracts control-label" for="name"><?php _e('Name','wpabstracts');?></label>
								<input class="wpabstracts form-control" type="text" name="name" id="name" value="<?php echo esc_attr($topic->name);?>" />
							</div>

							<div class="wpabstracts form-group">
								<label class="wpabstracts control-label" for="event_id"><?php _e('Event','wpabstracts');?></label>
								<select class="wpabstracts form-control" name="event_id" id="event_id">
									<?php foreach($events as $event){ ?>
										<option value="<?php echo esc_attr($event->event_id);?>" <?php selected($event->event_id, $topic->event_id)?>><?php echo esc_attr($event->name);?></option>
									<?php } ?>
								</select>
							</div>							
						</div>								
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
