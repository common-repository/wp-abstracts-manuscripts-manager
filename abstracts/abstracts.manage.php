<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(!class_exists('WPAbstract_Abstracts_Table')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'abstracts/abstracts.classes.php'));
}
if(!class_exists('WPAbstracts_Emailer')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_emailer.php'));
}

if(is_admin() && isset($_GET['tab']) && $_GET["tab"]=="abstracts"){
	if(isset($_GET["task"])){
		$task = sanitize_text_field($_GET["task"]);
		$status = isset($_GET["status"]) ? sanitize_text_field($_GET["status"]) : 0;
		$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

		switch($task){
			case 'new':
			wpabstracts_add_abstract();
			break;
			case 'edit':
			wpabstracts_edit_abstract($id);
			break;
			case 'view':
			wpabstracts_view_abstract($id);
			break;
			case 'status':
			wpabstracts_change_status($status);
			wpabstracts_display_abstracts();
			break;
			case 'delete':
			wpabstracts_delete_abstracts();
			wpabstracts_display_abstracts();
			break;
			default :
			if(has_action('wpabstracts_page_render')){
				do_action('wpabstracts_page_render');
			}else{
				wpabstracts_display_abstracts();
			}
			break;
		}

	}else{
        wpabstracts_display_abstracts();
	}
}

function wpabstracts_add_abstract($event_id = null) {
	if($_POST){
		$id = wpabstracts_manage_abstracts(0, 'insert');
		if($_FILES) {
			wpabstracts_upload_attachments($id);
		}
		wpabstracts_send_abs_notifications($id, 'submission');
		$redirect = (is_admin()) ? '?page=wpabstracts&tab=abstracts' : '?dashboard';
		wpabstracts_redirect($redirect);
	}
	else {
		wpabstracts_get_add_view('abstracts', $event_id);
	}
}

function wpabstracts_edit_abstract($id) {
	if ($_POST) {
		wpabstracts_manage_abstracts($id, 'update');
		if($_FILES){
			wpabstracts_upload_attachments($id);
		}
		wpabstracts_send_abs_notifications($id, 'revision');
		$redirect = (is_admin()) ? '?page=wpabstracts&tab=abstracts' : '?dashboard';
		wpabstracts_redirect($redirect);
	}else{
		$abstract = wpabstracts_get_edit_view('abstracts', $id);
        if($abstract){
            echo $abstract;
        }else{
            wpabstracts_show_message(__('Could not locate this resource. Please try again.', 'wpabstracts'), 'alert-danger');
		}
	}
}

function wpabstracts_view_abstract($id) {
	$abstract = wpabstracts_get_readonly_view('abstracts', $id);
	if($abstract){
		echo $abstract;
	}else{
		wpabstracts_show_message(__('Could not locate this resource. Please try again.', 'wpabstracts'), 'alert-danger');
	}
}

function wpabstracts_send_abs_notifications($aid, $trigger, $status_id = null){
	// sends author notifications
	$args = array( 
		'type' => 'abstract', 
		'trigger' => $trigger, 
		'receiver' => 'author',
		'status' => 1
	);
	if($status_id) {
        $args['status_id'] = $status_id;
	}
	$author_templates = wpabstracts_get_email_templates($args);
	if(!empty($author_templates)) {
		foreach ($author_templates as $template) {
			$abstract = wpabstracts_get_abstract($aid);
			$emailer = new WPAbstracts_Emailer($aid, $abstract->submit_by, $template->ID);
			$emailer->send();
		}
	}
	// sends admin notifications
	$args = array( 
		'type' => 'abstract', 
		'trigger' => $trigger, 
		'receiver' => 'admin',
		'status' => 1
	);
	if($status_id) {
        $args['status_id'] = $status_id;
	}
	$admin_templates = wpabstracts_get_email_templates($args);
	if(!empty($admin_templates)) {
		$super_admins = get_users( array('role'=>'administrator', 'fields'=>'ID') );
		foreach ($admin_templates as $template) {
			foreach ($super_admins as $super_admin_id) {
				$enabled = get_user_meta($super_admin_id, 'wpabstracts_enable_notification', true); 
				if($enabled){
					$emailer = new WPAbstracts_Emailer($aid, $super_admin_id, $template->ID);
					$emailer->send();
				}
			}
		}
	}
}

function wpabstracts_load_reviewers() {
	ob_start();
	echo '<div id=assignBtn>' .
	__('This and more reviewing features are available in the WPAbstracts Pro.', 'wpabstracts') . '<br><br> ' .
	'<a href="http://www.wpabstracts.com/pricing" target="_blank" class="button button-primary button-large">Get PRO Version</a>' .
	'</div>';
	$html = ob_get_contents();
	ob_end_clean();
	echo apply_filters('wpabstracts_get_reviewers', $html);
	die();
}

function wpabstracts_load_status() { 
	$statuses = wpabstracts_get_statuses();
	ob_start();
	$abs_ids = isset($_POST['abs_ids']) ? $_POST['abs_ids'] : array();
	$isBulk = isset($_POST['isBulk']) ? intval($_POST['isBulk']) : 0;
	$paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$currentStatus = ""; 
	if($isBulk){
		$abstract = wpabstracts_get_abstract($abs_ids[0]);
		$currentStatus = $abstract ? $abstract->status : "";
	}
	?>
	<div class="wpabstracts container-fluid">
		<form method="post" id="change_status_form" action="?page=wpabstracts&tab=abstracts&task=status&paged=<?php echo $paged;?>">
			<div class="wpabstracts modal-header">
				<div class="header"><?php echo apply_filters('wpabstracts_title_filter', __("Change Status", 'wpabstracts'), 'change_status');?></div>
			</div>
			<div class="wpabstracts modal-body" id="status_table">
				<select class="wpabstracts form-control" name="abs_status">
					<option value="" <?php selected("", $currentStatus);?>><?php _e('Select a status', 'wpabstracts'); ?></option>
					<?php foreach($statuses as $status) { ?>
						<option value="<?php echo $status->id;?>" <?php selected($status->id, $currentStatus);?>><?php echo $status->name;?></option>
					<?php } ?>
				</select>
				<?php foreach($abs_ids as $id) { ?>
					<input type="hidden" id="aid" name="abs_ids[]" value="<?php echo $id; ?>">
				<?php } ?>
			</div>
		</form>
	</div>
	<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo apply_filters('wpabstracts_load_status', $html);
	die();
}

function wpabstracts_change_status($status){
	global $wpdb;
	$wpdb->show_errors();
	if($_POST){
		$abs_ids = isset($_POST['abs_ids']) ? $_POST['abs_ids'] : array();
		$statusId = isset($_POST['abs_status']) ? intval($_POST['abs_status']) : null;
		$data = array('status' => $statusId);
		$updated = false;
		foreach($abs_ids as $abs_id) {
			$where = array('abstract_id' => $abs_id);
			$updated = $wpdb->update($wpdb->prefix."wpabstracts_abstracts", $data, $where);
			wpabstracts_send_abs_notifications($abs_id, 'status', $statusId);
		}
		if($updated){
			wpabstracts_show_message("Status was updated successfully.", 'alert-success');
		}
	}

}

function wpabstracts_delete_abstracts(){
	global $wpdb;
	$wpdb->show_errors();
	if($_POST){
		$abs_ids = isset($_POST['abs_ids']) ? $_POST['abs_ids'] : array();
		$isBulk = isset($_POST['isBulk']) ? intval($_POST['isBulk']) : 0;
		$paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
		foreach($abs_ids as $abs_id) {
			$wpdb->delete("{$wpdb->prefix}wpabstracts_abstracts", array( 'abstract_id' => $abs_id));
			$wpdb->delete("{$wpdb->prefix}wpabstracts_attachments", array( 'abstracts_id' => $abs_id));		
		}
		if(!$isBulk){
			wpabstracts_show_message("Abstract Id " . $abs_ids[0] . " was successfully deleted.", 'alert-success');
		}else{
			wpabstracts_show_message(__('The selected Abstracts were successfully deleted.', 'wpabstracts'), 'alert-success');
		}
	}
}

function wpabstracts_display_abstracts(){ ?>  
	<div class="wpabstracts container-fluid wpabstracts-admin-container">
		<h3><?php echo apply_filters('wpabstracts_title_filter', __('Abstracts','wpabstracts'), 'abstracts');?>  <a href="?page=wpabstracts&tab=abstracts&task=new" role="button" class="wpabstracts btn btn-primary"><?php _e('Add New', 'wpabstracts');?></a></h3>
	</div>
	<div class="wpabstracts-assign-modal"></div>
	<form id="showsAbstracts" name="abstracts_list" method="get">
		<input type="hidden" name="page" value="wpabstracts" />
		<input type="hidden" name="tab" value="abstracts" />
		<?php
			$abstracts = new WPAbstract_Abstracts_Table();
			$abstracts->prepare_items();
			$abstracts->display();
			$events = wpabstracts_get_events();
			$statuses = wpabstracts_get_statuses();
			$preferences = wpabstracts_get_preferences();
		?>
	</form>
	<script>
		
		jQuery(document).ready( function () {

			var abs_count = '<?php echo count($abstracts->items);?>';

			if(abs_count > 0) {
				var statuses = JSON.parse('<?php echo json_encode($statuses);?>');
				var preferences = JSON.parse('<?php echo json_encode($preferences);?>');
				var table = jQuery('.wp-list-table').DataTable({
					responsive: false,
					dom: 'Bfrltip',
					buttons: [],
					colReorder: false,
					lengthMenu: [ 25, 50, 100, 250, 500, 1000 ],
					columnDefs: [ { 
						type: 'natural', 
						targets: 'column-abs_id'
					}]
				});

				table.column('.column-event').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						jQuery('#wpa_topics').val('');
						column.search(jQuery(this).val()).draw();
					}).append(jQuery('<option value="">Filter by Event</option>')).attr('id', 'wpa_events').attr('name', 'wpa_events');
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});

				table.column('.column-topic').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by Topic</option>')).attr('id', 'wpa_topics').attr('name', 'wpa_topics');
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});

				table.column('.column-status').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by Status</option>')).attr('id', 'wpa_status').attr('name', 'wpa_status');
					statuses.forEach(status => {
						select.append( jQuery('<option value="'+status.name+'">'+status.name+'</option>'));
					});
				});

				table.column('.column-presenter_preference').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by Preference</option>')).attr('id', 'wpa_preference').attr('name', 'wpa_preference');
					preferences.forEach(preference => {
						select.append( jQuery('<option value="'+preference+'">'+preference+'</option>'));
					});
				});
			}
			
			jQuery('#doaction, #doaction2').on('click', function(event){
				event.preventDefault(); 
				var selected = jQuery('input[name="abstract\\[\\]"]:checked').map(function() {
					return jQuery(this).val();
				}).toArray();
				var action = -1;
				if(event.target.id == 'doaction'){
					action = jQuery('#bulk-action-selector-top').val();
				}else{
					action = jQuery('#bulk-action-selector-bottom').val();
				}
				switch(action){
					case 'assign_reviewer':
					wpabstracts_load_reviewers(selected, 1, 1);
					break;
					case 'change_status':
					wpabstracts_load_status(selected, 1, 1);
					break;
					case 'delete':
					wpabstracts_delete_abstracts(selected, 1, 1);
					break;
					default:
					jQuery("#showsAbstracts").submit();

				}
			});

		});
	</script>
	<?php
}
