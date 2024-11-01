<?php

defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPAbstracts_Emails extends WP_List_Table {

    function __construct(){
        global $status, $page;

        parent::__construct( array(
            'singular' => 'template', //singular name of the listed records
            'plural'    => 'templates',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function column_name($item){
        global $wpdb;
        $actions = array(
            'edit' => '<a href="?page=wpabstracts&tab=emails&subtab=templates&task=edit&id=' . $item->ID . '">' . __('Edit', 'wpabstracts') . '</a>',
            'delete' => '<a href="javascript:wpabstracts_delete_template(' . $item->ID . ', `abstracts`);">' . __('Delete', 'wpabstracts') . '</a>'
        );
        return sprintf('%1$s<span style="color:silver"> [%2$s]</span>%3$s', $item->name, $item->ID, $this->row_actions($actions));
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID);
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id': echo $item->$column_name; break;
            case 'name': _e($item->$column_name, 'wpabstracts');break;
            case 'subject': _e($item->$column_name, 'wpabstracts');break;
            case 'from_name': _e($item->$column_name, 'wpabstracts');break;
			case 'from_email': _e($item->$column_name, 'wpabstracts');break;
			case 'trigger': 
				switch($item->$column_name) {
					case 'submission': _e('Submission', 'wpabstracts'); break;
					case 'revision': _e('Revision', 'wpabstracts'); break;
					case 'status': _e('Status', 'wpabstracts'); break;
				}
				break;
			case 'receiver': 
				switch($item->$column_name) {
					case 'author': _e('Author', 'wpabstracts'); break;
					case 'reviewer': _e('Reviewer', 'wpabstracts'); break;
					case 'admin': _e('Admin', 'wpabstracts'); break;
				}
				break;
			case 'status': _e($item->$column_name ? "Yes" : "No", 'wpabstracts');break;
        }
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'name' => __('Email Template', 'wpabstracts'),
            'subject' => __('Subject', 'wpabstracts'),
            'from_name' => __('From Name', 'wpabstracts'),
            'from_email' => __('From Email', 'wpabstracts'),
            'trigger' => __('Trigger', 'wpabstracts'),
            'receiver' => __('Receiver', 'wpabstracts'),
            'status' => __('Active', 'wpabstracts')
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
        return apply_filters('wpabstracts_bulk_actions', $actions, 'emailtemplates'); // actions and tab
    }

    function process_bulk_action() {
        if(isset($_GET['template']) && $_GET['template']) {
            do_action('wpabstracts_bulk_actions', $this->current_action(), 'emailtemplates'); // action and tab 
            if ( 'delete'=== $this->current_action() ) {
                foreach($_GET['template'] as $event) {
                    wpabstracts_delete_email_template($event, false);
                }
                wpabstracts_show_message("Successfully deleted selected templates.", 'alert-success');
            }
        }
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
        $templates_tbl = $wpdb->prefix . "wpabstracts_emailtemplates";
        $query = $wpdb->prepare(
            "SELECT * FROM {$templates_tbl} WHERE `type` = %s",
            'abstract'
        );
        $orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'ID';
        $order = !empty($_GET["order"]) ? sanitize_text_field($_GET["order"]) : 'desc';
        if (!empty($orderby) && !empty($order)) {
            $query .= $wpdb->prepare(
                " ORDER BY %s %s",
                $orderby,
                $order
            );
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

class WPAbstracts_MailLog extends WP_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'maillog', //singular name of the listed records
			'plural'    => 'maillogs',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );

	}

	function column_cb($item) {
		return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->id);
	}

	function column_default( $item, $column_name ) {
		echo stripslashes($item->$column_name);
	}

	function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'to' => __('Recipient', 'wpabstracts'),
            'subject' => __('Subject', 'wpabstracts'),
            'body' => __('Message', 'wpabstracts'),
            'created' => __('Sent', 'wpabstracts')
        );
        return $columns;
    }

	function column_body($item) {
		echo '<a href="javascript:wpabstracts_load_maillog(' . $item->id . ')">' . __('View Email', 'wpabstracts') . '</a>';
	}

	function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __('Delete', 'wpabstracts'),
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'delete'=== $this->current_action() ) {
			foreach($_GET['maillog'] as $logId) {
				wpabstracts_delete_maillog($logId);
			}
			wpabstracts_show_message(__('Successfully deleted selected logs.', 'wpabstracts'), 'alert-success');
		}
	}

	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		$log_tbl = $wpdb->prefix."wpabstracts_maillog";
		$query = "SELECT * FROM $log_tbl AS logs ORDER BY created desc";
		$this->process_bulk_action();
		$this->items = $wpdb->get_results($query);
		$columns = $this->get_columns();
		$_wp_column_headers[$screen->id]=$columns;
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

}
