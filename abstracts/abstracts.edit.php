<script>
jQuery(function (){
	wpabstracts_updateWordCount();
});
</script>
<div class="wpabstracts container-fluid">
	<?php $edit_status = get_option('wpabstracts_edit_status');
	if(!is_admin() && !wpabstracts_user_can_edit($abstract->status)) { 
		$message = __('You are not allowed to edit this submission at this time.', 'wpabstracts');
		wpabstracts_show_message(apply_filters('wpabstracts_message_filter', $message, 'edit_restricted'), 'alert-danger');
		return;
	} 
	?>
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('Edit Abstract','wpabstracts'), 'edit_abstract');?>
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
							<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_title" placeholder="<?php _e('Enter title','wpabstracts');?>" value="<?php echo esc_attr( stripslashes($abstract->title)); ?>" id="title" />
						</div>
						<?php if (get_option('wpabstracts_show_description')) { ?>
							<div class="wpabstracts form-group">
								<?php
								$editorMedia = get_option('wpabstracts_editor_media');
								$settings = array( 'media_buttons' => $editorMedia, 'wpautop'=>true, 'dfw' => true, 'editor_height' => 360, 'quicktags' => true);
								wp_editor( stripslashes($abstract->text), 'abstext',  $settings);
								?>
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
								<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_keywords" id="abs_keywords" value="<?php echo esc_attr( htmlspecialchars( $abstract->keywords ) ); ?>" placeholder="<?php echo apply_filters('wpabstracts_title_filter', __('Enter comma separated keywords','wpabstracts'), 'enter_keywords');?>" />
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading" id="manage_attachments">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Manage Attachments','wpabstracts'), 'manage_attachments');?></h5>
					</div>
					<div class="wpabstracts panel-body manage_attachments">
						<?php
						if(count($attachments) < 1) {
							_e('No Attachments uploaded', 'wpabstracts');
						}
						else{
							foreach($attachments as $attachment) { ?>
								<dd>
									<span class="wpabstracts_attachments_counter" id="attachment_<?php echo $attachment->attachment_id;?>"><strong><?php echo $attachment->filename; ?></strong> [<?php echo number_format(($attachment->filesize/1048576), 2);?>MB]
									<a class="wpabstracts btn btn-danger" href="#removeAttachment" onclick="wpabstracts_remove_attachment('<?php echo $attachment->attachment_id; ?>');"><?php _e("Remove", "wpabstracts");?></a></span>
								</dd>
							<?php }
							} ?>
						</div>
					</div>
					<?php $currentUser = wp_get_current_user(); ?>
					<?php if($currentUser->roles[0]=='administrator' || ($currentUser->roles[0]=='editor' && get_option('wpabstracts_change_ownership') && !get_option('wpabstracts_blind_review'))){ ?>
						<div class="wpabstracts panel panel-default">
							<div class="wpabstracts panel-heading">
								<h5><?php echo apply_filters('wpabstracts_title_filter', __('Manage Ownership','wpabstracts'), 'manage_ownership');?></h5>
							</div>
							<div class="wpabstracts panel-body">
								<p><?php _e('Use this box to assign a new user / owner of this submission','wpabstracts');?></p>
								<?php
								$queryString = get_option('wpabstracts_reviewer_submit') ? array('role__in' => array('subscriber', 'editor')) : array('role' => 'subscriber');
								$users = get_users($queryString);
								$current_user = get_userdata($abstract->submit_by);
								$current_id = 0;
								?>
								<select class="wpabstracts form-control" name="abs_user" id="abs_user">
									<?php if($current_user) { 
										$current_id  = $current_user->ID; ?>
										<option selected="selected" value="<?php echo esc_attr($current_user->ID);?>" style="display:none;"><?php echo esc_attr($current_user->display_name);?></option>
									<?php } else { ?>
										<option selected="selected" value="" style="display:none;"><?php _e('User not found', 'wpabstracts');?></option>
									<?php } ?>
									<?php foreach($users as $user){ ?>
										<option <?php selected($user->ID, $current_id);?> value="<?php echo esc_attr($user->ID);?>"><?php echo esc_attr($user->display_name);?></option>
									<?php } ?>
								</select>
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
								<?php if(is_admin()){ ?>
									<div class="wpabstracts form-group">
										<label class="wpabstracts control-label" for="abs_event"><?php echo apply_filters('wpabstracts_title_filter', __('Event','wpabstracts'), 'event');?></label>
										<select name="abs_event" id="abs_event" class="wpabstracts form-control wpa_event_input" onchange="wpabstracts_load_topics(this.value);">
											<option value="" style="display:none;"><?php echo apply_filters('wpabstracts_title_filter', __('Select an Event','wpabstracts'), 'select_event');?></option>
											<?php foreach($events as $event){ ?>
												<option value="<?php echo esc_attr($event->event_id);?>" <?php selected($event->event_id, $abstract->event);?>><?php echo esc_attr($event->name);?></option>
											<?php } ?>
										</select>
									</div>
								<?php } else { ?>
									<div class="wpabstracts form-group">
										<?php echo esc_attr($event ? $event->name : 'Error: This event was removed');?>
										<input type="hidden" name="abs_event" value="<?php echo esc_attr($event->event_id);?>">
									</div>
								<?php } ?>
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_topic"><?php echo apply_filters('wpabstracts_title_filter', __('Topic','wpabstracts'), 'topic');?></label>
									<select name="abs_topic" id="abs_topic" class="wpabstracts form-control wpa_event_input">
										<option value="" style="display:none;"><?php echo apply_filters('wpabstracts_title_filter', __('Select a Topic','wpabstracts'), 'select_topic');?></option>
										<?php foreach($topics as $topic){ ?>
											<option value="<?php echo esc_attr($topic);?>" <?php selected($topic, $abstract->topic);?>><?php echo esc_attr($topic);?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<?php if(get_option('wpabstracts_show_author')){?>

						<?php $currentUser = wp_get_current_user(); ?>
						<?php if($currentUser->roles[0]=='administrator' || $currentUser->roles[0]=='subscriber' || ($currentUser->roles[0]=='editor' && !get_option('wpabstracts_blind_review'))){ ?>

							<div class="wpabstracts panel panel-default">

								<div class="wpabstracts panel-heading">
									<h5>
										<?php echo apply_filters('wpabstracts_title_filter', __('Author Information','wpabstracts'), 'author_information');?>
										<span class="wpabstracts glyphicon glyphicon-minus-sign delete_author" onclick="wpabstracts_delete_coauthor();"></span>
										<span class="wpabstracts glyphicon glyphicon-plus-sign add_author" onclick="wpabstracts_add_coauthor();"></span>
									</h5>
								</div>

								<div class="wpabstracts panel-body" id="coauthors_table">

									<?php
									$authors_name = explode(' | ', $abstract->author);
									$authors_emails = explode(' | ', $abstract->author_email);
									$authors_affiliation = explode(' | ', $abstract->author_affiliation);

									foreach($authors_name as $id => $author){ ?>

										<div class="wpabstracts form-group author_box">
											<label class="wpabstracts control-label" for="abs_author[]"><?php _e('Author Name','wpabstracts');?></label>
											<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_author[]" id="abs_author[]" value="<?php echo esc_attr($authors_name[$id]); ?>"/>

											<label class="wpabstracts control-label" for="abs_author_email[]"><?php _e('Author Email','wpabstracts');?></label>
											<input class="wpabstracts form-control wpa_event_input" type="email" name="abs_author_email[]" id="abs_author_email[]" value="<?php echo esc_attr($authors_emails[$id]); ?>" />

											<label class="wpabstracts control-label" for="abs_author_affiliation[]"><?php _e('Author Affiliation','wpabstracts');?></label>
											<input class="wpabstracts form-control wpa_event_input" type="text" name="abs_author_affiliation[]" id="abs_author_affiliation[]" value="<?php echo esc_attr($authors_affiliation[$id]); ?>" />
										</div>
									<?php } ?>
								</div>

							</div>
						<?php } ?>
					<?php } ?>

					<?php if(get_option('wpabstracts_show_presenter')){ ?>

						<?php $currentUser = wp_get_current_user(); ?>
						<?php if($currentUser->roles[0]=='administrator' || $currentUser->roles[0]=='subscriber' || ($currentUser->roles[0]=='editor' && !get_option('wpabstracts_blind_review'))){ ?>

							<div class="wpabstracts panel panel-default">

								<div class="wpabstracts panel-heading">
									<span class="wpabstracts glyphicon glyphicon-minus-sign delete_author" onclick="wpabstracts_delete_presenter();"></span>
									<span class="wpabstracts glyphicon glyphicon-plus-sign add_author" onclick="wpabstracts_add_presenter();"></span>
									<h5><?php echo apply_filters('wpabstracts_title_filter', __('Presenter Information','wpabstracts'), 'presenter_information');?></h5>
								</div>

								<div class="wpabstracts panel-body" id="presenter_table">

								<?php
									$presenter_names = explode(' | ', $abstract->presenter);
									$presenter_emails = explode(' | ', $abstract->presenter_email);
									$presenter_preferences = explode(' | ', $abstract->presenter_preference);

									foreach($presenter_names as $id => $presenter){ ?>

									<div class="presenter_box" style="border-bottom: 2px dotted #ccc; margin-bottom: 10px;">
										<div class="wpabstracts form-group">
											<label class="wpabstracts control-label" for="presenter[]"><?php _e('Name','wpabstracts');?></label>
											<input class="wpabstracts form-control wpa_event_input" type="text" name="presenter[]" id="presenter[]" value="<?php echo esc_attr($presenter_names[$id]);?>"/>
										</div>

										<div class="wpabstracts form-group">
											<label class="wpabstracts control-label" for="presenter_email[]"><?php _e('Email','wpabstracts');?></label>
											<input class="wpabstracts form-control wpa_event_input" type="email" name="presenter_email[]" id="presenter_email[]" value="<?php echo esc_attr($presenter_emails[$id]);?>"/>
										</div>

										<div class="wpabstracts form-group">
											<label class="wpabstracts control-label" for="presenter_preference[]"><?php _e('Presenter Preference','wpabstracts');?></label>
											<select class="wpabstracts form-control wpa_event_input" name="presenter_preference[]" id="presenter_preference[]" >
												<option value="<?php echo $presenter_preferences[$id];?>" selected style="display:none;"><?php echo esc_attr($presenter_preferences[$id]);?></option>
												<?php $presenter_preference = explode(',', get_option('wpabstracts_presenter_preference')); ?>
												<?php foreach($presenter_preference as $preference){ ?>
													<option value="<?php echo $preference; ?>"><?php echo $preference; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<?php } ?>
								</div>
								</div>
						<?php } ?>
					<?php } ?>

					<?php if(get_option('wpabstracts_show_attachments')){ ?>
						<div class="wpabstracts panel panel-default">

							<div class="wpabstracts panel-heading">
								<h5><?php echo apply_filters('wpabstracts_title_filter', __('Attachments','wpabstracts'), 'attachments');?></h5>
							</div>

							<div class="wpabstracts panel-body">
								 <div class="wpabstracts form-group">
									  <input type="hidden" id="wpabstracts_upload_limit" value="<?php echo get_option('wpabstracts_upload_limit'); ?>">
									  <?php $attachments_remaining = get_option('wpabstracts_upload_limit') - count($attachments); ?>
									  <?php $upload_info_class = ($attachments_remaining > 0) ? 'wpabstracts_upload_disallowed hidden' : 'wpabstracts_upload_disallowed'; ?>
									  <p><?php _e('Use this form to upload your images, photos or tables.', 'wpabstracts'); ?></p>
									  <p><?php _e('Supported formats', 'wpabstracts'); ?>: <strong><?php echo implode(' ', explode(' ', get_option('wpabstracts_permitted_attachments'))); ?></strong></p>
									  <p><?php _e('Maximum attachment size', 'wpabstracts'); ?>: <strong><?php echo number_format((get_option('wpabstracts_max_attach_size') / 1048576)); ?>MB</strong></p>
									  <span class="<?php echo $upload_info_class;?>">
											<strong class="wpabstracts text-danger"><?php _e('You have used all your attachment slots allowed.', 'wpabstracts'); ?></strong>
									  </span>
								 </div>
								 <div class="wpabstracts_attachments">
									  <?php
											for($i = 0; $i < $attachments_remaining; $i++){
												 $input_id = "abs_attachmment_".$i; ?>
												 <dd><input type="file" id="<?php echo $input_id;?>" name="attachments[]"><span class="wpabstracts glyphicon glyphicon-refresh remove_file" onclick="wpabstracts_refresh_file(this);"></span></dd>
									  <?php } ?>
								 </div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</form>
	</div>
