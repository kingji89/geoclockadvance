<?php
class Geo_Clock_Notifications {
    public static function send_clock_notification($user_id, $action, $location) {
        $user = get_userdata($user_id);
        $admin_email = get_option('admin_email');
        $subject = sprintf('[Geo Clock] Employee %s: %s', $user->display_name, ucfirst($action));
        $message = sprintf(
            "Employee %s has %s at %s.\n\nLocation: %s\nTime: %s",
            $user->display_name,
            $action,
            current_time('mysql'),
            $location,
            current_time('mysql')
        );
        wp_mail($admin_email, $subject, $message);
    }

    public static function send_leave_request_notification($user_id, $leave_type, $start_date, $end_date) {
        $user = get_userdata($user_id);
        $admin_email = get_option('admin_email');
        $subject = sprintf('[Geo Clock] New Leave Request from %s', $user->display_name);
        $message = sprintf(
            "Employee %s has submitted a new leave request.\n\nLeave Type: %s\nFrom: %s\nTo: %s",
            $user->display_name,
            $leave_type,
            $start_date,
            $end_date
        );
        wp_mail($admin_email, $subject, $message);
    }
}