<?php
/**
 * Chat Page
 * 
 * FIX #2: Include hidden fields to preserve agent_id and agent_name when saving
 */

if (!defined('ABSPATH')) exit;


function ttp_render_chat_page() {
    ttp_render_admin_styles();
    
    // FIX #2: Get current agent values to preserve them
    $current_agent_id = get_option('ttp_agent_id', '');
    $current_agent_name = get_option('ttp_agent_name', '');
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Chat Interface</h1>
        </div>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <!-- FIX #2: Hidden fields to preserve agent selection -->
            <input type="hidden" name="ttp_agent_id" value="<?php echo esc_attr($current_agent_id); ?>">
            <input type="hidden" name="ttp_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
            
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