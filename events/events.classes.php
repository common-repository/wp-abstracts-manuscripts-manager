<?php

defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPAbstract_Events_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'event', //singular name of the listed records
            'plural' => 'events', //plural name of the listed records
            'ajax' => false, //does this table support ajax?
        ));

    }

    function column_name($item) {
        $actions = array(
            'edit' => '<a href="?page=wpabstracts&tab=events&task=edit&id=' . $item->event_id . '">' . __('Edit', 'wpabstracts') . '</a>',
            'delete' => '<a href="javascript:wpabstracts_delete_event(' . $item->event_id . ',`' . wp_create_nonce("delete-event-" . $item->event_id) . '`);">' . __('Delete', 'wpabstracts') . '</a>',
        );
        if ($item->status == null || $item->status == '1') {
            $actions['archive'] = '<a href="?page=wpabstracts&tab=events&task=archive&id=' . $item->event_id . '">' . __('Archive', 'wpabstracts') . '</a>';
        }
        if ($item->status == '-1') {
            $actions['unarchive'] = '<a href="?page=wpabstracts&tab=events&task=unarchive&id=' . $item->event_id . '">' . __('Unarchive', 'wpabstracts') . '</a>';
        }
        return sprintf('%1$s <span style="color:silver">[ID: %2$s]</span>%3$s', $item->name, $item->event_id, $this->row_actions($actions));
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->event_id);
    }

    function column_shortcode($item) {
        $shortcode = "[wpabstracts event_id=" . $item->event_id . "]";
        $actions = array(
            'copy' => '<a href="javascript:wpabstracts_copy_to_clipboard(\'' . $shortcode . '\')">' . __('Copy', 'wpabstracts') . '</a>',
        );
        return sprintf($shortcode . ' %1$s', $this->row_actions($actions));
    }

    function column_count($item) {
        global $wpdb;
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpabstracts_abstracts WHERE `event` = " . $item->event_id);
        echo apply_filters('wpabstracts_event_submissions', $count, $item);
    }

    function column_topics($item) {
        $topics = wpabstracts_get_topics($item->event_id);
        $topic_list = null;
        if(count($topics) > 0) {
            foreach ($topics AS $topic) {
                $topic_list .= "<span class=\"reviewerList\">" . $topic->name . "</span>";
            }
            echo apply_filters('wpabstracts_event_topics', $topic_list);
        } else {
            echo '<a href="?page=wpabstracts&tab=events&subtab=topics&task=new">' . __('Create Topic', 'wpabstracts') . '</a>';
        }
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
        case 'host':echo stripslashes($item->$column_name);
            break;
        case 'start_date':echo stripslashes($item->$column_name);
            break;
        case 'end_date':echo stripslashes($item->$column_name);
            break;
        case 'deadline':echo stripslashes($item->$column_name);
            break;
        }
    }

    function column_status($item) {
        switch ($item->status) {
        case '1':
            $status = 'Active';
            break;
        case '-1':
            $status = 'Archived';
            break;
        default:
            $status = 'Active';
        }
        echo apply_filters('wpabstracts_event_status', $status, $item);
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
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
            'unarchive' => __('Unarchive', 'wpabstracts'),
        );
        return apply_filters('wpabstracts_bulk_actions', $actions, 'events'); // actions and tab
    }

    function process_bulk_action() {
        if (isset($_GET['event']) && $_GET['event']) {
            do_action('wpabstracts_bulk_actions', $this->current_action(), 'events'); // action and tab
            if ('archive' === $this->current_action()) {
                foreach ($_GET['event'] as $event_id) {
                    wpabstracts_event_status($event_id, '-1', false);
                }
                $event = count($_GET['event']) > 1 ? 'events were' : 'event was';
                wpabstracts_show_message("Your $event was successfully archived.", 'alert-success');
            }
            if ('unarchive' === $this->current_action()) {
                foreach ($_GET['event'] as $event_id) {
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
        $query = "SELECT * FROM " . $events_tbl;
        $orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'event_id';
        $order = !empty($_GET["order"]) ? sanitize_text_field($_GET["order"]) : 'desc';
        if (!empty($orderby) & !empty($order)) {$query .= ' ORDER BY ' . $orderby . ' ' . $order;}
        $this->process_bulk_action();
        $this->items = $wpdb->get_results($query);
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id] = $columns;
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
    }

} 

class WPAbstracts_Event_Topics extends WP_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'topic', //singular name of the listed records
			'plural'    => 'topics',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );

	}

	function column_cb($item) {
		return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->topic_id);
	}

	function column_name($item){
		$paged = ($_POST && isset($_POST["paged"]) && intval($_POST["paged"]) > 0) ? intval($_POST["paged"]) : 1; //send paged to ajax to maintain current paged
		$actions = array(
			'edit' => '<a href="?page=wpabstracts&tab=events&subtab=topics&task=edit&id=' . $item->topic_id . '">' . __('Edit', 'wpabstracts') . '</a>',
			'delete' => '<a href="javascript:wpabstracts_delete_topic(' . $item->topic_id . ')">' . __('Delete', 'wpabstracts') . '</a>'
		);
		return sprintf('%1$s %2$s', $item->name, $this->row_actions($actions));
	}

	function column_default($item, $column_name) {
		switch($column_name){
			case 'event_name':
				echo $item->event_name;
				break;
		} 
	}

	function get_columns(){
		$columns = array(
			'cb' => '<input type="checkbox" />', 
			'name' => 'Name',
			'event_name' => 'Event'
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __('Delete', 'wpabstracts')
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'delete'=== $this->current_action() ) {
			foreach($_GET['topic'] as $topic) {
				wpabstracts_delete_topic($topic, false);
			}
			$count = count($_GET['topic']);
			$desc = $count > 1 ? "topics" : "topic"; 
			wpabstracts_show_message(__($count  . ' ' . $desc . ' were successfully deleted.', 'wpabstracts'), 'alert-success');
		}
	}

	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		$wpa_topics = $wpdb->prefix."wpabstracts_topics";
		$wpa_events = $wpdb->base_prefix."wpabstracts_events";
		$query = "SELECT wpa_topics.*, wpa_events.name as event_name ";
		$query .= "FROM $wpa_topics AS wpa_topics ";
		$query .= "LEFT JOIN $wpa_events AS wpa_events ON wpa_events.event_id = wpa_topics.event_id ";
		$orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'wpa_topics.topic_id';
		$order = !empty($_GET["order"]) ? sanitize_text_field($_GET["order"]) : 'desc';
		if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
		$this->process_bulk_action();
		$this->items = $wpdb->get_results($query);
		$columns = $this->get_columns();
		$_wp_column_headers[$screen->id]=$columns;
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

}