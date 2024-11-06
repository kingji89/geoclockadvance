<div class="wrap geo-clock-dashboard">
    <div class="geo-clock-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    </div>
    
    <div class="geo-clock-content">
        <div class="geo-clock-sidebar">
            <ul>
                <li>
                    <a href="#" data-section="locations" class="active">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        Locations
                    </a>
                </li>
                <li>
                    <a href="#" data-section="employee-logs">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                        Employee Logs
                    </a>
                </li>
                <li>
                    <a href="#" data-section="manage-users">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.42 0-8 1.79-8 4v2h16v-2c0-2.21-3.58-4-8-4z"/>
                        </svg>
                        Manage Users
                    </a>
                </li>
                <li>
                    <a href="#" data-section="leave-review">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/>
                        </svg>
                        Leave Review
                    </a>
                </li>
                <li>
                    <a href="#" data-section="aesthetic-settings">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M12 16c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0-6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0-2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        Aesthetic Settings
                    </a>
                </li>
                <li>
                    <a href="#" data-section="assign-locations">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        Assign Locations
                    </a>
                </li>
                <li>
                    <a href="#" data-section="notifications">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                        Notifications
                    </a>
                </li>
                <li>
                    <a href="#" data-section="login-settings">
                        <svg class="geo-icon" viewBox="0 0 24 24">
                            <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                        </svg>
                        Login Settings
                    </a>
                </li>
            </ul>
        </div>
        <div class="geo-clock-main-content">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
</div>

<style>
/* Add any additional component-specific styles here */
.geo-clock-sidebar a {
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.geo-clock-sidebar a:hover,
.geo-clock-sidebar a.active {
    color: var(--primary-color);
    background: var(--background-light);
    border-left-color: var(--primary-color);
}

.geo-clock-sidebar a:hover .geo-icon,
.geo-clock-sidebar a.active .geo-icon {
    color: var(--primary-color);
}
</style>