<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <?php settings_errors('geo_clock_messages'); ?>
    <form method="post" action="" id="assign-locations-form">
        <?php wp_nonce_field('geo_clock_manage_users', 'geo_clock_manage_users_nonce'); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Assigned Locations</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : 
        $assigned_locations = $wpdb->get_col($wpdb->prepare(
    "SELECT location_name FROM $user_locations_table WHERE user_id = %d",
    $user->ID
));
    ?>
                    <tr>
                        <td><?php echo esc_html($user->user_login); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td>
                            <div class="geo-clock-admin-wrapper">
                                <?php
                                error_log('Rendering select element for user: ' . $user->ID);
                                error_log('Number of locations: ' . count($locations));
                                error_log('Number of assigned locations: ' . count($assigned_locations));
                                ?>
                                <select class="geo-clock-location-select" name="user_location[<?php echo $user->ID; ?>][]" multiple="multiple" style="width: 100%;">
                                    <?php foreach ($locations as $location) : ?>
                                        <option value="<?php echo esc_attr($location['name']); ?>" <?php echo in_array($location['name'], $assigned_locations) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($location['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="update_user_locations" class="button button-primary" value="Update User Locations">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#assign-locations-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        loadSection('assign-locations', formData);
    });
});
</script>

<script>
jQuery(document).ready(function($) {
    console.log('8. DOM fully loaded');
    console.log('9. Number of .geo-clock-location-select elements:', document.querySelectorAll('.geo-clock-location-select').length);
    console.log('10. Select2 availability:', typeof $.fn.select2 !== 'undefined' ? 'Available' : 'Not available');
});
</script>