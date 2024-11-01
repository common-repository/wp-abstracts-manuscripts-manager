<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(!class_exists('WPAbstract_Abstracts_Table')){
	require_once(apply_filters('wpabstracts_page_include', WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_classes.php'));
}

if(is_admin() && isset($_GET['tab']) && $_GET["tab"] == "attachments"){
	if(isset($_GET["task"]) && $_GET["task"]){

		$task = sanitize_text_field($_GET["task"]);

		switch($task){
			case 'delete':
			if(current_user_can(WPABSTRACTS_ACCESS_LEVEL)){
				wpabstracts_delete_attachment(intval($_GET['id']), true );
			}
			default :
			if(has_action('wpabstracts_page_render')){
				do_action('wpabstracts_page_render');
			}else{
				wpabstracts_show_attachments();
			}
		}
	}else{
		wpabstracts_show_attachments();
	}
}

function wpabstracts_show_attachments(){ ?>
	<div class="wpabstracts container-fluid wpabstracts-admin-container">
		<h3><?php echo apply_filters('wpabstracts_title_filter', __('Attachments','wpabstracts'), 'abstracts');?></h3>
	</div>
	<form id="showAttachments" method="get">
		<input type="hidden" name="page" value="wpabstracts" />
		<input type="hidden" name="tab" value="attachments" />
		<?php
			$attachments = new WPAbstract_Attachments_Table();
			$attachments->prepare_items();
			$attachments->display();
		?>
	</form>
	<script>
		jQuery(document).ready( function () {

			var atts_count = '<?php echo count($attachments->items);?>';

			if(atts_count > 0) {

				var table = jQuery('.wp-list-table').DataTable({
					responsive: false,
					dom: 'Bfrltip',
					buttons: [],
					colReorder: false
				});

				// event filter
				table.column('.column-event').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						jQuery('#wpa_topics').val('');
						column.search(jQuery(this).val()).draw();
					}).append(jQuery('<option value="">Filter by Event</option>')).attr('id', 'wpa_events');
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});

				// topic filter
				table.column('.column-topic').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by Topic</option>')).attr('id', 'wpa_topics');
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});

				// Filter by File Type
				table.column('.column-filetype').every( function () {
					var column = this;
					var select = jQuery('<select />').appendTo(jQuery('.dt-buttons')).on( 'change', function () {
						column.search( jQuery(this).val() ).draw();
					}).append(jQuery('<option value="">Filter by File Type</option>')).attr('id', 'wpa_filetype');
					column.data('search').sort().unique().each(function (val) {
						select.append( jQuery('<option value="'+val+'">'+val+'</option>'));
					});
				});
			}
			
		});
	</script>
	<?php
}
