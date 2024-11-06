jQuery(document).ready(function($) {
    // Login form submission
    $('#geo-clock-login-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var data = {
            action: 'geo_clock_login',
            nonce: geo_clock_ajax.nonce
        };

        // Add username if present
        var $username = $form.find('input[name="username"]');
        if ($username.length) {
            data.username = $username.val();
        }

        // Add PIN if present
        var $pin = $form.find('input[name="pin"]');
        if ($pin.length) {
            data.pin = $pin.val();
        }

        // Add password if present
        var $password = $form.find('input[name="password"]');
        if ($password.length) {
            data.password = $password.val();
        }

        $.ajax({
            url: geo_clock_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;
                } else {
                    console.error('Login failed:', response.data.message);
                    alert('Login failed: ' + response.data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                console.log('Response:', jqXHR.responseText);
                alert('An error occurred: ' + textStatus + '. Please check the console for more details.');
            }
        });
    });
  
    function checkLoginState() {
        // Check if we've just logged in
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('geo_clock_logged_in') === '1') {
            // Remove the parameter from the URL
            urlParams.delete('geo_clock_logged_in');
            var newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState({}, '', newUrl);
            return; // Exit the function to prevent further checks
        }

        // Only check login state if we're not already logged in
        if (!$('.geo-clock-wrapper').length) {
            $.ajax({
                url: geo_clock_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'geo_clock_check_login',
                    nonce: geo_clock_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.logged_in) {
                        // User is logged in, reload the page to show the clock interface
                        window.location.href = window.location.pathname + '?geo_clock_logged_in=1';
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Login check failed:', textStatus, errorThrown);
                    console.log('Response:', jqXHR.responseText);
                }
            });
        }
    }

    // Call checkLoginState function
    checkLoginState();

    // Clock in/out functionality
    var $clockButton = $('#clock-button');
    var $workTimer = $('#work-timer');
    var $dailyTotal = $('#daily-total');
    var startTime;
    var timerInterval;

    function updateTimer() {
        var now = new Date();
        var difference = now - startTime;
        var hours = Math.floor(difference / 3600000);
        var minutes = Math.floor((difference % 3600000) / 60000);
        var seconds = Math.floor((difference % 60000) / 1000);
        $workTimer.text(
            (hours < 10 ? '0' : '') + hours + ':' +
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (seconds < 10 ? '0' : '') + seconds
        );
    }

    function startTimer(lastClockIn) {
        startTime = lastClockIn ? new Date(lastClockIn) : new Date();
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    function stopTimer() {
        clearInterval(timerInterval);
        $workTimer.text('00:00:00');
    }
  
    function updateButtonState(isClockingIn) {
        if (isClockingIn) {
            $clockButton.removeClass('clocked-out').addClass('clocked-in');
            $('.animated-circles').addClass('active');
            setTimeout(function() {
                $clockButton.data('status', 'in').find('.button-text').text('CLOCK OUT');
            }, 1500);
        } else {
            $clockButton.removeClass('clocked-in').addClass('clocked-out');
            $('.animated-circles').removeClass('active');
            setTimeout(function() {
                $clockButton.data('status', 'out').find('.button-text').text('CLOCK IN');
            }, 1500);
        }
    }

    // Make sure this is called when the page loads
    $(document).ready(function() {
        if ($clockButton.hasClass('clocked-in')) {
            $('.animated-circles').addClass('active');
        }
    });

    // Initialize timer if user is clocked in
    if ($clockButton.hasClass('clocked-in')) {
        var lastClockIn = $workTimer.data('last-clock-in');
        startTimer(lastClockIn);
    }

    $clockButton.on('click', function() {
        try {
            var status = $(this).data('status');

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    try {
                        var lat = position.coords.latitude;
                        var lng = position.coords.longitude;

                        console.log('Sending AJAX request with:', {
                            url: geo_clock_ajax.ajax_url,
                            action: 'clock_in_out',
                            nonce: geo_clock_ajax.nonce,
                            lat: lat,
                            lng: lng
                        });

                        $.ajax({
                            url: geo_clock_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'clock_in_out',
                                nonce: geo_clock_ajax.nonce,
                                lat: lat,
                                lng: lng
                            },
                            success: function(response) {
                                console.log('AJAX response:', response);
                                if (response.success) {
                                    if (status === 'out') {
                                        startTimer(new Date());
                                        updateButtonState(true);
                                        $('.location').text(response.data.location || 'Unknown Location');
                                    } else {
                                        stopTimer();
                                        updateButtonState(false);
                                    }
                                    console.log(response.data.message);
                                    alert(response.data.message);
                                } else {
                                    console.error('Error:', response.data.message);
                                    alert(response.data.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('AJAX error:', textStatus, errorThrown);
                                console.log('Full error object:', jqXHR);
                                console.log('Response Text:', jqXHR.responseText);
                                alert('An error occurred. Please check the console for more details.');
                            }
                        });
                    } catch (e) {
                        console.error('Error in geolocation success callback:', e);
                    }
                }, function(error) {
                    console.error('Geolocation error:', error);
                    alert('Unable to retrieve your location. Please enable location services and try again.');
                });
            } else {
                console.error('Geolocation is not supported by your browser.');
                alert('Geolocation is not supported by your browser. Please use a modern browser with geolocation support.');
            }
        } catch (e) {
            console.error('Error in clock button click handler:', e);
        }
    });
  
    // Logout functionality
    $('#logout-button').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: geo_clock_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'geo_clock_logout',
                nonce: geo_clock_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to login page or refresh the current page
                    window.location.reload();
                } else {
                    console.error('Logout failed:', response.data.message);
                    alert('Logout failed. Please try again.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('An error occurred during logout. Please try again.');
            }
        });
    });

    // My requests modal
    var requestsModal = $('#my-requests-modal');
    var myRequestsBtn = $('#my-requests-button');
    var requestsSpan = requestsModal.find('.close');
    var newLeaveRequestBtn = $('#new-leave-request-button');
    var leaveRequestForm = $('#leave-request-form');
    var leaveRequestsList = $('#leave-requests-list');

    myRequestsBtn.on('click', function() {
        requestsModal.show();
        loadLeaveRequests();
    });

    requestsSpan.on('click', function() {
        requestsModal.hide();
        leaveRequestForm.hide();
        leaveRequestsList.show();
    });

    newLeaveRequestBtn.on('click', function() {
        leaveRequestsList.hide();
        leaveRequestForm.show();
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target == requestsModal[0]) {
            requestsModal.hide();
            leaveRequestForm.hide();
            leaveRequestsList.show();
        }
    });

    // Leave type button functionality
$('.leave-type-button').on('click', function() {
    $('.leave-type-button').removeClass('active');
    $(this).addClass('active');
    $('#selected-leave-type').val($(this).data('type'));
});

// Leave request form submission
$('#leave-application-form').on('submit', function(e) {
    e.preventDefault();
    var leaveType = $('#selected-leave-type').val();
    var subject = $('#leave-subject').val();
    var startDate = $('#leave-start-date').val();
    var endDate = $('#leave-end-date').val();
    var description = $('#leave-description').val();

    if (!leaveType) {
        alert('Please select a leave type.');
        return;
    }

    $.ajax({
        url: geo_clock_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'handle_leave_request',
            nonce: geo_clock_ajax.nonce,
            leaveType: leaveType,
            subject: subject,
            startDate: startDate,
            endDate: endDate,
            description: description
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                $('#leave-application-form')[0].reset();
                $('.leave-type-button').removeClass('active');
                $('#selected-leave-type').val('');
                leaveRequestForm.hide();
                leaveRequestsList.show();
                loadLeaveRequests();
            } else {
                alert('Error: ' + response.data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
            alert('An error occurred. Please try again.');
        }
    });
});
    function loadLeaveRequests() {
    $.ajax({
        url: geo_clock_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'get_leave_requests',
            nonce: geo_clock_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                displayLeaveRequests(response.data.requests);
            } else {
                alert('Error: ' + response.data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
            alert('An error occurred while loading leave requests.');
        }
    });
}
  
  // Add this function at the end of the file
function handleRFIDInput() {
    let rfidCode = '';
    let lastKeyTime = Date.now();

    $(document).on('keypress', function(e) {
        const currentTime = Date.now();
        if (currentTime - lastKeyTime > 100) {
            rfidCode = '';
        }
        lastKeyTime = currentTime;

        rfidCode += String.fromCharCode(e.which);

        if (rfidCode.length === 10) { // Adjust this length based on your RFID card format
            processRFIDCode(rfidCode);
            rfidCode = '';
            e.preventDefault(); // Prevent the character from being entered in any focused input
        }
    });
}

function processRFIDCode(rfidCode) {
    console.log('Processing RFID code:', rfidCode);
    $('#rfid-status').show().text('Processing RFID... Please wait.');
    
    $.ajax({
        url: geo_clock_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'geo_clock_rfid',
            nonce: geo_clock_ajax.nonce,
            rfid_code: rfidCode
        },
        success: function(response) {
            console.log('RFID AJAX response:', response);
            if (response.success) {
                $('#rfid-status').text(response.data.message + '\nWelcome, ' + response.data.user_name);
                updateClockStatus(response.data.clock_status);
                
                // Optionally, redirect to the main clock interface after a short delay
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                $('#rfid-status').text('Error: ' + response.data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('RFID AJAX error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            $('#rfid-status').text('An error occurred. Please try again or log in manually.');
        }
    });
}
  
  

function updateClockStatus(status) {
    // Update the UI to reflect the new clock status
    // This will depend on your specific UI implementation
    if (status === 'in') {
        $('#clock-button').text('CLOCK OUT').data('status', 'in');
    } else {
        $('#clock-button').text('CLOCK IN').data('status', 'out');
    }
}

    function displayLeaveRequests(requests) {
        var listHtml = '<table class="leave-requests-table">' +
                       '<tr><th>Type</th><th>From</th><th>To</th><th>Status</th></tr>';
        
        requests.forEach(function(request) {
            listHtml += '<tr>' +
                        '<td>' + request.leave_type + '</td>' +
                        '<td>' + request.start_date + '</td>' +
                        '<td>' + request.end_date + '</td>' +
                        '<td>' + request.status + '</td>' +
                        '</tr>';
        });
        
        listHtml += '</table>';
        leaveRequestsList.html(listHtml);
    }

    // Call handleRFIDInput to activate RFID functionality
handleRFIDInput();

}); // Add this closing parenthesis to close the jQuery(document).ready block