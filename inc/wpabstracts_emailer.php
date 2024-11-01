<?php

class WPAbstracts_Emailer{

	protected $abstract = null;
	protected $user = null;
	protected $event = null;
	protected $template = null;
	protected $submitter = null;
	protected $reviews = array();

	public function __construct($aid, $user_id, $template_id) {
		global $wpdb;
		$wpdb->hide_errors();
		if($aid){
			$this->abstract = wpabstracts_get_abstract($aid);
		}
		if($this->abstract && $this->abstract->event){
			$this->event = wpabstracts_get_event($this->abstract->event);
		}
		if($user_id){
			$this->user = get_user_by('id', $user_id);
		}
		if($template_id){
			$this->template = wpabstracts_get_email_template($template_id);
		}
		if($this->abstract->submit_by){
			$this->submitter = get_user_by('id', $this->abstract->submit_by);
		}
	}

	private function get_email_to() {
        return  apply_filters('wpabstracts_emailer_to', $this->user->user_email, $this->abstract->abstract_id);        
    }

    private function get_email_subject() {
        return apply_filters('wpabstracts_emailer_subject', $this->filter($this->template->subject), $this->abstract->abstract_id);
    }

    private function get_email_body() {
        return apply_filters('wpabstracts_emailer_message', $this->filter(wpautop(stripslashes($this->template->message))), $this->abstract->abstract_id);
    }

	private function format_reviews() {
		$reviews = "";
		foreach($this->reviews as $key => $review) {
			$reviews .= "<p>" . ++$key . "). " . stripslashes(wp_filter_nohtml_kses($review->comments)) . "</p>";
		}
		return $reviews;
	}

	private function format_author_info() {
        $authors_name        = explode(" | ", $this->abstract->author);
        $authors_emails      = explode(" | ", $this->abstract->author_email);
        $authors_affiliation = explode(" | ", $this->abstract->author_affiliation);
        foreach ($authors_name as $id => $key) {
            $authors[$key] = array(
                'name'        => $authors_name[$id],
                'email'       => $authors_emails[$id],
                'affiliation' => $authors_affiliation[$id],
            );
        }
        ob_start();
        foreach ($authors as $author) {?>
			<p><?php echo apply_filters('wpabstracts_title_filter', __('Name', 'wpabstracts'), 'author_name'); ?>: <?php echo esc_attr($author['name']); ?></p>
			<p><?php echo apply_filters('wpabstracts_title_filter', __('Email', 'wpabstracts'), 'author_email'); ?>: <?php echo esc_attr($author['email']); ?></p>
			<p><?php echo apply_filters('wpabstracts_title_filter', __('Affiliation', 'wpabstracts'), 'author_affiliation'); ?>: <?php echo esc_attr($author['affiliation']); ?></p>
		<?php }
        $author_info = ob_get_contents();
        ob_end_clean();
        return $author_info;
    }

    private function format_presenter_info() {
        $presenter_names       = explode(" | ", $this->abstract->presenter);
        $presenter_emails      = explode(" | ", $this->abstract->presenter_email);
        $presenter_preferences = explode(" | ", $this->abstract->presenter_preference);
        foreach ($presenter_names as $id => $key) {
            $presenters[$key] = array(
                'name'       => $presenter_names[$id],
                'email'      => $presenter_emails[$id],
                'preference' => $presenter_preferences[$id],
            );
        }
        ob_start();
        foreach ($presenters as $presenter) {?>
			<p><?php echo apply_filters('wpabstracts_title_filter', __('Name', 'wpabstracts'), 'presenter_name'); ?>: <?php echo esc_attr($presenter['name']); ?></p>
			<p><?php echo apply_filters('wpabstracts_title_filter', __('Email', 'wpabstracts'), 'presenter_email'); ?>: <?php echo esc_attr($presenter['email']); ?></p>
			<p><?php echo apply_filters('wpabstracts_title_filter', __('Preference', 'wpabstracts'), 'presenter_preference'); ?>: <?php echo esc_attr($presenter['preference']); ?></p>
		<?php }
        $presenter_info = ob_get_contents();
        ob_end_clean();
        return $presenter_info;
    }

	private function filter($text){

		$keys = array(
			'{DISPLAY_NAME}',
			'{USERNAME}',
			'{USER_EMAIL}',
			'{ABSTRACT_ID}',
			'{ABSTRACT_TITLE}',
			'{ABSTRACT_KEYWORDS}',
			'{ABSTRACT_TOPIC}',
			'{SUBMITTER_NAME}',
			'{SUBMITTER_EMAIL}',
			'{EVENT_NAME}',
			'{EVENT_START}',
			'{EVENT_END}',
			'{AUTHOR_INFO}',
            '{PRESENTER_INFO}',
			'{PRESENTER_PREF}',
			'{REVIEW_COMMENTS}',
			'{SITE_NAME}',
			'{SITE_URL}',
			'{ONE_WEEK_LATER}',
			'{TWO_WEEKS_LATER}'
		);

		$display_name = $this->user ? $this->user->display_name : "";
		$user_login = $this->user ? $this->user->user_login : "";
		$user_email = $this->user ? $this->user->user_email: "";
		$abstract_id = $this->abstract ? $this->abstract->abstract_id : "";
		$abstract_title = $this->abstract ? $this->abstract->title : "";
		$abstract_keywords = $this->abstract ? $this->abstract->keywords : "";
		$abstract_topic = $this->abstract ? $this->abstract->topic : "";
		$submitter_name = $this->submitter ? $this->submitter->display_name : "";
		$submitter_email = $this->submitter  ? $this->submitter->user_login : "";
		$event_name = $this->event ? $this->event->name : "";
		$event_start = $this->event ? $this->event->start_date : "";
		$event_end = $this->event ? $this->event->end_date : "";
		$author_info    = $this->abstract ? $this->format_author_info() : "";
        $presenter_info = $this->abstract ? $this->format_presenter_info() : "";
		$abstract_pref = $this->abstract ? $this->abstract->presenter_preference : "";
		$reviews = $this->reviews ? $this->format_reviews() : "";
		$site_name = get_option('blogname');
		$site_url = home_url();
		$one_week_later = date_i18n(get_option('date_format'), (60 * 60 * 24 * 7) + strtotime(current_time('mysql')));
		$two_weeks_later = date_i18n(get_option('date_format'), ((60 * 60 * 24 * 7) * 2) + strtotime(current_time('mysql')));

		$values = array(
			$display_name,
			$user_login,
			$user_email,
			$abstract_id,
			$abstract_title,
			$abstract_keywords,
			$abstract_topic,
			$submitter_name,
			$submitter_email,
			$event_name,
			$event_start,
			$event_end,
			$author_info,
            $presenter_info,
			$abstract_pref,
			$reviews,
			$site_name,
			$site_url,
			$one_week_later,
			$two_weeks_later
		);
		return str_replace($keys, $values, $text);
	}

	public function send(){
		$to = apply_filters('wpabstracts_emailer_to', $this->user->user_email, $this->abstract->abstract_id);
		$subject = apply_filters('wpabstracts_emailer_subject', $this->filter($this->template->subject), $this->abstract->abstract_id);
		$headers = apply_filters('wpabstracts_emailer_headers', __('From:', 'wpabstracts') . $this->template->from_name . " <" . $this->template->from_email . "> \r\n", $this->abstract->abstract_id);
		$message = apply_filters('wpabstracts_emailer_message', $this->filter(wpautop(stripslashes($this->template->message))), $this->abstract->abstract_id);
		// if template has include_submission enable, attach generated PDF as attachments 
		$attachment = '';
		if($this->template->include_submission) {
			$attachment = wpabstracts_download_pdf($this->abstract->abstract_id, $is_email = true);
		}
		add_filter('wp_mail_content_type', 'wpabstracts_set_html_content_type');
		$success = wp_mail($to, $subject, $message, $headers, array($attachment));
		remove_filter('wp_mail_content_type', 'wpabstracts_set_html_content_type');
		if($success){ // if success and log enabled
			$this->add_to_maillog();
		}
		return $success;
	}

	private function add_to_maillog() {
		$to = $this->get_email_to();
		$subject = $this->get_email_subject();
		$body = $this->get_email_body();
		wpabstracts_add_maillog($this->abstract->abstract_id, $this->user->ID, $to, $subject, $body);
	}

}
