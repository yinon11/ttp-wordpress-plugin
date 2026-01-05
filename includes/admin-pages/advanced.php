<?php
/**
 * Advanced Page
 */

if (!defined('ABSPATH')) exit;


function ttp_render_advanced_page() {
    ttp_render_admin_styles();
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Advanced</h1>
        </div>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <?php ttp_render_animation_settings(); ?>
            <?php ttp_render_accessibility_settings(); ?>
            <?php ttp_render_tooltip_settings(); ?>
            <?php ttp_render_custom_css(); ?>
            
            <div class="save-area">
                <button type="submit" class="button button-primary">Save Advanced Settings</button>
            </div>
        </form>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    ttp_render_common_scripts();
}
