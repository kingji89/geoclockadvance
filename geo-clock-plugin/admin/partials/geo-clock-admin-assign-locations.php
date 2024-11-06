<div class="wrap">
    <div class="geo-clock-card">
        <div class="geo-clock-card-header">
            <h2>Assign User Locations</h2>
        </div>
        <div class="geo-clock-card-body">
            <form method="post" action="" id="assign-locations-form">
                <?php wp_nonce_field('geo_clock_manage_users', 'geo_clock_manage_users_nonce'); ?>
                
                <div class="table-responsive">
                    <table class="geo-clock-table">
                        <thead>
                            <tr>
                                <th>User</th>
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
                                    <td>
                                        <div class="user-info">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html($user->user_login); ?>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td>
                                        <div class="location-select-wrapper">
                                            <select class="geo-clock-location-select" 
                                                    name="user_location[<?php echo $user->ID; ?>][]" 
                                                    multiple="multiple">
                                                <?php foreach ($locations as $location) : ?>
                                                    <option value="<?php echo esc_attr($location['name']); ?>" 
                                                            <?php echo in_array($location['name'], $assigned_locations) ? 'selected' : ''; ?>>
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
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="geo-clock-btn geo-clock-btn-primary">
                        <span class="dashicons dashicons-saved"></span>
                        Update Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.location-select-wrapper {
    min-width: 200px;
}

.select2-container {
    width: 100% !important;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .location-select-wrapper {
        min-width: 150px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.geo-clock-location-select').select2({
        theme: 'classic',
        placeholder: 'Select locations',
        allowClear: true,
        width: '100%'
    });
});
</script>