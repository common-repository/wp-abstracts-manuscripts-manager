<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if (!class_exists('WPABSTRACTS')) {
	require_once( WPABSTRACTS_PLUGIN_DIR . 'inc/wpabstracts_functions.php' );
}

class WPAbstract_Attachments_Table extends WP_List_Table {

    function __construct(){

        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'attachment',     //singular name of the listed records
            'plural'    => 'attachments',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }

    function column_filename($item){
		$paged = ($_POST && isset($_POST["paged"]) && intval($_POST["paged"]) > 0) ? intval($_POST["paged"]) : 1; //send paged to ajax to maintain current paged
        $actions = array(
            'download' => '<a href="?page=wpabstracts&tab=attachments&task=download&type=attachment&id=' . $item->attachment_id . '" ">' . __('Download', 'wpabstracts') . '</a>',
            'delete' => '<a href="javascript:wpabstracts_delete_attachment(' . $item->attachment_id . ', ' . $paged . ');">' . __('Delete', 'wpabstracts') . '</a>'
        );
        return sprintf('%1$s <span style="color:silver"></span>%2$s',$item->filename. " [" . $item->attachment_id . "]", $this->row_actions($actions));
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->attachment_id);
    }

    function column_author( $item ) {
        $user = get_user_by( 'id', $item->submit_by );
        echo $user->display_name . " (" . $user->user_login . ")";
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'abstracts_id': echo $item->title . " [" . $item->abstract_id . "]"; break;
            case 'event': echo $item->event_name; break;
            case 'topic': echo $item->topic; break;
            case 'filetype': $filetype = wp_check_filetype($item->filename); echo $filetype['ext'];break;
            case 'filesize': echo number_format(($item->filesize/1048576), 2) . " MB"; break;
        }
    }

    function column_download($item) {
        return sprintf('<a href="?page=wpabstracts&tab=attachments&task=download&type=attachment&id=' . $item->attachment_id . '" "><span class="dashicons dashicons-download"></span></a>');
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'filename' => __('Attachment Name', 'wpabstracts'),
            'abstracts_id' => __('Abstract Title', 'wpabstracts'),
            'event' => __('Event', 'wpabstracts'),
            'topic' => __('Topic', 'wpabstracts'),
            'author' => __('Author', 'wpabstracts'),
            'filetype' => __('File Type', 'wpabstracts'),
            'filesize' => __('File Size', 'wpabstracts'),
            'download' => __('Download', 'wpabstracts')
        );
        return $columns;
    }

    /**
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array();
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'download' => __('Download', 'wpabstracts'),
            'delete' => __('Delete', 'wpabstracts')
        );
        return apply_filters('wpabstracts_bulk_actions', $actions, 'attachments'); // actions and tab
    }

    function process_bulk_action() {
        if(isset($_GET['attachment']) && $_GET['attachment']) {
            do_action('wpabstracts_bulk_actions', $this->current_action(), 'attachments'); // action and tab 
            if ('download'=== $this->current_action() ) {
                // bulk downloads are handled in the downloads.php file due to 'headers already sent' limitations 
            }
            else if ('delete'=== $this->current_action() ) {
                foreach($_GET['attachment'] as $attachment) {
                    wpabstracts_delete_attachment($attachment, false);
                }
            }
        }
    }

    function get_search_box() { ?>
        <label>Search Attachments</label>
        <input type="search" class="wpabstracts form-control" id="abstract_search" placeholder="title or file name" name="s" value="<?php _admin_search_query(); ?>" />
        <?php submit_button( __('Search', 'wpabstracts'), 'secondary', false, false ); ?>
    <?php
    }

    function get_export_btn() { ?>
		<span class="wpabstracts wpabstracts-admin-container">
			<a href="?page=wpabstracts&tab=attachments&task=download&type=zip" class="wpabstracts btn btn-primary">
				<?php _e('Download All', 'wpabstracts');?>
				<i class="wpabstracts glyphicon glyphicon-cloud-download"></i> 
			</a>
		</span>
		<?php
	}

    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            $this->get_export_btn();
            //$this->get_search_box();
        }
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;
	    $screen = get_current_screen();
        $attachments_tbl = $wpdb->prefix . "wpabstracts_attachments";
        $abstracts_tbl = $wpdb->prefix . "wpabstracts_abstracts";
        $events_tbl = $wpdb->prefix . "wpabstracts_events";
        
        $query = "SELECT atts.attachment_id, atts.abstracts_id, atts.filename, atts.filesize, abs.abstract_id, abs.title, abs.event, abs.topic, abs.submit_by, event.name AS event_name 
        FROM {$attachments_tbl} AS atts 
        LEFT JOIN {$abstracts_tbl} AS abs ON atts.abstracts_id = abs.abstract_id 
        LEFT JOIN {$events_tbl} AS event ON event.event_id = abs.event 
        WHERE event.status = %d";
        
        $query .= $wpdb->prepare(
            " ORDER BY %s %s",
            !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'attachment_id',
            !empty($_GET["order"]) ? sanitize_text_field($_GET["order"]) : 'desc'
        );
        
        $this->process_bulk_action();
        $this->items = $wpdb->get_results($wpdb->prepare($query, 1));
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
    } // end prepare items

}

class WPAbstract_Events_Table extends WP_List_Table {

    function __construct(){

        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'event',     //singular name of the listed records
            'plural'    => 'events',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }

    function column_name($item){
        $actions = array(
            'edit' => '<a href="?page=wpabstracts&tab=events&task=edit&id=' . $item->event_id . '">' . __('Edit', 'wpabstracts') . '</a>',
            'delete' => '<a href="javascript:wpabstracts_delete_event(' . $item->event_id . ',`' . wp_create_nonce("delete-event-".$item->event_id) . '`);">' . __('Delete', 'wpabstracts') . '</a>'
        );
        if($item->status == null || $item->status == '1') { 
            $actions['archive'] = '<a href="?page=wpabstracts&tab=events&task=archive&id=' . $item->event_id . '">' . __('Archive', 'wpabstracts') . '</a>';
        }
        if($item->status == '-1') {
            $actions['unarchive'] = '<a href="?page=wpabstracts&tab=events&task=unarchive&id=' . $item->event_id . '">' . __('Unarchive', 'wpabstracts') . '</a>';
        }
        return sprintf('%1$s <span style="color:silver">[ID: %2$s]</span>%3$s',$item->name, $item->event_id, $this->row_actions($actions));
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->event_id);
    }

    function column_shortcode($item){
        $shortcode = "[wpabstracts event_id=" . $item->event_id . "]";
        $actions = array(
            'copy' => '<a href="javascript:wpabstracts_copy_to_clipboard(\'' . $shortcode . '\')">' . __('Copy', 'wpabstracts') . '</a>'
        );
        return sprintf($shortcode . ' %1$s', $this->row_actions($actions));
    }

    function column_count($item){
        global $wpdb;
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}wpabstracts_abstracts WHERE `event` = %d",
                $item->event_id
            )
        );
        echo apply_filters('wpabstracts_event_submissions', $count, $item); 
    }

     function column_default( $item, $column_name ) {
        switch( $column_name ) {
          case 'host': echo stripslashes($item->$column_name); break;
          case 'topics': echo stripslashes($item->$column_name); break;
          case 'start_date': echo stripslashes($item->$column_name); break;
          case 'end_date': echo stripslashes($item->$column_name); break;
          case 'deadline': echo stripslashes($item->$column_name); break;
        }
    }

    function column_status($item){
        switch($item->status){
            case '1':
            $status = 'Active'; break;
            case '-1':
            $status = 'Archived'; break;
            default:
            $status = 'Active'; 
        }
        echo apply_filters('wpabstracts_event_status', $status, $item); 
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'name' => __('Event Name', 'wpabstracts'),
            'shortcode' => __('Shortcode', 'wpabstracts'),
            'status' => __('Status', 'wpabstracts'),
            'host' => __('Host', 'wpabstracts'),
            'topics' => __('Topics', 'wpabstracts'),
            'start_date' => __('Start Date', 'wpabstracts'),
            'end_date' => __('End Date', 'wpabstracts'),
            'deadline' => __('Deadline', 'wpabstracts'),
            'count' => __('Submissions', 'wpabstracts'),
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array();
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'archive' => __('Archive', 'wpabstracts'),
            'unarchive' => __('Unarchive', 'wpabstracts')
        );
        return apply_filters('wpabstracts_bulk_actions', $actions, 'events'); // actions and tab
    }

    function process_bulk_action() {
        if(isset($_GET['event']) && $_GET['event']){
            do_action('wpabstracts_bulk_actions', $this->current_action(), 'events'); // action and tab 
            if ('archive' === $this->current_action() ) {
                foreach($_GET['event'] as $event_id) {
                    wpabstracts_event_status($event_id, '-1', false);
                }
                $event = count($_GET['event']) > 1 ? 'events were' : 'event was';
                wpabstracts_show_message("Your $event was successfully archived.", 'alert-success');
            }
            if ('unarchive' === $this->current_action() ) {
                foreach($_GET['event'] as $event_id) {
                    wpabstracts_event_status($event_id, '1', false);
                }
                $event = count($_GET['event']) > 1 ? 'events were' : 'event was';
                wpabstracts_show_message("Your $event was successfully unarchived.", 'alert-success');
            }
        }
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
        $events_tbl = $wpdb->prefix . "wpabstracts_events";
        $query = "SELECT * FROM {$events_tbl}";
        $orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'event_id';
        $order = !empty($_GET["order"]) ? sanitize_text_field($_GET["order"]) : 'desc';
        if (!empty($orderby) && !empty($order)) {
            $query .= " ORDER BY {$orderby} {$order}";
        }
        $this->process_bulk_action();
        $this->items = $wpdb->get_results($query);
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
    } // end prepare items

} 
