<div class="wrap">
    <div class="geo-clock-card">
        <div class="geo-clock-card-header">
            <div class="header-content">
                <h2>Employee Logs</h2>
                <div class="header-actions">
                    <button type="button" class="geo-clock-btn geo-clock-btn-secondary" id="export-logs">
                        <span class="dashicons dashicons-download"></span>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
        <div class="geo-clock-card-body">
            <form id="geo-clock-logs-form" method="post">
                <?php wp_nonce_field('geo_clock_delete_logs', 'geo_clock_logs_nonce'); ?>
                
                <div class="table-actions">
                    <div class="bulk-actions">
                        <select class="geo-clock-select" id="bulk-action-selector">
                            <option value="">Bulk Actions</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="button" class="geo-clock-btn geo-clock-btn-secondary" id="apply-bulk-action">
                            Apply
                        </button>
                    </div>
                    <div class="table-filters">
                        <input type="text" class="geo-clock-input" placeholder="Search logs..." id="search-logs">
                        <select class="geo-clock-select" id="filter-status">
                            <option value="">All Status</option>
                            <option value="clocked-in">Clocked In</option>
                            <option value="clocked-out">Clocked Out</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="geo-clock-table">
                        <thead>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" id="select-all-logs">
                                </th>
                                <th>Employee</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Location</th>
                                <th>Total Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): 
                                $is_clocked_in = $log['clock_out'] == '0000-00-00 00:00:00';
                                $status_class = $is_clocked_in ? 'success' : 'warning';
                            ?>
                                <tr data-log-id="<?php echo esc_attr($log['id']); ?>">
                                    <td>
                                        <input type="checkbox" name="log[]" value="<?php echo $log['id']; ?>">
                                    </td>
                                    <td>
                                        <div class="employee-info">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html($log['display_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="time-input">
                                            <span class="dashicons dashicons-clock"></span>
                                            <input type="text" class="geo-clock-input clock-in flatpickr" 
                                                   value="<?php echo date('Y-m-d H:i:s', strtotime($log['clock_in'])); ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="time-input">
                                            <span class="dashicons dashicons-clock"></span>
                                            <input type="text" class="geo-clock-input clock-out flatpickr" 
                                                   value="<?php echo $log['clock_out'] != '0000-00-00 00:00:00' ? 
                                                          date('Y-m-d H:i:s', strtotime($log['clock_out'])) : ''; ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="location-info">
                                            <span class="dashicons dashicons-location"></span>
                                            <?php echo esc_html($log['location_name']); ?>
                                        </div>
                                    </td>
                                    <td class="total-time">
                                        <?php
                                        $clock_in = new DateTime($log['clock_in']);
                                        $clock_out = $log['clock_out'] != '0000-00-00 00:00:00' ? 
                                                    new DateTime($log['clock_out']) : new DateTime();
                                        $interval = $clock_out->diff($clock_in);
                                        echo $interval->format('%H:%I:%S');
                                        ?>
                                    </td>
                                    <td>
                                        <span class="geo-clock-badge geo-clock-badge-<?php echo $status_class; ?>">
                                            <?php echo $is_clocked_in ? 'Clocked In' : 'Clocked Out'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="geo-clock-btn geo-clock-btn-secondary update-log" 
                                                    title="Update">
                                                <span class="dashicons dashicons-update"></span>
                                            </button>
                                            <button type="button" class="geo-clock-btn geo-clock-btn-danger delete-log" 
                                                    title="Delete">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-pagination">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                    ?>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.bulk-actions,
.table-filters {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.geo-clock-select {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    background: white;
}

.geo-clock-input {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    background: white;
}

.table-responsive {
    overflow-x: auto;
    margin: 0 -1rem;
    padding: 0 1rem;
}

.employee-info,
.location-info,
.time-input {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.table-pagination {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
}

.flatpickr-input {
    background: transparent !important;
}

@media (max-width: 768px) {
    .table-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .bulk-actions,
    .table-filters {
        flex-wrap: wrap;
    }
    
    .geo-clock-select,
    .geo-clock-input {
        flex: 1;
    }
}
</style>