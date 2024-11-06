<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields('geo_clock_notification_settings');
        do_settings_sections('geo_clock_notification_settings');
        submit_button('Save Settings');
        ?>
    </form>
</div>