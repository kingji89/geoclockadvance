<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    if (isset($_GET['updated'])) {
        echo '<div class="updated"><p>Leave request updated successfully.</p></div>';
    }
    ?>

    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
                <select name="status">
                    <option value="all" <?php selected($current_status, 'all'); ?>>All Requests</option>
                    <option value="Pending Approval" <?php selected($current_status, 'Pending Approval'); ?>>Pending Approval</option>
                    <option value="Approved" <?php selected($current_status, 'Approved'); ?>>Approved</option>
                    <option value="Disapproved" <?php selected($current_status, 'Disapproved'); ?>>Disapproved</option>
                </select>
                <input type="submit" class="button" value="Filter">
            </form>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaves as $leave): 
                if ($current_status != 'all' && $leave->status != $current_status) continue;
            ?>
                <tr>
                    <td><?php echo esc_html(get_user_by('id', $leave->user_id)->display_name); ?></td>
                    <td><?php echo esc_html($leave->leave_type); ?></td>
                    <td><?php echo esc_html($leave->start_date); ?></td>
                    <td><?php echo esc_html($leave->end_date); ?></td>
                    <td><?php echo esc_html($leave->status); ?></td>
                    <td>
                        <a href="#" class="button view-leave-details" data-leave-id="<?php echo $leave->id; ?>">View Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>