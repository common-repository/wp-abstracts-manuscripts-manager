<script>
jQuery(document).ready(function (){
	wpabstracts_updateWordCount();
});
</script>
<?php
if($event_id && !wpabstracts_is_event_active($event_id)){
	wpabstracts_show_message(__('Abstract submission for this event has past', 'wpabstracts'), 'alert-danger');
	return;
}
if(!is_super_admin() && wpabstracts_user_submission_count() >= get_option('wpabstracts_submit_limit')) {
	wpabstracts_show_message(__('You have reached the maximum amount of submissions allowed per user.', 'wpabstracts'), 'alert-danger');
	return;
}
?>
<div class="wpabstracts container-fluid">
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('New Abstract','wpabstracts'), 'new_abstract'); ?>
		<button type="button" onclick="wpabstracts_validateAbstract();" class="wpabstracts btn btn-primary"><?php _e('Submit','wpabstracts');?></button>
	</h3>
	<form method="post" enctype="multipart/form-data" id="abs_form">
		<div class="wpabstracts row">
			<div class="wpabstracts col-xs-12 col-sm-12 col-md-8">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Abstract Information','wpabstracts'), 'abstract_information');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group">
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_title" placeholder="<?php echo apply_filters('wpabstracts_title_filter', __('Enter Title','wpabstracts'), 'enter_title');?>" value="" id="title" />
						</div>
						<?php if (get_option('wpabstracts_show_description')) { ?>
							<div class="wpabstracts form-group">
								<?php
                                $editorMedia = get_option('wpabstracts_editor_media');
								$settings = array( 'media_buttons' => $editorMedia, 'wpautop'=>true, 'editor_height' => 360, 'quicktags' => true, 'drag_drop_upload'=> true);?>
								<?php wp_editor(stripslashes(get_option('wpabstracts_author_instructions')), 'abstext', $settings); ?>
								<span class="wpabstracts max-word-count" style="display: none;"><?php echo get_option('wpabstracts_chars_count'); ?></span>
								<table id="post-status-info" cellspacing="0">
									<tbody>
										<tr>
											<td><?php printf( __( 'Words Used: %s' ), '<span class="wpabstracts abs-word-count">0</span>' ); ?></td>
											<td><?php printf( __( 'Words Remaining: %s' ), '<span class="wpabstracts abs-word-remaining">0</span>' ); ?></td>
											</tr>
									</tbody>
								</table>
							</div>
						<?php } ?>
						<?php if(get_option('wpabstracts_show_keywords')){ ?>
							<div class="wpabstracts form-group">
								<label class="wpabstracts control-label" for="abs_keywords"><?php echo apply_filters('wpabstracts_title_filter', __('Keywords','wpabstracts'), 'keywords');?></label>
								<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_keywords" id="abs_keywords" placeholder="<?php echo apply_filters('wpabstracts_title_filter', __('Enter comma separated keywords','wpabstracts'), 'enter_keywords');?>" value="" />
							</div>
						<?php } ?>
					</div>
				</div>
				<?php if(get_option('wpabstracts_show_attachments')){ ?>
					<div class="wpabstracts panel panel-default">

						<div class="wpabstracts panel-heading">
							<h5><?php echo apply_filters('wpabstracts_title_filter', __('Attachments','wpabstracts'), 'attachments');?></h5>
						</div>

						<div class="wpabstracts panel-body">

							<div class="wpabstracts form-group">
								<div><?php echo apply_filters('wpabstracts_title_filter', __('Use this form to upload your images, photos or tables.','wpabstracts'), 'attachment_help'); ?></div>
								<div><?php echo apply_filters('wpabstracts_title_filter', __('Supported formats','wpabstracts'), 'supported_formats');?>: <strong><?php echo implode(' ', explode(' ', get_option('wpabstracts_permitted_attachments'))); ?></strong></div>
								<div><?php echo apply_filters('wpabstracts_title_filter', __('Maximum attachment size','wpabstracts'), 'max_attachment_size'); ?>: <strong><?php echo number_format((get_option('wpabstracts_max_attach_size') / 1048576)); ?>MB</strong></div>
							</div>
							<div class="wpabstracts form-group">
								<?php
								for($i = 0; $i < get_option('wpabstracts_upload_limit'); $i++){ ?>
									<div class="wpabstracts input-group">
										<input type="file" name="attachments[]" id="abs_attachments" class="<?php echo get_option('wpabstracts_attachment_pref');?> wpabstracts form-control wpa_event_input">										
										<span class="wpabstracts glyphicon glyphicon-refresh remove_file input-group-addon" onclick="wpabstracts_refresh_file(this);"></span>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php if(get_option('wpabstracts_show_conditions')){ ?>
					<div class="wpabstracts panel panel-default">

						<div class="wpabstracts panel-heading">
							<h5>
								<?php echo apply_filters('wpabstracts_title_filter', __('Terms and Conditions','wpabstracts'), 'terms_conditions');?>
								<input type="checkbox" name="abs_terms" id="abs_terms" class="wpabstracts--checkbox">
							</h5>
						</div>

						<div class="wpabstracts panel-body">
							<div class="wpabstracts terms_conditons">
								<?php echo stripslashes(get_option('wpabstracts_terms_conditions'));?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="wpabstracts col-xs-12 col-md-4">
				<div class="wpabstracts panel panel-default">

					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Event Information','wpabstracts'), 'event_information');?></h5>
					</div>

					<div class="wpabstracts panel-body">

						<div class="wpabstracts form-group">
							<?php if(!$event_id){ ?>
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_event"><?php echo apply_filters('wpabstracts_title_filter', __('Event','wpabstracts'), 'event');?></label>
									<select name="abs_event" id="abs_event" class="wpabstracts form-control wpa_event_input" onchange="wpabstracts_load_topics(this.value);">
										<option value="" style="display:none;"><?php echo apply_filters('wpabstracts_title_filter', __('Select an Event','wpabstracts'), 'select_event');?></option>
										<?php foreach($events as $event){ ?>
											<option value="<?php echo esc_attr($event->event_id);?>"><?php echo esc_attr($event->name);?></option>
										<?php } ?>
									</select>
								</div>
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_topic"><?php echo apply_filters('wpabstracts_title_filter', __('Topic','wpabstracts'), 'topic');?></label>
									<select name="abs_topic" id="abs_topic" class="wpabstracts form-control wpa_event_input">
										<option value="" style="display:none;"><?php echo apply_filters('wpabstracts_title_filter', __('Select a Topic','wpabstracts'), 'select_topic');?></option>
									</select>
								</div>
							<?php } else { ?>
								<?php $event = wpabstracts_get_event($event_id); ?>
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_topic"><?php echo apply_filters('wpabstracts_title_filter', __('Event','wpabstracts'), 'event');?></label>
									<?php echo esc_attr($event->name);?>
									<input type="hidden" name="abs_event" value="<?php echo esc_attr($event->event_id);?>">
								</div>
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_topic"><?php echo apply_filters('wpabstracts_title_filter', __('Topic','wpabstracts'), 'topic');?></label>
									<select name="abs_topic" id="abs_topic" class="wpabstracts form-control wpa_event_input">
										<option value="" style="display:none;"><?php echo apply_filters('wpabstracts_title_filter', __('Select a Topic','wpabstracts'), 'select_topic');?></option>
										<?php $topics = explode('|', $event->topics);?>
										<?php foreach($topics as $topic){ ?>
											<option value="<?php echo esc_attr($topic);?>"><?php echo esc_attr($topic);?></option>
										<?php } ?>
									</select>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php if(get_option('wpabstracts_show_author')){?>
					<div class="wpabstracts panel panel-default">

						<div class="wpabstracts panel-heading">
							<h5>
								<?php echo apply_filters('wpabstracts_title_filter', __('Author Information','wpabstracts'), 'author_information');?>
								<span class="wpabstracts glyphicon glyphicon-minus-sign delete_author" onclick="wpabstracts_delete_coauthor();"></span>
								<span class="wpabstracts glyphicon glyphicon-plus-sign add_author" onclick="wpabstracts_add_coauthor();"></span>
							</h5>
						</div>

						<div class="wpabstracts panel-body" id="coauthors_table">

							<div class="wpabstracts form-group author_box">
								<label class="wpabstracts control-label" for="abs_author[]"><?php echo apply_filters('wpabstracts_title_filter', __('Author Name','wpabstracts'), 'author_name');?></label>
								<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_author[]" id="abs_author[]"  />

								<label class="wpabstracts control-label" for="abs_author_email[]"><?php echo apply_filters('wpabstracts_title_filter', __('Author Email','wpabstracts'), 'author_email');?></label>
								<input class="wpabstracts form-control wpa_event_input" type="email" name="abs_author_email[]" id="abs_author_email[]" />

								<label class="wpabstracts control-label" for="abs_author_affiliation[]"><?php echo apply_filters('wpabstracts_title_filter', __('Author Affiliation','wpabstracts'), 'author_affiliation');?></label>
								<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_author_affiliation[]" id="abs_author_affiliation[]"/>

							</div>
						</div>

					</div>
				<?php } ?>

				<?php if(get_option('wpabstracts_show_presenter')){ ?>
					<div class="wpabstracts panel panel-default">

						<div class="wpabstracts panel-heading">
							<span class="wpabstracts glyphicon glyphicon-minus-sign delete_author" onclick="wpabstracts_delete_presenter();"></span>
							<span class="wpabstracts glyphicon glyphicon-plus-sign add_author" onclick="wpabstracts_add_presenter();"></span>
							<h5><?php echo apply_filters('wpabstracts_title_filter', __('Presenter Information','wpabstracts'), 'presenter_information');?></h5>
						</div>

						<div class="wpabstracts panel-body" id="presenter_table">
							<div class="presenter_box">
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="presenter[]"><?php echo apply_filters('wpabstracts_title_filter', __('Name','wpabstracts'), 'presenter_name');?></label>
									<input class="wpabstracts form-control wpa_event_input" type="text" name="presenter[]" id="presenter[]"/>
								</div>

								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="presenter_email[]"><?php echo apply_filters('wpabstracts_title_filter', __('Email','wpabstracts'), 'presenter_email');?></label>
									<input class="wpabstracts form-control wpa_event_input" type="email" name="presenter_email[]" id="presenter_email[]"/>
								</div>

								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="presenter_preference[]"><?php echo apply_filters('wpabstracts_title_filter', __('Presenter Preference','wpabstracts'), 'presenter_preference');?></label>
									<select class="wpabstracts form-control wpa_event_input" name="presenter_preference[]" id="presenter_preference[]">
										<option value="" style="display:none;"><?php _e('Select Preference','wpabstracts');?></option>
										<?php
										$presenter_preference = explode(',', get_option('wpabstracts_presenter_preference'));
										foreach($presenter_preference as $preference){ ?>
											<option value="<?php echo $preference; ?>"><?php echo $preference; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</form>
</div>
