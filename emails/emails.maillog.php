<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if(!class_exists('WPAbstracts_MailLog')){
	require_once( WPABSTRACTS_PLUGIN_DIR . 'emails/emails.classes.php' );
}

if(is_admin() && isset($_GET['tab']) && ($_GET["tab"]=="emails")){
	wpabstracts_maillog_display();
}

function wpabstracts_maillog_display(){ ?>

	<form id="maillog" method="get">
		<input type="hidden" name="page" value="wpabstracts" />
		<input type="hidden" name="tab" value="emails" />
		<input type="hidden" name="subtab" value="maillog" />
		<?php
			$log = new WPAbstracts_MailLog();
			$log->prepare_items();
			$log->display();
		?>
	</form>
	<script>
		jQuery(document).ready( function () {
			var count = '<?php echo count($log->items);?>';
			if(count > 0) {
				var table = jQuery('.wp-list-table').DataTable({
					responsive: false,
					dom: 'Bfrltip',
					buttons: [],
					colReorder: false
				});
			}
		});
	</script>
	<?php
}
