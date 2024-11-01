<?php
/*
Plugin Name: WP Abstracts
Plugin URI: http://www.wpabstracts.com
Description: Allow abstracts submissions on your site. Manage everything from events, abstracts, authors, reviews, attachments, notifications and more.
Version: 2.7.2
Author: Kevon Adonis
Author URI: http://www.kevonadonis.com
Tags: wp abstracts, abstracts, manuscripts, wordpress conference plugin, wpabstracts, peer review, submission management, conference
Text Domain: wpabstracts
*/

defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
define('WPABSTRACTS_ACCESS_LEVEL', 'manage_options');
define('WPABSTRACTS_PLUGIN_DIR', dirname(__FILE__) . '/');
define('WPABSTRACTS_VERSION', '2.7.2');
define('WPABSTRACTS_SECRET_KEY', '5a22d6e80bf870.68089106');
register_activation_hook(__FILE__, 'wpabstracts_install');
register_deactivation_hook(__FILE__, 'wpabstracts_deactivation');

global $pagenow;
if ('admin.php' == $pagenow && isset($_GET['page']) && ($_GET['page'] == 'wpabstracts')) {
	add_action('admin_head', 'wpabstracts_load_js');
	add_action('admin_head', 'wpabstracts_load_fb_js');
	add_action('admin_head', 'wpabstracts_load_css');
	add_action('admin_init', 'wpabstracts_editor_admin_init');
	add_action('tiny_mce_before_init', 'wpabstracts_editor_init');
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'loadtopics'):
	do_action('wp_ajax_' . $_REQUEST['action']);
endif;

add_action('init', 'wpabstracts_init');
function wpabstracts_init()
{
	load_plugin_textdomain('wpabstracts', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	include_once(WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_functions.php');
	include_once(WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_downloads.php');
}

add_action('admin_menu', 'wpabstracts_register_menu');
function wpabstracts_register_menu()
{
	$page_title = __('WP Abstracts', 'wpabstracts');
	add_menu_page($page_title, $page_title, 'manage_options', 'wpabstracts', 'wpabstracts_admin_dashboard', plugins_url('assets/images/icon.png', __FILE__), 99);
	$submenus = array(
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Events', 'wpabstracts'), 'events'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=events'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Abstracts', 'wpabstracts'), 'abstracts'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=abstracts'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Reviews', 'wpabstracts'), 'reviews'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=reviews'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Users', 'wpabstracts'), 'users'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=users'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Emails', 'wpabstracts'), 'emails'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=emails'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Exports', 'wpabstracts'), 'exports'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=exports'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Custom Titles', 'wpabstracts'), 'titles'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=titles'),
		array('page_title' => $page_title, 'menu_name' => apply_filters('wpabstracts_title_filter', __('Help', 'wpabstracts'), 'help'), 'capability' => 'manage_options', 'url' => 'admin.php?page=wpabstracts&tab=help'),
	);

	$filter_menus = apply_filters('wpabstracts_menu_filter', $submenus);

	foreach ($filter_menus as $submenu) {
		add_submenu_page('wpabstracts',  $submenu['page_title'], $submenu['menu_name'], $submenu['capability'], $submenu['url']);
	}
	remove_submenu_page('wpabstracts', 'wpabstracts');
}

add_shortcode('wpabstracts', 'wpabstracts_shortcode');
function wpabstracts_shortcode($atts)
{
	wpabstracts_load_css(); // load css only on dashboard pages
	wpabstracts_load_js($frontend = true);  // load js only on dashboard pages
	add_action('tiny_mce_before_init', 'wpabstracts_editor_init'); //  only load wpabstracts_updateWordCount on wpabstract pages
	add_filter('edit_post_link', '__return_false'); // remove edit page link from dashboard
	do_action('wpabstracts_actions_shortcode');
	$args = array('event_id' => 0); // shortcode args with defaults
	$a = shortcode_atts($args, $atts);
	$event_id = intval($a['event_id']); // if an event id was enter, make it available to dashboard
	ob_start();
	$dashboard = apply_filters('wpabstracts_page_include', 'dashboard/wpabstracts.dashboard.php');
	include_once($dashboard);
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

add_shortcode('wpabstracts_register', 'wpabstracts_register_shortcode');
function wpabstracts_register_shortcode($atts)
{
	wpabstracts_load_css(); //load css only on dashboard pages
	wpabstracts_load_js($frontend = true);  //load js only on dashboard pages
	wpabstracts_load_fb_js();
	do_action('wpabstracts_actions_shortcode');
	$html = wpabstracts_user_getview("register", false);
	return $html;
}

add_shortcode('wpabstracts_login', 'wpabstracts_login_shortcode');
function wpabstracts_login_shortcode($atts)
{
	wpabstracts_load_css(); //load css only on dashboard pages
	wpabstracts_load_js($frontend = true);  //load js only on dashboard pages
	do_action('wpabstracts_actions_shortcode');
	wpabstracts_get_login();
}

add_shortcode('wpabstracts_accepted', 'wpabstracts_accepted_shortcode');
function wpabstracts_accepted_shortcode($atts)
{
	wpabstracts_load_css(); //load css only on dashboard pages
	wpabstracts_load_js($frontend = true); //load js only on dashboard pages
	do_action('wpabstracts_actions_shortcode');
	$default = array('event_id' => 0); // shortcode args with defaults
	$args = shortcode_atts($default, $atts);
	$event_id = intval($args['event_id']);
	wpabstracts_display_accepted($event_id);
}

add_action('admin_init', 'wpabstracts_disable_dashboard');
function wpabstracts_disable_dashboard()
{
	if (!get_option('wpabstracts_frontend_dashboard')) {
		if (is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX)) {
			wp_redirect(home_url());
			exit;
		}
	}
}

add_filter('show_admin_bar', 'wpabstracts_disable_adminbar');
function wpabstracts_disable_adminbar()
{
	if (is_user_logged_in() && get_option('wpabstracts_show_adminbar')) {
		return true;
	}
	return false;
}

add_filter('plugin_row_meta', 'wpabstracts_plugin_links', 10, 2);
function wpabstracts_plugin_links($links, $file)
{

	if ($file == plugin_basename(__FILE__)) {
		$links[] = '<a href="http://www.wpabstracts.com/support" target="_blank">' . __('Support', 'wpabstracts') . '</a>';
		$links[] = '<a href="http://www.wpabstracts.com/pricing" target="_blank">' . __('Get Pro Version', 'wpabstracts') . '</a>';
	}
	return $links;
}

function wpabstracts_admin_dashboard()
{
	global $pagenow;

	if ($pagenow == 'admin.php' && $_GET['page'] == 'wpabstracts') {

		$tab = isset($_GET['tab']) ? $_GET['tab']  : 'abstracts';

		wpabstracts_admin_header();
		wpabstracts_admin_tabs($tab);

		switch ($tab) {
			case 'events':
				$page =  'wpabstracts.events.php';
				break;
			case 'abstracts':
				$page = 'wpabstracts.abstracts.php';
				break;
			case 'reviews':
				$page = 'wpabstracts.reviews.php';
				break;
			case 'users':
				$page = 'wpabstracts.users.php';
				break;
			case 'emails':
				$page = 'wpabstracts.emails.php';
				break;
			case 'exports':
				$page = 'wpabstracts.exports.php';
				break;
			case 'titles':
				$page = 'wpabstracts.titles.php';
				break;
			case 'help':
				$page = 'wpabstracts.help.php';
				break;
			default:
				$page = 'wpabstracts.abstracts.php';
		}

		$page = apply_filters('wpabstracts_page_include', $page);
		ob_start();
		include_once($page);
		$html = ob_get_contents();
		ob_end_clean();
		echo apply_filters('wpabstracts_admin_pages', $html, $tab);
	}
}

function wpabstracts_admin_tabs($current = 'abstracts')
{
	$basic_tabs = array(
		'events' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-calendar"></i> ' . __('Events', 'wpabstracts'), 'events'),
		'abstracts' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-th-list"></i> ' . __('Abstracts', 'wpabstracts'), 'abstracts'),
		'reviews' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-star"></i> ' . __('Reviews', 'wpabstracts'), 'reviews'),
		'users' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-user"></i> ' . __('Users', 'wpabstracts'), 'users'),
		'emails' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-email"></i> ' . __('Emails', 'wpabstracts'), 'users'),
		'exports' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-stats"></i> ' . __('Exports', 'wpabstracts'), 'exports'),
		'titles' => apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-cog"></i> ' . __('Custom Titles', 'wpabstracts'), 'titles')
	);

	$tabs = apply_filters('wpabstracts_admin_tabs', $basic_tabs);
	$tabs['help'] = apply_filters('wpabstracts_title_filter', '<i class="wpabstracts glyphicon glyphicon-question-sign"></i> ' . __('Help', 'wpabstracts'), 'help');

	$top_menu = '<div class="wpabstracts container-fluid">';
	$top_menu .= '<ul class="wpabstracts nav nav-tabs">';
	foreach ($tabs as $tab => $name) {
		$class = ($tab == $current) ? "wpabstracts active" : "";
		$top_menu .= "<li role='presentation' class='" . $class . "'><a href='?page=wpabstracts&tab=$tab'><strong>$name</strong></a></li>";
	}
	$top_menu .= '</ul>';
	$top_menu .= '</div>';
	echo $top_menu;
}

function wpabstracts_install()
{
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	// abstracts table
	$abs_tbl = $wpdb->prefix . "wpabstracts_abstracts";
	$abs_sql = "CREATE TABLE " . $abs_tbl . " (
		`abstract_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title` text,
		`text` longtext,
		`event` int(11),
		`topic_id` int(11),
        `topic` text,
		`status` int(11),
		`author` text,
		`author_email` text,
		`author_affiliation` text,
		`presenter` varchar(255),
		`presenter_email` varchar(255),
		`presenter_preference` varchar(255),
		`keywords` text,
		`submit_by` int(11),
		`submit_date` datetime,
		`modified_date` datetime,
		PRIMARY KEY (abstract_id)
	) $charset_collate;";
	dbDelta($abs_sql);

	// events Table
	$events_tbl = $wpdb->prefix . "wpabstracts_events";
	$events_sql = "CREATE TABLE " . $events_tbl . " (
		`event_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(255),
		`description` longtext,
		`address` longtext,
		`host` varchar(255),
		`topics` text,
		`start_date` date,
		`end_date` date,
		`deadline` date,
		`status` tinyint(1) DEFAULT 1,
		PRIMARY KEY  (event_id)
	) $charset_collate;";
	dbDelta($events_sql);

	// setup topics table
	$topics_tbl = $wpdb->prefix . "wpabstracts_topics";
	$topics_sql = "CREATE TABLE " . $topics_tbl . " (
		`topic_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(255),
		`event_id` int(11),
		`order` tinyint(1),
		`status` tinyint(1) DEFAULT 1,
		PRIMARY KEY  (topic_id)
	) $charset_collate;";
	dbDelta($topics_sql);

	// attachments
	$atts_tbl = $wpdb->prefix . "wpabstracts_attachments";
	$atts_sql = "CREATE TABLE " . $atts_tbl . " (
		`attachment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`abstracts_id` int(11),
		`filecontent` longblob,
		`filename` varchar(255),
		`filetype` varchar(255),
		`filesize` varchar(255),
		`format` tinyint(1),
		`status` tinyint(1),
		PRIMARY KEY  (attachment_id)
	) $charset_collate;";
	dbDelta($atts_sql);

	// setup email templates table
	$email_tbl = $wpdb->prefix . "wpabstracts_emailtemplates";
	$email_sql = "CREATE TABLE " . $email_tbl . " (
		`ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(255),
		`subject` varchar(255),
		`message` text,
		`from_name` varchar(255),
		`from_email` varchar(255),
		`type` varchar(255),
		`receiver` varchar(255),
		`trigger` varchar(255),
		`status_id` tinyint(1),
		`include_submission` tinyint(1) DEFAULT 0,
		`status` tinyint(1),
		PRIMARY KEY (ID)
	) $charset_collate;";
	dbDelta($email_sql);

	// setup status table - Since 2.3.0
	$status_tbl = $wpdb->prefix . "wpabstracts_statuses";
	$status_sql = "CREATE TABLE " . $status_tbl . " (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(255) NOT NULL,
		`order` int(11) DEFAULT NULL,
		`template_id` int(11),
		`status` TINYINT(1) NOT NULL DEFAULT 1,
		PRIMARY KEY (id)
	) $charset_collate;";
	dbDelta($status_sql);

	// add new email reminder table
	$maillog_tbl = $wpdb->prefix . "wpabstracts_maillog";
	$maillog_sql = "CREATE TABLE " . $maillog_tbl . " (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `abs_id` varchar(255) NOT NULL,
        `user_id` varchar(255) NOT NULL,
        `to` varchar(255) NOT NULL,
        `subject` text NOT NULL,
        `body` longtext NOT NULL,
        `created` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
	dbDelta($maillog_sql);

	// setup status if not inserted
	$statusesExist = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpabstracts_statuses");
	if (!$statusesExist) {
		$statuses = array('Pending', 'Under Review', 'Accepted', 'Rejected');
		foreach ($statuses as $key => $status) {
			$data = array('name' => $status, 'template_id' => $key + 1);
			$wpdb->insert($wpdb->prefix . "wpabstracts_statuses", $data);
		}
	}

	// install user table and settings
	wpabstracts_users_install();

	// init email templates if installing for the first time
	$templatesExist = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpabstracts_emailtemplates");
	if (!$templatesExist) {
		wpabstracts_init_email_templates();
	}

	// set default settings
	add_option("wpabstracts_chars_count", 250);
	add_option("wpabstracts_upload_limit", 3);
	add_option("wpabstracts_max_attach_size", 2048000);
	add_option('wpabstracts_author_instructions', "Enter description here.");
	add_option("wpabstracts_presenter_preference", "Poster,Panel,Roundtable,Projector");
	add_option("wpabstracts_email_admin", 1);
	add_option("wpabstracts_email_author", 1);
	add_option("wpabstracts_frontend_dashboard", 1);
	add_option("wpabstracts_reviewer_submit", 0);
	add_option("wpabstracts_reviewer_edit", 0);
	add_option("wpabstracts_blind_review", 0);
	add_option("wpabstracts_show_adminbar", 0);
	add_option("wpabstracts_permitted_attachments", 'pdf,doc,xls,docx,xlsx,txt,rtf');
	add_option("wpabstracts_change_ownership", 1);
	add_option("wpabstracts_show_reviews", 1);
	add_option("wpabstracts_show_description", 1);
	add_option("wpabstracts_show_author", 1);
	add_option("wpabstracts_show_presenter", 1);
	add_option("wpabstracts_show_attachments", 1);
	add_option("wpabstracts_show_keywords", 0);
	add_option("wpabstracts_show_conditions", 0);
	add_option("wpabstracts_terms_conditions", "Enter your terms and conditions here.");
	add_option("wpabstracts_sync_status", 0);
	add_option("wpabstracts_captcha", 1);
	add_option("wpabstracts_captcha_secret", "JSuqfZDXakXbxrW3CzZgY");
	add_option("wpabstracts_editor_media", 1);
	add_option("wpabstracts_enable_register", 1);
	add_option("wpabstracts_login_redirect", 1);
	add_option("wpabstracts_edit_status", array(1));
	add_option("wpabstracts_default_status", 1);
	add_option("wpabstracts_submit_limit", 2);
	add_option("wpabstracts_attachment_pref", 'optional');
	add_option("wpabstracts_accepted_status", 3);

	// default abstracts admin columns
	$abstracts_columns['event'] = array('label' => __('Event', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['topic'] = array('label' => __('Topic', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['author'] = array('label' => __('Author', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['preference'] = array('label' => __('Preference', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['status'] = array('label' => __('Status', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['reviewers'] = array('label' => __('Reviewers', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['date_submitted'] = array('label' => __('Date Submitted', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['attachments'] = array('label' => __('Attachments', 'wpabstracts'), 'enabled' => true);
	$abstracts_columns['submit_by'] = array('label' => __('Submit By', 'wpabstracts'), 'enabled' => false);
	$abstracts_columns['date_modified'] = array('label' => __('Date Modified', 'wpabstracts'), 'enabled' => false);
	$abstracts_columns['keywords'] = array('label' => __('Keywords', 'wpabstracts'), 'enabled' => false);

	add_option("wpabstracts_abstracts_columns", $abstracts_columns);

	// default accepted shortcode options
	$view_pdf_options['abstract_id'] = array('label' => __('ID', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['title'] = array('label' => __('Title', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['text'] = array('label' => __('Description', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['event'] = array('label' => __('Event', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['topic'] = array('label' => __('Topic', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['keywords'] = array('label' => __('Keywords', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['author'] = array('label' => __('Author Name', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['author_affiliation'] = array('label' => __('Author Affiliation', 'wpabstracts'), 'enabled' => true);
	$view_pdf_options['presenter'] = array('label' => __('Presenter Name', 'wpabstracts'), 'enabled' => false);
	$view_pdf_options['presenter_preference'] = array('label' => __('Preference Preference', 'wpabstracts'), 'enabled' => false);
	$view_pdf_options['status'] = array('label' => __('Status', 'wpabstracts'), 'enabled' => false);
	$view_pdf_options['date_submitted'] = array('label' => __('Date Submitted', 'wpabstracts'), 'enabled' => false);

	add_option("wpabstracts_pdf_options", $view_pdf_options);
	$view_pdf_options['attachments'] = array('label' => __('Attachments', 'wpabstracts'), 'enabled' => false);
	add_option("wpabstracts_accepted_shortcode", $view_pdf_options);

	// clean up as of v2.5
	// these are now being enabled / disabled directly on the templates
	delete_option("wpabstracts_submission_notification");
	delete_option("wpabstracts_edit_notification");
	delete_option("wpabstracts_review_notification");
	delete_option("wpabstracts_status_notification");
	// templates now have triggers, reviewers and status
	delete_option("wpabstracts_submit_templateId");
	delete_option("wpabstracts_author_edit_templateId");
	delete_option("wpabstracts_admin_templateId");
	delete_option("wpabstracts_admin_edit_templateId");
	delete_option("wpabstracts_approval_templateId");
	delete_option("wpabstracts_rejected_templateId");
	delete_option("wpabstracts_underreview_templateId");
}

function wpabstracts_users_install()
{
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	$user_tbl = $wpdb->prefix . "wpabstracts_users";
	$wp_user = $wpdb->prefix . "wp_users";
	$create_users = "CREATE TABLE " . $user_tbl . " (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) UNIQUE NOT NULL,
		data longtext,
		activation_key varchar(255),
		status TINYINT(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (id)
	) $charset_collate;";
	dbDelta($create_users);

	// sync users to wpabstracts profile
	$users = get_users();
	foreach ($users as $user) {
		$userExist = $wpdb->get_var("SELECT COUNT(*) FROM " . $user_tbl . " WHERE user_id = " . $user->ID);
		if (!$userExist) {
			$data = array('user_id' => $user->ID, 'status' => 1);
			$wpdb->insert($wpdb->prefix . 'wpabstracts_users', $data);
		}
	}

	$default_form = '[{"type":"paragraph","label":"Please take a moment and tell us about yourself."},{"type":"select","required":true,"label":"Title","name":"title","className":"form-control","layout":"form-group col-sm-4","values":[{"label":"Mr","value":"Mr"},{"label":"Mrs","value":"Mrs"},{"label":"Dr","value":"Dr"},{"label":"Prof","value":"Prof"}]},{"type":"text","required":true,"label":"First Name","description":"Please enter first name","name":"firstname","className":"form-control","layout":"form-group col-sm-4","wpSync":"first_name"},{"type":"text","required":true,"label":"Last Name","description":"Enter last name","name":"lastname","className":"form-control","layout":"form-group col-sm-4","wpSync":"last_name"},{"type":"select","required":true,"label":"Gender","description":"Select a gender","name":"gender","className":"form-control","layout":"form-group col-sm-4","values":[{"label":"Male","value":"Male"},{"label":"Female","value":"Female"}]},{"type":"text","label":"Phone","description":"How can you reach you?","name":"phone","className":"form-control","layout":"form-group col-sm-4"},{"type":"text","label":"Designation","description":"Tell us your role","name":"designation","className":"form-control","layout":"form-group col-sm-4"},{"type":"text","label":"Personal URL","description":"Enter your personal website  Twitter or LinkedIn URL","name":"personalurl","className":"form-control","layout":"form-group col-sm-4","wpSync":"user_url"},{"type":"text","label":"Organization","description":"What organization are you affiliated with.","name":"organization","className":"form-control","layout":"form-group col-sm-4"},{"type":"select","label":"Contact Preference","description":"What is your preferred method of contact?","name":"contact-preference","className":"form-control","layout":"form-group col-sm-4","values":[{"label":"Email","value":"Email","selected":true},{"label":"Phone","value":"Phone"}]},{"type":"text","label":"Address","description":"Enter full physical address","name":"address","className":"form-control","layout":"form-group col-sm-12","wpSync":"address"},{"type":"textarea","label":"Bio","description":"Tell us about yourself","name":"bio","rows":"3","className":"form-control","layout":"form-group col-sm-12","wpSync":"description"}]';

	add_option('wpabstracts_registration_form', $default_form);

	$formOptions = new stdClass();
	$formOptions->admin_name = get_option('blogname');
	$formOptions->admin_email = get_option('admin_email');
	$formOptions->email_subject = "Your Account Registration";
	$formOptions->auto_activate_on = 0;
	$formOptions->ignore_activation = 0;
	$formOptions->sync_fields = 1;
	$formOptions->display_name = 'firstname';
	$formOptions->reg_message_on = 1;
	$formOptions->reg_message = "Thank you for registering. You will receive a confirmation email shortly.";
	$formOptions->redirect_after = 5;
	$formOptions->redirect_url = home_url();
	$formOptions->reg_email_on = 1;
	$formOptions->reg_email = 'Hello {DISPLAY_NAME},
	You have successfully registered for this Event.
	Please click the link below to activate your account.

	Activate Account: {ACTIVATE_LINK}

	Please visit your dashboard at: {SITE_URL} to submit or manage your abstracts.


	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';

	$formOptions->admin_email_on = 0;
	$formOptions->admin_from_name = get_option('blogname');
	$formOptions->admin_from_email = get_option('admin_email');
	$formOptions->admin_email_subject = "A new user has registered";
	$formOptions->admin_reg_email = 'Hello {DISPLAY_NAME},

	A new user has just registered.
    
	Please login to your admin area if you need to manage the user.

	Please visit your dashboard at: {SITE_URL} to submit or manage your abstracts.


	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';

	// password Rules
	$pw_rules = new stdClass();
	$pw_rules->min_pwd = 7;
	$pw_rules->max_pwd = 14;
	$pw_rules->number = 1;
	$pw_rules->uppercase = 1;
	$pw_rules->lowercase = 1;
	$pw_rules->special = 1;
	$formOptions->password_rules = $pw_rules;

	// add admin column_status
	$columns['firstname'] = 'First Name';
	$columns['lastname'] = 'Last Name';
	$columns['phone'] = 'Phone';
	$columns['designation'] = 'Designation';
	$columns['personalurl'] = 'Personal URL';
	$formOptions->admin_columns = $columns;

	add_option('wpabstracts_user_settings', $formOptions);
}

function wpabstracts_init_email_templates()
{
	global $wpdb;

	// user submission confirmation template
	$submitConfirmationMsg = 'Hi {DISPLAY_NAME},
	You have successfully submitted your abstract.
	Abstracts Title: {ABSTRACT_TITLE}
	Abstracts ID: {ABSTRACT_ID}
	Event: {EVENT_NAME}
	To make changes to your submission or view the status visit {SITE_URL} and sign in to your dashboard.
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';

	$submitConfirmationTemplate = array(
		'name' => "Abstracts Submitted - Author Notification",
		'subject' => "Your abstract was submitted successfully",
		'message' => $submitConfirmationMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "submission",
		'receiver' => "author",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $submitConfirmationTemplate);

	// user edit confirmation template
	$editConfirmationMsg = 'Hi {DISPLAY_NAME},
	You have successfully edited your abstract.
	Abstracts Title: {ABSTRACT_TITLE}
	Abstracts ID: {ABSTRACT_ID}
	Event: {EVENT_NAME}
	To make changes to your submission or view the status visit {SITE_URL} and sign in to your dashboard.
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';

	$editConfirmationTemplate = array(
		'name' => "Abstracts Edited - Author Notification",
		'subject' => "Your abstract was edited successfully",
		'message' => $editConfirmationMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "revision",
		'receiver' => "author",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $editConfirmationTemplate);

	// admin submission notification template
	$adminSubmitEmailMsg = 'Hello {DISPLAY_NAME},
	You have a new abstract for {SITE_NAME}
	Abstract Title: {ABSTRACT_TITLE}
	Abstract ID: {ABSTRACT_ID}
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';
	$adminSubmitEmailTemplate = array(
		'name' => "Abstract Submitted - Admin Notification",
		'subject' => "A new abstract was submitted",
		'message' => $adminSubmitEmailMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "submission",
		'receiver' => "admin",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $adminSubmitEmailTemplate);


	// admin abstract edit notification template
	$adminEditEmailMsg = 'Hello {DISPLAY_NAME},
	Abstract Title: {ABSTRACT_TITLE} was edited by {SUBMITTER_NAME}.
	Abstract ID: {ABSTRACT_ID}
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';
	$adminEditEmailTemplate = array(
		'name' => "Abstract Edited - Admin Notification",
		'subject' => "An abstract was edited",
		'message' => $adminEditEmailMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "revision",
		'receiver' => "admin",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $adminEditEmailTemplate);

	// author acceptance notification template
	$authorApprovalMsg = 'Hello {DISPLAY_NAME},
	We are happy to announce that your abstract entitled {ABSTRACT_TITLE} was accepted.
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';
	$authorApprovalTemplate = array(
		'name' => "Abstract Accepted - Author Notification",
		'subject' => "Your abstract was accepted",
		'message' => $authorApprovalMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "status",
		'receiver' => "author",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $authorApprovalTemplate);

	// author rejection notification template
	$authorRejectedMsg = 'Hello {DISPLAY_NAME},
	We are sorry to inform you that your abstract entitled {ABSTRACT_TITLE} was rejected.
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';
	$authorRejectedTemplate = array(
		'name' => "Abstract Rejected - Author Notification",
		'subject' => "Your abstract was rejected",
		'message' => $authorRejectedMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "status",
		'receiver' => "author",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $authorRejectedTemplate);

	// author under review notification template
	$abstractsUnderReviewMsg = 'Hello {DISPLAY_NAME},
	We are happy to inform you that your abstract entitled {ABSTRACT_TITLE} is now under review.
	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';
	$abstractsUnderReviewTemplate = array(
		'name' => "Abstract Under Review - Author Notification",
		'subject' => "Your abstract is under review",
		'message' => $abstractsUnderReviewMsg,
		'from_name' => get_option('blogname'),
		'from_email' => get_option('admin_email'),
		'type' => "abstract",
		'trigger' => "status",
		'receiver' => "author",
		'status' => 0
	);
	$success = $wpdb->insert($wpdb->prefix . 'wpabstracts_emailtemplates', $abstractsUnderReviewTemplate);
}

function wpabastracts_option_updates()
{
	$pdf_options = get_option('wpabstracts_pdf_options');
	if (!array_key_exists('remove_branding', $pdf_options)) { // since v2.6.0
		$pdf_options['remove_branding'] = array('label' => __('Remove Header/Footer', 'wpabstracts'), 'enabled' => false);
		update_option("wpabstracts_pdf_options", $pdf_options);
	}
	// update user settings
	$user_settings = get_option('wpabstracts_user_settings');
	$user_settings->admin_email_on = 0;
	$user_settings->admin_from_name = get_option('blogname');
	$user_settings->admin_from_email = get_option('admin_email');
	$user_settings->admin_email_subject = "A new user has registered";
	$user_settings->admin_reg_email = 'Hello {DISPLAY_NAME},
	A new user has just registered.
	Please login to your admin area if you need to manage the user.

	Please visit your dashboard at: {SITE_URL} to submit or manage your abstracts.


	Regards,
	WP Abstracts Team
	{SITE_NAME}
	{SITE_URL}';
	update_option("wpabstracts_user_settings", $user_settings);
}

function wpabastracts_db_migrations()
{
	global $wpdb;
	$wpdb->show_errors();
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$abs_tbl = $wpdb->prefix . "wpabstracts_abstracts";
	$topic_tbl = $wpdb->prefix . "wpabstracts_topics";
	// retrieve topics from events table
	$sqlGetTopics = "SELECT DISTINCT topics, event_id FROM {$wpdb->prefix}wpabstracts_events";
	$results = $wpdb->get_results($sqlGetTopics, ARRAY_A);

	if ($results) {
		// Loop through each row and insert topics into topics table
		foreach ($results as $event) {
			$topics = explode('|', $event['topics']); // Split topics by pipe delimiter
			foreach ($topics as $topic) {
				$topic = trim($topic); // Trim whitespace
				// Insert topic into topics table
				$insert_result = $wpdb->insert(
					$topic_tbl,
					array(
						'name' => $topic,
						'event_id' => $event['event_id']
					),
					array('%s', '%d')
				);
			}
		}
	}

	// update abstracts table with topic_ids
	$sqlUpdateAbstracts = "UPDATE {$abs_tbl} abs JOIN {$topic_tbl} t ON abs.topic = t.name SET abs.topic_id = t.topic_id";
	dbDelta($sqlUpdateAbstracts);
}

add_action('admin_init', 'wpabstracts_version_check');
function wpabstracts_version_check()
{
	if (version_compare(WPABSTRACTS_VERSION, get_option("wpabstracts_version"), '>')) {
		// update tables 
		wpabstracts_install();
		wpabastracts_db_migrations();
		wpabastracts_option_updates(); // update options
		update_option("wpabstracts_version", WPABSTRACTS_VERSION);
	}
}

function wpabstracts_deactivation()
{
	delete_option('wpabstracts_version');
}

function wpabstracts_load_js($frontend)
{
	if ($frontend && isset($_GET['task']) && ($_GET['task'] == 'profile' || $_GET['task'] == 'register')) {
		wpabstracts_load_fb_js();
	}
	wp_enqueue_script('wpabstracts-multiselect', plugins_url('assets/js/multiselect.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-datatables', plugins_url('assets/js/datatables.min.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-dt-natural', plugins_url('assets/js/datatables.natural.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-bootstrap', plugins_url('assets/js/bootstrap.min.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-jquery-ui', plugins_url('assets/js/jquery-ui.min.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-alertify', plugins_url('assets/js/alertify.min.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-scripts', plugins_url('assets/js/wpabstracts.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wpabstracts_localize();
}

function wpabstracts_load_fb_js()
{
	wp_enqueue_script('wpabstracts-form-polyfill', plugins_url('assets/js/polyfill.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-form-builder', plugins_url('assets/js/form-builder.min.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
	wp_enqueue_script('wpabstracts-form-render', plugins_url('assets/js/form-render.min.js', __FILE__), array('jquery'), WPABSTRACTS_VERSION, true);
}

function wpabstracts_load_css()
{
	wp_enqueue_style('wpabstracts-datatables', plugins_url('assets/css/datatables.min.css', __FILE__), null, WPABSTRACTS_VERSION);
	wp_enqueue_style('wpabstracts-multiselect-style', plugins_url('assets/css/jquery.multiselect.css', __FILE__), null, WPABSTRACTS_VERSION);
	wp_enqueue_style('wpabstracts-style', plugins_url('assets/css/wpabstracts.css', __FILE__));
	wp_enqueue_style('wpabstracts-fonts', plugins_url('assets/css/fontawesome.css', __FILE__), null, WPABSTRACTS_VERSION);
	wp_enqueue_style('wpabstracts-alertify-css', plugins_url('assets/css/alertify.min.css', __FILE__));
	wp_enqueue_style('wpabstracts-jquery-ui-css', plugins_url('assets/css/jquery-ui.css', __FILE__));
	wp_enqueue_style('wpabstracts-jquery-ui-ie-css', plugins_url('assets/css/jquery-ui-ie.css', __FILE__));
}

add_filter('wp_enqueue_scripts', 'wpabstracts_force_jquery_inhead', 1);
function wpabstracts_force_jquery_inhead()
{
	wp_enqueue_script('jquery', false, array(), false, false);
}

function wpabstracts_localize()
{
	$schema = is_ssl() ? 'https' : 'http';
	$data = array(
		'ajaxurl' => admin_url('admin-ajax.php', $schema),
		'security' => wp_create_nonce(WPABSTRACTS_SECRET_KEY),
		'confirm_abstract_delete' => __('Do you really want to delete this abstract, its reviews and all its attachments?', 'wpabstracts'),
		'confirm_abstracts_delete' => __('Do you really want to delete the selected abstracts, their reviews and all their attachments?', 'wpabstracts'),
		'confirm_event_delete' => __('Are you sure you want to delete this event? Deleting this event will delete all submissions, reviews and attachments related to this event. This cannot be undone. Type DELETE to confirm.', 'wpabstracts'),
		'confirm_template_delete' => __('Do you really want to delete this email template?', 'wpabstracts'),
		'confirm_review_delete' => __('Do you really want to delete this review?', 'wpabstracts'),
		'confirm_atts_delete' => __('Do you really want to delete this attachment?', 'wpabstracts'),
		'confirm_user_delete' => __('Are you sure you want to delete this user?', 'wpabstracts'),
		'confirm_topic_delete' => __('Are you sure you want to delete this topic?', 'wpabstracts'),
		'sign_in_msg' => __('Please enter a username and password to sign in.', 'wpabstracts'),
		'captcha_required' => __('Please enter a the security code.', 'wpabstracts'),
		'change_status' => apply_filters('wpabstracts_title_filter', __("Change Status", 'wpabstracts'), 'change_status'),
		'select_status' => apply_filters('wpabstracts_title_filter', __("Please select a status", 'wpabstracts'), 'select_status'),
		'required_fields' => __('Please fill in all required fields.', 'wpabstracts'),
		'desc_required' => __('Please add your abstract description', 'wpabstracts'),
		'word_count_err' => __("You have exceeded the maximum words allowed for this submission.", 'wpabstracts'),
		'file_ext_err' => __('One or more of your file extension is not supported.', 'wpabstracts'),
		'file_size_err' => __('One or more of your files exceeds the maximum file size allowed.', 'wpabstracts'),
		'max_atts_size' => get_option('wpabstracts_max_attach_size'),
		'approved_exts' => explode(',', get_option('wpabstracts_permitted_attachments')),
		'no_attachments' => apply_filters('wpabstracts_title_filter', __("No Attachments Uploaded", 'wpabstracts'), 'no_attachments'),
		'no_order_email' => apply_filters('wpabstracts_title_filter', __("Please enter the email address used for the order.", 'wpabstracts'), 'no_order_email'),
		'reg_fields_success' => __("Successfully updated user registration form.", 'wpabstracts'),
		'reg_fields_failure' => __("Failed to save register form fields.", 'wpabstracts'),
		'copy_success' => __("Successfully copied to clipboard.", 'wpabstracts'),
		'copy_failure' => __("Failed to copy text to clipboard.", 'wpabstracts'),
		'confirm_status_delete' => __('Submissions with this status will no longer have a status. Do you really want to delete this status?', 'wpabstracts'),
		'status_required' => __('At least one status is required.', 'wpabstracts'),
		'topic_required' => __('At least one topic is required.', 'wpabstracts'),
		'confirm_form_restore' => __('Are you sure you want to restore this form to the default inputs? This cannot be undone.', 'wpabstracts'),
		'confirm_title_restore' => __('Are you sure you want to restore your default titles?', 'wpabstracts'),
		'confirm_usermeta_sync' => __('Are you sure you want to SYNC profile data from WP Abstracts to Wordpress user profiles? This cannot be undone. Type SYNC to confirm.', 'wpabstracts'),
		'invalid_email' => __('One or more of your email address seems invalid.', 'wpabstracts'),
	);
	wp_localize_script('wpabstracts-scripts', 'wpabstracts', $data);
}

add_action('wp_ajax_loadreviewers', 'wpabstracts_load_reviewers_ajax');
function wpabstracts_load_reviewers_ajax()
{
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'abstracts/abstracts.manage.php'));
	wpabstracts_load_reviewers();
}

add_action('wp_ajax_loadstatus', 'wpabstracts_load_status_ajax');
function wpabstracts_load_status_ajax()
{
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'abstracts/abstracts.manage.php'));
	wpabstracts_load_status();
}

add_action('wp_ajax_loadtopics', 'wpabstracts_load_topics_ajax');
function wpabstracts_load_topics_ajax()
{
	if ($_POST['event_id']) {
		$event_id = intval($_POST['event_id']);
		$topics = wpabstracts_get_topics($event_id);
		foreach ($topics as $topic) { ?>
			<option value="<?php echo esc_attr($topic->topic_id); ?>"><?php echo esc_attr($topic->name); ?></option>;
<?php }
	} else {
		_e("Error! Missing Event ID from wp_ajax_loadtopics.", 'wpabstracts');
	}
	die();
}

function wpabstracts_admin_header()
{
	$header = '<div class="wpabstracts container-fluid wpabstracts-admin-container">' .
		'<div class="wpabstracts row logo">' .
		'<div class="wpabstracts col-xs-12 col-md-8">' .
		'<a href="?page=wpabstracts"><img src="' . plugins_url("assets/images/admin_logo.png", __FILE__) . '"></a>' .
		'<span style="vertical-align: middle; font-size: 11px; color: #44648A;"> v' . WPABSTRACTS_VERSION . '</span>' .
		'</div>' .
		'<div class="wpabstracts col-xs-12 col-md-4">' .
		__('Need more features?', 'wpabstracts') . ' <a href="https://www.wpabstracts.com" target="_blank"><span class="wpabstracts btn btn-primary">GET PRO</span></a>' .
		'</div>' .
		'</div>' .
		'</div>';
	echo apply_filters('wpabstracts_admin_header', $header);
}

function wpabstracts_set_html_content_type()
{
	return 'text/html';
}

function wpabstracts_editor_admin_init()
{
	wp_enqueue_script('post');
	wp_enqueue_script('editor');
	wp_enqueue_script('media-upload');
}

function wpabstracts_editor_init($initArray)
{
	$initArray['setup'] = 'function(ed){ed.on("keyup", function(ed, e){ wpabstracts_updateWordCount()})}';
	return $initArray;
}

add_action('wp_ajax_wpamaillog', 'wpabstracts_maillog_ajax');
function wpabstracts_maillog_ajax()
{
	wpabstracts_get_maillog();
}
