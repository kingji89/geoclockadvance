<div class="wrap geo-clock-locations">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="">
        <?php
        settings_fields('geo_clock_locations');
        wp_nonce_field('geo_clock_locations_nonce', 'geo_clock_locations_nonce');
        $locations = $this->get_locations();
        ?>
        <div id="geo-clock-locations">
            <?php 
            if (!empty($locations)) :
                foreach ($locations as $index => $location) : 
            ?>
                <div class="location-card">
                    <div class="location-header">
                        <input type="text" class="location-name" name="geo_clock_locations[<?php echo $index; ?>][name]" value="<?php echo esc_attr($location['name']); ?>" placeholder="Location Name" required>
                        <button type="button" class="remove-location">Remove</button>
                    </div>
                    <div class="location-details">
                        <div class="form-group">
                            <label for="location-lat-<?php echo $index; ?>">Latitude:</label>
                            <input type="number" id="location-lat-<?php echo $index; ?>" step="any" name="geo_clock_locations[<?php echo $index; ?>][lat]" value="<?php echo esc_attr($location['lat']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="location-lng-<?php echo $index; ?>">Longitude:</label>
                            <input type="number" id="location-lng-<?php echo $index; ?>" step="any" name="geo_clock_locations[<?php echo $index; ?>][lng]" value="<?php echo esc_attr($location['lng']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="location-radius-<?php echo $index; ?>">Radius (meters):</label>
                            <input type="number" id="location-radius-<?php echo $index; ?>" name="geo_clock_locations[<?php echo $index; ?>][radius]" value="<?php echo esc_attr($location['radius']); ?>" required>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            endif;
            ?>
        </div>
        <div class="form-actions">
            <button type="button" id="add-location" class="button button-secondary">Add Location</button>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
        </div>
    </form>
</div>

<script type="text/template" id="location-row-template">
    <div class="location-card">
        <div class="location-header">
            <input type="text" class="location-name" name="geo_clock_locations[{index}][name]" placeholder="Location Name" required>
            <button type="button" class="remove-location">Remove</button>
        </div>
        <div class="location-details">
            <div class="form-group">
                <label for="location-lat-{index}">Latitude:</label>
                <input type="number" id="location-lat-{index}" step="any" name="geo_clock_locations[{index}][lat]" required>
            </div>
            <div class="form-group">
                <label for="location-lng-{index}">Longitude:</label>
                <input type="number" id="location-lng-{index}" step="any" name="geo_clock_locations[{index}][lng]" required>
            </div>
            <div class="form-group">
                <label for="location-radius-{index}">Radius (meters):</label>
                <input type="number" id="location-radius-{index}" name="geo_clock_locations[{index}][radius]" required>
            </div>
        </div>
    </div>
</script>