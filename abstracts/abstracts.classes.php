<?php

defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPAbstract_Abstracts_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'abstract', //singular name of the listed records
            'plural' => 'abstracts', //plural name of the listed records
            'ajax' => false, //does this table support ajax?
        ));
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->abstract_id);
    }

    function column_abs_id($item) {
        echo apply_filters('wpabstracts_abstracts_column', $item->abstract_id, $item, 'abs_id'); // column value, row item and column name
    }

    function column_title($item) {
        global $wpdb;
        $paged = ($_POST && isset($_POST["paged"]) && intval($_POST["paged"]) > 0) ? intval($_POST["paged"]) : 1; //send paged to ajax to maintain current paged
        $statuses = wpabstracts_get_statuses();
        $prefilter_actions = array(
            'view' => '<a href="?page=wpabstracts&tab=abstracts&task=view&id=' . $item->abstract_id . '">' . __('View', 'wpabstracts') . '</a>',
            'edit' => '<a href="?page=wpabstracts&tab=abstracts&task=edit&id=' . $item->abstract_id . '">' . __('Edit', 'wpabstracts') . '</a>',
            'change' => '<a href="javascript:wpabstracts_load_status(' . $item->abstract_id . ', ' . $paged . ', 0);">' . __('Change Status', 'wpabstracts') . '</a>',
            'assign' => '<a href="javascript:wpabstracts_load_reviewers(' . $item->abstract_id . ', ' . $paged . ', 0);">' . __('Assign Reviewer', 'wpabstracts') . '</a>',
            'reviews' => '<a href="?page=wpabstracts&tab=reviews&task=view&id=' . $item->abstract_id . '";>' . __('Reviews', 'wpabstracts') . '</a>',
            'pdf' => '<a href="?page=wpabstracts&tab=abstracts&task=download&type=pdf&id=' . $item->abstract_id . '">' . __('Export PDF', 'wpabstracts') . '</a>',
            'delete' => '<a href="javascript:wpabstracts_delete_abstracts(' . $item->abstract_id . ', ' . $paged . ', 0)">' . __('Delete', 'wpabstracts') . '</a>',
        );
        $actions = apply_filters('wpabstracts_abstract_actions', $prefilter_actions, $item);
        return sprintf('%1$s%2$s', stripslashes($item->title), $this->row_actions($actions));
    }

    function column_event($item) {
        if ($item && array_key_exists('name', (array) $item)) {
            echo apply_filters('wpabstracts_abstracts_column', $item->name, $item, 'event'); // column value, row item and column name
        } else {
            _e('Event Not Found', 'wpabstracts');
        }
    }

    function column_topic($item) {
        $topic = wpabstracts_get_topic($item->topic_id);
        if ($topic) {
            echo apply_filters('wpabstracts_abstracts_column', $topic->name, $item, 'topic'); // column value, row item and column name
        } else {
            _e('Topic Not Found', 'wpabstracts');
        }
    }

    function column_author($item) {
        echo apply_filters('wpabstracts_abstracts_column', $item->author, $item, 'author'); // column value, row item and column name
    }

    function column_preference($item) {
        echo apply_filters('wpabstracts_abstracts_column', $item->presenter_preference, $item, 'presenter_preference'); // column value, row item and column name
    }

    function column_status($item) {
        $statuses = wpabstracts_get_statuses();
        $status = sprintf('%1$s', wpabstracts_map_status_name($statuses, $item->abs_status));
        echo apply_filters('wpabstracts_abstracts_column', $status, $item, 'status'); // column value, row item and column name
    }

    function column_reviewers($item) {
        $reviewers = [];
        $reviewer_list = null;
        foreach ($reviewers AS $reviewer) {
            if ($reviewer) {
                $reviewer_list .= "<span class=\"reviewerList\">" . $reviewer->display_name . "</span>";
            }
        }
        $current_reviewers = (empty($reviewer_list)) ? __("Not Assigned", 'wpabstracts') : $reviewer_list;
        $filtered_reviewers = apply_filters('wpabstracts_abstracts_reviewers', $current_reviewers, $item); // for backward compatibilty and customization who use `wpabstracts_abstracts_reviewers`
        echo apply_filters('wpabstracts_abstracts_column', $current_reviewers, $item, 'reviewers'); // column value, row item and column name
    }

    function column_date_submitted($item) {
        $date = '---';
        if ($item->submit_date) {
            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->submit_date));
        }
        echo apply_filters('wpabstracts_abstracts_column', $date, $item, 'date_submitted'); // column value, row item and column name
    }

    function column_date_modified($item) {
        $date = '---';
        if ($item->modified_date) {
            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->modified_date));
        }
        echo apply_filters('wpabstracts_abstracts_column', $date, $item, 'date_modified'); // column value, row item and column name
    }

    function column_submit_by($item) {
        $user = get_user_by('email', $item->user_email);
        $submit_by = "---";
        if ($user) {
            if ($item->display_name) {
                $submit_by = $item->display_name . " (" . $item->user_email . ")";
            } else {
                $submit_by = $item->user_login . " (" . $item->user_email . ")";
            }
        } else {
            $submit_by = __('User not found', 'wpabstracts');
        }
        echo apply_filters('wpabstracts_abstracts_column', $submit_by, $item, 'submit_by'); // column value, row item and column name
    }

    function column_keywords($item) {
        echo apply_filters('wpabstracts_abstracts_column', $item->keywords, $item, 'keywords'); // column value, row item and column name
    }

    function column_attachments($item) {
        global $wpdb;
        $attachments_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpabstracts_attachments WHERE `abstracts_id` = " . $item->abstract_id);
        echo apply_filters('wpabstracts_abstracts_column', $attachments_count, $item, 'attachments'); // column value, row item and column name
    }

    function get_columns() {
        $admin_columns = get_option('wpabstracts_abstracts_columns');
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'abs_id' => __('ID', 'wpabstracts'),
            'title' => __('Title', 'wpabstracts'),
        );
        if ($admin_columns) {
            foreach ($admin_columns as $key => $column) {
                if ($column['enabled']) {
                    $columns[$key] = $key != 'attachments' ? __($column['label'], 'wpabstracts') : '';
                }
            }
        }
        return apply_filters('wpabstacts_abstracts_columns', $columns);
    }

    function get_sortable_columns() {
        $sortable_columns = array();
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array();
        $actions['assign_reviewer'] = __('Assign Reviewer', 'wpabstracts');
        $actions['change_status'] = __('Change Status', 'wpabstracts');
        $actions['generate_book'] = __('Generate Book', 'wpabstracts');
        $actions['delete'] = __('Delete', 'wpabstracts');
        return apply_filters('wpabstracts_bulk_actions', $actions, 'abstracts'); // actions and tab
    }

    function process_bulk_action() {
        do_action('wpabstracts_bulk_action', $this->current_action(), 'abstracts'); // action and tab
    }

    function get_status_filter() {
        $current = (isset($_GET['status_filter']) && $_GET['status_filter']) ? $_GET['status_filter'] : "";
        $statuses = wpabstracts_get_statuses();?>
		<label><?php _e('Filter by Status', 'wpabstracts');?></label>
		<select name="status_filter" onchange="abstracts_list.submit();">
			<option value="" <?php selected($current, "");?>><?php _e('All', 'wpabstracts');?></option>
			<?php
		foreach ($statuses as $status) {?>
			<option value="<?php echo $status->id; ?>" <?php selected($current, $status->id);?>><?php echo $status->name; ?>
			</option>
			<?php }?>
		</select>
		<?php
	}

    function get_preference_filter() {
        $current = (isset($_GET['preference_filter']) && $_GET['preference_filter']) ? $_GET['preference_filter'] : "";
        $preferences = explode(',', get_option('wpabstracts_presenter_preference'));
        ?>
		<label><?php _e('Filter by Preference', 'wpabstracts');?></label>
		<select name="preference_filter" onchange="abstracts_list.submit();">
			<option value="" <?php selected($current, "");?>><?php _e('All', 'wpabstracts');?></option>
			<?php foreach ($preferences as $preference) {?>
			<option value="<?php echo $preference; ?>" <?php selected($current, $preference);?>><?php echo $preference; ?>
			</option>
			<?php }?>
		</select> 
		<?php
	}

    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
        $abs_tbl = $wpdb->prefix . "wpabstracts_abstracts";
        $events_tbl = $wpdb->prefix . "wpabstracts_events";
        $users_tbl = $wpdb->base_prefix . "users";
        $query = "SELECT abs.abstract_id, abs.title, abs.event, abs.topic_id, abs.author, abs.presenter_preference, abs.status as abs_status, abs.submit_date, abs.modified_date, abs.submit_by, abs.keywords, event.event_id, event.name, users.display_name, users.user_login, users.user_email ";
        $query .= "FROM " . $abs_tbl . " as abs ";
        $query .= "LEFT JOIN " . $users_tbl . " as users ";
        $query .= "ON abs.submit_by = users.ID ";
        $query .= "LEFT JOIN " . $events_tbl . " as event ";
        $query .= "ON abs.event = event.event_id WHERE event.status = 1";
        $searched = false;
        $statusFiltered = false;
        $preferenceFiltered = false;
        if (isset($_GET['s']) && $_GET['s']) {
            $term = trim(sanitize_text_field($_GET['s']));
            $query .= " WHERE (abs.abstract_id LIKE '%$term%' OR abs.title LIKE '%$term%' OR abs.topic LIKE '%$term%' OR abs.author LIKE '%$term%' OR users.user_login LIKE '%$term%' OR users.display_name LIKE '%$term%')";
            $searched = true;
        }
        if (isset($_GET['status_filter']) && $_GET['status_filter']) {
            $status_filter = trim(sanitize_text_field($_GET['status_filter']));
            $statusFiltered = true;
            if ($searched || $preferenceFiltered) {
                $query .= " AND status = " . $status_filter;
            } else {
                $query .= " WHERE abs.status = " . $status_filter;
            }
        }
        if (isset($_GET['preference_filter']) && $_GET['preference_filter']) {
            $preference_filter = trim(sanitize_text_field($_GET['preference_filter']));
            if ($searched || $statusFiltered) {
                $query .= " AND abs.presenter_preference = '$preference_filter'";
            } else {
                $query .= " WHERE abs.presenter_preference = '$preference_filter'";
            }
        }
        $orderby = !empty($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'abs.abstract_id';
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