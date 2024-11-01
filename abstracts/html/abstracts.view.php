<div class="wpabstracts container-fluid">
	<h3>
		<?php echo apply_filters('wpabstracts_title_filter', __('View Abstract','wpabstracts'), 'view_abstract');?>
	</h3>
		<div class="wpabstracts row">
			<div class="wpabstracts col-xs-12 col-sm-12 col-md-8">
				<div class="wpabstracts panel panel-default">
					<div class="wpabstracts panel-heading">
						<h5><?php echo apply_filters('wpabstracts_title_filter', __('Abstract Information','wpabstracts'), 'abstract_information');?></h5>
					</div>
					<div class="wpabstracts panel-body">
						<div class="wpabstracts form-group">
							<label class="wpabstracts control-label"><?php echo apply_filters('wpabstracts_title_filter', __('Title','wpabstracts'), 'title');?></label>
							<?php echo stripslashes($abstract->title);?>
						</div>
						<?php if (get_option('wpabstracts_show_description')) { ?>
							<div class="wpabstracts form-group">
								<label class="wpabstracts control-label"><?php echo apply_filters('wpabstracts_title_filter', __('Description','wpabstracts'), 'description');?></label>
								<?php echo wpautop(stripslashes($abstract->text));?>
							</div>
						<?php } ?>
						<?php if(get_option('wpabstracts_show_keywords')){ ?>
							<label class="wpabstracts control-label"><?php echo apply_filters('wpabstracts_title_filter', __('Keywords','wpabstracts'), 'keywords');?></label>
							<?php echo stripslashes($abstract->keywords);?>
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
								<a href="?task=download&type=attachment&id=<?php echo $attachment->attachment_id; ?>">
									<?php echo $attachment->filename ?>
									<span class="wpabstracts glyphicon glyphicon-download-alt" aria-hidden="true"></span></a><br>
								<?php } ?>
							<?php } ?>

						</div>
					</div>
				</div>

				<div class="wpabstracts col-xs-12 col-md-4">

					<div class="wpabstracts panel panel-default">
						<div class="wpabstracts panel-heading">
							<h5><?php echo apply_filters('wpabstracts_title_filter', __('Event Information','wpabstracts'), 'event_information');?></h5>
						</div>

						<div class="wpabstracts panel-body">
							<div class="wpabstracts form-group">
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_event"><?php echo apply_filters('wpabstracts_title_filter', __('Event','wpabstracts'), 'event');?></label>
									<?php echo esc_attr($event ? $event->name : 'Error: This event was removed');?>
								</div>
								<div class="wpabstracts form-group">
									<label class="wpabstracts control-label" for="abs_topic"><?php echo apply_filters('wpabstracts_title_filter', __('Topic','wpabstracts'), 'topic');?></label>
									<?php echo esc_attr($topic ? $topic->name : 'Error: This topic was removed');?>
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
									</h5>
								</div>

								<div class="wpabstracts panel-body" id="coauthors_table">

									<?php
									$authors_name = explode(' | ', $abstract->author);
									$authors_emails = explode(' | ', $abstract->author_email);
									$authors_affiliation = explode(' | ', $abstract->author_affiliation);

									foreach($authors_name as $id => $author){ ?>

										<div class="wpabstracts author_box">
											<div class="wpabstracts form-group">
												<label class="wpabstracts control-label"><?php _e('Name','wpabstracts');?></label>
												<?php echo esc_attr($authors_name[$id]); ?>
											</div>
											<div class="wpabstracts form-group">
											<label class="wpabstracts control-label"><?php _e('Email','wpabstracts');?></label>
												<?php echo esc_attr($authors_emails[$id]); ?>
											</div>
											<div class="wpabstracts form-group">
												<label class="wpabstracts control-label"><?php _e('Affiliation','wpabstracts');?></label>
												<?php echo esc_attr($authors_affiliation[$id]); ?>
											</div>
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
									<h5><?php echo apply_filters('wpabstracts_title_filter', __('Presenter Information','wpabstracts'), 'presenter_information');?></h5>
								</div>

								<div class="wpabstracts panel-body" id="presenter_table">

								<?php
									$presenter_names = explode(' | ', $abstract->presenter);
									$presenter_emails = explode(' | ', $abstract->presenter_email);
									$presenter_preferences = explode(' | ', $abstract->presenter_preference);

									foreach($presenter_names as $id => $presenter){ ?>

										<div class="wpabstracts author_box">
											<div class="wpabstracts form-group">
												<label class="wpabstracts control-label"><?php _e('Name','wpabstracts');?></label>
												<?php echo esc_attr($presenter_names[$id]); ?>
											</div>
											<div class="wpabstracts form-group">
											<label class="wpabstracts control-label"><?php _e('Email','wpabstracts');?></label>
												<?php echo esc_attr($presenter_emails[$id]); ?>
											</div>
											<div class="wpabstracts form-group">
												<label class="wpabstracts control-label"><?php _e('Presenter Preference','wpabstracts');?></label>
												<?php echo esc_attr($presenter_preferences[$id]); ?>
											</div>
										</div>
									<?php } ?>
								</div>
								</div>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
	</div>
