<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");?>

<div class="wpabstracts container-fluid wpabstracts-admin-container"></br>
    <div class="wpabstracts row">
        <div class="wpabstracts col-xs-12 col-sm-6 col-md-8">

			<div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
                    <h6 class="wpabstracts panel-title"><?php _e('Quick Start', 'wpabstracts');?></h6>
                </div>

                <div class="wpabstracts panel-body">
                    <h5><?php _e('Get setup and running in three simple steps', 'wpabstracts');?></h5>
					<ol>
						<li><?php _e('Create an event (make a note of the event ID for step 2). This may be the name of a conference or anything relating to the submission of abstracts.', 'wpabstracts');?></li>
						<li><?php _e('Create a wordpress page and add [wpabstracts event_id="EVENT_ID_HERE"] and link this page to your site menu to allow frontend access.', 'wpabstracts');?></li>
						<li><?php _e('Visit the settings page and choose your preference for the plugin. Also ensure your wordpress permanent links are set to post-name or custom.', 'wpabstracts');?></li>
					</ol>
                </div>
            </div>

			<div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
					<h6 class="wpabstracts panel-title"><?php _e('Frequently Asked Questions', 'wpabstracts');?></h6>
                </div>

                <div class="wpabstracts panel-body">
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('Do I have to create accounts for authors and reviewers?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('Authors and reviewers need an account on your site to access the dashboard and take part in your conference. Users may self register on your website or the admin may create accounts for users and forward the credentials to the user.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('I cannot find the registration area, how do authors or reviewers register?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('The registration link (Create an Account) is located on the Login page. Authors and Reviewers may self-register on your website. To enable this, go to your WordPress Settings -> General tab and check the box that says “Any can Register”.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('After signing in to the dashboard I see "You do not have permission to access this page". What is happening?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('This error message is common when the user accessing the dashboard is not permitted. At the moment only two user types can access the dashboard; Authors which maps to the "Subscriber" user role and "Reviewers" which maps to the "Editor" user role. You may also see this message is you are visiting the front-end dashboard while currently signed in as the Administrator.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('How do I specify the types of abstract submissions (poster, panel etc) for my conference?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('Simply go to WP Abstracts Settings Tab and enter the types you want to allow for presenters. Submission types are comma separated.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('When clicking “New Abstracts” I get the following message “Abstract submission for this event has passed”. What can I do?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('This simply means that the deadline for your event has passed. If the event is still active simply go to the events tab in the admin area and extend the deadline date of the event.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('How do I setup Authors and Reviewers?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('Authors are equivalent to Wordpress subscriber user role while reviewers are the editor user roles. Users may self register as authors and the site admin can then upgrade subscriber account to editor thus making that user a reviewer.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('How do I upgrade from the Free to the Pro version?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('Upload WP Abstracts Pro from the your plugins admin page then simply deactivate the free version and activate WP Abstracts Pro.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('When are automated emails triggered in WP Abstracts?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('Automated emails may be triggered on abstract submission, abstract status changes, reviewer assignments and review submissions.', 'wpabstracts');?></p>
					<p class="text-danger"><strong><?php _e('Question', 'wpabstracts');?></strong>: <?php _e('Can I edit or customize the templates for automated email notifications?', 'wpabstracts');?></p>
					<p class="text-success"><strong><?php _e('Answer', 'wpabstracts');?></strong>: <?php _e('Yes. All email notification templates are customizable using various shortcode pertaining to the submission and event.', 'wpabstracts');?></p>
        
				</div>
            </div>

        </div>

		<div class="wpabstracts col-xs-12 col-sm-6 col-md-4">
            <div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
					<h6 class="wpabstracts panel-title"><?php _e('Shortcodes', 'wpabstracts');?></h6>
                </div>
                <div class="wpabstracts panel-body">
					<p><?php _e('[wpabstracts event_id="x"] renders the user dashboard', 'wpabstracts');?></p>
					<p><?php _e('[wpabstracts_register] renders the user registration form', 'wpabstracts');?></p>
					<p><?php _e('[wpabstracts_login] renders the user login screen', 'wpabstracts');?></p>
				</div>
			</div>

			 <div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
					<h6 class="wpabstracts panel-title"><?php _e('Email Notifications', 'wpabstracts');?></h6>
                </div>
                <div class="wpabstracts panel-body">
					<p><?php _e('Use the Email tab to create various email templates then set those templates for various email triggers. Current email triggers includes abstract submission, all abstract status change, reviewer assignments and review submissions.', 'wpabstracts');?></p>
				</div>
			</div>

			<div class="wpabstracts panel panel-primary">
                <div class="wpabstracts panel-heading">
					<h6 class="wpabstracts panel-title"><?php _e('Help us Improve', 'wpabstracts');?></h6>
                </div>
                <div class="wpabstracts panel-body">
					<p><a href="http://wordpress.org/plugins/wp-abstracts-manuscripts-manager/" target="_blank"><?php _e('Rate', 'wpabstracts');?></a> <?php _e('the plugin 5 stars on WordPress.org', 'wpabstracts');?>.</p>
				</div>
			</div>
		</div>
	</div>
</div>

