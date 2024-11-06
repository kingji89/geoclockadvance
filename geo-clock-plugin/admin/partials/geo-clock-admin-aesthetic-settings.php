<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('geo_clock_aesthetic_settings');
        do_settings_sections('geo_clock_aesthetic_settings');
        submit_button('Save Changes');
        ?>
    </form>
</div>