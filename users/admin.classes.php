<?php

defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPAbstracts_Users extends WP_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'user', //singular name of the listed records
			'plural'    => 'users',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );

	}

	function column_cb($item) {
		return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->user_id);
	}

	function column_username($item){
		$user = get_userdata($item->user_id);
		$paged = ($_POST && isset($_POST["paged"])) ? intval($_POST["paged"]) : (isset($_GET["paged"]) && !empty($_GET["paged"])) ? intval($_GET["paged"]) : 1; //send paged to ajax to maintain current paged
		$actions = array(
			'edit' => '<a href="?page=wpabstracts&tab=users&task=edit&id=' . $item->user_id . '">' . __('Edit Profile', 'wpabstracts') . '</a>',
			'activate' => '<a href="?page=wpabstracts&tab=users&task=activate&id=' . $item->user_id . '">' . __('Activate', 'wpabstracts') . '</a>',
			'reset' => '<a href=' . admin_url( "user-edit.php?user_id=" . $item->user_id ) . '>' . __('Change Password', 'wpabstracts') . '</a>',
			'delete' => '<a href="javascript:wpabstracts_delete_user(' . $item->user_id . ', ' . $paged .')">' . __('Delete', 'wpabstracts') . '</a>',
		);
		return sprintf('%1$s %2$s', $user->user_login, $this->row_actions($actions));
	}

	function column_default( $item, $column_name ) {
		$user_data = unserialize($item->data);
		$settings = get_option('wpabstracts_user_settings');
		$admin_columns = $settings->admin_columns;
		$columns = array('cb' => '<input type="checkbox" />', 'user_id' => 'User ID');

		if($admin_columns){
			foreach($admin_columns as $cid => $label){
				$columns[$cid] = $label;
			}
		}

		switch($column_name){
			case 'status':
				$icon = $item->status ? 'dashicons-thumbs-up' : 'dashicons-thumbs-down';
				echo '<i class="dashicons '.$icon.'"></i>';
			break;
			case 'user_role':
			$roles = unserialize($item->user_role);
			foreach ($roles as $key => $value) {
				echo ucfirst($key);
				break;
			}
			break;
			default :
			if(is_array($user_data) && array_key_exists($column_name, $user_data)){
				if(!is_array($user_data[$column_name])) {
					echo stripslashes($user_data[$column_name]);
				}else {
					echo $user_data[$column_name];
				}
			}else{
				_e('--not entered--','wpabstracts');
			}
			break;
		}
	}

	function form_field_exists($fields, $name) {
		foreach($fields as $field) {
			if($field->type !== 'header' && $field->type !== 'paragraph' && $field->name == $name) {
				return true;
			}
		}
		return false;
	}

	function get_columns(){
		
		$settings = get_option('wpabstracts_user_settings');	
		$admin_columns = $settings->admin_columns;
		$form_fields = json_decode(get_option('wpabstracts_registration_form'));
		$columns = array('cb' => '<input type="checkbox" />', 'username' => 'Username');

		if($admin_columns){
			foreach($admin_columns as $name => $label){
				if($this->form_field_exists($form_fields, $name)){ // check if field still exist in saved form
					$columns[$name] = $label;
				}
			}
		}
		$columns['user_role'] = 'User Roles';
		$columns['status'] = 'Activation';
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'activate' => __('Activate', 'wpabstracts'),
			'delete' => __('Delete', 'wpabstracts'),
		);
		return $actions;
	}

	function process_bulk_action() {
		if ( 'activate'=== $this->current_action() ) {
			foreach($_GET['user'] as $user) {
				wpabstracts_activate_user($user, false);
			}
			$count = count($_GET['user']);
			$desc = $count > 1 ? __("users were ", "wpabstracts") : __("user was ", "wpabstracts"); 
			wpabstracts_show_message($count  . ' ' . $desc . __(' successfully activated.', 'wpabstracts'), 'alert-success');
		}
		
		if ( 'delete'=== $this->current_action() ) {
			foreach($_GET['user'] as $user) {
				wpabstracts_user_delete($user, false);
			}
			$count = count($_GET['user']);
			$desc = $count > 1 ? "users" : "user"; 
			wpabstracts_show_message($count  . ' ' . $desc . __(' successfully deleted.', 'wpabstracts'), 'alert-success');
		}
	}

	function get_status_filter(){
		$current = (isset($_GET['status_filter']) && $_GET['status_filter']) ? $_GET['status_filter'] : ""; ?>
		<select name="status_filter" onchange="showUsers.submit();">
			<option value="" <?php selected($current , ""); ?>>All Users</option>
			<option value="subscriber" <?php selected($current , "subscriber"); ?>>Authors</option>
			<option value="editor" <?php selected($current , "editor"); ?>>Reviewers</option>
			<option value="administrator" <?php selected($current , "administrator"); ?>>Admins</option>
		</select>
		<?php
	}

	function get_search_box() { ?>
		<input type="search" class="wpabstracts form-control" id="application_search" placeholder="Username or Email" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( __('Search', 'wpabstracts'), 'secondary', false, false ); ?>
		<?php
	}

	function get_export_btn() { ?>
		<span class="wpabstracts wpabstracts-admin-container">
			<a href="?page=wpabstracts&tab=users&task=download&type=users" class="wpabstracts btn btn-primary">
				<?php _e('Download CSV', 'wpabstracts');?>
				<i class="wpabstracts glyphicon glyphicon-cloud-download"></i> 
			</a>
		</span>
		<?php
	}

	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			$this->get_status_filter();
			//$this->get_search_box();
			$this->get_export_btn();
		}
	}

	function sync_message(){?>
		<div class="wpabstracts row">
			<br>
			<div class="wpabstracts alert alert-info" role="alert">
				<label><?php _e('You have WordPress users with no WP Abstracts profiles, do you want to sync users? ', 'wpabstracts'); ?></label>
				<a href="?page=wpabstracts&tab=users&task=sync" class="wpabstracts btn btn-primary"><?php _e('Sync Users', 'wpabstracts'); ?></a>
			</div>
		</div>
		<?php
	}

	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		$wpausers = $wpdb->prefix."wpabstracts_users";
		$wpusers = $wpdb->base_prefix."users";
		$wpusermeta = $wpdb->base_prefix."usermeta";
		$query = "SELECT wpausers.*, wpusers.ID as wpid, wpusers.user_email, wpusers.user_login, wpusermeta.meta_value as user_role FROM $wpausers AS wpausers " .
		"LEFT JOIN $wpusers AS wpusers ON wpausers.user_id = wpusers.ID " .
		"LEFT JOIN $wpusermeta AS wpusermeta ON wpusers.ID = wpusermeta.user_id WHERE wpusermeta.meta_key = '" . $wpdb->base_prefix . "capabilities'";
		if(isset($_GET['s']) && $_GET['s']){
			$search = trim(sanitize_text_field($_GET['s']));
			$query .= " and ($wpusers.user_login LIKE '%" . $search . "%' OR $wpusers.user_email LIKE '%" . $search . "%')";
		}
		if(isset($_GET['status_filter']) && $_GET['status_filter']){
			$status_filter = trim(sanitize_text_field($_GET['status_filter']));
			$query .= " and wpusermeta.meta_value LIKE '%" . $status_filter . "%'";
		}
		$orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'wpausers.user_id';
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
