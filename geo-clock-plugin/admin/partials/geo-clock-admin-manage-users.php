<?php
// File: admin/partials/geo-clock-admin-manage-users.php

// Add this in the <head> section or enqueue these scripts properly
?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('geo_clock_messages'); ?>
 <button id="test-button" style="margin-bottom: 10px;">Test JavaScript</button>
    <h2>Create New User</h2>
    <form method="post" action="">
        <?php wp_nonce_field('geo_clock_create_user', 'geo_clock_create_user_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="username">Username</label></th>
                <td><input type="text" name="username" id="username" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="email">Email</label></th>
                <td><input type="email" name="email" id="email" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="password">Password</label></th>
                <td><input type="password" name="password" id="password" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="pin">PIN</label></th>
                <td><input type="text" name="pin" id="pin" class="regular-text" pattern="\d{6}" title="Please enter a 6-digit PIN" maxlength="6" required></td>
              
            </tr>
        </table>
        <?php submit_button('Create User', 'primary', 'create_user'); ?>
    </form>

    <h2>Existing Users</h2>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="export_selected_users_timesheets">
        <?php wp_nonce_field('geo_clock_export_timesheets', 'geo_clock_export_nonce'); ?>
      
        <div class="date-range-picker">
            <label for="start_date">Start Date:</label>
            <input type="text" id="start_date" name="start_date" class="datepicker" required>
            
            <label for="end_date">End Date:</label>
            <input type="text" id="end_date" name="end_date" class="datepicker" required>
        </div>
     
        <table class="wp-list-table widefat fixed striped">
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
    error_log("Retrieved RFID for user {$user->ID}: $rfid");
                ?>
                    <tr>
                        <td><input type="checkbox" name="selected_users[]" value="<?php echo $user->ID; ?>"></td>
                        <td><?php echo esc_html($user->user_login); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td>
                            <input type="text" class="user-pin" data-user-id="<?php echo $user->ID; ?>" value="<?php echo esc_attr($pin); ?>" pattern="\d{6}" maxlength="6">
<button type="button" class="button update-pin" data-user-id="<?php echo $user->ID; ?>">Update PIN</button>
                        </td>
                      <td>
    <input type="text" class="user-rfid" data-user-id="<?php echo $user->ID; ?>" value="<?php echo esc_attr($rfid); ?>" maxlength="10">
<button type="button" class="button update-rfid" data-user-id="<?php echo $user->ID; ?>">Update RFID</button>
    <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>" class="button">Edit</a>
    <a href="#" class="button view-logs" data-user-id="<?php echo $user->ID; ?>">View Logs</a>
</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <input type="submit" class="button action" value="Export Selected Users' Timesheets">
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');

    // Test button
    var testButton = document.getElementById('test-button');
    if (testButton) {
        testButton.addEventListener('click', function() {
            alert('JavaScript is working!');
        });
    }

    // Update PIN functionality
    var updatePinButtons = document.querySelectorAll('.update-pin');
    updatePinButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // ... (PIN update code remains the same)
        });
    });

    // RFID update functionality
    var updateRfidButtons = document.querySelectorAll('.update-rfid');
    console.log('Found ' + updateRfidButtons.length + ' RFID update buttons');
    
    updateRfidButtons.forEach(function(button) {
        console.log('Adding click listener to RFID button:', button);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('RFID update button clicked');
            
            var row = this.closest('tr');
            var userId = row.querySelector('.user-rfid').dataset.userId;
            var newRfid = row.querySelector('.user-rfid').value;
            
            console.log('Updating RFID for user:', userId, 'New RFID:', newRfid);
            
            this.disabled = true;
            this.textContent = 'Updating...';
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    button.disabled = false;
                    button.textContent = 'Update RFID';
                    
                    if (xhr.status === 200) {
                        console.log('RFID update response:', xhr.responseText);
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alert('RFID updated successfully');
                            } else {
                                alert('Failed to update RFID: ' + (response.data ? response.data.message : 'Unknown error'));
                            }
                        } catch (error) {
                            console.error('Error parsing JSON response:', error);
                            alert('Error parsing server response');
                        }
                    } else {
                        console.error('AJAX error:', xhr.status, xhr.statusText);
                        alert('An error occurred while updating the RFID');
                    }
                }
            };
            xhr.send('action=update_user_rfid&nonce=<?php echo wp_create_nonce('geo-clock-admin-nonce'); ?>&user_id=' + userId + '&rfid=' + newRfid);
        });
    })

    // jQuery code
    jQuery(function($) {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        $('#select-all-users').change(function() {
            $('input[name="selected_users[]"]').prop('checked', this.checked);
        });
    });
});
</script>