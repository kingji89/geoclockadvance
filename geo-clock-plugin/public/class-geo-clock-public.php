<?php
class Geo_Clock_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('wp_ajax_nopriv_geo_clock_login', array($this, 'handle_login'));
        add_action('wp_ajax_geo_clock_login', array($this, 'handle_login'));
        add_action('wp_ajax_clock_in_out', array($this, 'handle_clock_in_out'));
        add_action('wp_ajax_geo_clock_check_login', array($this, 'check_login'));
        add_action('wp_ajax_nopriv_geo_clock_check_login', array($this, 'check_login'));
      add_action('wp_ajax_geo_clock_logout', array($this, 'handle_logout'));
      add_action('wp_head', array($this, 'apply_aesthetic_settings'));
      add_action('wp_ajax_handle_leave_request', array($this, 'handle_leave_request'));
    add_action('wp_ajax_get_leave_requests', array($this, 'get_leave_requests'));
      add_action('wp_ajax_nopriv_geo_clock_rfid', array($this, 'handle_rfid_clock'));
    add_action('wp_ajax_geo_clock_rfid', array($this, 'handle_rfid_clock'));


    }
  
  
  // Add this new method to the Geo_Clock_Public class
public function handle_rfid_clock() {
    check_ajax_referer('geo_clock_nonce', 'nonce');

    $rfid_code = isset($_POST['rfid_code']) ? sanitize_text_field($_POST['rfid_code']) : '';
  
    if (empty($rfid_code)) {
        wp_send_json_error(array('message' => 'RFID code is missing'));
        return;
    }

    $user = $this->get_user_by_rfid($rfid_code);

    if (!$user) {
        wp_send_json_error(array('message' => 'Invalid RFID card'));
        return;
    }

    if (!get_user_meta($user->ID, 'geo_clock_user', true)) {
        wp_send_json_error(array('message' => 'You do not have permission to use this system.'));
        return;
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);

    $clock_status = $this->get_user_clock_status($user->ID);
    $action = $clock_status['status'] === 'in' ? 'out' : 'in';

    $result = $this->process_clock_action($user->ID, null, null, array('name' => 'RFID Clock-in'));

    if ($result) {
        wp_send_json_success(array(
            'message' => "Clocked " . $action . " successfully",
            'user_name' => $user->display_name,
            'clock_status' => $action
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to process clock action'));
    }
}

// Add this new method to the Geo_Clock_Public class
private function get_user_by_rfid($rfid_code) {
    $users = get_users(array(
        'meta_key' => 'geo_clock_rfid',
    ));

    foreach ($users as $user) {
        $stored_rfid = get_user_meta($user->ID, 'geo_clock_rfid', true);
        
        // Compare both full RFID and last 7 digits
        if ($rfid_code === $stored_rfid || substr($rfid_code, -7) === substr($stored_rfid, -7)) {
            return $user;
        }
    }

    return false;
}
  
  
  
  public function handle_logout() {
        check_ajax_referer('geo_clock_nonce', 'nonce');

        if (is_user_logged_in()) {
            wp_logout();
            wp_send_json_success(array('message' => 'Logged out successfully'));
        } else {
            wp_send_json_error(array('message' => 'No user is currently logged in'));
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/geo-clock-public.css', array(), $this->version, 'all');
    }
  
   public function check_login() {
        $is_logged_in = is_user_logged_in() && get_user_meta(get_current_user_id(), 'geo_clock_user', true);
        wp_send_json_success(array('logged_in' => $is_logged_in));
    }

   public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name, 
            plugin_dir_url(__FILE__) . 'js/geo-clock.js', 
            array('jquery'), 
            $this->version, 
            false
        );
        wp_localize_script($this->plugin_name, 'geo_clock_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('geo_clock_nonce'), // Changed nonce name
        ));
    }

    public function employee_clock_shortcode() {
    ob_start();
    
    // Check for the redirect parameter
    if (isset($_GET['geo_clock_logged_in']) && $_GET['geo_clock_logged_in'] == '1') {
        // Clear the parameter to prevent issues with browser back button
        echo '<script>history.replaceState({}, "", location.pathname);</script>';
    }

    if (is_user_logged_in() && get_user_meta(get_current_user_id(), 'geo_clock_user', true)) {
        $this->display_clock_interface();
    } else {
        $this->display_login_form();
    }
    return ob_get_clean();
}
  
  public function apply_aesthetic_settings() {
    $options = get_option('geo_clock_aesthetic_options');
    if (!empty($options)) {
        echo '<style type="text/css">';
        if (!empty($options['title_font'])) {
            echo '.geo-clock-wrapper h1, .geo-clock-wrapper h2 { font-family: ' . esc_html($options['title_font']) . '; }';
        }
        if (!empty($options['body_font'])) {
            echo '.geo-clock-wrapper { font-family: ' . esc_html($options['body_font']) . '; }';
        }
        if (!empty($options['primary_color'])) {
            echo '.geo-clock-wrapper .time-display { background-color: ' . esc_html($options['primary_color']) . '; }';
        }
        if (!empty($options['secondary_color'])) {
            echo '.geo-clock-wrapper .daily-total { background-color: ' . esc_html($options['secondary_color']) . '; }';
        }
        if (!empty($options['transition_period'])) {
            echo '.geo-clock-wrapper * { transition: all ' . intval($options['transition_period']) . 'ms; }';
        }
        echo '</style>';
    }
}

    private function display_login_form() {
        $login_method = get_option('geo_clock_login_method', 'username');
        ?>
        <div class="geo-clock-login-form">
            <h2>Employee Clock In/Out</h2>
          <div class="rfid-instructions">
        <p>To clock in or out, tap your RFID card on the reader.</p>
    </div>

    <div class="manual-login-instructions">
        <p>Or log in manually:</p>
    </div>
            <form id="geo-clock-login-form" method="post">
                <?php if ($login_method === 'username' || $login_method === 'both'): ?>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <?php endif; ?>
                <?php if ($login_method === 'pin' || $login_method === 'both'): ?>
                <div class="form-group">
                    <label for="pin">PIN:</label>
                    <input type="password" id="pin" name="pin" maxlength="6" required>
                </div>
                <?php endif; ?>
                <?php if ($login_method === 'username'): ?>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <input type="submit" value="Login">
                </div>
                <?php wp_nonce_field('geo_clock_login_nonce', 'geo_clock_login_nonce'); ?>
            </form>
          <div id="rfid-status" style="display: none;">
        <p>Processing RFID... Please wait.</p>
    </div>
          
        </div>
        <?php
    }

    private function display_clock_interface() {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $clock_info = $this->get_user_clock_status($user_id);
        $location = $this->get_user_location($user_id);
        $daily_total = $this->get_daily_total($user_id);
        $day_log = $this->get_day_log($user_id);
        $logs_per_page = 5;
        $total_pages = ceil(count($day_log) / $logs_per_page);
        
        $is_clocked_in = $clock_info['status'] === 'in';
        $button_class = $is_clocked_in ? 'clocked-in' : 'clocked-out';
        $button_text = $is_clocked_in ? 'CLOCK OUT' : 'CLOCK IN';
      
      // Get the clock title from aesthetic settings
    $aesthetic_options = get_option('geo_clock_aesthetic_options', array());
    $clock_title = isset($aesthetic_options['clock_title']) ? $aesthetic_options['clock_title'] : 'Employee Clock In/Out';
      
        ?>
        <div class="geo-clock-wrapper">
        <h1 class="app-title"><?php echo esc_html($clock_title); ?></h1>
        <h2 class="welcome-message">Welcome, <?php echo esc_html($user->display_name); ?>!</h2>
        <div class="time-display">
            <div class="status-bar">
                <span class="status"><?php echo $clock_info['status'] === 'in' ? 'Working' : 'Not working'; ?></span>
            </div>
            <div class="timer" id="work-timer" data-last-clock-in="<?php echo esc_attr($clock_info['last_clock_in']); ?>">00:00:00</div>
            <div class="location"><i class="location-icon"></i> <?php echo esc_html($location); ?></div>
        </div>
        <div class="daily-total">
            Total hours for <?php echo esc_html(wp_date('D, M j')); ?>: <span id="daily-total"><?php echo esc_html($daily_total); ?></span>
        </div>
        <div class="day-log-header">
            <h2>My day log</h2>
            <button class="map-button">Map</button>
        </div>
        <div class="day-log" id="day-log-container">
            <?php foreach (array_slice($day_log, 0, $logs_per_page) as $index => $log): ?>
                <div class="log-entry">
                    <span class="log-type <?php echo esc_attr(strtolower($log['type'])); ?>"><?php echo esc_html($log['type']); ?> <?php echo esc_html($log['time']); ?></span>
                    <?php if (isset($log['message'])): ?>
                        <span class="log-message"><?php echo esc_html($log['message']); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
      
      <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <button id="prev-page" disabled>&lt; Previous</button>
                <span id="page-info">Page 1 of <?php echo $total_pages; ?></span>
                <button id="next-page">Next &gt;</button>
            </div>
        <?php endif; ?>
      
   
      
     
        <div class="clock-button-container">
            <div class="animated-circles <?php echo $is_clocked_in ? 'active' : ''; ?>">
                <div class="circle outer"></div>
                <div class="circle inner"></div>
            </div>
            <button id="clock-button" class="clock-button <?php echo $button_class; ?>" data-status="<?php echo $is_clocked_in ? 'in' : 'out'; ?>">
                <div class="clock-icon"></div>
                <div class="button-text"><?php echo $button_text; ?></div>
            </button>
        </div>

        <div class="additional-options">
                <button class="option-button" id="my-requests-button">My requests</button>
                <button class="option-button">My timesheet</button>
            </div>
            <div class="logout-container">
                <button id="logout-button" class="logout-button">Logout</button>
            </div>
        
          
          <div id="my-requests-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>My Requests</h2>
                    <button id="new-leave-request-button">New Leave Request</button>
                    <div id="leave-requests-list"></div>
                    
                    <!-- Leave Request Form (initially hidden) -->
                    <div id="leave-request-form" style="display: none;">
    <h3>New Leave Request</h3>
    <form id="leave-application-form">
        <div class="leave-type-buttons">
            <button type="button" class="leave-type-button" data-type="annual">Annual Leave</button>
            <button type="button" class="leave-type-button" data-type="sick">Sick Leave</button>
            <button type="button" class="leave-type-button" data-type="casual">Casual Leave</button>
        </div>
        <input type="hidden" id="selected-leave-type" name="leave_type" value="">
        <input type="text" id="leave-subject" placeholder="Subject" required>
        <div class="date-inputs">
            <input type="date" id="leave-start-date" required>
            <input type="date" id="leave-end-date" required>
        </div>
        <textarea id="leave-description" placeholder="Description" required></textarea>
        <button type="submit" class="submit-leave-request">APPLY FOR LEAVE</button>
    </form>
</div>
                </div>
            </div>
          </div>
          
          
          
          
    <script>
    jQuery(document).ready(function($) {
        var dayLog = <?php echo json_encode($day_log); ?>;
        var logsPerPage = <?php echo $logs_per_page; ?>;
        var currentPage = 1;
        var totalPages = <?php echo $total_pages; ?>;

        function displayLogs(page) {
            var start = (page - 1) * logsPerPage;
            var end = start + logsPerPage;
            var pageLog = dayLog.slice(start, end);
            var html = '';

            pageLog.forEach(function(log) {
                html += '<div class="log-entry">' +
                    '<span class="log-type ' + log.type.toLowerCase() + '">' + log.type + ' ' + log.time + '</span>';
                if (log.message) {
                    html += '<span class="log-message">' + log.message + '</span>';
                }
                html += '</div>';
            });

            $('#day-log-container').html(html);
            $('#page-info').text('Page ' + page + ' of ' + totalPages);
            $('#prev-page').prop('disabled', page === 1);
            $('#next-page').prop('disabled', page === totalPages);
        }

        $('#next-page').click(function() {
            if (currentPage < totalPages) {
                currentPage++;
                displayLogs(currentPage);
            }
        });

        $('#prev-page').click(function() {
            if (currentPage > 1) {
                currentPage--;
                displayLogs(currentPage);
            }
        });
    });
    </script>

    <style>
    .day-log {
        max-height: 300px;
        overflow-y: auto;
    }
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }
    .pagination button {
        background-color: #4a86e8;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
    }
    .pagination button:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
    </style>

    <?php
}

    
// Update the handle_login method
public function handle_login() {
        try {
            check_ajax_referer('geo_clock_nonce', 'nonce');

            $login_method = get_option('geo_clock_login_method', 'username');

            if ($login_method === 'username' || $login_method === 'both') {
                if (!isset($_POST['username'])) {
                    throw new Exception('Username is missing.');
                }
                $username = sanitize_user($_POST['username']);
            }

            if ($login_method === 'pin' || $login_method === 'both') {
                if (!isset($_POST['pin'])) {
                    throw new Exception('PIN is missing.');
                }
                $pin = sanitize_text_field($_POST['pin']);
            }

            if ($login_method === 'username') {
                if (!isset($_POST['password'])) {
                    throw new Exception('Password is missing.');
                }
                $password = $_POST['password'];
                $user = wp_authenticate($username, $password);
            } elseif ($login_method === 'pin') {
                $user = $this->get_user_by_pin($pin);
            } else { // both
                $user = $this->get_user_by_username_and_pin($username, $pin);
            }

            if (is_wp_error($user)) {
                error_log('Geo Clock: Login failed. Error: ' . $user->get_error_message());
                throw new Exception('Invalid credentials.');
            }

            if (!get_user_meta($user->ID, 'geo_clock_user', true)) {
                error_log('Geo Clock: User ' . $user->user_login . ' attempted to log in but is not a Geo Clock user.');
                throw new Exception('You do not have permission to use this system.');
            }

            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);

            $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url('/');

            wp_send_json_success(array(
                'message' => 'Login successful.',
                'redirect_url' => $redirect_url
            ));

        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
  
  private function get_user_by_pin($pin) {
        $users = get_users(array(
            'meta_key' => 'geo_clock_pin',
            'meta_value' => $pin,
            'number' => 1,
        ));

        return !empty($users) ? $users[0] : false;
    }

    private function get_user_by_username_and_pin($username, $pin) {
        $user = get_user_by('login', $username);
        if ($user && get_user_meta($user->ID, 'geo_clock_pin', true) === $pin) {
            return $user;
        }
        return false;
    }
  

    public function handle_clock_in_out() {
        check_ajax_referer('geo_clock_nonce', 'nonce'); // Changed nonce name

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }

        $user_id = get_current_user_id();
        $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
        $lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

        if ($lat === null || $lng === null) {
            wp_send_json_error(array('message' => 'Location data is missing'));
        }

        error_log("Geo Clock: User $user_id attempting to clock in/out at lat: $lat, lng: $lng");

        $location_check = $this->get_closest_location($lat, $lng);

    if (!$location_check['within_radius']) {
        error_log("Geo Clock: User not within allowed radius. " . $location_check['message']);
        wp_send_json_error(array(
            'message' => $location_check['message'],
            'closest_location' => $location_check['location']['name'],
            'distance' => round($location_check['distance'])
        ));
        return;
    }

    global $wpdb;
    $user_locations_table = $wpdb->prefix . 'geo_clock_user_locations';
    $assigned_locations = $wpdb->get_col($wpdb->prepare(
        "SELECT location_name FROM $user_locations_table WHERE user_id = %d",
        $user_id
    ));

    if (!empty($assigned_locations) && !in_array($location_check['location']['name'], $assigned_locations)) {
        wp_send_json_error(array(
            'message' => "You are not allowed to clock in/out at this location. Your assigned locations are: " . implode(', ', $assigned_locations)
        ));
        return;
    }

    $result = $this->process_clock_action($user_id, $lat, $lng, $location_check['location']);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => $result['message'],
                'location' => $location_check['location']['name']
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to process clock action'));
        }
    }

    private function get_closest_location($lat, $lng) {
        $locations = get_option('geo_clock_locations', array());
        $within_radius = false;
        $closest_location = null;
        $closest_distance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = $this->calculate_distance($lat, $lng, $location['lat'], $location['lng']);
            error_log("Geo Clock: Distance to {$location['name']}: $distance meters (allowed radius: {$location['radius']} meters)");
            
            if ($distance <= $closest_distance) {
                $closest_distance = $distance;
                $closest_location = $location;
            }
            
            if ($distance <= $location['radius']) {
                $within_radius = true;
                break;
            }
        }

        $message = $within_radius 
            ? sprintf('Within allowed radius of %s', $closest_location['name'])
            : sprintf('Not within allowed area. Closest location: %s (%.2f meters away, allowed radius: %d meters)',
                $closest_location['name'],
                $closest_distance,
                $closest_location['radius']
            );

        return array(
            'within_radius' => $within_radius,
            'location' => $closest_location,
            'distance' => $closest_distance,
            'message' => $message
        );
    }

    private function process_clock_action($user_id, $lat = null, $lng = null, $location = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_records';
    $user_logs_table = $wpdb->prefix . 'geo_clock_user_logs';

    $last_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
        $user_id
    ));

    $current_time = current_time('mysql');

    if ($last_record && $last_record->clock_out == '0000-00-00 00:00:00') {
        // Clock out
        $wpdb->update(
            $table_name,
            array('clock_out' => $current_time),
            array('id' => $last_record->id)
        );
        $wpdb->update(
            $user_logs_table,
            array('clock_out' => $current_time),
            array('user_id' => $user_id, 'clock_out' => '0000-00-00 00:00:00')
        );
        delete_user_meta($user_id, 'geo_clock_last_clock_in');
        $message = 'Clocked out successfully';
        $action = 'clocked out';
    } else {
        // Clock in
        $insert_data = array(
            'user_id' => $user_id,
            'clock_in' => $current_time,
            'location_name' => is_array($location) ? $location['name'] : 'Unknown'
        );

        // Only add lat and lng if they are provided
        if ($lat !== null && $lng !== null) {
            $insert_data['location_lat'] = $lat;
            $insert_data['location_lng'] = $lng;
        }

        $wpdb->insert($table_name, $insert_data);

        $wpdb->insert(
            $user_logs_table,
            array(
                'user_id' => $user_id,
                'clock_in' => $current_time,
                'location_name' => is_array($location) ? $location['name'] : 'Unknown'
            )
        );
        update_user_meta($user_id, 'geo_clock_last_clock_in', $current_time);
        $message = 'Clocked in successfully';
        $action = 'clocked in';
    }

    // Send notification if enabled
    $options = get_option('geo_clock_notification_options', array());
    if (isset($options['notify_clock_in_out']) && $options['notify_clock_in_out']) {
        Geo_Clock_Notifications::send_clock_notification($user_id, $action, $location['name']);
    }

    return array('message' => $message);
}

    private function calculate_distance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371000; // in meters
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        $delta_lat = $lat2 - $lat1;
        $delta_lon = $lon2 - $lon1;
        $a = sin($delta_lat/2) * sin($delta_lat/2) + cos($lat1) * cos($lat2) * sin($delta_lon/2) * sin($delta_lon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earth_radius * $c;
        return $distance;
    }

    public function get_user_clock_status($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        $last_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ));

        $status = ($last_record && $last_record->clock_out == '0000-00-00 00:00:00') ? 'in' : 'out';
        $last_clock_in = get_user_meta($user_id, 'geo_clock_last_clock_in', true);

        return array(
            'status' => $status,
            'last_clock_in' => $last_clock_in
        );
    }

    private function get_user_location($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        $last_record = $wpdb->get_row($wpdb->prepare(
            "SELECT location_name FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ));

        return $last_record ? $last_record->location_name : 'Unknown Location';
    }

    private function get_daily_total($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        $today = wp_date('Y-m-d');
        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT clock_in, clock_out FROM $table_name WHERE user_id = %d AND DATE(clock_in) = %s",
            $user_id, $today
        ));

        $total_seconds = 0;
        foreach ($records as $record) {
            $clock_in = new DateTime($record->clock_in, wp_timezone());
            $clock_out = $record->clock_out != '0000-00-00 00:00:00' ? new DateTime($record->clock_out, wp_timezone()) : current_datetime();
            $diff = $clock_out->diff($clock_in);
            $total_seconds += $diff->s + ($diff->i * 60) + ($diff->h * 3600);
        }

        return $this->format_time_duration($total_seconds);
    }

    private function format_time_duration($total_seconds) {
        $hours = floor($total_seconds / 3600);
        $minutes = floor(($total_seconds % 3600) / 60);
        $seconds = $total_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
  
  public function handle_leave_request() {
    check_ajax_referer('geo_clock_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in'));
    }

    $user_id = get_current_user_id();
    $leave_type = sanitize_text_field($_POST['leaveType']);
    $subject = sanitize_text_field($_POST['subject']);
    $start_date = sanitize_text_field($_POST['startDate']);
    $end_date = sanitize_text_field($_POST['endDate']);
    $description = sanitize_textarea_field($_POST['description']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_leave_requests';

    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'leave_type' => $leave_type,
            'subject' => $subject,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'reason' => $description,
            'status' => 'Pending Approval',
            'submission_date' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result) {
        wp_send_json_success(array('message' => 'Leave request submitted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to submit leave request'));
    }
}
  
  public function get_leave_requests() {
    check_ajax_referer('geo_clock_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in'));
    }

    $user_id = get_current_user_id();

    global $wpdb;
    $table_name = $wpdb->prefix . 'geo_clock_leave_requests';

    $requests = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY submission_date DESC",
        $user_id
    ));

    wp_send_json_success(array('requests' => $requests));
}

    private function get_day_log($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'geo_clock_records';
        $today = wp_date('Y-m-d');
        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND DATE(clock_in) = %s ORDER BY clock_in DESC",
            $user_id, $today
        ));

        $log = array();
        foreach ($records as $record) {
            $clock_in_time = new DateTime($record->clock_in, wp_timezone());
            $clock_out_time = $record->clock_out !== '0000-00-00 00:00:00' ? new DateTime($record->clock_out, wp_timezone()) : null;
            
            $log[] = array(
                'type' => 'Clock In',
                'time' => $clock_in_time->format('H:i'),
                'message' => 'Clocked in at ' . $record->location_name
            );

            if ($clock_out_time) {
                $log[] = array(
                    'type' => 'Clock Out',
                    'time' => $clock_out_time->format('H:i'),
                    'message' => 'Clocked out'
                );
            }
        }

        return $log;
    }
}