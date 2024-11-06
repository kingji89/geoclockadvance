(function($) {
    'use strict';

    // Make loadSection a global function
    window.loadSection = function(section, additionalData) {
        var data = {
            action: 'load_dashboard_section',
            nonce: geo_clock_admin.nonce,
            section: section
        };

        // Merge additionalData into data object
        if (additionalData) {
            $.extend(data, additionalData);
        }

        $.ajax({
            url: geo_clock_admin.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                try {
                    if (response.success && response.data) {
                        $('.geo-clock-main-content').html(response.data.content);
                        $(document).trigger('geo_clock_content_loaded');
                        if (section === 'locations') {
                            initializeLocationManagement();
                        }
                        if (response.data.message) {
                            showMessage(response.data.message, response.data.message_type);
                        }
                    } else {
                        console.error('Error loading section:', response);
                        showMessage('Error loading section: ' + (response.data ? response.data.message : 'Unknown error'), 'error');
                    }
                } catch (e) {
                    console.error('Error parsing AJAX response:', e);
                    console.log('Raw response:', response);
                    showMessage('Error parsing server response. Please check the console for details.', 'error');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                showMessage('Ajax request failed: ' + textStatus, 'error');
            }
        });
    };

    function showMessage(message, type) {
        var $messageDiv = $('<div class="notice ' + (type === 'updated' ? 'notice-success' : 'notice-error') + ' is-dismissible"><p>' + message + '</p></div>');
        $('.geo-clock-main-content').prepend($messageDiv);
        setTimeout(function() {
            $messageDiv.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    $(document).ready(function() {
        console.log('1. Geo Clock Admin JS loaded');

        // Function to initialize Select2
        function initializeSelect2() {
            var $select = $('.geo-clock-location-select');
            console.log('2. Number of .geo-clock-location-select elements found:', $select.length);
            if ($select.length > 0) {
                try {
                    $select.select2({
                        placeholder: "Select locations",
                        allowClear: true,
                        width: '100%',
                        theme: "classic",
                        tags: true,
                        tokenSeparators: [',', ' '],
                        closeOnSelect: false
                    });
                    console.log('3. Select2 initialized successfully');
                } catch (error) {
                    console.error('Error initializing Select2:', error);
                }

                // Test if Select2 is working
                $select.on('select2:open', function(e) {
                    console.log('4. Select2 dropdown opened');
                });
                $select.on('select2:close', function(e) {
                    console.log('5. Select2 dropdown closed');
                });
            } else {
                console.log('No .geo-clock-location-select elements found, Select2 not initialized');
            }
        }
      
      // Handle PIN updates
    $(document).on('click', '.update-pin', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        var userId = $row.find('.user-pin').data('user-id');
        var newPin = $row.find('.user-pin').val();

        console.log('Updating PIN for user:', userId, 'New PIN:', newPin);

        $.ajax({
            url: geo_clock_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'update_user_pin',
                nonce: geo_clock_admin.nonce,
                user_id: userId,
                pin: newPin
            },
            success: function(response) {
                console.log('PIN update response:', response);
                if (response.success) {
                    alert(response.data.message || 'PIN updated successfully');
                } else {
                    alert('Failed to update PIN: ' + (response.data ? response.data.message : 'Unknown error'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                console.log('Response:', jqXHR.responseText);
                alert('An error occurred while updating the PIN: ' + textStatus);
            }
        });
    });

        // Initialize Select2 on page load
        initializeSelect2();

        // Re-initialize Select2 when loading new content
        $(document).on('geo_clock_content_loaded', function() {
            console.log('6. Content loaded event triggered');
            initializeSelect2();
        });

        // Add the jQuery test element
        $('body').append('<div id="jquery-test">jQuery is working</div>');
        console.log('7. jQuery test element added');

        // Handle "Select All" checkbox
        $('.wp-list-table').on('click', 'input[id^="cb-select-all-"]', function() {
            var isChecked = $(this).prop('checked');
            $('.wp-list-table').find('input[name="log[]"]').prop('checked', isChecked);
        });

        // Handle form submission for bulk actions
        $('#geo-clock-logs-form').on('submit', function(e) {
            var selectedAction = $('#bulk-action-selector-top').val();
            if (selectedAction === 'delete_selected_logs') {
                var checkedBoxes = $('input[name="log[]"]:checked').length;
                if (checkedBoxes === 0) {
                    e.preventDefault();
                    showMessage('Please select at least one log entry to delete.', 'error');
                } else {
                    return confirm('Are you sure you want to delete the selected log entries? This action cannot be undone.');
                }
            }
        });

        // Handle View Logs button click
        $(document).on('click', '.view-logs', function(e) {
            e.preventDefault();
            var userId = $(this).data('user-id');
            loadSection('view-user-logs', { user_id: userId });
        });

        // Handle View Leave Details button click
        $(document).on('click', '.view-leave-details', function(e) {
            e.preventDefault();
            var leaveId = $(this).data('leave-id');
            loadSection('view-leave-details', { leave_id: leaveId });
        });

        // Handle individual log entry updates
        $('.update-log').on('click', function(e) {
            e.preventDefault();
            var $row = $(this).closest('tr');
            var logId = $(this).data('id');
            var clockIn = $row.find('.clock-in').val();
            var clockOut = $row.find('.clock-out').val();

            console.log('Updating log:', logId, clockIn, clockOut);

            $.ajax({
                url: geo_clock_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_employee_log',
                    nonce: geo_clock_admin.nonce,
                    log_id: logId,
                    clock_in: clockIn,
                    clock_out: clockOut
                },
                success: function(response) {
                    console.log('Update response:', response);
                    if (response.success) {
                        showMessage('Log updated successfully', 'updated');
                        // You might want to update the row content here
                    } else {
                        showMessage('Failed to update log: ' + response.data, 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    showMessage('An error occurred while updating the log: ' + textStatus, 'error');
                }
            });
        });

        // Handle sidebar link clicks
        $('.geo-clock-sidebar a').on('click', function(e) {
            e.preventDefault();
            var section = $(this).data('section');
            loadSection(section);
        });

        // Load the first section by default
        loadSection('locations');

        // Handle individual log entry deletions
        $('.delete-log').on('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this log entry?')) {
                var $row = $(this).closest('tr');
                var logId = $(this).data('id');

                console.log('Deleting log:', logId);

                $.ajax({
                    url: geo_clock_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'delete_employee_log',
                        nonce: geo_clock_admin.nonce,
                        log_id: logId
                    },
                    success: function(response) {
                        console.log('Delete response:', response);
                        if (response.success) {
                            $row.remove();
                            showMessage('Log deleted successfully', 'updated');
                        } else {
                            showMessage('Failed to delete log: ' + response.data, 'error');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX error:', textStatus, errorThrown);
                        showMessage('An error occurred while deleting the log: ' + textStatus, 'error');
                    }
                });
            }
        });

        // Initialize location management if the section is already loaded
        if ($('#geo-clock-locations').length) {
            initializeLocationManagement();
        }

        // Handle form submission for updating user locations
        $(document).on('submit', '#assign-locations-form', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            var formData = $(this).serialize();
            console.log('Form data:', formData);
            $.ajax({
                url: geo_clock_admin.ajax_url,
                type: 'POST',
                data: formData + '&action=update_user_locations&nonce=' + geo_clock_admin.nonce,
                success: function(response) {
                    console.log('Response:', response);
                    if (response.success) {
                        showMessage(response.data.message, 'updated');
                        // Reload the assign-locations section
                        loadSection('assign-locations');
                    } else {
                        console.error('Error updating user locations:', response);
                        showMessage('Error updating user locations', 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);
                    showMessage('Ajax request failed', 'error');
                }
            });
        });
    });

    function initializeLocationManagement() {
        console.log('Initializing location management');
        var $locationsContainer = $('#geo-clock-locations');
        var $addLocationButton = $('#add-location');
        var locationRowTemplate = $('#location-row-template').html();

        console.log('Add Location button:', $addLocationButton.length ? 'Found' : 'Not found');
        console.log('Locations container:', $locationsContainer.length ? 'Found' : 'Not found');
        console.log('Location row template:', locationRowTemplate ? 'Found' : 'Not found');

        // Handle remove location
        $locationsContainer.on('click', '.remove-location', function(e) {
            e.preventDefault();
            console.log('Remove location button clicked');
            $(this).closest('.location-card').remove();
        });

        // Add form submission handler
        $('form', $locationsContainer.closest('.wrap')).on('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            var formData = new FormData(this);
            formData.append('action', 'save_locations');
            formData.append('nonce', geo_clock_admin.nonce);

            // Log form data
            console.log('Form data:');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: geo_clock_admin.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Save response:', response);
                    if (response.success) {
                        showMessage(response.data.message || 'Locations saved successfully', 'updated');
                        // Reload the locations section to reflect the changes
                        loadSection('locations');
                    } else {
                        showMessage('Failed to save locations: ' + (response.data ? response.data.message : 'Unknown error'), 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);
                    showMessage('An error occurred while saving locations: ' + textStatus, 'error');
                }
            });
        });
    }

    // Global event handler for Add Location button
    $(document).on('click', '#add-location', function(e) {
        e.preventDefault();
        console.log('Add Location button clicked (document event)');
        var $locationsContainer = $('#geo-clock-locations');
        var locationRowTemplate = $('#location-row-template').html();
        if ($locationsContainer.length && locationRowTemplate) {
            var newIndex = $locationsContainer.children('.location-card').length;
            var newRow = locationRowTemplate.replace(/{index}/g, newIndex);
            $locationsContainer.append(newRow);
            console.log('New location row added');
        } else {
            console.error('Locations container or template not found');
        }
    });

// PIN update functionality
        $(document).on('click', '.update-pin', function(e) {
            e.preventDefault();
            var $row = $(this).closest('tr');
            var userId = $row.find('.user-pin').data('user-id');
            var newPin = $row.find('.user-pin').val();

            console.log('Updating PIN for user:', userId, 'New PIN:', newPin);

            $.ajax({
                url: geo_clock_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_user_pin',
                    nonce: geo_clock_admin.nonce,
                    user_id: userId,
                    pin: newPin
                },
                success: function(response) {
                    console.log('PIN update response:', response);
                    if (response.success) {
                        alert(response.data.message || 'PIN updated successfully');
                    } else {
                        alert('Failed to update PIN: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    alert('An error occurred while updating the PIN: ' + textStatus);
                }
            });
        });

        // RFID update functionality
        $(document).on('click', '.update-rfid', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var userId = $row.find('.user-rfid').data('user-id');
    var newRfid = $row.find('.user-rfid').val();

    // Validate RFID length
    if (newRfid.length !== 7 && newRfid.length !== 10) {
        alert('RFID must be either 7 or 10 digits.');
        return;
    }

    console.log('Updating RFID for user:', userId, 'New RFID:', newRfid);

    $.ajax({
        url: geo_clock_admin.ajax_url,
        type: 'POST',
        data: {
            action: 'update_user_rfid',
            nonce: geo_clock_admin.nonce,
            user_id: userId,
            rfid: newRfid
        },
        success: function(response) {
            console.log('RFID update response:', response);
            if (response.success) {
                alert(response.data.message || 'RFID updated successfully');
            } else {
                alert('Failed to update RFID: ' + (response.data ? response.data.message : 'Unknown error'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
            alert('An error occurred while updating the RFID: ' + textStatus);
        }
    });
});
    

})(jQuery);