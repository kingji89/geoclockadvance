<?php
class Geo_Clock_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . 'geo_clock_records';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            clock_in datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            clock_out datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            location_lat decimal(10,8) NOT NULL,
            location_lng decimal(11,8) NOT NULL,
            location_name varchar(255) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $user_logs_table = $wpdb->prefix . 'geo_clock_user_logs';
        $sql .= "CREATE TABLE $user_logs_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            clock_in datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            clock_out datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            location_name varchar(255) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Add the leave requests table
        $leave_requests_table = $wpdb->prefix . 'geo_clock_leave_requests';
        $sql .= "CREATE TABLE $leave_requests_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            leave_type varchar(50) NOT NULL,
            subject varchar(255) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            reason text NOT NULL,
            status varchar(20) NOT NULL,
            submission_date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
      
      $user_locations_table = $wpdb->prefix . 'geo_clock_user_locations';
    $sql .= "CREATE TABLE $user_locations_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        location_name varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Add version to options to track schema changes
        add_option('geo_clock_db_version', '1.1');
    }
}