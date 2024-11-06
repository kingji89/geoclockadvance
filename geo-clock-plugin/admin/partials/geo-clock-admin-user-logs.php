<div class="wrap">
    <h1>Logs for <?php echo esc_html($user->display_name); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-manage-users'); ?>" class="button">Back to User Management</a>

    <?php foreach ($grouped_logs as $date => $logs): ?>
        <div class="date-section">
            <h2 class="date-header collapsible"><?php echo esc_html($date); ?> <span class="toggle-icon">▼</span></h2>
            <div class="date-content">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr data-log-id="<?php echo esc_attr($log->id); ?>">
                                <td><input type="text" class="clock-in" value="<?php echo esc_attr($log->clock_in); ?>"></td>
                                <td><input type="text" class="clock-out" value="<?php echo esc_attr($log->clock_out); ?>"></td>
                                <td><?php echo esc_html($log->location_name); ?></td>
                                <td>
                                    <button class="button update-log">Update</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.date-header {
    background-color: #f1f1f1;
    padding: 10px;
    margin-bottom: 0;
    cursor: pointer;
}
.date-header .toggle-icon {
    float: right;
}
.date-content {
    display: none;
    padding: 10px;
    border: 1px solid #ddd;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.date-header').click(function() {
        $(this).next('.date-content').slideToggle('fast');
        $(this).find('.toggle-icon').text(function(_, value) {
            return value === '▼' ? '▲' : '▼';
        });
    });

    $('.update-log').on('click', function() {
        var $row = $(this).closest('tr');
        var logId = $row.data('log-id');
        var clockIn = $row.find('.clock-in').val();
        var clockOut = $row.find('.clock-out').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_user_log',
                nonce: '<?php echo wp_create_nonce('geo-clock-admin-nonce'); ?>',
                log_id: logId,
                clock_in: clockIn,
                clock_out: clockOut
            },
            success: function(response) {
                if (response.success) {
                    alert('Log updated successfully');
                } else {
                    alert('Failed to update log: ' + response.data);
                }
            }
        });
    });
});
</script>