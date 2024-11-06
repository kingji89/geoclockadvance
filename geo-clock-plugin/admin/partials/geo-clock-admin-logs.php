<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="employee-logs-table-container">
        <form id="geo-clock-logs-form" method="post">
            <?php wp_nonce_field('geo_clock_delete_logs', 'geo_clock_logs_nonce'); ?>
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                    <select name="action" id="bulk-action-selector-top">
                        <option value="-1">Bulk Actions</option>
                        <option value="delete_selected_logs">Delete</option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="Apply">
                </div>
            </div>

            <div class="table-wrapper">
                <table class="employee-logs-table wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-cb manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                                <input id="cb-select-all-1" type="checkbox">
                            </th>
                            <th class="column-employee">Employee</th>
                            <th class="column-clock-in">Clock In</th>
                            <th class="column-clock-out">Clock Out</th>
                            <th class="column-location">Location</th>
                            <th class="column-total-time">Total Time</th>
                            <th class="column-status">Status</th>
                            <th class="column-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): 
                        $is_clocked_in = $log['clock_out'] == '0000-00-00 00:00:00';
                        $status_class = $is_clocked_in ? 'clocked-in' : 'clocked-out';
                    ?>
                        <tr data-log-id="<?php echo esc_attr($log['id']); ?>" class="<?php echo $status_class; ?>">
                            <td class="check-column">
                                <input id="cb-select-<?php echo $log['id']; ?>" type="checkbox" name="log[]" value="<?php echo $log['id']; ?>">
                            </td>
                            <td><?php echo esc_html($log['display_name']); ?></td>
                            <td class="clock-column">
                                <div class="flatpickr calendar-input-icon">
                                    <input type="text" class="clock-in" value="<?php echo date('Y-m-d H:i:s', strtotime($log['clock_in'])); ?>" data-input>
                                    <a class="input-button" title="Clock In" data-toggle>
                                        <i class="dashicons dashicons-calendar-alt"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="clock-column">
                                <div class="flatpickr calendar-input-icon">
                                   <input type="text" class="clock-out" value="<?php echo $log['clock_out'] != '0000-00-00 00:00:00' ? date('Y-m-d H:i:s', strtotime($log['clock_out'])) : '0000-00-00 00:00:00'; ?>" data-input>
                                    <a class="input-button" title="Clock Out" data-toggle>
                                        <i class="dashicons dashicons-calendar-alt"></i>
                                    </a>
                                </div>
                            </td>
                            <td><?php echo esc_html($log['location_name']); ?></td>
                            <td class="total-time">
                                <?php
                                $clock_in = new DateTime($log['clock_in']);
                                $clock_out = $log['clock_out'] != '0000-00-00 00:00:00' ? new DateTime($log['clock_out']) : new DateTime();
                                $interval = $clock_out->diff($clock_in);
                                echo $interval->format('%H:%I:%S');
                                ?>
                            </td>
                            <td class="status-column">
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $is_clocked_in ? 'Clocked In' : 'Clocked Out'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-icons">
                                    <button class="update-log" data-id="<?php echo $log['id']; ?>" title="Update"><i class="dashicons dashicons-update"></i></button>
                                    <button class="delete-log" data-id="<?php echo $log['id']; ?>" title="Delete"><i class="dashicons dashicons-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                    <select name="action2" id="bulk-action-selector-bottom">
                        <option value="-1">Bulk Actions</option>
                        <option value="delete_selected_logs">Delete</option>
                    </select>
                    <input type="submit" id="doaction2" class="button action" value="Apply">
                </div>
                <?php
                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));

                if ($page_links) {
                    echo '<div class="tablenav-pages">' . $page_links . '</div>';
                }
                ?>
            </div>
        </form>
    </div>
</div>

<script>
function makeResizableTable() {
    const table = document.querySelector('.employee-logs-table');
    const cols = table.querySelectorAll('th');
    
    [].forEach.call(cols, function(col) {
        // Add a resizer element to the column
        const resizer = document.createElement('div');
        resizer.classList.add('resizer');
        resizer.style.height = table.offsetHeight + 'px';
        col.appendChild(resizer);
        createResizableColumn(col, resizer);
    });
}

function createResizableColumn(col, resizer) {
    let x = 0;
    let w = 0;

    const mouseDownHandler = function(e) {
        x = e.clientX;
        const styles = window.getComputedStyle(col);
        w = parseInt(styles.width, 10);

        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
        
        resizer.classList.add('resizing');
    };

    const mouseMoveHandler = function(e) {
        const dx = e.clientX - x;
        col.style.width = (w + dx) + 'px';
    };

    const mouseUpHandler = function() {
        document.removeEventListener('mousemove', mouseMoveHandler);
        document.removeEventListener('mouseup', mouseUpHandler);
        resizer.classList.remove('resizing');
    };

    resizer.addEventListener('mousedown', mouseDownHandler);
}
  
  function calculateTotalTime(clockIn, clockOut) {
    var start = moment(clockIn, "YYYY-MM-DD HH:mm:ss");
    var end = clockOut !== '0000-00-00 00:00:00' ? moment(clockOut, "YYYY-MM-DD HH:mm:ss") : moment();
    var duration = moment.duration(end.diff(start));
    var hours = Math.floor(duration.asHours());
    var minutes = Math.floor(duration.asMinutes()) % 60;
    var seconds = Math.floor(duration.asSeconds()) % 60;
    return (hours < 10 ? '0' : '') + hours + ':' +
           (minutes < 10 ? '0' : '') + minutes + ':' +
           (seconds < 10 ? '0' : '') + seconds;
}

function initializeFlatpickr() {
    jQuery(".flatpickr").each(function() {
        if (this._flatpickr) {
            this._flatpickr.destroy();
        }
        flatpickr(this, {
            enableTime: true,
            dateFormat: "Y-m-d H:i:s",
            time_24hr: true,
            wrap: true,
            allowInput: true,
            disableMobile: true,
            parseDate: (datestr, format) => {
                return moment(datestr, "YYYY-MM-DD HH:mm:ss").toDate();
            },
            formatDate: (date, format) => {
                return moment(date).format("YYYY-MM-DD HH:mm:ss");
            },
            onClose: function(selectedDates, dateStr, instance) {
                jQuery(instance.element).find('input').trigger('change');
            }
        });
    });
}

function reinitializeFlatpickr() {
    initializeFlatpickr();
}

jQuery(document).ready(function($) {
    initializeFlatpickr();
    makeResizableTable();

    $('.update-log').on('click', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        var logId = $row.data('log-id');
        var clockIn = $row.find('.clock-in').val();
        var clockOut = $row.find('.clock-out').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_employee_log',
                nonce: '<?php echo wp_create_nonce('geo-clock-admin-nonce'); ?>',
                log_id: logId,
                clock_in: clockIn,
                clock_out: clockOut
            },
            success: function(response) {
                if (response.success) {
                    alert('Log updated successfully');
                    // Update total time
                    var totalTime = calculateTotalTime(clockIn, clockOut);
                $row.find('.total-time').text(totalTime);

                    
                    // Update status
                    var isClockedIn = clockOut === '0000-00-00 00:00:00';
                $row.removeClass('clocked-in clocked-out').addClass(isClockedIn ? 'clocked-in' : 'clocked-out');
                $row.find('.status-column .status-badge').text(isClockedIn ? 'Clocked In' : 'Clocked Out');
                    
                    // Reinitialize flatpickr
                    reinitializeFlatpickr();
                    makeResizableTable();
                } else {
                    alert('Failed to update log: ' + response.data);
                }
            }
        });
    });

    $('.delete-log').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this log entry?')) {
            var $row = $(this).closest('tr');
            var logId = $row.data('log-id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_employee_log',
                    nonce: '<?php echo wp_create_nonce('geo-clock-admin-nonce'); ?>',
                    log_id: logId
                },
                success: function(response) {
                    if (response.success) {
                        $row.remove();
                        alert('Log deleted successfully');
                        makeResizableTable();
                    } else {
                        alert('Failed to delete log: ' + response.data);
                    }
                }
            });
        }
    });

    // Handle "Select All" checkbox
    $('input[id^="cb-select-all-"]').on('change', function() {
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
                alert('Please select at least one log entry to delete.');
            } else {
                return confirm('Are you sure you want to delete the selected log entries? This action cannot be undone.');
            }
        }
    });
});
</script>

<style>
.employee-logs-table-container {
    max-width: 100%;
    overflow-x: auto;
}

.table-wrapper {
    overflow-x: auto;
}

.employee-logs-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin-top: 10px;
    background-color: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 4px;
    overflow: hidden;
    min-width: 100%;
    table-layout: fixed;
}

.employee-logs-table th,
.employee-logs-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.employee-logs-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    position: relative;
}

.employee-logs-table th:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background-color: #ccc;
    cursor: col-resize;
}

.employee-logs-table tr:hover {
    background-color: #f5f5f5;
}

.employee-logs-table input[type="text"] {
    width: 100%;
    padding: 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-badge.clocked-in {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.clocked-out {
    background-color: #f8d7da;
    color: #721c24;
}

.action-icons {
    display: flex;
    gap: 8px;
}

.action-icons button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.action-icons button:hover {
    background-color: #e9ecef;
}

.action-icons .dashicons {
    font-size: 18px;
}

.calendar-input-icon {
    position: relative;
    display: inline-block;
}

.calendar-input-icon input {
    padding-right: 30px;
}

.calendar-input-icon .input-button {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 100%;
    display: flex;
    align-items: center;
    padding: 0 5px;
    cursor: pointer;
}

.calendar-input-icon .dashicons {
    font-size: 20px;
}

.flatpickr-calendar {
    z-index: 99999 !important;
}

/* Column widths */
.employee-logs-table .column-cb { width: 30px; }
.employee-logs-table .column-employee { width: 100px; }
.employee-logs-table .column-clock-in,
.employee-logs-table .column-clock-out { width: 150px; }
.employee-logs-table .column-location { width: 100px; }
.employee-logs-table .column-total-time { width: 80px; }
.employee-logs-table .column-status { width: 80px; }
.employee-logs-table .column-actions { width: 80px; }

.resizer {
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    cursor: col-resize;
    user-select: none;
}

.resizer:hover,
.resizing {
    border-right: 2px solid blue;
}
</style>