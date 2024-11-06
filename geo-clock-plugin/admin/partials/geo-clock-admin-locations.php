<div class="wrap">
    <div class="geo-clock-card">
        <div class="geo-clock-card-header">
            <h2>Manage Locations</h2>
        </div>
        <div class="geo-clock-card-body">
            <form method="post" action="" id="locations-form">
                <?php 
                wp_nonce_field('geo_clock_locations_nonce', 'geo_clock_locations_nonce');
                $locations = $this->get_locations();
                ?>
                
                <div id="geo-clock-locations">
                    <?php if (!empty($locations)) : ?>
                        <?php foreach ($locations as $index => $location) : ?>
                            <div class="location-card geo-clock-fade-in">
                                <div class="location-header">
                                    <div class="location-title">
                                        <span class="dashicons dashicons-location"></span>
                                        <input type="text" 
                                               class="location-name-input" 
                                               name="geo_clock_locations[<?php echo $index; ?>][name]" 
                                               value="<?php echo esc_attr($location['name']); ?>" 
                                               placeholder="Location Name" 
                                               required>
                                    </div>
                                    <button type="button" class="geo-clock-btn geo-clock-btn-danger remove-location">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                                <div class="location-details">
                                    <div class="location-coordinates">
                                        <div class="geo-clock-form-group">
                                            <label>
                                                <span class="dashicons dashicons-location-alt"></span>
                                                Latitude
                                            </label>
                                            <input type="number" 
                                                   step="any" 
                                                   name="geo_clock_locations[<?php echo $index; ?>][lat]" 
                                                   value="<?php echo esc_attr($location['lat']); ?>" 
                                                   required>
                                        </div>
                                        <div class="geo-clock-form-group">
                                            <label>
                                                <span class="dashicons dashicons-location-alt"></span>
                                                Longitude
                                            </label>
                                            <input type="number" 
                                                   step="any" 
                                                   name="geo_clock_locations[<?php echo $index; ?>][lng]" 
                                                   value="<?php echo esc_attr($location['lng']); ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="geo-clock-form-group">
                                        <label>
                                            <span class="dashicons dashicons-marker"></span>
                                            Radius (meters)
                                        </label>
                                        <input type="number" 
                                               name="geo_clock_locations[<?php echo $index; ?>][radius]" 
                                               value="<?php echo esc_attr($location['radius']); ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="geo-clock-actions mt-4">
                    <button type="button" id="add-location" class="geo-clock-btn geo-clock-btn-secondary">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        Add Location
                    </button>
                    <button type="submit" name="submit_locations" class="geo-clock-btn geo-clock-btn-primary">
                        <span class="dashicons dashicons-saved"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/template" id="location-row-template">
    <div class="location-card geo-clock-fade-in">
        <div class="location-header">
            <div class="location-title">
                <span class="dashicons dashicons-location"></span>
                <input type="text" 
                       class="location-name-input" 
                       name="geo_clock_locations[{index}][name]" 
                       placeholder="Location Name" 
                       required>
            </div>
            <button type="button" class="geo-clock-btn geo-clock-btn-danger remove-location">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <div class="location-details">
            <div class="location-coordinates">
                <div class="geo-clock-form-group">
                    <label>
                        <span class="dashicons dashicons-location-alt"></span>
                        Latitude
                    </label>
                    <input type="number" 
                           step="any" 
                           name="geo_clock_locations[{index}][lat]" 
                           required>
                </div>
                <div class="geo-clock-form-group">
                    <label>
                        <span class="dashicons dashicons-location-alt"></span>
                        Longitude
                    </label>
                    <input type="number" 
                           step="any" 
                           name="geo_clock_locations[{index}][lng]" 
                           required>
                </div>
            </div>
            <div class="geo-clock-form-group">
                <label>
                    <span class="dashicons dashicons-marker"></span>
                    Radius (meters)
                </label>
                <input type="number" 
                       name="geo_clock_locations[{index}][radius]" 
                       required>
            </div>
        </div>
    </div>
</script>

<script>
jQuery(document).ready(function($) {
    // Handle adding new location
    $('#add-location').on('click', function() {
        var template = $('#location-row-template').html();
        var index = $('.location-card').length;
        template = template.replace(/{index}/g, index);
        $('#geo-clock-locations').append(template);
    });

    // Handle removing location
    $(document).on('click', '.remove-location', function() {
        $(this).closest('.location-card').remove();
    });

    // Form submission
    $('#locations-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'save_locations');
        formData.append('nonce', '<?php echo wp_create_nonce("geo-clock-admin-nonce"); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Locations saved successfully');
                    window.location.reload();
                } else {
                    alert('Failed to save locations: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while saving locations');
            }
        });
    });
});
</script>

<style>
.location-grid {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.location-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 1rem;
}

.location-header {
    padding: 1rem;
    background: var(--background-light);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.location-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.location-name-input {
    font-size: 1rem;
    font-weight: 500;
    border: 1px solid transparent;
    background: transparent;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    width: 200px;
}

.location-name-input:hover,
.location-name-input:focus {
    border-color: var(--border-color);
    background: white;
}

.location-details {
    padding: 1rem;
}

.location-coordinates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.geo-clock-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.geo-clock-btn-danger {
    background: var(--danger-color);
    color: white;
}

.geo-clock-btn-danger:hover {
    background: #dc2626;
}

@media (max-width: 768px) {
    .location-coordinates {
        grid-template-columns: 1fr;
    }
}
</style>