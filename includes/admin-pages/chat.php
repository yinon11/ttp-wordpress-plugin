<?php
/**
 * Chat Page
 */

if (!defined('ABSPATH')) exit;


function ttp_render_chat_page() {
    ttp_render_admin_styles();
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Chat Interface</h1>
        </div>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <?php ttp_render_behavior_settings(); ?>
            <?php ttp_render_message_settings(); ?>
            <?php ttp_render_voice_settings(); ?>
            <?php ttp_render_text_settings(); ?>
            
            <div class="save-area">
                <button type="submit" class="button button-primary">Save Chat Settings</button>
            </div>
        </form>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    ttp_render_common_scripts();
}

