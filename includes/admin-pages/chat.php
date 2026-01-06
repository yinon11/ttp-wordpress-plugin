<?php
/**
 * Chat Page
 * 
 * FIX #2: Include hidden fields to preserve agent_id and agent_name when saving
 */

if (!defined('ABSPATH')) exit;


function talktopc_render_chat_page() {
    // Styles are now enqueued via admin_enqueue_scripts hook
    
    // FIX #2: Get current agent values to preserve them
    $current_agent_id = get_option('talktopc_agent_id', '');
    $current_agent_name = get_option('talktopc_agent_name', '');
    ?>
    <div class="wrap talktopc-admin-wrap">
        <div class="wp-header">
            <h1>Chat Interface</h1>
        </div>
        
        <form method="post" action="options.php">
            <?php settings_fields('talktopc_settings'); ?>
            
            <!-- FIX #2: Hidden fields to preserve agent selection -->
            <input type="hidden" name="talktopc_agent_id" value="<?php echo esc_attr($current_agent_id); ?>">
            <input type="hidden" name="talktopc_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
            
            <?php talktopc_render_behavior_settings(); ?>
            <?php talktopc_render_message_settings(); ?>
            <?php talktopc_render_voice_settings(); ?>
            <?php talktopc_render_text_settings(); ?>
            
            <div class="save-area">
                <button type="submit" class="button button-primary">Save Chat Settings</button>
            </div>
        </form>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    // Scripts are now enqueued via admin_enqueue_scripts hook
}