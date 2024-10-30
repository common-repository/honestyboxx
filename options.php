<div class="wrap">
    <?php screen_icon(); ?>
    <h2>HonestyBoxx</h2>

    <form action="options.php" method="post">
        <?php settings_fields('honestyboxx_options'); ?>
        <?php do_settings_sections('honestyboxx'); ?>
        
        <?php submit_button(); ?>
    </form>    
</div>