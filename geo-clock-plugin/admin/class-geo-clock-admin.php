<?php
class Geo_Clock_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('wp_ajax_delete_selected_logs', array($this, 'ajax_delete_selected_logs'));
        add_action('admin_post_export_selected_users_timesheets', array($this, 'export_selected_users_timesheets'));
        add_action('admin_init', array($this, 'register_aesthetic_settings'));
      add_action('admin_post_geo_clock_process_leave', array(__CLASS__, 'process_leave_action'));
      add_action('admin_init', array($this, 'register_notification_settings'));
      add_action('wp_ajax_load_dashboard_section', array($this, 'load_dashboard_section'));
      add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
          add_action('wp_ajax_save_locations', array($this, 'save_locations'));
      add_action('wp_ajax_update_user_locations', array($this, 'update_user_locations'));
      add_action('admin_init', array($this, 'register_login_settings'));
      add_action('wp_ajax_update_user_pin', array($this, 'update_user_pin'));
      add_action('wp_ajax_update_user_rfid', array($this, 'update_user_rfid'));
      // Keep: This line is added as per the suggestion
        add_action('wp_ajax_ajax_update_user_pin', array($this, 'ajax_update_user_pin'));
        add_action('admin_post_create_geo_clock_user', array($this, 'create_user'));
        add_action('admin_post_assign_geo_clock_users', array($this, 'assign_geo_clock_users'));
    }

    public function enqueue_styles() {
    wp_enqueue_style($this->plugin_name, GEO_CLOCK_PLUGIN_URL . 'admin/css/geo-clock-admin.css', array(), $this->version . '.' . time(), 'all');
}

   public function enqueue_scripts($hook) {
    // Check if we're on a Geo Clock admin page
    if (strpos($hook, $this->plugin_name) !== false) {
      
      wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.9');
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), '4.6.9', true);
      wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js', array(), '2.29.1', true);
      // Add this line to enqueue the monthSelect plugin
        wp_enqueue_script('flatpickr-monthSelect-plugin', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js', array('flatpickr'), '4.6.9', true);
      
        // Enqueue Select2 CSS
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
        
        // Enqueue Select2 JS
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
        
        // Enqueue our plugin's CSS
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/geo-clock-admin.css', array('select2'), $this->version . '.' . time());
        
        // Enqueue our plugin's JS
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/geo-clock-admin.js', array('jquery', 'select2'), $this->version . '.' . time(), true);
        
        // Localize the script with new data
        wp_localize_script($this->plugin_name, 'geo_clock_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('geo-clock-admin-nonce'),
        ));
    }
}
  
  // Add this method to the Geo_Clock_Admin class
public function update_user_rfid() {
    check_ajax_referer('geo-clock-admin-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        return;
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $rfid = isset($_POST['rfid']) ? sanitize_text_field($_POST['rfid']) : '';

    if (!$user_id || !$rfid) {
        wp_send_json_error(array('message' => 'Invalid user ID or RFID.'));
        return;
    }

    // Allow both 7 and 10 digit RFIDs
    if (strlen($rfid) != 7 && strlen($rfid) != 10) {
        wp_send_json_error(array('message' => 'RFID must be either 7 or 10 digits.'));
        return;
    }

    $result = update_user_meta($user_id, 'geo_clock_rfid', $rfid);

    if ($result !== false) {
        wp_send_json_success(array('message' => 'RFID updated successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update RFID. No changes were made.'));
    }
}
  
  public function register_login_settings() {
        register_setting('geo_clock_login_settings', 'geo_clock_login_method');

        add_settings_section(
            'geo_clock_login_section',
            'Login Settings',
            array($this, 'login_section_callback'),
            'geo_clock_login_settings'
        );

        add_settings_field(
            'login_method',
            'Login Method',
            array($this, 'login_method_callback'),
            'geo_clock_login_settings',
            'geo_clock_login_section'
        );
    }

    public function login_section_callback() {
        echo '<p>Configure the login method for Geo Clock.</p>';
    }

    public function login_method_callback() {
        $login_method = get_option('geo_clock_login_method', 'username');
        ?>
        <select name="geo_clock_login_method">
            <option value="username" <?php selected($login_method, 'username'); ?>>Username and Password</option>
            <option value="pin" <?php selected($login_method, 'pin'); ?>>PIN only</option>
            <option value="both" <?php selected($login_method, 'both'); ?>>Username and PIN</option>
        </select>
        <?php
    }
  
   public function update_user_pin() {
    check_ajax_referer('geo-clock-admin-nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        return;
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $pin = isset($_POST['pin']) ? sanitize_text_field($_POST['pin']) : '';

    if (!$user_id || !$pin) {
        wp_send_json_error(array('message' => 'Invalid user ID or PIN.'));
        return;
    }

    if (!preg_match('/^\d{6}$/', $pin)) {
        wp_send_json_error(array('message' => 'PIN must be exactly 6 digits.'));
        return;
    }

    $result = update_user_meta($user_id, 'geo_clock_pin', $pin);

    if ($result !== false) {
        wp_send_json_success(array('message' => 'PIN updated successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update PIN. No changes were made.'));
    }
}
  


    // Add this new method
    public function display_login_settings_page() {
        ob_start();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('geo_clock_login_settings');
                do_settings_sections('geo_clock_login_settings');
                submit_button('Save Changes');
                ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
  
  
  
  
    public function add_plugin_admin_menu() {
        add_menu_page(
            'Geo Clock Dashboard', 
            'Geo Clock', 
            'manage_options', 
            $this->plugin_name, 
            array($this, 'display_plugin_dashboard'),
            'dashicons-clock',
            6
        );
    }

    public function display_plugin_dashboard() {
        include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-dashboard.php';
    }

    public function load_dashboard_section() {
        check_ajax_referer('geo-clock-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $section = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $leave_id = isset($_POST['leave_id']) ? intval($_POST['leave_id']) : 0;
        
        error_log('Loading section: ' . $section);

        try {
            // Modify: This line is added as per the suggestion
            ob_start();
            switch ($section) {
                case 'locations':
                    $content = $this->display_location_page();
                    break;
                case 'employee-logs':
                    $content = $this->display_logs_page();
                    break;
                case 'manage-users':
                    $content = $this->display_manage_users_page();
                    break;
                case 'leave-review':
                    $content = $this->display_leave_review_page();
                    break;
                case 'aesthetic-settings':
                    $content = $this->display_aesthetic_settings_page();
                    break;
                case 'assign-locations':
                    $content = $this->assign_user_locations();
                    break;
                case 'notifications':
                    $content = $this->display_notification_settings_page();
                    break;
                case 'view-user-logs':
                    $content = $this->display_user_logs($user_id);
                    break;
                case 'login-settings':
                    $content = $this->display_login_settings_page();
                    break;
                case 'view-leave-details':
                    $content = $this->display_leave_details($leave_id);
                    break;
                default:
                    $content = '<p>Invalid section.</p>';
            }
            $errors = ob_get_clean();
            if (!empty($errors)) {
                error_log('Errors captured: ' . $errors);
                throw new Exception('Errors occurred while loading the section.');
            }
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage(), 'errors' => $errors));
            return;
        }

        error_log('Content length: ' . strlen($content));

        wp_send_json_success(array('content' => $content));
    }
  
  public function display_notification_settings_page() {
    ob_start();
    include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-notifications.php';
    return ob_get_clean();
}

public function register_notification_settings() {
    register_setting('geo_clock_notification_settings', 'geo_clock_notification_options');

    add_settings_section(
        'geo_clock_notification_section',
        'Email Notification Settings',
        array($this, 'notification_section_callback'),
        'geo_clock_notification_settings'
    );

    add_settings_field(
        'notify_clock_in_out',
        'Notify on Clock In/Out',
        array($this, 'notification_checkbox_callback'),
        'geo_clock_notification_settings',
        'geo_clock_notification_section',
        array('notify_clock_in_out')
    );

    add_settings_field(
        'notify_leave_request',
        'Notify on Leave Request',
        array($this, 'notification_checkbox_callback'),
        'geo_clock_notification_settings',
        'geo_clock_notification_section',
        array('notify_leave_request')
    );
}

public function notification_section_callback() {
    echo '<p>Configure email notifications for employee actions.</p>';
}

public function notification_checkbox_callback($args) {
    $options = get_option('geo_clock_notification_options');
    $field = $args[0];
    $checked = isset($options[$field]) ? $options[$field] : 0;
    echo "<input type='checkbox' id='$field' name='geo_clock_notification_options[$field]' value='1' " . checked(1, $checked, false) . "/>";
}

    public function display_leave_review_page() {
        ob_start();
        if (isset($_GET['action']) && $_GET['action'] === 'view_leave_details' && isset($_GET['leave_id'])) {
            $this->display_leave_details($_GET['leave_id']);
        } else {
            $this->display_leave_list();
        }
        return ob_get_clean();
    }
      
    private function display_leave_list() {
        $leaves = $this->get_pending_leaves();
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-leave-review.php';
    }

 private function display_leave_details($leave_id) {
    if (!current_user_can('manage_options')) {
        return 'Unauthorized access';
    }

    error_log("Attempting to display leave details for ID: " . $leave_id);

    $leave = $this->get_leave_details($leave_id);
    if (!$leave) {
        error_log("Leave details not found for ID: " . $leave_id);
        return 'Leave details not found';
    }

    $leave->calculated_days = $this->calculate_leave_days($leave->start_date, $leave->end_date);

    ob_start();
    include GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-leave-details.php';
    return ob_get_clean();
}
    private function get_pending_leaves() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_leave_requests';
    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY submission_date DESC");
}

    private function get_leave_details($leave_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_leave_requests';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $leave_id));
    }

    public static function process_leave_action() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    check_admin_referer('geo_clock_leave_action', 'geo_clock_leave_nonce');

    $leave_id = isset($_POST['leave_id']) ? intval($_POST['leave_id']) : 0;
    $action = isset($_POST['leave_action']) ? sanitize_text_field($_POST['leave_action']) : '';

    if ($leave_id && $action) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_leave_requests';
        $status = $action === 'approve' ? 'Approved' : 'Disapproved';
        
        $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $leave_id),
            array('%s'),
            array('%d')
        );

        $redirect_url = add_query_arg(
            array(
                'page' => 'geo-based-employee-clock-leave-review',
                'updated' => 'true',
                'status' => $status
            ),
            admin_url('admin.php')
        );
        wp_redirect($redirect_url);
        exit;
    }
}
  
  private function calculate_leave_days($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days + 1; // Add 1 to include both start and end dates
}
  
    public function register_aesthetic_settings() {
        register_setting('geo_clock_aesthetic_settings', 'geo_clock_aesthetic_options');

        add_settings_section(
            'geo_clock_aesthetic_general',
            'General Aesthetic Settings',
            array($this, 'aesthetic_section_callback'),
            'geo_clock_aesthetic_settings'
        );

        $this->add_aesthetic_field('title_font', 'Title Font', 'text');
        $this->add_aesthetic_field('body_font', 'Body Font', 'text');
        $this->add_aesthetic_field('primary_color', 'Primary Color', 'color');
        $this->add_aesthetic_field('secondary_color', 'Secondary Color', 'color');
        $this->add_aesthetic_field('transition_period', 'Transition Period (ms)', 'number');
        $this->add_aesthetic_field('clock_title', 'Clock Interface Title', 'text');
    }

    public function aesthetic_section_callback() {
        echo '<p>Customize the appearance of the clock interface.</p>';
    }

    private function add_aesthetic_field($id, $label, $type) {
        add_settings_field(
            'geo_clock_' . $id,
            $label,
            array($this, 'aesthetic_field_callback'),
            'geo_clock_aesthetic_settings',
            'geo_clock_aesthetic_general',
            array(
                'label_for' => 'geo_clock_' . $id,
                'field_id' => $id,
                'type' => $type
            )
        );
    }

    public function aesthetic_field_callback($args) {
        $options = get_option('geo_clock_aesthetic_options');
        $id = $args['label_for'];
        $field_id = $args['field_id'];
        $type = $args['type'];
        $value = isset($options[$field_id]) ? $options[$field_id] : '';

        printf(
            '<input type="%s" id="%s" name="geo_clock_aesthetic_options[%s]" value="%s" class="regular-text">',
            $type,
            $id,
            $field_id,
            esc_attr($value)
        );
    }

    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge($settings_link, $links);
    }
  
    public function display_aesthetic_settings_page() {
        ob_start();
        include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-aesthetic-settings.php';
        return ob_get_clean();
    }
  
    public function export_selected_users_timesheets() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        check_admin_referer('geo_clock_export_timesheets', 'geo_clock_export_nonce');

        $selected_users = isset($_POST['selected_users']) ? array_map('intval', $_POST['selected_users']) : array();
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        if (empty($selected_users) || empty($start_date) || empty($end_date)) {
            wp_die('Please select users and specify a date range for export');
        }

        $filename = 'geo-clock-timesheets-' . $start_date . '-to-' . $end_date . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        foreach ($selected_users as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                fputcsv($output, array("Timesheet for " . $user->user_login));
                fputcsv($output, array('Clock In', 'Clock Out', 'Location', 'Total Time'));

                $logs = $this->get_user_logs($user_id, $start_date, $end_date);
                foreach ($logs as $log) {
                    $clock_in = new DateTime($log->clock_in);
                    $clock_out = $log->clock_out != '0000-00-00 00:00:00' ? new DateTime($log->clock_out) : new DateTime();
                    $interval = $clock_out->diff($clock_in);
                    $total_time = $interval->format('%H:%I:%S');

                    fputcsv($output, array(
                        $log->clock_in,
                        $log->clock_out,
                        $log->location_name,
                        $total_time
                    ));
                }

                fputcsv($output, array()); // Add a blank line between users
            }
        }

        fclose($output);
        exit;
    }

    public function display_plugin_setup_page() {
        include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-display.php';
    }

    public function display_location_page() {
    ob_start();
    include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-locations.php';
    return ob_get_clean();
}

   public function save_locations() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }

    check_ajax_referer('geo-clock-admin-nonce', 'nonce');

    error_log('Received POST data in save_locations: ' . print_r($_POST, true));

    if (isset($_POST['geo_clock_locations']) && is_array($_POST['geo_clock_locations'])) {
        $locations = array();
        foreach ($_POST['geo_clock_locations'] as $location) {
            if (!empty($location['name'])) {
                $locations[] = array(
                    'name' => sanitize_text_field($location['name']),
                    'lat' => floatval($location['lat']),
                    'lng' => floatval($location['lng']),
                    'radius' => intval($location['radius'])
                );
            }
        }

        update_option('geo_clock_locations', $locations);
        wp_send_json_success(array('message' => 'Locations saved successfully'));
    } else {
        error_log('No location data received in $_POST for save_locations');
        wp_send_json_error(array('message' => 'No location data received'));
    }
}

    public function get_locations() {
        return get_option('geo_clock_locations', array());
    }

    public function get_employee_logs($per_page = 20, $offset = 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_records';

    $sql = "SELECT r.*, u.display_name 
            FROM $table_name r
            JOIN {$wpdb->users} u ON r.user_id = u.ID
            ORDER BY r.clock_in DESC
            LIMIT %d OFFSET %d";

    return $wpdb->get_results($wpdb->prepare($sql, $per_page, $offset), ARRAY_A);
}

    public function get_employee_logs_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    public function update_employee_log() {
    check_ajax_referer('geo-clock-admin-nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $log_id = intval($_POST['log_id']);
    $clock_in = sanitize_text_field($_POST['clock_in']);
    $clock_out = sanitize_text_field($_POST['clock_out']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_records';
    
    $result = $wpdb->update(
        $table_name,
        array(
            'clock_in' => $clock_in,
            'clock_out' => $clock_out
        ),
        array('id' => $log_id),
        array('%s', '%s'),
        array('%d')
    );
    
    if ($result !== false) {
        $total_time = $this->calculate_total_time($clock_in, $clock_out);
        wp_send_json_success(array(
            'message' => 'Log updated successfully',
            'total_time' => $total_time
        ));
    } else {
        wp_send_json_error('Failed to update log: ' . $wpdb->last_error);
    }
}

private function calculate_total_time($clock_in, $clock_out) {
    $start = new DateTime($clock_in);
    $end = $clock_out != '0000-00-00 00:00:00' ? new DateTime($clock_out) : new DateTime();
    $interval = $end->diff($start);
    return sprintf('%02d:%02d:%02d', $interval->h, $interval->i, $interval->s);
}

    public function delete_employee_log() {
        check_ajax_referer('geo-clock-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $log_id = intval($_POST['log_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        
        $result = $wpdb->delete($table_name, array('id' => $log_id), array('%d'));
        
        if ($result !== false) {
            wp_send_json_success('Log deleted successfully');
        } else {
            wp_send_json_error('Failed to delete log: ' . $wpdb->last_error);
        }
    }

    public function ajax_delete_selected_logs() {
        check_ajax_referer('geo-clock-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $log_ids = isset($_POST['log_ids']) ? array_map('intval', $_POST['log_ids']) : array();

        $result = $this->delete_logs($log_ids);

        if ($result !== false) {
            wp_send_json_success(array('message' => sprintf('%d log entries deleted successfully.', $result)));
        } else {
            wp_send_json_error('Error deleting log entries.');
        }
    }

    public function process_bulk_action() {
        $action = $this->current_action();
        
        if ('delete_selected_logs' === $action) {
            if (!isset($_POST['geo_clock_logs_nonce']) || !wp_verify_nonce($_POST['geo_clock_logs_nonce'], 'geo_clock_delete_logs')) {
                wp_die('Security check failed');
            }

            if (!current_user_can('manage_options')) {
                wp_die('You do not have sufficient permissions to access this page.');
            }

            $log_ids = isset($_POST['log']) ? array_map('intval', $_POST['log']) : array();

            if (empty($log_ids)) {
                add_settings_error('geo_clock_messages', 'geo_clock_message', 'No logs selected for deletion.', 'error');
                return;
            }

            $result = $this->delete_logs($log_ids);

            if ($result !== false) {
                add_settings_error('geo_clock_messages', 'geo_clock_message', sprintf('%d log entries deleted successfully.', $result), 'updated');
            } else {
                add_settings_error('geo_clock_messages', 'geo_clock_message', 'Error deleting log entries.', 'error');
            }
        }
    }

    private function current_action() {
        if (isset($_POST['action']) && -1 != $_POST['action']) {
            return $_POST['action'];
        }

        if (isset($_POST['action2']) && -1 != $_POST['action2']) {
            return $_POST['action2'];
        }

        return false;
    }

    private function delete_logs($log_ids) {
        if (!empty($log_ids)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'geo_clock_records';
            $ids_placeholder = implode(',', array_fill(0, count($log_ids), '%d'));
            
            $query = $wpdb->prepare("DELETE FROM $table_name WHERE id IN ($ids_placeholder)", $log_ids);
            return $wpdb->query($query);
        }
        return false;
    }

    public function display_logs_page() {
    ob_start();
    $this->process_bulk_action();
    settings_errors('geo_clock_messages');
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    $logs = $this->get_employee_logs($per_page, $offset);
    $total_items = $this->get_employee_logs_count();
    $total_pages = ceil($total_items / $per_page);

    include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-logs.php';
    return ob_get_clean();
}

    private function get_pagenum() {
        $pagenum = isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : 0;
        if (isset($pagenum))
            $pagenum = max(1, $pagenum);
        return $pagenum;
    }

    public function display_manage_users_page() {
        ob_start();
        if (isset($_GET['action']) && $_GET['action'] === 'view_logs' && isset($_GET['user_id'])) {
            $this->display_user_logs();
        } else {
            if (isset($_POST['create_user']) && check_admin_referer('geo_clock_create_user', 'geo_clock_create_user_nonce')) {
                $username = sanitize_user($_POST['username']);
                $email = sanitize_email($_POST['email']);
                $password = $_POST['password'];
                // Modify: Added PIN handling
                $pin = sanitize_text_field($_POST['pin']);

                $user_id = wp_create_user($username, $password, $email);

                if (is_wp_error($user_id)) {
                    $error_message = $user_id->get_error_message();
                    add_settings_error('geo_clock_messages', 'geo_clock_user_error', $error_message, 'error');
                } else {
                    add_user_meta($user_id, 'geo_clock_user', true);
                    // Modify: Added PIN saving
                    add_user_meta($user_id, 'geo_clock_pin', $pin);
                    add_settings_error('geo_clock_messages', 'geo_clock_user_created', 'User created successfully.', 'updated');
                }
            }

            include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-manage-users.php';
        }
        return ob_get_clean();
    }
  
   public function display_user_logs($user_id) {
    if (!current_user_can('manage_options')) {
        return 'Unauthorized access';
    }

    error_log("Attempting to display logs for user ID: " . $user_id);

    $user = get_user_by('id', $user_id);
    if (!$user) {
        error_log("User not found for ID: " . $user_id);
        return 'User not found';
    }

    $logs = $this->get_user_logs($user_id);
    $grouped_logs = $this->group_logs_by_date($logs);

    ob_start();
    include GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-user-logs.php';
    return ob_get_clean();
}

    private function get_user_logs($user_id, $start_date = null, $end_date = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        
        if ($start_date && $end_date) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name 
                WHERE user_id = %d 
                AND clock_in >= %s 
                AND clock_in <= %s 
                ORDER BY clock_in DESC",
                $user_id,
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name 
                WHERE user_id = %d 
                ORDER BY clock_in DESC",
                $user_id
            ));
        }
    }

    private function group_logs_by_date($logs) {
        $grouped = array();
        foreach ($logs as $log) {
            $date = date('Y-m-d', strtotime($log->clock_in));
            if (!isset($grouped[$date])) {
                $grouped[$date] = array();
            }
            $grouped[$date][] = $log;
        }
        return $grouped;
    }

    public function update_user_log() {
        check_ajax_referer('geo-clock-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $log_id = intval($_POST['log_id']);
        $clock_in = sanitize_text_field($_POST['clock_in']);
        $clock_out = sanitize_text_field($_POST['clock_out']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'clock_in' => $clock_in,
                'clock_out' => $clock_out
            ),
            array('id' => $log_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Log updated successfully');
        } else {
            wp_send_json_error('Failed to update log: ' . $wpdb->last_error);
        }
    }
  
  public function update_user_locations() {
    check_ajax_referer('geo-clock-admin-nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_user_locations';

    $user_locations = isset($_POST['user_location']) ? $_POST['user_location'] : array();
    
    error_log('User locations: ' . print_r($user_locations, true));

    foreach ($user_locations as $user_id => $locations) {
        $wpdb->delete($table_name, array('user_id' => $user_id), array('%d'));

        if (!empty($locations)) {
            foreach ($locations as $location) {
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'user_id' => $user_id,
                        'location_name' => sanitize_text_field($location)
                    ),
                    array('%d', '%s')
                );
                error_log("Inserting location for user $user_id: $location. Result: " . ($result ? 'success' : 'failed'));
            }
        }
    }

    wp_send_json_success(array('message' => 'User locations updated successfully.'));
}
  

   public function assign_user_locations() {
    global $wpdb;

    $users = get_users(array('meta_key' => 'geo_clock_user', 'meta_value' => true));
    $locations = $this->get_locations();
    $user_locations_table = $wpdb->prefix . 'geo_clock_user_locations';
    
    ob_start();
    include_once GEO_CLOCK_PLUGIN_DIR . 'admin/partials/geo-clock-admin-assign-locations.php';
    $content = ob_get_clean();

    wp_send_json_success(array('content' => $content));
}
  
  // Add: New method for handling PIN updates
    public function ajax_update_user_pin() {
        check_ajax_referer('geo-clock-admin-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $pin = isset($_POST['pin']) ? sanitize_text_field($_POST['pin']) : '';

        if (!$user_id || !$pin || !preg_match('/^\d{6}$/', $pin)) {
            wp_send_json_error('Invalid user ID or PIN');
        }

        $result = update_user_meta($user_id, 'geo_clock_pin', $pin);

        if ($result) {
            wp_send_json_success('PIN updated successfully');
        } else {
            wp_send_json_error('Failed to update PIN');
        }
    }

    public function create_user() {
        if (!isset($_POST['geo_clock_create_user_nonce']) || !wp_verify_nonce($_POST['geo_clock_create_user_nonce'], 'geo_clock_create_user')) {
            wp_die('Nonce verification failed');
        }

        // Validate and sanitize input
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $pin = sanitize_text_field($_POST['pin']);

        // Create the user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            // Handle error
            wp_redirect(add_query_arg('error', 'user_creation_failed', admin_url('admin.php?page=geo_clock_manage_users')));
            exit;
        }

        // Set user meta for PIN
        update_user_meta($user_id, 'geo_clock_pin', $pin);

        // Redirect back to the manage users page
        wp_redirect(admin_url('admin.php?page=geo_clock_manage_users'));
        exit;
    }

    public function assign_users() {
        if (!isset($_POST['geo_clock_assign_users_nonce']) || !wp_verify_nonce($_POST['geo_clock_assign_users_nonce'], 'geo_clock_assign_users')) {
            wp_die('Nonce verification failed');
        }

        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : [];

        foreach ($user_ids as $user_id) {
            // Add user to geo clock system (e.g., set user meta)
            update_user_meta($user_id, 'geo_clock_user', true);
        }

        // Redirect back to the manage users page
        wp_redirect(admin_url('admin.php?page=geo_clock_manage_users'));
        exit;
    }

    public function assign_geo_clock_users() {
        check_admin_referer('geo_clock_assign_users', 'geo_clock_assign_users_nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access');
        }

        // Your logic to assign users goes here

        wp_redirect(admin_url('admin.php?page=geo_clock_manage_users'));
        exit;
    }

  
  
  }