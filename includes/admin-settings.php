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
        'talktopc',
        'ttp_settings_page',
        'dashicons-microphone',
        30
    );
});

// =============================================================================
// REGISTER SETTINGS
// =============================================================================
add_action('admin_init', function() {
    
    // =========================================================================
    // CONNECTION (set via OAuth, not editable)
    // =========================================================================
    register_setting('ttp_connection', 'ttp_api_key', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_connection', 'ttp_app_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_connection', 'ttp_user_email', ['sanitize_callback' => 'sanitize_email']);
    
    // =========================================================================
    // AGENT SELECTION
    // =========================================================================
    register_setting('ttp_settings', 'ttp_agent_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_agent_name', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // AGENT OVERRIDES
    // =========================================================================
    register_setting('ttp_settings', 'ttp_override_prompt', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting('ttp_settings', 'ttp_override_first_message', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_override_voice', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_override_voice_speed', ['sanitize_callback' => 'ttp_sanitize_float']);
    register_setting('ttp_settings', 'ttp_override_language', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_override_temperature', ['sanitize_callback' => 'ttp_sanitize_float']);
    register_setting('ttp_settings', 'ttp_override_max_tokens', ['sanitize_callback' => 'absint']);
    register_setting('ttp_settings', 'ttp_override_max_call_duration', ['sanitize_callback' => 'absint']);
    
    // =========================================================================
    // BEHAVIOR
    // =========================================================================
    register_setting('ttp_settings', 'ttp_mode', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'unified']);
    register_setting('ttp_settings', 'ttp_direction', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'ltr']);
    register_setting('ttp_settings', 'ttp_auto_open', ['sanitize_callback' => 'rest_sanitize_boolean']);
    register_setting('ttp_settings', 'ttp_welcome_message', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // BUTTON
    // =========================================================================
    register_setting('ttp_settings', 'ttp_position', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'bottom-right']);
    register_setting('ttp_settings', 'ttp_button_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    register_setting('ttp_settings', 'ttp_button_shape', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'circle']);
    register_setting('ttp_settings', 'ttp_button_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_button_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_button_shadow', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('ttp_settings', 'ttp_button_shadow_color', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // ICON
    // =========================================================================
    register_setting('ttp_settings', 'ttp_icon_type', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'custom']);
    register_setting('ttp_settings', 'ttp_icon_custom_image', ['sanitize_callback' => 'esc_url_raw']);
    register_setting('ttp_settings', 'ttp_icon_emoji', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_icon_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_icon_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    register_setting('ttp_settings', 'ttp_icon_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // PANEL
    // =========================================================================
    register_setting('ttp_settings', 'ttp_panel_width', ['sanitize_callback' => 'absint', 'default' => 350]);
    register_setting('ttp_settings', 'ttp_panel_height', ['sanitize_callback' => 'absint', 'default' => 500]);
    register_setting('ttp_settings', 'ttp_panel_border_radius', ['sanitize_callback' => 'absint', 'default' => 12]);
    register_setting('ttp_settings', 'ttp_panel_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_panel_border', ['sanitize_callback' => 'sanitize_text_field', 'default' => '1px solid #E5E7EB']);
    register_setting('ttp_settings', 'ttp_panel_backdrop_filter', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // HEADER
    // =========================================================================
    register_setting('ttp_settings', 'ttp_header_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_header_show_title', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('ttp_settings', 'ttp_header_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_header_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_header_show_close', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    
    // =========================================================================
    // FOOTER (TTP Branding)
    // =========================================================================
    register_setting('ttp_settings', 'ttp_footer_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_footer_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_footer_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // MESSAGES
    // =========================================================================
    register_setting('ttp_settings', 'ttp_msg_user_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_agent_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_system_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_error_bg', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_msg_font_size', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_msg_border_radius', ['sanitize_callback' => 'absint']);
    
    // =========================================================================
    // LANDING SCREEN
    // =========================================================================
    register_setting('ttp_settings', 'ttp_landing_bg_color', ['sanitize_callback' => 'sanitize_text_field']); // Can be gradient
    register_setting('ttp_settings', 'ttp_landing_logo', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_title_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_landing_card_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_landing_card_border_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_landing_card_hover_border_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_landing_card_icon_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_landing_card_title_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_landing_voice_icon', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_text_icon', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_voice_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_landing_text_title', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // VOICE INTERFACE
    // =========================================================================
    register_setting('ttp_settings', 'ttp_voice_mic_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_mic_active_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_avatar_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_avatar_active_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_status_title_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_status_subtitle_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_start_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_voice_start_subtitle', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_voice_start_btn_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_voice_start_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_start_btn_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_transcript_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_transcript_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_transcript_label_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_control_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_control_btn_secondary_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_voice_end_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    
    // =========================================================================
    // TEXT INTERFACE
    // =========================================================================
    register_setting('ttp_settings', 'ttp_text_send_btn_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_send_btn_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_send_btn_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_text_send_btn_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_input_placeholder', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_text_input_border_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_input_focus_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_input_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_input_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('ttp_settings', 'ttp_text_input_border_radius', ['sanitize_callback' => 'absint']);
    
    // =========================================================================
    // TOOLTIPS
    // =========================================================================
    register_setting('ttp_settings', 'ttp_tooltip_new_chat', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_tooltip_back', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_tooltip_close', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_tooltip_mute', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_tooltip_speaker', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_tooltip_end_call', ['sanitize_callback' => 'sanitize_text_field']);
    
    // =========================================================================
    // ANIMATION
    // =========================================================================
    register_setting('ttp_settings', 'ttp_anim_enable_hover', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('ttp_settings', 'ttp_anim_enable_pulse', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('ttp_settings', 'ttp_anim_enable_slide', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    register_setting('ttp_settings', 'ttp_anim_duration', ['sanitize_callback' => 'ttp_sanitize_float', 'default' => 0.3]);
    
    // =========================================================================
    // ACCESSIBILITY
    // =========================================================================
    register_setting('ttp_settings', 'ttp_a11y_aria_label', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_a11y_aria_description', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_a11y_keyboard_nav', ['sanitize_callback' => 'rest_sanitize_boolean', 'default' => true]);
    
    // =========================================================================
    // CUSTOM CSS
    // =========================================================================
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
    
    $response = wp_remote_request(TTP_API_URL . '/api/public/wordpress/agents/' . $agent_id, [
        'method' => 'PUT',
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'body' => json_encode($update_data),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code === 200) {
        } else {
        }
    }
}