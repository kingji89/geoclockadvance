<div class="geo-clock-card">
    <div class="geo-clock-card-header">
        <h2>Create New User</h2>
    </div>
    <div class="geo-clock-card-body">
        <form method="post" action="" class="geo-clock-form">
            <?php wp_nonce_field('geo_clock_create_user', 'geo_clock_create_user_nonce'); ?>
            
            <div class="geo-clock-form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="regular-text" required>
            </div>

            <div class="geo-clock-form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="regular-text" required>
            </div>

            <div class="geo-clock-form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="regular-text" required>
            </div>

            <div class="geo-clock-form-group">
                <label for="pin">PIN</label>
                <input type="text" name="pin" id="pin" class="regular-text" pattern="\d{6}" title="Please enter a 6-digit PIN" maxlength="6" required>
            </div>

            <div class="geo-clock-form-actions">
                <button type="submit" name="create_user" class="geo-clock-btn geo-clock-btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<div class="geo-clock-card mt-6">
    <div class="geo-clock-card-header">
        <h2>Existing Users</h2>
    </div>
    <div class="geo-clock-card-body">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="geo-clock-form">
            <input type="hidden" name="action" value="export_selected_users_timesheets">
            <?php wp_nonce_field('geo_clock_export_timesheets', 'geo_clock_export_nonce'); ?>

            <div class="geo-clock-form-row">
                <div class="geo-clock-form-group">
                    <label for="start_date">Start Date</label>
                    <input type="text" id="start_date" name="start_date" class="datepicker" required>
                </div>
                <div class="geo-clock-form-group">
                    <label for="end_date">End Date</label>
                    <input type="text" id="end_date" name="end_date" class="datepicker" required>
                </div>
            </div>

            <table class="geo-clock-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-users"></th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>PIN</th>
                        <th>RFID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = get_users(array('meta_key' => 'geo_clock_user', 'meta_value' => true));
                    foreach ($users as $user) :
                        $pin = get_user_meta($user->ID, 'geo_clock_pin', true);
                        $rfid = get_user_meta($user->ID, 'geo_clock_rfid', true);
                    ?>
                        <tr>
                            <td><input type="checkbox" name="selected_users[]" value="<?php echo $user->ID; ?>"></td>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td>
                                <div class="geo-clock-input-group">
                                    <input type="text" class="user-pin" data-user-id="<?php echo $user->ID; ?>" value="<?php echo esc_attr($pin); ?>" pattern="\d{6}" maxlength="6">
                                    <button type="button" class="geo-clock-btn geo-clock-btn-secondary update-pin">Update PIN</button>
                                </div>
                            </td>
                            <td>
                                <div class="geo-clock-input-group">
                                    <input type="text" class="user-rfid" data-user-id="<?php echo $user->ID; ?>" value="<?php echo esc_attr($rfid); ?>" maxlength="10">
                                    <button type="button" class="geo-clock-btn geo-clock-btn-secondary update-rfid">Update RFID</button>
                                </div>
                            </td>
                            <td>
                                <div class="geo-clock-btn-group">
                                    <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>" class="geo-clock-btn geo-clock-btn-secondary">Edit</a>
                                    <button type="button" class="geo-clock-btn geo-clock-btn-primary view-logs" data-user-id="<?php echo $user->ID; ?>">View Logs</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="geo-clock-form-actions mt-4">
                <button type="submit" class="geo-clock-btn geo-clock-btn-primary">Export Selected Users' Timesheets</button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    $('#select-all-users').change(function() {
        $('input[name="selected_users[]"]').prop('checked', this.checked);
    });

    $('.update-pin, .update-rfid').on('click', function() {
        var $row = $(this).closest('tr');
        var userId = $row.find('input[type="checkbox"]').val();
        var isPin = $(this).hasClass('update-pin');
        var value = isPin ? $row.find('.user-pin').val() : $row.find('.user-rfid').val();
        var action = isPin ? 'update_user_pin' : 'update_user_rfid';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: action,
                nonce: '<?php echo wp_create_nonce("geo-clock-admin-nonce"); ?>',
                user_id: userId,
                [isPin ? 'pin' : 'rfid']: value
            },
            success: function(response) {
                if (response.success) {
                    alert(isPin ? 'PIN updated successfully' : 'RFID updated successfully');
                } else {
                    alert('Update failed: ' + response.data.message);
                }
            }
        });
    });
});
</script>