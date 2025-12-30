<?php
/**
 * Admin Settings Registration & Sanitizers
 * 
 * Handles:
 * - WordPress settings registration
 * - Custom sanitizer functions
 * - Admin menu creation
 * - Backend sync hooks (sync settings to TalkToPC API)
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// ADMIN MENU
// =============================================================================
add_action('admin_menu', function() {
    add_menu_page(
        'TalkToPC Voice Widget',
        'TalkToPC',
        'manage_options',
        'ttp-voice-widget',
        'ttp_settings_page',
        'dashicons-microphone',
        30
    );
});

// =============================================================================
// REGISTER SETTINGS
// =============================================================================
add_action('admin_init', function() {
    // Connection settings (set via OAuth, not editable)
    register_setting('ttp_connection', 'ttp_api_key', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_connection', 'ttp_app_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_connection', 'ttp_user_email', ['sanitize_callback' => 'sanitize_email']);
    
    // Agent selection
    register_setting('ttp_settings', 'ttp_agent_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_agent_name', ['sanitize_callback' => 'sanitize_text_field']);
    
    // Agent overrides
    register_setting('ttp_settings', 'ttp_override_prompt', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('ttp_settings', 'ttp_override_first_message', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_override_voice', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_override_voice_speed', ['sanitize_callback' => 'ttp_sanitize_float']);
    register_setting('ttp_settings', 'ttp_override_language', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_override_temperature', ['sanitize_callback' => 'ttp_sanitize_float']);
    register_setting('ttp_settings', 'ttp_override_max_tokens', ['sanitize_callback' => 'absint']);
    register_setting('ttp_settings', 'ttp_override_max_call_duration', ['sanitize_callback' => 'absint']);
    
    // Behavior
    register_setting('ttp_settings', 'ttp_mode', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'unified']);
    register_setting('ttp_settings', 'ttp_direction', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'ltr']);
    register_setting('ttp_settings', 'ttp_auto_open', ['sanitize_callback' => 'rest_sanitize_boolean']);
    register_setting('ttp_settings', 'ttp_welcome_message', ['sanitize_callback' => 'sanitize_text_field']);
    
    // Button
    register_setting('ttp_settings', 'ttp_position', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'bottom-right']);
    register_setting('ttp_settings', 'ttp_button_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    register_setting('ttp_settings', 'ttp_button_shape', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'circle']);
    register_setting('ttp_settings', 'ttp_button_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_button_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_button_shadow', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    
    // Icon
    register_setting('ttp_settings', 'ttp_icon_type', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'custom']);
    register_setting('ttp_settings', 'ttp_icon_custom_image', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('ttp_settings', 'ttp_icon_emoji', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_icon_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_icon_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    
    // Panel
    register_setting('ttp_settings', 'ttp_panel_width', ['sanitize_callback' => 'absint', 'default' => 350]);
    register_setting('ttp_settings', 'ttp_panel_height', ['sanitize_callback' => 'absint', 'default' => 500]);
    register_setting('ttp_settings', 'ttp_panel_border_radius', ['sanitize_callback' => 'absint', 'default' => 12]);
    register_setting('ttp_settings', 'ttp_panel_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // Header
    register_setting('ttp_settings', 'ttp_header_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_header_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_header_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_header_show_close', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    
    // Voice interface
    register_setting('ttp_settings', 'ttp_voice_mic_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_mic_active_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_avatar_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_start_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_end_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // Text interface
    register_setting('ttp_settings', 'ttp_text_send_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_input_placeholder', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_text_input_focus_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // Landing screen
    register_setting('ttp_settings', 'ttp_landing_logo', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_title_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // Messages
    register_setting('ttp_settings', 'ttp_msg_user_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_agent_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // Advanced
    register_setting('ttp_settings', 'ttp_custom_css', ['sanitize_callback' => 'wp_strip_all_tags']);
});

// =============================================================================
// CUSTOM SANITIZERS
// =============================================================================
function ttp_sanitize_float($input) {
    if ($input === '' || $input === null) return '';
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// =============================================================================
// BACKEND SYNC HOOKS
// Sync agent settings to TalkToPC backend when WordPress settings are saved
// =============================================================================
add_action('update_option_ttp_override_prompt', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_first_message', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_voice', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_voice_speed', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_language', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_temperature', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_max_tokens', 'ttp_sync_agent_to_backend', 10, 0);
add_action('update_option_ttp_override_max_call_duration', 'ttp_sync_agent_to_backend', 10, 0);

function ttp_sync_agent_to_backend() {
    // Prevent multiple syncs in the same request
    static $synced = false;
    if ($synced) return;
    $synced = true;
    
    $api_key = get_option('ttp_api_key');
    $agent_id = get_option('ttp_agent_id');
    
    if (empty($api_key) || empty($agent_id)) {
        return;
    }
    
    // Collect all override values - use camelCase for backend
    $update_data = [];
    
    $prompt = get_option('ttp_override_prompt');
    $first_message = get_option('ttp_override_first_message');
    $voice = get_option('ttp_override_voice');
    $voice_speed = get_option('ttp_override_voice_speed');
    $language = get_option('ttp_override_language');
    $temperature = get_option('ttp_override_temperature');
    $max_tokens = get_option('ttp_override_max_tokens');
    $max_call_duration = get_option('ttp_override_max_call_duration');
    
    if (!empty($prompt)) $update_data['systemPrompt'] = $prompt;
    if (!empty($first_message)) $update_data['firstMessage'] = $first_message;
    if (!empty($voice)) $update_data['voiceId'] = $voice;
    if (!empty($voice_speed)) $update_data['voiceSpeed'] = floatval($voice_speed);
    if (!empty($language)) $update_data['agentLanguage'] = $language;
    if (!empty($temperature)) $update_data['temperature'] = floatval($temperature);
    if (!empty($max_tokens)) $update_data['maxTokens'] = intval($max_tokens);
    if (!empty($max_call_duration)) $update_data['maxCallDuration'] = intval($max_call_duration);
    
    if (empty($update_data)) {
        return;
    }
    
    error_log('TTP Widget: Syncing agent ' . $agent_id . ' to backend with data: ' . json_encode($update_data));
    
    $response = wp_remote_request(TTP_API_URL . '/api/public/wordpress/agents/' . $agent_id, [
        'method' => 'PUT',
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'body' => json_encode($update_data),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        error_log('TTP Widget: Backend sync failed - ' . $response->get_error_message());
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code === 200) {
            error_log('TTP Widget: Backend sync successful');
        } else {
            error_log('TTP Widget: Backend sync failed - Status ' . $status_code . ' - ' . wp_remote_retrieve_body($response));
        }
    }
}
