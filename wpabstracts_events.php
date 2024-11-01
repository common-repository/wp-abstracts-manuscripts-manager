<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
if(!class_exists('WPAbstract_Abstracts_Table')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_classes.php'));
}
if($_GET["tab"]=="events") {
	if(isset($_GET["task"])){
		$task = $_GET["task"];
		$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
		switch($task){
			case 'new':
			wpabstracts_add_event();
			break;
			case 'edit':
			wpabstracts_edit_event($id);
			break;
			case 'archive':
			wpabstracts_event_status($id, '-1', true);
			wpabstracts_show_events();
			break;
			case 'unarchive':
			wpabstracts_event_status($id, '1', true);
			wpabstracts_show_events();
			break;
			case 'delete':
			wpabstracts_delete_event($id);
			wpabstracts_show_events();
			break;
			default :
			if(has_action('wpabstracts_page_render')){
				do_action('wpabstracts_page_render');
			}else{
				wpabstracts_show_events();
			}
		}
	}else{
		wpabstracts_show_events();
	}
}
else{
	echo "You do not have permission to view this page";
}

function wpabstracts_add_event() {
	global $wpdb;
	$tab = "?page=wpabstracts&tab=events";
	if ($_POST) {
		$abs_event_name = sanitize_text_field($_POST["abs_event_name"]);
		$abs_event_desc = wp_kses_post($_POST["abs_event_desc"]);
		$abs_event_address = sanitize_text_field($_POST["abs_event_address"]);
		$abs_event_status = sanitize_text_field($_POST["abs_event_status"]);
		$abs_event_host = sanitize_text_field($_POST["abs_event_host"]);
		$abs_event_start = sanitize_text_field($_POST["abs_event_start"]);
		$abs_event_end = sanitize_text_field($_POST["abs_event_end"]);
		$abs_event_deadline = sanitize_text_field($_POST["abs_event_deadline"]);
		// get and sanitize topics
		if(sizeof($_POST["topics"])>1) {
			foreach($_POST["topics"] as $key=>$topic) {
				$topic = sanitize_text_field($_POST["topics"][$key]);
				$topics[] = $topic;
			}
			$event_topics = implode('|',$topics);
		} else {
			$topic = sanitize_text_field($_POST["topics"][0]);
			$event_topics = $topic;
		}

		$event_data = array(
			'name' => $abs_event_name,
			'description' => $abs_event_desc,
			'address' => $abs_event_address,
			'status'  => $abs_event_status,
			'host'  => $abs_event_host,
			'topics' => $event_topics,
			'start_date' => $abs_event_start,
			'end_date' => $abs_event_end,
			'deadline' => $abs_event_deadline
		);

		$filtered_data = apply_filters('wpabstracts_save_event', $event_data, $_POST);

		$wpdb->show_errors();
		$wpdb->insert($wpdb->prefix.'wpabstracts_events', $filtered_data);

		wpabstracts_redirect($tab);
	}
	else {
		wpabstracts_get_add_view('Event', null);
	}
}

function wpabstracts_edit_event($id){
	global $wpdb;
	$tab = "?page=wpabstracts&tab=events";
	if ($_POST) {
		$abs_event_name = sanitize_text_field($_POST["abs_event_name"]);
		$abs_event_desc = wp_kses_post($_POST["abs_event_desc"]);
		$abs_event_address = sanitize_text_field($_POST["abs_event_address"]);
		$abs_event_status = sanitize_text_field($_POST["abs_event_status"]);
		$abs_event_host = sanitize_text_field($_POST["abs_event_host"]);
		$abs_event_start = sanitize_text_field($_POST["abs_event_start"]);
		$abs_event_end = sanitize_text_field($_POST["abs_event_end"]);
		$abs_event_deadline = sanitize_text_field($_POST["abs_event_deadline"]);
		// get and sanitize topics
		if(sizeof($_POST["topics"])>1) {
			foreach($_POST["topics"] as $key=>$topic) {
				$topic = sanitize_text_field($_POST["topics"][$key]);
				$topics[] = $topic;
			}
			$event_topics = implode('|', $topics);
		} else {
			$topic = sanitize_text_field($_POST["topics"][0]);
			$event_topics = $topic;
		}

		$event_data = array(
			'name' => $abs_event_name,
			'description' => $abs_event_desc,
			'address' => $abs_event_address,
			'status'  => $abs_event_status,
			'host'  => $abs_event_host,
			'topics' => $event_topics,
			'start_date' => $abs_event_start,
			'end_date' => $abs_event_end,
			'deadline' => $abs_event_deadline
		);

		$filtered_data = apply_filters('wpabstracts_save_event', $event_data, $_POST);

		$where = array('event_id' => $id);

		$wpdb->show_errors();
		$wpdb->update($wpdb->prefix.'wpabstracts_events', $filtered_data, $where);

		wpabstracts_redirect($tab);
	}
	else {
		$event = wpabstracts_get_edit_view('Event', $id);
        if($event){
            echo $event;
        }else{
            wpabstracts_show_message(__('Could not locate this resource. Please try again.', 'wpabstracts'), 'alert-danger');
		}
	}
}

function wpabstracts_event_status($id, $status, $show_message) {
	global $wpdb;
	$data = array('status'  => $status);
	$where = array('event_id' => $id);
	$wpdb->show_errors();
	$success = $wpdb->update($wpdb->prefix.'wpabstracts_events', $data, $where);
	if($success && $show_message) {
		$actions = array(
			'1' => 'Unarhived',
			'-1' => 'Archived'
		);
		wpabstracts_show_message("Your event was successfully " . $actions[$status], 'alert-success');
	}
}

function wpabstracts_delete_event($id){
	if(wp_verify_nonce($_GET['_wpnonce'], "delete-event-".$id)) {
		$event = wpabstracts_get_event($id);
		if($event) {
			global $wpdb;
			$wpdb->show_errors();
			$abs_ids = wpabstracts_get_abstracts('event_id', $id);
			if($abs_ids) {
				$wpdb->query("DELETE FROM {$wpdb->prefix}wpabstracts_abstracts WHERE `abstract_id` IN($abs_ids)");
				$wpdb->query("DELETE FROM {$wpdb->prefix}wpabstracts_attachments WHERE `abstracts_id` IN($abs_ids)");
			}
			$wpdb->query("DELETE FROM {$wpdb->prefix}wpabstracts_events WHERE `event_id` = " . intval($id));
			wpabstracts_show_message("Event ID ". intval($id) . " was successfully deleted", 'alert-success');
		}else {
			wpabstracts_show_message("The event you are attempting to delete does not exists.", 'alert-danger');
		}
	}else {
		wpabstracts_show_message("Action terminated due to failed security checks.", 'alert-danger');
	}
}

function wpabstracts_load_topics() {
	global $wpdb;
	if($_POST['event_id']){
		$event_id = intval($_POST['event_id']);
		$event = $wpdb->get_row("SELECT topics FROM {$wpdb->prefix}wpabstracts_events Where event_id =" . $event_id);
		$topics = explode('|',$event->topics);
		foreach($topics as $topic){ ?>
			<option value="<?php echo esc_attr($topic);?>"><?php echo esc_attr($topic);?></option>;
		<?php }
	}else{
		_e("Error!", 'wpabstracts');
	}
	die();
}

function wpabstracts_show_events(){ ?>
	<div class="wpabstracts container-fluid wpabstracts-admin-container">
		<h3><?php echo apply_filters('wpabstracts_title_filter', __('Events','wpabstracts'), 'events');?> <a href="?page=wpabstracts&tab=events&task=new" class="wpabstracts btn btn-primary" /><?php _e('Add New', 'wpabstracts');?></a></h3>
	</div>
	<form id="abs_event_list" method="get">
		<input type="hidden" name="page" value="wpabstracts" />
		<input type="hidden" name="tab" value="events" />
		<?php
		$events = new WPAbstract_Events_Table();
		$events->prepare_items();
		$events->display();
		?>
	</form>
	<script>
		jQuery(document).ready( function () {
			var event_count = '<?php echo count($events->items);?>';
			if(event_count > 0){
				var table = jQuery('.wp-list-table').DataTable({
					responsive: false,
					dom: 'Bfrltip',
					buttons: [],
					colReorder: false,
				});

				table.column('.column-status').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on('change', function () {
						column.search(jQuery(this).val()).draw();
					}).append(jQuery('<option value="">Filter by Status</option>')).attr('id', 'wpa_event_status').attr('name', 'wpa_event_status');
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery(`<option value="${val}">${val}</option>`));
					});
				});
			} 
		});
	</script>
	<?php
}
