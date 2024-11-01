<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(!class_exists('WPAbstracts_Users')){
	require_once( WPABSTRACTS_PLUGIN_DIR . 'users/users.classes.php' );
}

if(is_admin() && isset($_GET['tab']) && ($_GET["tab"]=="users")){
	$synced = wpabstracts_user_check();
	if(isset($_GET['task'])){
		$task = sanitize_text_field($_GET['task']);
		$id = isset($_GET['id']) ? intval($_GET['id']) : null;

		switch($task){
			case 'sync':
			wpabstracts_user_sync();
			wpabstracts_user_display(true);
			break;
			case 'add':
			$html = wpabstracts_user_getview("register", false);
			echo $html;
			break;
			case 'edit':
			$html = wpabstracts_user_getview("profile", $id);
			echo $html;
			break;
			case 'activate':
			wpabstracts_activate_user($id, $message = true); 
			wpabstracts_user_display($synced);
			break;
			case 'delete':
			wpabstracts_user_delete($id, $message = true);
			default :
			if(has_action('wpabstracts_page_render')){
				do_action('wpabstracts_page_render');
			}else{
				wpabstracts_user_display($synced);
			}
			break;
		}
	}else{
		wpabstracts_user_display($synced);
	}
}

function wpabstracts_user_check(){
	global $wpdb;
	$wpusers = get_users(array('fields' => 'ID'));
	$wpausers = $wpdb->get_col('SELECT user_id FROM '. $wpdb->prefix."wpabstracts_users");
	$strays = array_diff($wpusers, $wpausers);
	return (count($strays) == 0);
}

function wpabstracts_user_display($synced){ ?>

	<form id="showUsers" method="get">
		<input type="hidden" name="page" value="wpabstracts" />
		<input type="hidden" name="tab" value="users" />
		<?php
			$users = new WPAbstracts_Users();
			if(!$synced){
				$users->sync_message();
			}
			$users->prepare_items();
			$users->display();
		?>
	</form>
	<script>
		jQuery(document).ready( function () {
			var user_count = '<?php echo count($users->items);?>';
			if(user_count > 0) {
				var table = jQuery('.wp-list-table').DataTable({
					responsive: false,
					dom: 'Bfrltip',
					buttons: [],
					colReorder: false
				});

				// filter by user role
				table.column('.column-user_role').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by User Role</option>'));
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});

				// Filter by File Type
				table.column('.column-gender').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by Gender</option>'));
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});
			}
		});
	</script>
	<?php
}
