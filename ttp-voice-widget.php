<?php
/**
 * Plugin Name: TalkToPC Voice Widget
 * Description: Add AI voice conversations to your WordPress site. Let visitors talk to your AI agent with natural voice interactions.
 * Version: 1.9.0
 * Author: TalkToPC
 * Author URI: https://talktopc.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ttp-voice-widget
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

// Constants
define('TTP_API_URL', 'https://backend.talktopc.com');
define('TTP_CONNECT_URL', 'https://talktopc.com/connect/wordpress');
define('TTP_VERSION', '1.9.0');

// Clean up all plugin data on uninstall
register_uninstall_hook(__FILE__, 'ttp_uninstall_cleanup');
function ttp_uninstall_cleanup() {
    $all_options = [
        'ttp_api_key', 'ttp_app_id', 'ttp_user_email',
        'ttp_agent_id', 'ttp_agent_name',
        'ttp_override_prompt', 'ttp_override_first_message', 'ttp_override_voice',
        'ttp_override_voice_speed', 'ttp_override_language', 'ttp_override_temperature',
        'ttp_override_max_tokens', 'ttp_override_max_call_duration',
        'ttp_mode', 'ttp_direction', 'ttp_auto_open', 'ttp_welcome_message',
        'ttp_position', 'ttp_button_size', 'ttp_button_shape', 'ttp_button_bg_color',
        'ttp_button_hover_color', 'ttp_button_shadow',
        'ttp_icon_type', 'ttp_icon_custom_image', 'ttp_icon_emoji', 'ttp_icon_text', 'ttp_icon_size',
        'ttp_panel_width', 'ttp_panel_height', 'ttp_panel_border_radius', 'ttp_panel_bg_color',
        'ttp_header_title', 'ttp_header_bg_color', 'ttp_header_text_color', 'ttp_header_show_close',
        'ttp_voice_mic_color', 'ttp_voice_mic_active_color', 'ttp_voice_avatar_color',
        'ttp_voice_start_btn_color', 'ttp_voice_end_btn_color',
        'ttp_text_send_btn_color', 'ttp_text_input_placeholder', 'ttp_text_input_focus_color',
        'ttp_landing_logo', 'ttp_landing_title', 'ttp_landing_title_color',
        'ttp_msg_user_bg', 'ttp_msg_agent_bg', 'ttp_msg_text_color',
        'ttp_custom_css'
    ];
    foreach ($all_options as $option) {
        delete_option($option);
    }
}

// =============================================================================
// ADMIN MENU & SETTINGS
// =============================================================================

add_action('admin_menu', function() {
    add_menu_page(
        'TalkToPC Voice Widget',      // Page title
        'TalkToPC',                    // Menu title
        'manage_options',              // Capability
        'ttp-voice-widget',            // Menu slug
        'ttp_settings_page',           // Callback function
        'dashicons-microphone',        // Icon (microphone)
        30                             // Position (below Comments)
    );
});

// Register all settings
add_action('admin_init', function() {
    // Connection settings - separate group (set via OAuth)
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

function ttp_sanitize_float($input) {
    if ($input === '' || $input === null) return '';
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// Sync agent settings to backend when WordPress settings are saved
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

// =============================================================================
// OAUTH CALLBACK HANDLER
// =============================================================================

add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ttp-voice-widget') return;
    
    // Handle OAuth callback
    if (isset($_GET['api_key']) && isset($_GET['state'])) {
        if (!wp_verify_nonce($_GET['state'], 'ttp_connect')) {
            add_settings_error('ttp_settings', 'invalid_state', 'Invalid security token. Please try again.');
            return;
        }
        
        $oauth_api_key = sanitize_text_field($_GET['api_key']);
        $app_id = isset($_GET['app_id']) ? sanitize_text_field($_GET['app_id']) : '';
        $user_email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
        
        // Get or create system API key using the OAuth key for initial auth
        $system_key = ttp_get_or_create_system_api_key($oauth_api_key);
        
        if ($system_key) {
            update_option('ttp_api_key', $system_key);
        } else {
            // Fallback to OAuth key if system key creation fails
            update_option('ttp_api_key', $oauth_api_key);
        }
        
        if ($app_id) update_option('ttp_app_id', $app_id);
        if ($user_email) update_option('ttp_user_email', $user_email);
        
        wp_redirect(admin_url('admin.php?page=ttp-voice-widget&connected=1'));
        exit;
    }
    
    // Handle disconnect
    if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'ttp_disconnect')) {
            add_settings_error('ttp_settings', 'invalid_nonce', 'Invalid security token.');
            return;
        }
        
        // Try to delete the system API key from TalkToPC before disconnecting
        ttp_delete_system_api_key();
        
        // Delete ALL plugin settings for clean slate on reconnect
        $all_options = [
            // Connection
            'ttp_api_key', 'ttp_app_id', 'ttp_user_email',
            // Agent
            'ttp_agent_id', 'ttp_agent_name',
            // Agent overrides
            'ttp_override_prompt', 'ttp_override_first_message', 'ttp_override_voice',
            'ttp_override_voice_speed', 'ttp_override_language', 'ttp_override_temperature',
            'ttp_override_max_tokens', 'ttp_override_max_call_duration',
            // Behavior
            'ttp_mode', 'ttp_direction', 'ttp_auto_open', 'ttp_welcome_message',
            // Button
            'ttp_position', 'ttp_button_size', 'ttp_button_shape', 'ttp_button_bg_color',
            'ttp_button_hover_color', 'ttp_button_shadow',
            // Icon
            'ttp_icon_type', 'ttp_icon_custom_image', 'ttp_icon_emoji', 'ttp_icon_text', 'ttp_icon_size',
            // Panel
            'ttp_panel_width', 'ttp_panel_height', 'ttp_panel_border_radius', 'ttp_panel_bg_color',
            // Header
            'ttp_header_title', 'ttp_header_bg_color', 'ttp_header_text_color', 'ttp_header_show_close',
            // Voice interface
            'ttp_voice_mic_color', 'ttp_voice_mic_active_color', 'ttp_voice_avatar_color',
            'ttp_voice_start_btn_color', 'ttp_voice_end_btn_color',
            // Text interface
            'ttp_text_send_btn_color', 'ttp_text_input_placeholder', 'ttp_text_input_focus_color',
            // Landing
            'ttp_landing_logo', 'ttp_landing_title', 'ttp_landing_title_color',
            // Messages
            'ttp_msg_user_bg', 'ttp_msg_agent_bg', 'ttp_msg_text_color',
            // Custom CSS
            'ttp_custom_css'
        ];
        
        foreach ($all_options as $option) {
            delete_option($option);
        }
        
        wp_redirect(admin_url('admin.php?page=ttp-voice-widget&disconnected=1'));
        exit;
    }
});

/**
 * Get or create a system API key for WordPress
 * Flow: List keys → Delete existing "WordPress System Key" if found → Create fresh one
 * 
 * @param string $auth_key API key to use for authentication
 * @return string|null The new system API key or null on failure
 */
function ttp_get_or_create_system_api_key($auth_key) {
    // Step 1: List existing API keys
    $list_response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/api-keys', [
        'headers' => ['X-API-Key' => $auth_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (!is_wp_error($list_response) && wp_remote_retrieve_response_code($list_response) === 200) {
        $list_body = json_decode(wp_remote_retrieve_body($list_response), true);
        $api_keys = isset($list_body['api_keys']) ? $list_body['api_keys'] : [];
        
        // Step 2: Find and delete existing "WordPress System Key"
        foreach ($api_keys as $key) {
            if (isset($key['key_name']) && $key['key_name'] === 'WordPress System Key' && isset($key['id'])) {
                // Delete existing system key
                wp_remote_request(TTP_API_URL . '/api/public/wordpress/api-keys/' . $key['id'], [
                    'method' => 'DELETE',
                    'headers' => ['X-API-Key' => $auth_key, 'Content-Type' => 'application/json'],
                    'timeout' => 30
                ]);
                break;
            }
        }
    }
    
    // Step 3: Create fresh system API key
    $create_response = wp_remote_post(TTP_API_URL . '/api/public/wordpress/api-keys', [
        'headers' => ['X-API-Key' => $auth_key, 'Content-Type' => 'application/json'],
        'body' => json_encode([
            'key_name' => 'WordPress System Key',
            'description' => 'System-generated API key for WordPress plugin (' . home_url() . ')',
            'permissions' => ['agents:read', 'agents:write', 'voices:read']
        ]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($create_response)) {
        error_log('TTP Widget: Failed to create system API key - ' . $create_response->get_error_message());
        return null;
    }
    
    $status_code = wp_remote_retrieve_response_code($create_response);
    $create_body = json_decode(wp_remote_retrieve_body($create_response), true);
    
    if ($status_code === 200 && isset($create_body['api_key'])) {
        return $create_body['api_key'];
    }
    
    error_log('TTP Widget: Failed to create system API key - Status: ' . $status_code);
    return null;
}

/**
 * Delete the system API key when disconnecting
 */
function ttp_delete_system_api_key() {
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) return;
    
    // List keys to find system key ID
    $list_response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/api-keys', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (is_wp_error($list_response) || wp_remote_retrieve_response_code($list_response) !== 200) {
        return;
    }
    
    $list_body = json_decode(wp_remote_retrieve_body($list_response), true);
    $api_keys = isset($list_body['api_keys']) ? $list_body['api_keys'] : [];
    
    // Find and delete "WordPress System Key"
    foreach ($api_keys as $key) {
        if (isset($key['key_name']) && $key['key_name'] === 'WordPress System Key' && isset($key['id'])) {
            wp_remote_request(TTP_API_URL . '/api/public/wordpress/api-keys/' . $key['id'], [
                'method' => 'DELETE',
                'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
                'timeout' => 30
            ]);
            break;
        }
    }
}

// =============================================================================
// AJAX HANDLERS
// =============================================================================

// Save agent selection via AJAX (for auto-save after authorization)
add_action('wp_ajax_ttp_save_agent_selection', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    
    $agent_id = isset($_POST['agent_id']) ? sanitize_text_field($_POST['agent_id']) : '';
    $agent_name = isset($_POST['agent_name']) ? sanitize_text_field($_POST['agent_name']) : '';
    
    if (empty($agent_id)) {
        wp_send_json_error(['message' => 'Agent ID is required']);
    }
    
    // Save the agent selection
    update_option('ttp_agent_id', $agent_id);
    update_option('ttp_agent_name', $agent_name);
    
    wp_send_json_success(['message' => 'Agent saved successfully', 'agent_id' => $agent_id]);
});

// Update agent configuration in backend
add_action('wp_ajax_ttp_update_agent', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $agent_id = isset($_POST['agent_id']) ? sanitize_text_field($_POST['agent_id']) : '';
    if (empty($agent_id)) wp_send_json_error(['message' => 'Agent ID required']);
    
    // Build update data - use camelCase keys to match backend expectations
    $update_data = [];
    
    if (isset($_POST['system_prompt']) && $_POST['system_prompt'] !== '') {
        $update_data['systemPrompt'] = sanitize_textarea_field($_POST['system_prompt']);
    }
    if (isset($_POST['first_message']) && $_POST['first_message'] !== '') {
        $update_data['firstMessage'] = sanitize_text_field($_POST['first_message']);
    }
    if (isset($_POST['voice_id']) && $_POST['voice_id'] !== '') {
        $update_data['voiceId'] = sanitize_text_field($_POST['voice_id']);
    }
    if (isset($_POST['voice_speed']) && $_POST['voice_speed'] !== '') {
        $update_data['voiceSpeed'] = floatval($_POST['voice_speed']);
    }
    if (isset($_POST['language']) && $_POST['language'] !== '') {
        $update_data['agentLanguage'] = sanitize_text_field($_POST['language']);
    }
    if (isset($_POST['temperature']) && $_POST['temperature'] !== '') {
        $update_data['temperature'] = floatval($_POST['temperature']);
    }
    if (isset($_POST['max_tokens']) && $_POST['max_tokens'] !== '') {
        $update_data['maxTokens'] = intval($_POST['max_tokens']);
    }
    if (isset($_POST['max_call_duration']) && $_POST['max_call_duration'] !== '') {
        $update_data['maxCallDuration'] = intval($_POST['max_call_duration']);
    }
    
    // If no data to update, just return success
    if (empty($update_data)) {
        wp_send_json_success(['message' => 'No changes to save']);
    }
    
    error_log('TTP Widget: Updating agent ' . $agent_id . ' with data: ' . json_encode($update_data));
    
    $response = wp_remote_request(TTP_API_URL . '/api/public/wordpress/agents/' . $agent_id, [
        'method' => 'PUT',
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'body' => json_encode($update_data),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        error_log('TTP Widget: Update failed - ' . $response->get_error_message());
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        error_log('TTP Widget: Update failed - Status ' . $status_code . ' - ' . wp_remote_retrieve_body($response));
        wp_send_json_error(['message' => isset($body['message']) ? $body['message'] : 'Failed to update agent', 'status' => $status_code]);
    }
    
    error_log('TTP Widget: Agent updated successfully');
    wp_send_json_success(['message' => 'Agent updated successfully', 'agent' => $body]);
});

// Generate system prompt from site content (AI with local fallback)
add_action('wp_ajax_ttp_generate_prompt', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    
    $api_key = get_option('ttp_api_key');
    
    // Collect site information
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    $site_url = home_url();
    $site_language = get_locale(); // e.g., 'en_US', 'he_IL', 'fr_FR'
    
    // Get all published pages
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => 50,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ]);
    
    $pages_content = [];
    foreach ($pages as $page) {
        $content = wp_strip_all_tags($page->post_content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (!empty($content)) {
            $pages_content[] = [
                'title' => $page->post_title,
                'content' => mb_substr($content, 0, 2000)
            ];
        }
    }
    
    // Get recent blog posts
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 10,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    $posts_content = [];
    foreach ($posts as $post) {
        $content = wp_strip_all_tags($post->post_content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (!empty($content)) {
            $posts_content[] = [
                'title' => $post->post_title,
                'excerpt' => mb_substr($content, 0, 500)
            ];
        }
    }
    
    // Check for WooCommerce products
    $products_content = [];
    $currency_symbol = '$';
    if (class_exists('WooCommerce')) {
        $currency_symbol = get_woocommerce_currency_symbol();
        $products = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => 100,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        foreach ($products as $product) {
            $wc_product = wc_get_product($product->ID);
            if ($wc_product) {
                $products_content[] = [
                    'name' => $product->post_title,
                    'price' => $wc_product->get_price(),
                    'description' => mb_substr(wp_strip_all_tags($product->post_content), 0, 300),
                    'in_stock' => $wc_product->is_in_stock()
                ];
            }
        }
    }
    
    // Get menu items for navigation context
    $menus = [];
    $menu_locations = get_nav_menu_locations();
    foreach ($menu_locations as $location => $menu_id) {
        if ($menu_id) {
            $menu_items = wp_get_nav_menu_items($menu_id);
            if ($menu_items) {
                foreach ($menu_items as $item) {
                    $menus[] = $item->title;
                }
            }
        }
    }
    $menus = array_unique(array_values($menus));
    
    $stats = [
        'pages' => count($pages_content),
        'posts' => count($posts_content),
        'products' => count($products_content),
        'menu_items' => count($menus)
    ];
    
    // Try backend AI generation if connected
    $ai_prompt = null;
    if (!empty($api_key)) {
        $payload = [
            'site' => [
                'name' => $site_name,
                'description' => $site_description,
                'url' => $site_url,
                'language' => $site_language
            ],
            'pages' => $pages_content,
            'posts' => $posts_content,
            'products' => $products_content,
            'currency' => $currency_symbol,
            'menus' => $menus,
            'stats' => $stats
        ];
        
        $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $compressed_payload = gzencode($json_payload, 9);
        
        error_log('TTP Widget: Trying AI generation - Raw: ' . strlen($json_payload) . ' bytes, Compressed: ' . strlen($compressed_payload) . ' bytes');
        
        $response = wp_remote_post(TTP_API_URL . '/api/public/wordpress/generate-prompt', [
            'headers' => [
                'X-API-Key' => $api_key,
                'Content-Type' => 'application/json',
                'Content-Encoding' => 'gzip'
            ],
            'body' => $compressed_payload,
            'timeout' => 120
        ]);
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($body['prompt'])) {
                $ai_prompt = $body['prompt'];
                error_log('TTP Widget: AI generation successful');
            }
        } else {
            $err = is_wp_error($response) ? $response->get_error_message() : 'Status ' . wp_remote_retrieve_response_code($response);
            error_log('TTP Widget: AI generation failed (' . $err . '), falling back to local');
        }
    }
    
    // Use AI prompt if available, otherwise generate locally
    if ($ai_prompt) {
        wp_send_json_success([
            'prompt' => $ai_prompt,
            'stats' => $stats,
            'source' => 'ai'
        ]);
        return;
    }
    
    // Fallback: Generate prompt locally
    $prompt = "You are a helpful voice assistant for {$site_name}";
    if (!empty($site_description)) {
        $prompt .= " - {$site_description}";
    }
    $prompt .= ".\n\n";
    
    $prompt .= "Your role is to assist visitors with questions about the website, its services, and content. Be friendly, professional, and helpful.\n\n";
    
    // Add pages context
    if (!empty($pages_content)) {
        $prompt .= "=== WEBSITE PAGES ===\n";
        foreach ($pages_content as $page) {
            $prompt .= "\n## {$page['title']}\n";
            $prompt .= "{$page['content']}\n";
        }
        $prompt .= "\n";
    }
    
    // Add products context if WooCommerce
    if (!empty($products_content)) {
        $prompt .= "=== PRODUCTS ===\n";
        foreach ($products_content as $product) {
            $prompt .= "- {$product['name']}";
            if (!empty($product['price'])) {
                $prompt .= " ({$currency_symbol}{$product['price']})";
            }
            if (!empty($product['description'])) {
                $prompt .= ": {$product['description']}";
            }
            $prompt .= "\n";
        }
        $prompt .= "\n";
    }
    
    // Add blog posts context
    if (!empty($posts_content)) {
        $prompt .= "=== RECENT BLOG POSTS ===\n";
        foreach ($posts_content as $post) {
            $prompt .= "- {$post['title']}: {$post['excerpt']}\n";
        }
        $prompt .= "\n";
    }
    
    // Add navigation context
    if (!empty($menus)) {
        $prompt .= "=== MAIN NAVIGATION ===\n";
        $prompt .= "The website has these main sections: " . implode(', ', array_slice($menus, 0, 15)) . "\n\n";
    }
    
    // Add instructions
    $prompt .= "=== INSTRUCTIONS ===\n";
    $prompt .= "- Answer questions based on the website content above\n";
    $prompt .= "- If asked about something not covered, politely say you don't have that information and suggest contacting the website directly\n";
    $prompt .= "- Keep responses concise and conversational since this is a voice interface\n";
    $prompt .= "- Direct visitors to relevant pages when appropriate\n";
    $prompt .= "- Be warm and welcoming to new visitors\n";
    
    wp_send_json_success([
        'prompt' => $prompt,
        'stats' => $stats,
        'source' => 'local'
    ]);
});

add_action('wp_ajax_ttp_fetch_agents', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/agents', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);
    wp_send_json_success(json_decode(wp_remote_retrieve_body($response), true));
});

add_action('wp_ajax_ttp_fetch_voices', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/voices', [
        'headers' => ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);
    wp_send_json_success(json_decode(wp_remote_retrieve_body($response), true));
});

add_action('wp_ajax_ttp_create_agent', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) wp_send_json_error(['message' => 'Not connected']);
    
    $agent_name = isset($_POST['agent_name']) ? sanitize_text_field($_POST['agent_name']) : '';
    if (empty($agent_name)) wp_send_json_error(['message' => 'Agent name required']);
    
    // Check if we should auto-generate prompt from site content
    $auto_generate = isset($_POST['auto_generate_prompt']) && $_POST['auto_generate_prompt'] === 'true';
    
    // Build agent configuration
    $agent_data = [
        'name' => $agent_name,
        'site_url' => home_url(),
        'site_name' => get_bloginfo('name')
    ];
    
    // Optional configuration fields (used for manual creation)
    if (!empty($_POST['first_message'])) {
        $agent_data['first_message'] = sanitize_text_field($_POST['first_message']);
    }
    if (!empty($_POST['system_prompt'])) {
        $agent_data['system_prompt'] = sanitize_textarea_field($_POST['system_prompt']);
    }
    if (!empty($_POST['voice_id'])) {
        $agent_data['voice_id'] = sanitize_text_field($_POST['voice_id']);
    }
    if (!empty($_POST['language'])) {
        $agent_data['language'] = sanitize_text_field($_POST['language']);
    }
    
    // If auto-generate, collect site content for AI prompt generation
    if ($auto_generate) {
        error_log('TTP Widget: Auto-generating prompt from site content');
        
        $site_language = get_locale();
        
        // Collect site info
        $site_content = [
            'site' => [
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => home_url(),
                'language' => $site_language
            ],
            'pages' => [],
            'posts' => [],
            'products' => [],
            'menus' => []
        ];
        
        // Get pages
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => 50,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ]);
        
        foreach ($pages as $page) {
            $content = wp_strip_all_tags($page->post_content);
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);
            
            if (!empty($content)) {
                $site_content['pages'][] = [
                    'title' => $page->post_title,
                    'content' => mb_substr($content, 0, 2000)
                ];
            }
        }
        
        // Get posts
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        foreach ($posts as $post) {
            $content = wp_strip_all_tags($post->post_content);
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);
            
            if (!empty($content)) {
                $site_content['posts'][] = [
                    'title' => $post->post_title,
                    'excerpt' => mb_substr($content, 0, 500)
                ];
            }
        }
        
        // Get WooCommerce products
        if (class_exists('WooCommerce')) {
            $site_content['currency'] = get_woocommerce_currency_symbol();
            $products = get_posts([
                'post_type' => 'product',
                'post_status' => 'publish',
                'numberposts' => 100,
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            foreach ($products as $product) {
                $wc_product = wc_get_product($product->ID);
                if ($wc_product) {
                    $site_content['products'][] = [
                        'name' => $product->post_title,
                        'price' => $wc_product->get_price(),
                        'description' => mb_substr(wp_strip_all_tags($product->post_content), 0, 300),
                        'in_stock' => $wc_product->is_in_stock()
                    ];
                }
            }
        }
        
        // Get menus
        $menu_locations = get_nav_menu_locations();
        foreach ($menu_locations as $location => $menu_id) {
            if ($menu_id) {
                $menu_items = wp_get_nav_menu_items($menu_id);
                if ($menu_items) {
                    foreach ($menu_items as $item) {
                        $site_content['menus'][] = $item->title;
                    }
                }
            }
        }
        $site_content['menus'] = array_unique(array_values($site_content['menus']));
        
        // Add site content to agent data
        $agent_data['site_content'] = $site_content;
        
        // Set language from site locale
        $lang_code = explode('_', $site_language)[0];
        $agent_data['language'] = $lang_code;
        
        error_log('TTP Widget: Collected site content - pages: ' . count($site_content['pages']) . 
                  ', posts: ' . count($site_content['posts']) . 
                  ', products: ' . count($site_content['products']));
    }
    
    // Prepare request - use gzip if site_content is included
    $json_body = json_encode($agent_data, JSON_UNESCAPED_UNICODE);
    $headers = ['X-API-Key' => $api_key, 'Content-Type' => 'application/json'];
    $body = $json_body;
    $timeout = 30;
    
    if ($auto_generate && !empty($agent_data['site_content'])) {
        // Gzip compress for large payloads
        $body = gzencode($json_body, 9);
        $headers['Content-Encoding'] = 'gzip';
        $timeout = 120; // Longer timeout for AI generation
        error_log('TTP Widget: Sending gzipped request - raw: ' . strlen($json_body) . ' bytes, compressed: ' . strlen($body) . ' bytes');
    }
    
    $response = wp_remote_post(TTP_API_URL . '/api/public/wordpress/agents', [
        'headers' => $headers,
        'body' => $body,
        'timeout' => $timeout
    ]);
    
    if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);
    
    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        error_log('TTP Widget: Create agent failed - Status: ' . $status_code . ', Body: ' . wp_remote_retrieve_body($response));
        wp_send_json_error(['message' => $response_body['error'] ?? 'Failed to create agent']);
    }
    
    wp_send_json_success($response_body);
});

function ttp_get_signed_url() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ttp_widget_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }
    
    $api_key = get_option('ttp_api_key');
    $app_id = get_option('ttp_app_id');
    $agent_id = get_option('ttp_agent_id');
    
    if (empty($api_key) || empty($agent_id)) wp_send_json_error(['message' => 'Widget not configured']);
    
    $response = wp_remote_post(TTP_API_URL . '/api/public/agents/signed-url', [
        'headers' => ['Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json'],
        'body' => json_encode(['agentId' => $agent_id, 'appId' => $app_id, 'allowOverride' => true, 'expirationMs' => 3600000]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        wp_send_json_error(['message' => isset($body['message']) ? $body['message'] : 'Failed to get signed URL', 'code' => $status_code]);
    }
    
    wp_send_json_success(['signedUrl' => $body['signedLink']]);
}
add_action('wp_ajax_ttp_get_signed_url', 'ttp_get_signed_url');
add_action('wp_ajax_nopriv_ttp_get_signed_url', 'ttp_get_signed_url');

// =============================================================================
// ADMIN SETTINGS PAGE
// =============================================================================

function ttp_settings_page() {
    $is_connected = !empty(get_option('ttp_api_key'));
    $user_email = get_option('ttp_user_email', '');
    $current_agent_id = get_option('ttp_agent_id', '');
    $current_agent_name = get_option('ttp_agent_name', '');
    
    $state = wp_create_nonce('ttp_connect');
    $redirect_uri = admin_url('admin.php?page=ttp-voice-widget');
    $connect_url = TTP_CONNECT_URL . '?' . http_build_query([
        'redirect_uri' => $redirect_uri, 'state' => $state,
        'site_url' => home_url(), 'site_name' => get_bloginfo('name')
    ]);
    $disconnect_url = wp_nonce_url(admin_url('admin.php?page=ttp-voice-widget&action=disconnect'), 'ttp_disconnect');
    
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    ?>
    <div class="wrap ttp-settings-wrap">
        <h1><?php esc_html_e('TalkToPC Voice Widget', 'ttp-voice-widget'); ?> <small style="font-size: 12px; color: #666;">v<?php echo TTP_VERSION; ?></small></h1>
        
        <?php settings_errors(); ?>
        <?php if (isset($_GET['settings-updated'])): ?><div class="notice notice-success is-dismissible"><p>Settings saved!</p></div><?php endif; ?>
        <?php if (isset($_GET['connected'])): ?><div class="notice notice-success is-dismissible"><p>Connected to TalkToPC!</p></div><?php endif; ?>
        <?php if (isset($_GET['disconnected'])): ?><div class="notice notice-info is-dismissible"><p>Disconnected.</p></div><?php endif; ?>
        
        <!-- Connection -->
        <div class="ttp-card">
            <h2>Account Connection</h2>
            <?php if ($is_connected): ?>
                <div class="ttp-connected-status">
                    <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                    <strong>Connected</strong>
                    <?php if ($user_email): ?><span class="ttp-email">(<?php echo esc_html($user_email); ?>)</span><?php endif; ?>
                    <a href="<?php echo esc_url($disconnect_url); ?>" class="button button-link-delete" style="margin-left: 10px;">Disconnect</a>
                </div>
            <?php else: ?>
                <p>Connect your TalkToPC account to get started.</p>
                <a href="<?php echo esc_url($connect_url); ?>" class="button button-primary button-hero">Connect to TalkToPC</a>
            <?php endif; ?>
        </div>
        
        <?php if ($is_connected): ?>
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <!-- Agent Selection -->
            <div class="ttp-card">
                <h2>Select Agent</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="ttp_agent_select">Agent</label></th>
                        <td>
                            <select id="ttp_agent_select" name="ttp_agent_id" class="regular-text">
                                <option value="">Loading agents...</option>
                            </select>
                            <span class="spinner" id="ttp-agents-loading"></span>
                            <input type="hidden" name="ttp_agent_name" id="ttp_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
                        </td>
                    </tr>
                </table>
                <div id="ttp-create-agent" style="display: none;">
                    <button type="button" class="button" id="ttp-show-create-agent">+ Create New Agent</button>
                    <div id="ttp-create-agent-form" style="display: none; margin-top: 10px;">
                        <input type="text" id="ttp-new-agent-name" placeholder="Agent name" class="regular-text">
                        <button type="button" class="button button-primary" id="ttp-create-agent-btn">Create</button>
                        <button type="button" class="button" id="ttp-cancel-create-agent">Cancel</button>
                    </div>
                </div>
            </div>
            
            <!-- Agent Settings Override -->
            <div class="ttp-card ttp-collapsible open">
                <h2 class="ttp-collapsible-header">Agent Settings (Override) <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <p class="description">Override agent defaults. Leave empty to use agent settings.</p>
                    <table class="form-table">
                        <tr><th><label for="ttp_override_prompt">System Prompt</label></th>
                            <td>
                                <textarea id="ttp_override_prompt" name="ttp_override_prompt" rows="6" class="large-text"><?php echo esc_textarea(get_option('ttp_override_prompt')); ?></textarea>
                                <p style="margin-top: 8px;">
                                    <button type="button" class="button" id="ttp-generate-prompt-btn">
                                        <span class="dashicons dashicons-admin-site" style="vertical-align: middle; margin-right: 4px;"></span>
                                        Generate from Site Content
                                    </button>
                                    <span id="ttp-generate-prompt-status" style="margin-left: 10px;"></span>
                                </p>
                                <p class="description">Automatically create a prompt based on your website's pages and content.</p>
                            </td></tr>
                        <tr><th><label for="ttp_override_first_message">First Message</label></th>
                            <td><input type="text" id="ttp_override_first_message" name="ttp_override_first_message" value="<?php echo esc_attr(get_option('ttp_override_first_message')); ?>" class="large-text"></td></tr>
                        <tr><th><label for="ttp_override_voice">Voice</label></th>
                            <td><select id="ttp_override_voice" name="ttp_override_voice" class="regular-text"><option value="">-- Use agent default --</option></select><span id="ttp-voice-loading" class="spinner"></span></td></tr>
                        <tr><th><label for="ttp_override_voice_speed">Voice Speed</label></th>
                            <td><input type="number" id="ttp_override_voice_speed" name="ttp_override_voice_speed" value="<?php echo esc_attr(get_option('ttp_override_voice_speed')); ?>" class="small-text" min="0.5" max="2.0" step="0.1"> <span class="description">0.5 to 2.0</span></td></tr>
                        <tr><th><label for="ttp_override_language">Language</label></th>
                            <td><select id="ttp_override_language" name="ttp_override_language" class="regular-text">
                                <option value="">-- All languages --</option>
                            </select><span id="ttp-language-loading" class="spinner"></span></td></tr>
                        <tr><th><label for="ttp_override_temperature">Temperature</label></th>
                            <td><input type="number" id="ttp_override_temperature" name="ttp_override_temperature" value="<?php echo esc_attr(get_option('ttp_override_temperature')); ?>" class="small-text" min="0" max="2" step="0.1"> <span class="description">0 to 2</span></td></tr>
                        <tr><th><label for="ttp_override_max_tokens">Max Tokens</label></th>
                            <td><input type="number" id="ttp_override_max_tokens" name="ttp_override_max_tokens" value="<?php echo esc_attr(get_option('ttp_override_max_tokens')); ?>" class="small-text" min="50" max="4000"></td></tr>
                        <tr><th><label for="ttp_override_max_call_duration">Max Call Duration</label></th>
                            <td><input type="number" id="ttp_override_max_call_duration" name="ttp_override_max_call_duration" value="<?php echo esc_attr(get_option('ttp_override_max_call_duration')); ?>" class="small-text" min="30" max="3600"> <span class="description">seconds</span></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Behavior -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Behavior <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_mode">Widget Mode</label></th>
                            <td><select id="ttp_mode" name="ttp_mode">
                                <option value="unified" <?php selected(get_option('ttp_mode', 'unified'), 'unified'); ?>>Unified (Voice + Text)</option>
                                <option value="voice-only" <?php selected(get_option('ttp_mode'), 'voice-only'); ?>>Voice Only</option>
                                <option value="text-only" <?php selected(get_option('ttp_mode'), 'text-only'); ?>>Text Only</option>
                            </select></td></tr>
                        <tr><th><label for="ttp_direction">Text Direction</label></th>
                            <td><select id="ttp_direction" name="ttp_direction">
                                <option value="ltr" <?php selected(get_option('ttp_direction', 'ltr'), 'ltr'); ?>>Left to Right</option>
                                <option value="rtl" <?php selected(get_option('ttp_direction'), 'rtl'); ?>>Right to Left</option>
                            </select></td></tr>
                        <tr><th>Auto Open</th>
                            <td><label><input type="checkbox" name="ttp_auto_open" value="1" <?php checked(get_option('ttp_auto_open'), '1'); ?>> Open widget on page load</label></td></tr>
                        <tr><th><label for="ttp_welcome_message">Welcome Message</label></th>
                            <td><input type="text" id="ttp_welcome_message" name="ttp_welcome_message" value="<?php echo esc_attr(get_option('ttp_welcome_message')); ?>" class="large-text" placeholder="Hello! How can I help you today?"></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Button Appearance -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Button Appearance <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_position">Position</label></th>
                            <td><select id="ttp_position" name="ttp_position">
                                <option value="bottom-right" <?php selected(get_option('ttp_position', 'bottom-right'), 'bottom-right'); ?>>Bottom Right</option>
                                <option value="bottom-left" <?php selected(get_option('ttp_position'), 'bottom-left'); ?>>Bottom Left</option>
                                <option value="top-right" <?php selected(get_option('ttp_position'), 'top-right'); ?>>Top Right</option>
                                <option value="top-left" <?php selected(get_option('ttp_position'), 'top-left'); ?>>Top Left</option>
                            </select></td></tr>
                        <tr><th><label for="ttp_button_size">Size</label></th>
                            <td><select id="ttp_button_size" name="ttp_button_size">
                                <option value="small" <?php selected(get_option('ttp_button_size'), 'small'); ?>>Small</option>
                                <option value="medium" <?php selected(get_option('ttp_button_size', 'medium'), 'medium'); ?>>Medium</option>
                                <option value="large" <?php selected(get_option('ttp_button_size'), 'large'); ?>>Large</option>
                                <option value="xl" <?php selected(get_option('ttp_button_size'), 'xl'); ?>>Extra Large</option>
                            </select></td></tr>
                        <tr><th><label for="ttp_button_shape">Shape</label></th>
                            <td><select id="ttp_button_shape" name="ttp_button_shape">
                                <option value="circle" <?php selected(get_option('ttp_button_shape', 'circle'), 'circle'); ?>>Circle</option>
                                <option value="rounded" <?php selected(get_option('ttp_button_shape'), 'rounded'); ?>>Rounded</option>
                                <option value="square" <?php selected(get_option('ttp_button_shape'), 'square'); ?>>Square</option>
                            </select></td></tr>
                        <tr><th><label for="ttp_button_bg_color">Background Color</label></th>
                            <td><input type="text" id="ttp_button_bg_color" name="ttp_button_bg_color" value="<?php echo esc_attr(get_option('ttp_button_bg_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                        <tr><th><label for="ttp_button_hover_color">Hover Color</label></th>
                            <td><input type="text" id="ttp_button_hover_color" name="ttp_button_hover_color" value="<?php echo esc_attr(get_option('ttp_button_hover_color')); ?>" class="ttp-color-picker" data-default-color="#F5F5F5"></td></tr>
                        <tr><th>Shadow</th>
                            <td><label><input type="checkbox" name="ttp_button_shadow" value="1" <?php checked(get_option('ttp_button_shadow', '1'), '1'); ?>> Enable button shadow</label></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Icon -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Button Icon <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_icon_type">Icon Type</label></th>
                            <td><select id="ttp_icon_type" name="ttp_icon_type">
                                <option value="custom" <?php selected(get_option('ttp_icon_type', 'custom'), 'custom'); ?>>Custom Image</option>
                                <option value="microphone" <?php selected(get_option('ttp_icon_type'), 'microphone'); ?>>Microphone</option>
                                <option value="emoji" <?php selected(get_option('ttp_icon_type'), 'emoji'); ?>>Emoji</option>
                                <option value="text" <?php selected(get_option('ttp_icon_type'), 'text'); ?>>Text</option>
                            </select></td></tr>
                        <tr class="ttp-icon-custom-row"><th><label for="ttp_icon_custom_image">Image URL</label></th>
                            <td><input type="url" id="ttp_icon_custom_image" name="ttp_icon_custom_image" value="<?php echo esc_attr(get_option('ttp_icon_custom_image')); ?>" class="large-text" placeholder="https://talktopc.com/logo192.png"></td></tr>
                        <tr class="ttp-icon-emoji-row" style="display:none;"><th><label for="ttp_icon_emoji">Emoji</label></th>
                            <td><input type="text" id="ttp_icon_emoji" name="ttp_icon_emoji" value="<?php echo esc_attr(get_option('ttp_icon_emoji', '🎤')); ?>" class="small-text"></td></tr>
                        <tr class="ttp-icon-text-row" style="display:none;"><th><label for="ttp_icon_text">Text</label></th>
                            <td><input type="text" id="ttp_icon_text" name="ttp_icon_text" value="<?php echo esc_attr(get_option('ttp_icon_text', 'AI')); ?>" class="small-text" maxlength="4"></td></tr>
                        <tr><th><label for="ttp_icon_size">Icon Size</label></th>
                            <td><select id="ttp_icon_size" name="ttp_icon_size">
                                <option value="small" <?php selected(get_option('ttp_icon_size'), 'small'); ?>>Small</option>
                                <option value="medium" <?php selected(get_option('ttp_icon_size', 'medium'), 'medium'); ?>>Medium</option>
                                <option value="large" <?php selected(get_option('ttp_icon_size'), 'large'); ?>>Large</option>
                                <option value="xl" <?php selected(get_option('ttp_icon_size'), 'xl'); ?>>Extra Large</option>
                            </select></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Panel -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Panel Settings <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_panel_width">Width (px)</label></th>
                            <td><input type="number" id="ttp_panel_width" name="ttp_panel_width" value="<?php echo esc_attr(get_option('ttp_panel_width', 350)); ?>" class="small-text" min="280" max="600"></td></tr>
                        <tr><th><label for="ttp_panel_height">Height (px)</label></th>
                            <td><input type="number" id="ttp_panel_height" name="ttp_panel_height" value="<?php echo esc_attr(get_option('ttp_panel_height', 500)); ?>" class="small-text" min="300" max="800"></td></tr>
                        <tr><th><label for="ttp_panel_border_radius">Border Radius (px)</label></th>
                            <td><input type="number" id="ttp_panel_border_radius" name="ttp_panel_border_radius" value="<?php echo esc_attr(get_option('ttp_panel_border_radius', 12)); ?>" class="small-text" min="0" max="30"></td></tr>
                        <tr><th><label for="ttp_panel_bg_color">Background Color</label></th>
                            <td><input type="text" id="ttp_panel_bg_color" name="ttp_panel_bg_color" value="<?php echo esc_attr(get_option('ttp_panel_bg_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Header -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Header Settings <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_header_title">Title</label></th>
                            <td><input type="text" id="ttp_header_title" name="ttp_header_title" value="<?php echo esc_attr(get_option('ttp_header_title')); ?>" class="regular-text" placeholder="Chat Assistant"></td></tr>
                        <tr><th><label for="ttp_header_bg_color">Background Color</label></th>
                            <td><input type="text" id="ttp_header_bg_color" name="ttp_header_bg_color" value="<?php echo esc_attr(get_option('ttp_header_bg_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                        <tr><th><label for="ttp_header_text_color">Text Color</label></th>
                            <td><input type="text" id="ttp_header_text_color" name="ttp_header_text_color" value="<?php echo esc_attr(get_option('ttp_header_text_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                        <tr><th>Close Button</th>
                            <td><label><input type="checkbox" name="ttp_header_show_close" value="1" <?php checked(get_option('ttp_header_show_close', '1'), '1'); ?>> Show close button</label></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Voice Interface -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Voice Interface Colors <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_voice_mic_color">Mic Button Color</label></th>
                            <td><input type="text" id="ttp_voice_mic_color" name="ttp_voice_mic_color" value="<?php echo esc_attr(get_option('ttp_voice_mic_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                        <tr><th><label for="ttp_voice_mic_active_color">Mic Active Color</label></th>
                            <td><input type="text" id="ttp_voice_mic_active_color" name="ttp_voice_mic_active_color" value="<?php echo esc_attr(get_option('ttp_voice_mic_active_color')); ?>" class="ttp-color-picker" data-default-color="#EF4444"></td></tr>
                        <tr><th><label for="ttp_voice_avatar_color">Avatar Color</label></th>
                            <td><input type="text" id="ttp_voice_avatar_color" name="ttp_voice_avatar_color" value="<?php echo esc_attr(get_option('ttp_voice_avatar_color')); ?>" class="ttp-color-picker" data-default-color="#667eea"></td></tr>
                        <tr><th><label for="ttp_voice_start_btn_color">Start Call Button</label></th>
                            <td><input type="text" id="ttp_voice_start_btn_color" name="ttp_voice_start_btn_color" value="<?php echo esc_attr(get_option('ttp_voice_start_btn_color')); ?>" class="ttp-color-picker" data-default-color="#667eea"></td></tr>
                        <tr><th><label for="ttp_voice_end_btn_color">End Call Button</label></th>
                            <td><input type="text" id="ttp_voice_end_btn_color" name="ttp_voice_end_btn_color" value="<?php echo esc_attr(get_option('ttp_voice_end_btn_color')); ?>" class="ttp-color-picker" data-default-color="#EF4444"></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Text Interface -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Text Interface <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_text_send_btn_color">Send Button Color</label></th>
                            <td><input type="text" id="ttp_text_send_btn_color" name="ttp_text_send_btn_color" value="<?php echo esc_attr(get_option('ttp_text_send_btn_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                        <tr><th><label for="ttp_text_input_placeholder">Input Placeholder</label></th>
                            <td><input type="text" id="ttp_text_input_placeholder" name="ttp_text_input_placeholder" value="<?php echo esc_attr(get_option('ttp_text_input_placeholder')); ?>" class="regular-text" placeholder="Type your message..."></td></tr>
                        <tr><th><label for="ttp_text_input_focus_color">Input Focus Color</label></th>
                            <td><input type="text" id="ttp_text_input_focus_color" name="ttp_text_input_focus_color" value="<?php echo esc_attr(get_option('ttp_text_input_focus_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Messages -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Message Colors <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr><th><label for="ttp_msg_user_bg">User Message Background</label></th>
                            <td><input type="text" id="ttp_msg_user_bg" name="ttp_msg_user_bg" value="<?php echo esc_attr(get_option('ttp_msg_user_bg')); ?>" class="ttp-color-picker" data-default-color="#E5E7EB"></td></tr>
                        <tr><th><label for="ttp_msg_agent_bg">Agent Message Background</label></th>
                            <td><input type="text" id="ttp_msg_agent_bg" name="ttp_msg_agent_bg" value="<?php echo esc_attr(get_option('ttp_msg_agent_bg')); ?>" class="ttp-color-picker" data-default-color="#F3F4F6"></td></tr>
                        <tr><th><label for="ttp_msg_text_color">Message Text Color</label></th>
                            <td><input type="text" id="ttp_msg_text_color" name="ttp_msg_text_color" value="<?php echo esc_attr(get_option('ttp_msg_text_color')); ?>" class="ttp-color-picker" data-default-color="#1F2937"></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Landing Screen -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Landing Screen <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <p class="description">Shown in unified mode when user first opens widget.</p>
                    <table class="form-table">
                        <tr><th><label for="ttp_landing_logo">Logo (emoji/text)</label></th>
                            <td><input type="text" id="ttp_landing_logo" name="ttp_landing_logo" value="<?php echo esc_attr(get_option('ttp_landing_logo')); ?>" class="small-text" placeholder="🤖"></td></tr>
                        <tr><th><label for="ttp_landing_title">Title</label></th>
                            <td><input type="text" id="ttp_landing_title" name="ttp_landing_title" value="<?php echo esc_attr(get_option('ttp_landing_title')); ?>" class="regular-text" placeholder="How would you like to chat?"></td></tr>
                        <tr><th><label for="ttp_landing_title_color">Title Color</label></th>
                            <td><input type="text" id="ttp_landing_title_color" name="ttp_landing_title_color" value="<?php echo esc_attr(get_option('ttp_landing_title_color')); ?>" class="ttp-color-picker" data-default-color="#1e293b"></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Custom CSS -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">Custom CSS <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
                <div class="ttp-collapsible-content">
                    <p class="description">Add custom CSS to further customize the widget.</p>
                    <textarea id="ttp_custom_css" name="ttp_custom_css" rows="8" class="large-text code" placeholder="#text-chat-button { /* your styles */ }"><?php echo esc_textarea(get_option('ttp_custom_css')); ?></textarea>
                </div>
            </div>
            
            <?php submit_button('Save Settings'); ?>
        </form>
        
        <?php if (!empty($current_agent_id)): ?>
        <div class="ttp-card ttp-status-card">
            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
            <strong>Widget is active!</strong> Visit your site to see the voice widget.
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <style>
        .ttp-settings-wrap { max-width: 800px; }
        .ttp-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; }
        .ttp-card h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .ttp-connected-status { display: flex; align-items: center; gap: 5px; }
        .ttp-email { color: #666; }
        .ttp-collapsible-header { cursor: pointer; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0 !important; border-bottom: none !important; }
        .ttp-collapsible-content { display: none; padding-top: 15px; border-top: 1px solid #eee; margin-top: 15px; }
        .ttp-collapsible.open .ttp-collapsible-content { display: block; }
        .ttp-collapsible.open .dashicons { transform: rotate(180deg); }
        .ttp-status-card { display: flex; align-items: center; gap: 8px; background: #d4edda; border-color: #c3e6cb; }
        .form-table th { width: 200px; }
    </style>
    
    <script>
    console.log('🔧 TTP Voice Widget v<?php echo TTP_VERSION; ?> loaded');
    var agentsData = {}; // Global for debugging - access via console
    var voicesData = []; // Global voices data
    var languageMap = {}; // Language code to name mapping
    jQuery(document).ready(function($) {
        console.log('🔧 Document ready');
        var ajaxNonce = '<?php echo wp_create_nonce("ttp_ajax_nonce"); ?>';
        var currentAgentId = '<?php echo esc_js($current_agent_id); ?>';
        var currentVoice = '<?php echo esc_js(get_option("ttp_override_voice")); ?>';
        var currentLanguage = '<?php echo esc_js(get_option("ttp_override_language")); ?>';
        console.log('🔧 Current saved values - agentId:', currentAgentId, 'voice:', currentVoice, 'language:', currentLanguage);
        
        $('.ttp-color-picker').wpColorPicker();
        $('.ttp-collapsible-header').on('click', function() { $(this).closest('.ttp-collapsible').toggleClass('open'); });
        
        $('#ttp_icon_type').on('change', function() {
            var type = $(this).val();
            $('.ttp-icon-custom-row, .ttp-icon-emoji-row, .ttp-icon-text-row').hide();
            if (type === 'custom') $('.ttp-icon-custom-row').show();
            else if (type === 'emoji') $('.ttp-icon-emoji-row').show();
            else if (type === 'text') $('.ttp-icon-text-row').show();
        }).trigger('change');
        
        <?php if ($is_connected): ?>
        // Load voices FIRST, then agents (to avoid race condition with dropdowns)
        fetchVoices(function() {
            fetchAgents();
        });
        <?php endif; ?>
        
        function fetchAgents() {
            $('#ttp-agents-loading').addClass('is-active');
            $.post(ajaxurl, { action: 'ttp_fetch_agents', nonce: ajaxNonce }, function(r) {
                var agents = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || [])) : [];
                $('#ttp-agents-loading').removeClass('is-active');
                
                // If no agents exist, create a default one
                if (agents.length === 0) {
                    createDefaultAgent();
                    return;
                }
                
                populateAgentsDropdown(agents);
            });
        }
        
        // Create default agent when none exist - with AI-generated prompt
        function createDefaultAgent() {
            $('#ttp-agents-loading').addClass('is-active');
            
            // Show generating message
            var $status = $('<div id="ttp-generating-status" style="margin-top: 10px; padding: 10px; background: #f0f6fc; border-left: 4px solid #2271b1; font-style: italic;">🤖 Creating your AI assistant and generating personalized prompt from your website content... This may take a minute.</div>');
            $('#ttp-agents-loading').after($status);
            
            $.post(ajaxurl, {
                action: 'ttp_create_agent',
                nonce: ajaxNonce,
                agent_name: '<?php echo esc_js(get_bloginfo("name")); ?>' + ' Assistant',
                auto_generate_prompt: 'true'
            }, function(r) {
                $('#ttp-agents-loading').removeClass('is-active');
                $('#ttp-generating-status').remove();
                
                if (r.success && r.data) {
                    // Get the agent from response (handle nested structure)
                    var agent = r.data.data || r.data;
                    var agentId = agent.agentId || agent.id;
                    
                    // Store in agentsData
                    agentsData[agentId] = agent;
                    
                    // Populate dropdown with the new agent
                    var $s = $('#ttp_agent_select').empty().append('<option value="">-- Select an agent --</option>');
                    $s.append('<option value="'+agentId+'" selected>'+agent.name+'</option>');
                    
                    // Set the agent ID and name in hidden fields
                    currentAgentId = agentId;
                    $('#ttp_agent_name').val(agent.name);
                    
                    // Populate all override fields with agent's settings
                    populateAgentSettings(agent);
                    
                    // Show create agent section
                    $('#ttp-create-agent').show();
                    
                    // Show success message
                    var $success = $('<div class="notice notice-success" style="margin: 10px 0; padding: 10px;"><p>✅ <strong>AI Assistant Created!</strong> Your agent has been configured with a personalized prompt based on your website content.</p></div>');
                    $('#ttp-create-agent').before($success);
                    setTimeout(function() { $success.fadeOut(function() { $(this).remove(); }); }, 8000);
                    
                    // Setup change handler
                    $s.off('change').on('change', function() {
                        var selectedId = $(this).val();
                        var selectedText = $(this).find('option:selected').text();
                        $('#ttp_agent_name').val(selectedText !== '-- Select an agent --' ? selectedText : '');
                        if (selectedId && agentsData[selectedId]) {
                            populateAgentSettings(agentsData[selectedId]);
                        }
                    });
                    
                    // Auto-save the settings so the agent is persisted in plugin settings
                    autoSaveSettings(agentId, agent.name);
                } else {
                    // Show error but still allow manual creation
                    var errorMsg = r.data?.message || 'Failed to auto-create agent';
                    var $error = $('<div class="notice notice-warning" style="margin: 10px 0; padding: 10px;"><p>⚠️ ' + errorMsg + ' - You can create an agent manually below.</p></div>');
                    $('#ttp-create-agent').before($error);
                    populateAgentsDropdown([]);
                }
            }).fail(function(xhr, status, error) {
                $('#ttp-agents-loading').removeClass('is-active');
                $('#ttp-generating-status').remove();
                
                // Show error but still allow manual creation
                var $error = $('<div class="notice notice-warning" style="margin: 10px 0; padding: 10px;"><p>⚠️ Could not auto-create agent: ' + error + ' - You can create an agent manually below.</p></div>');
                $('#ttp-create-agent').before($error);
                populateAgentsDropdown([]);
            });
        }
        
        // Fetch agents without auto-create (used after manual creation)
        function fetchAgentsWithoutAutoCreate() {
            $('#ttp-agents-loading').addClass('is-active');
            $.post(ajaxurl, { action: 'ttp_fetch_agents', nonce: ajaxNonce }, function(r) {
                var agents = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || [])) : [];
                $('#ttp-agents-loading').removeClass('is-active');
                populateAgentsDropdown(agents);
            });
        }
        
        // Populate agents dropdown
        function populateAgentsDropdown(agents) {
            // Store full agent data for later use
            agentsData = {};
            agents.forEach(function(a) {
                var id = a.agentId || a.id;
                agentsData[id] = a;
            });
            
            // Populate dropdown
            var $s = $('#ttp_agent_select').empty().append('<option value="">-- Select an agent --</option>');
            agents.forEach(function(a) {
                var id = a.agentId || a.id;
                $s.append('<option value="'+id+'"'+(id===currentAgentId?' selected':'')+'>'+a.name+'</option>');
            });
            
            // Handle agent selection change
            $s.off('change').on('change', function() {
                var selectedId = $(this).val();
                var selectedText = $(this).find('option:selected').text();
                $('#ttp_agent_name').val(selectedText !== '-- Select an agent --' ? selectedText : '');
                
                // Populate override fields with agent's current settings
                if (selectedId && agentsData[selectedId]) {
                    populateAgentSettings(agentsData[selectedId]);
                }
            });
            
            // Populate fields for initially selected agent
            var hasCurrentAgent = currentAgentId && currentAgentId !== '' && agentsData[currentAgentId];
            
            if (hasCurrentAgent) {
                populateAgentSettings(agentsData[currentAgentId]);
            }
            // If no agent currently selected but agents exist, auto-select first one and save
            else if (agents.length > 0) {
                var firstAgent = agents[0];
                var firstAgentId = firstAgent.agentId || firstAgent.id;
                var firstName = firstAgent.name;
                
                // Select the first agent in dropdown
                $s.val(firstAgentId);
                $('#ttp_agent_name').val(firstName);
                
                // Populate settings
                populateAgentSettings(firstAgent);
                
                // Auto-save the settings - pass values directly
                autoSaveSettings(firstAgentId, firstName);
                return; // Exit early, page will reload
            }
            
            $('#ttp-create-agent').show();
        }
        
        // Auto-save agent selection via AJAX
        function autoSaveSettings(agentId, agentName) {
            // Use passed parameters, or fall back to DOM values
            agentId = agentId || $('#ttp_agent_select').val();
            agentName = agentName || $('#ttp_agent_name').val();
            
            if (!agentId) {
                return;
            }
            
            // Show saving indicator immediately
            var $notice = $('<div class="notice notice-info" id="ttp-autosave-notice" style="margin: 10px 0; padding: 10px;"><p>⏳ Auto-saving agent selection...</p></div>');
            $('.wrap h1').after($notice);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ttp_save_agent_selection',
                    nonce: ajaxNonce,
                    agent_id: agentId,
                    agent_name: agentName
                },
                success: function(r) {
                    if (r.success) {
                        $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-success').html('<p>✅ Agent saved! Reloading...</p>');
                        setTimeout(function() {
                            window.location.reload();
                        }, 300);
                    } else {
                        $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-error').html('<p>❌ Failed to save agent. Please click Save Settings manually.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-error').html('<p>❌ Connection error. Please click Save Settings manually.</p>');
                }
            });
        }
        
        // Populate override fields with agent's configuration
        function populateAgentSettings(agent) {
            // Get configuration - handle different structures
            var config = {};
            
            // Check if configuration exists and has a value property (JSONB from database)
            if (agent.configuration && agent.configuration.value) {
                // Parse the JSON string - may be double-escaped
                try {
                    var parsed = JSON.parse(agent.configuration.value);
                    // Check if result is still a string (double-escaped JSON)
                    if (typeof parsed === 'string') {
                        config = JSON.parse(parsed);
                    } else {
                        config = parsed;
                    }
                } catch (e) {
                    config = agent.configuration;
                }
            } else if (agent.configuration && typeof agent.configuration === 'object') {
                config = agent.configuration;
            } else {
                config = agent;
            }
            
            // System Prompt
            var prompt = config.systemPrompt || config.prompt || '';
            $('#ttp_override_prompt').val(prompt);
            
            // First Message
            var firstMsg = config.firstMessage || '';
            $('#ttp_override_first_message').val(firstMsg);
            
            // Voice
            var voiceId = config.voiceId || '';
            console.log('🔧 Setting voice:', voiceId);
            $('#ttp_override_voice').val(voiceId);
            
            // Voice Speed - prefer voice's specific default over generic 1.0
            var voiceSpeed = config.voiceSpeed;
            var voiceDefaultSpeed = null;
            
            console.log('🔧 Voice speed from agent config:', voiceSpeed, 'type:', typeof voiceSpeed);
            
            // Look up voice's default speed
            if (voiceId && voicesData.length > 0) {
                var voice = voicesData.find(function(v) {
                    return (v.voiceId || v.id) === voiceId;
                });
                console.log('🔧 Found voice in voicesData:', voice ? voice.name : 'NOT FOUND');
                if (voice) {
                    console.log('🔧 Voice object:', JSON.stringify(voice));
                    voiceDefaultSpeed = voice.defaultVoiceSpeed;
                    console.log('🔧 Voice defaultVoiceSpeed:', voiceDefaultSpeed);
                }
            }
            
            // Use voice's specific default if:
            // - Agent has no voiceSpeed, OR
            // - Agent has generic default (1.0) but voice has different default
            if (voiceDefaultSpeed && (!voiceSpeed || voiceSpeed == 1 || voiceSpeed == 1.0)) {
                console.log('🔧 Replacing agent speed', voiceSpeed, 'with voice default', voiceDefaultSpeed);
                voiceSpeed = voiceDefaultSpeed;
            }
            
            console.log('🔧 Final voice speed to display:', voiceSpeed);
            $('#ttp_override_voice_speed').val(voiceSpeed || '');
            
            // Language - use the full language code (e.g., "en-US")
            var lang = config.agentLanguage || config.language || '';
            console.log('🔧 Setting language:', lang);
            $('#ttp_override_language').val(lang);
            
            // Filter voices by selected language and re-select voice
            if (lang && typeof populateVoicesDropdown === 'function') {
                populateVoicesDropdown(lang);
                // Re-select the voice after filtering
                if (voiceId) {
                    $('#ttp_override_voice').val(voiceId);
                }
            }
            
            // Temperature
            var temp = config.temperature || '';
            $('#ttp_override_temperature').val(temp);
            
            // Max Tokens
            var maxTokens = config.maxTokens || '';
            $('#ttp_override_max_tokens').val(maxTokens);
            
            // Max Call Duration
            var maxDuration = config.maxCallDuration || '';
            $('#ttp_override_max_call_duration').val(maxDuration);
        }
        
        function fetchVoices(callback) {
            console.log('🎤 fetchVoices() called');
            $('#ttp-voice-loading, #ttp-language-loading').addClass('is-active');
            $.post(ajaxurl, { action: 'ttp_fetch_voices', nonce: ajaxNonce }, function(r) {
                $('#ttp-voice-loading, #ttp-language-loading').removeClass('is-active');
                voicesData = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || [])) : [];
                console.log('🎤 Voices loaded:', voicesData.length);
                
                // Language name mapping
                var langNames = {
                    'en': 'English', 'en-US': 'English (US)', 'en-GB': 'English (UK)',
                    'es': 'Spanish', 'es-ES': 'Spanish (Spain)', 'es-MX': 'Spanish (Mexico)',
                    'fr': 'French', 'fr-FR': 'French (France)', 'fr-CA': 'French (Canada)',
                    'de': 'German', 'de-DE': 'German',
                    'he': 'Hebrew', 'he-IL': 'Hebrew',
                    'ar': 'Arabic', 'ar-SA': 'Arabic',
                    'zh': 'Chinese', 'zh-CN': 'Chinese (Simplified)', 'zh-TW': 'Chinese (Traditional)',
                    'ja': 'Japanese', 'ja-JP': 'Japanese',
                    'pt': 'Portuguese', 'pt-BR': 'Portuguese (Brazil)', 'pt-PT': 'Portuguese (Portugal)',
                    'ru': 'Russian', 'ru-RU': 'Russian',
                    'it': 'Italian', 'it-IT': 'Italian',
                    'nl': 'Dutch', 'nl-NL': 'Dutch',
                    'ko': 'Korean', 'ko-KR': 'Korean',
                    'pl': 'Polish', 'pl-PL': 'Polish',
                    'tr': 'Turkish', 'tr-TR': 'Turkish',
                    'hi': 'Hindi', 'hi-IN': 'Hindi',
                    'sv': 'Swedish', 'sv-SE': 'Swedish',
                    'da': 'Danish', 'da-DK': 'Danish',
                    'fi': 'Finnish', 'fi-FI': 'Finnish',
                    'no': 'Norwegian', 'nb-NO': 'Norwegian',
                    'uk': 'Ukrainian', 'uk-UA': 'Ukrainian',
                    'cs': 'Czech', 'cs-CZ': 'Czech',
                    'el': 'Greek', 'el-GR': 'Greek',
                    'ro': 'Romanian', 'ro-RO': 'Romanian',
                    'hu': 'Hungarian', 'hu-HU': 'Hungarian',
                    'th': 'Thai', 'th-TH': 'Thai',
                    'vi': 'Vietnamese', 'vi-VN': 'Vietnamese',
                    'id': 'Indonesian', 'id-ID': 'Indonesian',
                    'ms': 'Malay', 'ms-MY': 'Malay'
                };
                
                // Extract unique languages from all voices
                languageMap = {};
                voicesData.forEach(function(v) {
                    var langs = v.languages || [];
                    langs.forEach(function(lang) {
                        if (!languageMap[lang]) {
                            languageMap[lang] = langNames[lang] || lang;
                        }
                    });
                });
                
                // Populate language dropdown
                var $langSelect = $('#ttp_override_language');
                $langSelect.find('option:not(:first)').remove(); // Keep "All languages" option
                
                // Sort languages by name
                var sortedLangs = Object.keys(languageMap).sort(function(a, b) {
                    return languageMap[a].localeCompare(languageMap[b]);
                });
                
                sortedLangs.forEach(function(code) {
                    $langSelect.append('<option value="' + code + '"' + (code === currentLanguage ? ' selected' : '') + '>' + languageMap[code] + '</option>');
                });
                
                // Populate voices (filtered by current language if set)
                populateVoicesDropdown(currentLanguage);
                
                // Language change handler - filter voices
                $langSelect.off('change').on('change', function() {
                    var selectedLang = $(this).val();
                    populateVoicesDropdown(selectedLang);
                });
                
                // Call callback if provided (for chaining)
                if (typeof callback === 'function') {
                    console.log('🎤 Voices ready, calling callback');
                    callback();
                }
            });
        }
        
        // Populate voices dropdown, optionally filtered by language
        function populateVoicesDropdown(filterLang) {
            var $voiceSelect = $('#ttp_override_voice');
            $voiceSelect.find('option:not(:first)').remove(); // Keep "Use agent default" option
            
            var filteredVoices = voicesData;
            if (filterLang) {
                filteredVoices = voicesData.filter(function(v) {
                    var langs = v.languages || [];
                    return langs.some(function(l) {
                        // Match exact or base language (e.g., "en" matches "en-US")
                        return l === filterLang || l.startsWith(filterLang + '-') || filterLang.startsWith(l + '-');
                    });
                });
            }
            
            filteredVoices.forEach(function(v) {
                var id = v.voiceId || v.id;
                var defaultSpeed = v.defaultVoiceSpeed || 1.0;
                $voiceSelect.append('<option value="' + id + '" data-default-speed="' + defaultSpeed + '"' + (id === currentVoice ? ' selected' : '') + '>' + v.name + '</option>');
            });
            
            // If current voice is not in filtered list, clear selection
            if (currentVoice && $voiceSelect.find('option[value="' + currentVoice + '"]').length === 0) {
                $voiceSelect.val('');
            }
        }
        
        // Voice change handler - update voice speed to voice's default
        $('#ttp_override_voice').on('change', function() {
            var $selected = $(this).find('option:selected');
            var defaultSpeed = $selected.data('default-speed');
            if (defaultSpeed) {
                $('#ttp_override_voice_speed').val(defaultSpeed);
                console.log('🎤 Voice changed, setting default speed:', defaultSpeed);
            }
        });
        
        $('#ttp-show-create-agent').on('click', function() { $(this).hide(); $('#ttp-create-agent-form').show(); });
        $('#ttp-cancel-create-agent').on('click', function() { $('#ttp-create-agent-form').hide(); $('#ttp-show-create-agent').show(); });
        $('#ttp-create-agent-btn').on('click', function() {
            var name = $('#ttp-new-agent-name').val().trim();
            if (!name) { alert('Enter agent name'); return; }
            var $btn = $(this).prop('disabled', true).text('Creating...');
            $.post(ajaxurl, { action: 'ttp_create_agent', nonce: ajaxNonce, agent_name: name }, function(r) {
                if (r.success) {
                    var agent = r.data.data || r.data;
                    var agentId = agent.agentId || agent.id;
                    
                    // Store agent data
                    agentsData[agentId] = agent;
                    currentAgentId = agentId;
                    
                    // Add to dropdown and select it
                    $('#ttp_agent_select').append('<option value="'+agentId+'">'+agent.name+'</option>').val(agentId);
                    $('#ttp_agent_name').val(agent.name);
                    
                    // Populate settings from returned agent
                    populateAgentSettings(agent);
                    
                    // Clean up form
                    $('#ttp-new-agent-name').val('');
                    $('#ttp-create-agent-form').hide();
                    $('#ttp-show-create-agent').show();
                }
                else { alert('Error: ' + (r.data?.message || 'Failed')); }
                $btn.prop('disabled', false).text('Create');
            });
        });
        
        // Generate prompt from site content
        $('#ttp-generate-prompt-btn').on('click', function() {
            var $btn = $(this);
            var $status = $('#ttp-generate-prompt-status');
            var $textarea = $('#ttp_override_prompt');
            
            // Check if textarea has content
            if ($textarea.val().trim() !== '') {
                if (!confirm('This will replace the current system prompt. Continue?')) {
                    return;
                }
            }
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; animation: spin 1s linear infinite;"></span> Generating...');
            $status.text('Scanning website content...');
            
            $.post(ajaxurl, { action: 'ttp_generate_prompt', nonce: ajaxNonce }, function(r) {
                if (r.success) {
                    $textarea.val(r.data.prompt);
                    
                    var stats = r.data.stats;
                    var statsText = 'Generated from: ';
                    var parts = [];
                    if (stats.pages > 0) parts.push(stats.pages + ' pages');
                    if (stats.posts > 0) parts.push(stats.posts + ' posts');
                    if (stats.products > 0) parts.push(stats.products + ' products');
                    if (stats.menu_items > 0) parts.push(stats.menu_items + ' menu items');
                    statsText += parts.join(', ');
                    
                    $status.html('<span style="color: green;">✓ ' + statsText + '</span>');
                    
                    // Highlight the textarea briefly
                    $textarea.css('background-color', '#e8f5e9');
                    setTimeout(function() {
                        $textarea.css('background-color', '');
                    }, 2000);
                } else {
                    $status.html('<span style="color: red;">Error: ' + (r.data?.message || 'Failed to generate') + '</span>');
                }
                
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-site" style="vertical-align: middle; margin-right: 4px;"></span> Generate from Site Content');
            }).fail(function() {
                $status.html('<span style="color: red;">Error: Request failed</span>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-site" style="vertical-align: middle; margin-right: 4px;"></span> Generate from Site Content');
            });
        });
    });
    </script>
    
    <style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .dashicons.spin {
        display: inline-block;
    }
    </style>
    <?php
}

// =============================================================================
// FRONTEND WIDGET
// =============================================================================

add_action('wp_enqueue_scripts', function() {
    $api_key = get_option('ttp_api_key');
    $agent_id = get_option('ttp_agent_id');
    if (empty($api_key) || empty($agent_id)) return;
    
    wp_enqueue_script('ttp-agent-widget', 'https://cdn.talktopc.com/agent-widget.js', [], TTP_VERSION, true);
    
    $config = [];
    
    // Direction & Language
    if ($v = get_option('ttp_direction')) $config['direction'] = $v;
    if ($v = get_option('ttp_override_language')) $config['language'] = $v;
    if ($v = get_option('ttp_position')) $config['position'] = $v;
    
    // Button
    $button = [];
    if ($v = get_option('ttp_button_size')) $button['size'] = $v;
    if ($v = get_option('ttp_button_shape')) $button['shape'] = $v;
    if ($v = get_option('ttp_button_bg_color')) $button['backgroundColor'] = $v;
    if ($v = get_option('ttp_button_hover_color')) $button['hoverColor'] = $v;
    $button['shadow'] = get_option('ttp_button_shadow', '1') === '1';
    if (!empty($button)) $config['button'] = $button;
    
    // Icon
    $icon = [];
    if ($v = get_option('ttp_icon_type')) $icon['type'] = $v;
    if ($v = get_option('ttp_icon_custom_image')) $icon['customImage'] = $v;
    if ($v = get_option('ttp_icon_emoji')) $icon['emoji'] = $v;
    if ($v = get_option('ttp_icon_text')) $icon['text'] = $v;
    if ($v = get_option('ttp_icon_size')) $icon['size'] = $v;
    if (!empty($icon)) $config['icon'] = $icon;
    
    // Panel
    $panel = [];
    if ($v = get_option('ttp_panel_width')) $panel['width'] = intval($v);
    if ($v = get_option('ttp_panel_height')) $panel['height'] = intval($v);
    if ($v = get_option('ttp_panel_border_radius')) $panel['borderRadius'] = intval($v);
    if ($v = get_option('ttp_panel_bg_color')) $panel['backgroundColor'] = $v;
    if (!empty($panel)) $config['panel'] = $panel;
    
    // Header
    $header = [];
    if ($v = get_option('ttp_header_title')) $header['title'] = $v;
    if ($v = get_option('ttp_header_bg_color')) $header['backgroundColor'] = $v;
    if ($v = get_option('ttp_header_text_color')) $header['textColor'] = $v;
    $header['showCloseButton'] = get_option('ttp_header_show_close', '1') === '1';
    if (!empty($header)) $config['header'] = $header;
    
    // Voice (with audio fix)
    $voice = ['outputContainer'=>'raw','outputEncoding'=>'pcm','outputSampleRate'=>44100,'outputChannels'=>1,'outputBitDepth'=>16];
    if ($v = get_option('ttp_voice_mic_color')) $voice['micButtonColor'] = $v;
    if ($v = get_option('ttp_voice_mic_active_color')) $voice['micButtonActiveColor'] = $v;
    if ($v = get_option('ttp_voice_avatar_color')) { $voice['avatarBackgroundColor'] = $v; $voice['avatarActiveBackgroundColor'] = $v; }
    if ($v = get_option('ttp_voice_start_btn_color')) $voice['startCallButtonColor'] = $v;
    if ($v = get_option('ttp_voice_end_btn_color')) $voice['endCallButtonColor'] = $v;
    $config['voice'] = $voice;
    
    // Text
    $text = [];
    if ($v = get_option('ttp_text_send_btn_color')) $text['sendButtonColor'] = $v;
    if ($v = get_option('ttp_text_input_placeholder')) $text['inputPlaceholder'] = $v;
    if ($v = get_option('ttp_text_input_focus_color')) $text['inputFocusColor'] = $v;
    if (!empty($text)) $config['text'] = $text;
    
    // Messages
    $messages = [];
    if ($v = get_option('ttp_msg_user_bg')) $messages['userBackgroundColor'] = $v;
    if ($v = get_option('ttp_msg_agent_bg')) $messages['agentBackgroundColor'] = $v;
    if ($v = get_option('ttp_msg_text_color')) $messages['textColor'] = $v;
    if (!empty($messages)) $config['messages'] = $messages;
    
    // Landing
    $landing = [];
    if ($v = get_option('ttp_landing_logo')) $landing['logo'] = $v;
    if ($v = get_option('ttp_landing_title')) $landing['title'] = $v;
    if ($v = get_option('ttp_landing_title_color')) $landing['titleColor'] = $v;
    if (!empty($landing)) $config['landing'] = $landing;
    
    // Behavior
    $behavior = [];
    if ($v = get_option('ttp_mode')) $behavior['mode'] = $v;
    if (get_option('ttp_auto_open') === '1') $behavior['autoOpen'] = true;
    if ($v = get_option('ttp_welcome_message')) { $behavior['showWelcomeMessage'] = true; $behavior['welcomeMessage'] = $v; }
    if (!empty($behavior)) $config['behavior'] = $behavior;
    
    // Agent Overrides
    $override = [];
    if ($v = get_option('ttp_override_prompt')) $override['prompt'] = $v;
    if ($v = get_option('ttp_override_first_message')) $override['firstMessage'] = $v;
    if ($v = get_option('ttp_override_voice')) $override['voiceId'] = $v;
    if ($v = get_option('ttp_override_voice_speed')) $override['voiceSpeed'] = floatval($v);
    if ($v = get_option('ttp_override_language')) $override['language'] = $v;
    if ($v = get_option('ttp_override_temperature')) $override['temperature'] = floatval($v);
    if ($v = get_option('ttp_override_max_tokens')) $override['maxTokens'] = intval($v);
    if ($v = get_option('ttp_override_max_call_duration')) $override['maxCallDuration'] = intval($v);
    if (!empty($override)) $config['agentSettingsOverride'] = $override;
    
    // Custom CSS
    if ($css = get_option('ttp_custom_css')) $config['customStyles'] = $css;
    
    $nonce = wp_create_nonce('ttp_widget_nonce');
    
    $script = sprintf('(function(){var c=%s,u=%s,n=%s;function f(){var x=new XMLHttpRequest();x.open("POST",u,true);x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");x.onreadystatechange=function(){if(x.readyState===4&&x.status===200){try{var r=JSON.parse(x.responseText);if(r.success&&r.data.signedUrl){c.signedUrl=r.data.signedUrl;i();}}catch(e){console.error("TTP Widget error",e);}}};x.send("action=ttp_get_signed_url&nonce="+n);}function i(){if(typeof TTPAgentSDK!=="undefined"&&TTPAgentSDK.TTPChatWidget){new TTPAgentSDK.TTPChatWidget(c);}else{setTimeout(i,100);}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",f);}else{f();}})();',
        wp_json_encode($config), wp_json_encode(admin_url('admin-ajax.php')), wp_json_encode($nonce));
    
    wp_add_inline_script('ttp-agent-widget', $script);
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    array_unshift($links, '<a href="' . admin_url('admin.php?page=ttp-voice-widget') . '">Settings</a>');
    return $links;
});