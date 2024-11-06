<div class="wrap">
    <h1>Leave Request Details</h1>
    
    <table class="form-table">
        <tr>
            <th>Employee</th>
            <td><?php echo esc_html(get_user_by('id', $leave->user_id)->display_name); ?></td>
        </tr>
        <tr>
            <th>Leave Type</th>
            <td><?php echo esc_html($leave->leave_type); ?></td>
        </tr>
        <tr>
            <th>From</th>
            <td><?php echo esc_html($leave->start_date); ?></td>
        </tr>
        <tr>
            <th>To</th>
            <td><?php echo esc_html($leave->end_date); ?></td>
        </tr>
        <tr>
    <th>Days</th>
    <td><?php echo esc_html($leave->calculated_days); ?></td>
</tr>
        <tr>
            <th>Reason</th>
            <td><?php echo esc_html($leave->reason); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo esc_html($leave->status); ?></td>
        </tr>
        <tr>
            <th>Submission Date</th>
            <td><?php echo esc_html($leave->submission_date); ?></td>
        </tr>
    </table>

    <h2>Action</h2>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="geo_clock_process_leave">
        <input type="hidden" name="leave_id" value="<?php echo esc_attr($leave->id); ?>">
        <?php wp_nonce_field('geo_clock_leave_action', 'geo_clock_leave_nonce'); ?>
        
        <p>
            <input type="submit" name="leave_action" value="approve" class="button button-primary" onclick="return confirm('Are you sure you want to approve this leave request?');" <?php disabled($leave->status, 'Approved'); ?>>
            <input type="submit" name="leave_action" value="disapprove" class="button" onclick="return confirm('Are you sure you want to disapprove this leave request?');" <?php disabled($leave->status, 'Disapproved'); ?>>
        </p>
    </form>

    <p><a href="<?php echo admin_url('admin.php?page=' . $this->plugin_name . '-leave-review'); ?>" class="button">Back to Leave Review</a></p>
</div>