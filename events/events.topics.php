<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
if(!class_exists('WPAbstracts_Event_Topics')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'events/events.classes.php'));
}
if(!class_exists('WPAbstracts_Emailer')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_emailer.php'));
}
if($_GET["subtab"]=="topics") {
	if(isset($_GET["task"])){
		$task = $_GET["task"];
		switch($task){
			case 'new':
			wpabstracts_add_topic();
			break;
			case 'edit':
			wpabstracts_edit_topic(intval($_GET["id"]));
			break;
			case 'delete':
			wpabstracts_delete_topic(intval($_GET["id"]), true);
			default:
			wpabstracts_show_topics();
			break;
		}
	}else{
		wpabstracts_show_topics();
	}
}
else{
	echo "You do not have permission to view this page";
}

function wpabstracts_add_topic() {
	global $wpdb;
	$tab = "?page=wpabstracts&tab=events&subtab=topics";
	if ($_POST) {
        $data = array(
			'name' => isset($_POST["name"]) ? sanitize_text_field($_POST["name"]) : '',
			'event_id' => isset($_POST["event_id"]) ? intval($_POST["event_id"]) : null,
		);
		$wpdb->show_errors();
		$wpdb->insert($wpdb->prefix.'wpabstracts_topics', $data);
		wpabstracts_redirect($tab);
	}
	else {
		wpabstracts_get_add_view('topics', null);
	}
}

function wpabstracts_edit_topic($id){
	global $wpdb;
	$tab = "?page=wpabstracts&tab=events&subtab=topics";
	if ($_POST) {
        $data = array(
			'name' => isset($_POST["name"]) ? sanitize_text_field($_POST["name"]) : '',
			'event_id' => isset($_POST["event_id"]) ? intval($_POST["event_id"]) : null,
		);
		$where = array('topic_id' => $id);
		$wpdb->show_errors();
		$wpdb->update($wpdb->prefix.'wpabstracts_topics', $data, $where);
		wpabstracts_redirect($tab);
	}
	else {
		echo wpabstracts_get_edit_view('topics', $id);
	}
}

function wpabstracts_delete_topic($id, $message){
	global $wpdb;
	$wpdb->show_errors();
    $wpdb->query("DELETE from {$wpdb->prefix}wpabstracts_topics WHERE topic_id = " . intval($id));
    if($message){
        wpabstracts_show_message("Topic ID " . intval($id) . " was successfully deleted", 'alert-success');
    }
}

function wpabstracts_show_topics(){ ?>
	<div class="wpabstracts container-fluid wpabstracts-admin-container">
		<h3><?php echo apply_filters('wpabstracts_title_filter', __('Topics','wpabstracts'), 'topics');?> <a href="?page=wpabstracts&tab=events&subtab=topics&task=new" class="wpabstracts btn btn-primary" /><?php _e('Add New', 'wpabstracts');?></a></h3>
	</div>
	<form id="showsEvents" method="get">
		<input type="hidden" name="page" value="wpabstracts" />
		<input type="hidden" name="tab" value="events" />
        <input type="hidden" name="subtab" value="topics" />
		<?php
		$showEvents = new WPAbstracts_Event_Topics();
		$showEvents->prepare_items();
		$showEvents->display();
		?>
	</form>
	<script>
		jQuery(document).ready( function () {
			jQuery('.wp-list-table').DataTable();
		});
	</script>
	<?php
}
