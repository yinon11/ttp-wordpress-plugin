<?php
/**
 * Plugin Name: TalkToPC Voice Widget
 * Description: Add AI voice conversations to your WordPress site. Let visitors talk to your AI agent with natural voice interactions.
 * Version: 1.1.3
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
define('TTP_VERSION', '1.1.3');

// =============================================================================
// ADMIN MENU & SETTINGS
// =============================================================================

add_action('admin_menu', function() {
    add_options_page(
        'TalkToPC Voice Widget',
        'TalkToPC Widget',
        'manage_options',
        'ttp-voice-widget',
        'ttp_settings_page'
    );
});

// Register all settings
add_action('admin_init', function() {
    // Connection settings - separate group, not part of the form
    // These are set via OAuth callback and should not be affected by form submission
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
    
    // Widget appearance
    register_setting('ttp_settings', 'ttp_position', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'bottom-right']);
    register_setting('ttp_settings', 'ttp_mode', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'unified']);
    register_setting('ttp_settings', 'ttp_direction', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'ltr']);
    register_setting('ttp_settings', 'ttp_button_size', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'medium']);
    register_setting('ttp_settings', 'ttp_button_shape', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'circle']);
    register_setting('ttp_settings', 'ttp_button_bg_color', ['sanitize_callback' => 'sanitize_hex_color', 'default' => '#FFFFFF']);
    register_setting('ttp_settings', 'ttp_header_title', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('ttp_settings', 'ttp_header_bg_color', ['sanitize_callback' => 'sanitize_hex_color', 'default' => '#7C3AED']);
    register_setting('ttp_settings', 'ttp_header_text_color', ['sanitize_callback' => 'sanitize_hex_color', 'default' => '#FFFFFF']);
});

function ttp_sanitize_float($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// =============================================================================
// OAUTH CALLBACK HANDLER
// =============================================================================

add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ttp-voice-widget') return;
    
    // Handle OAuth callback
    if (isset($_GET['api_key']) && isset($_GET['state'])) {
        if (!wp_verify_nonce($_GET['state'], 'ttp_connect')) {
            add_settings_error('ttp_settings', 'invalid_state', 'Security check failed. Please try again.', 'error');
            return;
        }
        
        // Save credentials
        update_option('ttp_api_key', sanitize_text_field($_GET['api_key']));
        
        if (isset($_GET['app_id'])) {
            update_option('ttp_app_id', sanitize_text_field($_GET['app_id']));
        }
        if (isset($_GET['email'])) {
            update_option('ttp_user_email', sanitize_email($_GET['email']));
        }
        
        // Redirect to clean URL
        wp_redirect(admin_url('options-general.php?page=ttp-voice-widget&connected=1'));
        exit;
    }
    
    // Handle disconnect
    if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'ttp_disconnect')) {
            add_settings_error('ttp_settings', 'invalid_nonce', 'Security check failed.', 'error');
            return;
        }
        
        // Clear all connection data
        delete_option('ttp_api_key');
        delete_option('ttp_app_id');
        delete_option('ttp_user_email');
        delete_option('ttp_agent_id');
        delete_option('ttp_agent_name');
        
        wp_redirect(admin_url('options-general.php?page=ttp-voice-widget&disconnected=1'));
        exit;
    }
});

// =============================================================================
// AJAX HANDLERS
// =============================================================================

// Fetch agents
add_action('wp_ajax_ttp_fetch_agents', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) {
        wp_send_json_error(['message' => 'Not connected']);
    }
    
    $response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/agents', [
        'headers' => [
            'X-API-Key' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    wp_send_json_success($body);
});

// Fetch voices
add_action('wp_ajax_ttp_fetch_voices', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) {
        wp_send_json_error(['message' => 'Not connected']);
    }
    
    $response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/voices', [
        'headers' => [
            'X-API-Key' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    wp_send_json_success($body);
});

// Create new agent
add_action('wp_ajax_ttp_create_agent', function() {
    check_ajax_referer('ttp_ajax_nonce', 'nonce');
    
    $api_key = get_option('ttp_api_key');
    if (empty($api_key)) {
        wp_send_json_error(['message' => 'Not connected']);
    }
    
    $agent_name = isset($_POST['agent_name']) ? sanitize_text_field($_POST['agent_name']) : '';
    if (empty($agent_name)) {
        wp_send_json_error(['message' => 'Agent name is required']);
    }
    
    $response = wp_remote_post(TTP_API_URL . '/api/public/wordpress/agents', [
        'headers' => [
            'X-API-Key' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => wp_json_encode([
            'name' => $agent_name,
            'site_url' => home_url(),
            'site_name' => get_bloginfo('name')
        ]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    wp_send_json_success($body);
});

// =============================================================================
// ADMIN SETTINGS PAGE
// =============================================================================

function ttp_settings_page() {
    $is_connected = !empty(get_option('ttp_api_key'));
    $user_email = get_option('ttp_user_email', '');
    $current_agent_id = get_option('ttp_agent_id', '');
    $current_agent_name = get_option('ttp_agent_name', '');
    
    // Generate connect URL
    $state = wp_create_nonce('ttp_connect');
    $redirect_uri = admin_url('options-general.php?page=ttp-voice-widget');
    $connect_url = TTP_CONNECT_URL . '?' . http_build_query([
        'redirect_uri' => $redirect_uri,
        'state' => $state,
        'site_url' => home_url(),
        'site_name' => get_bloginfo('name')
    ]);
    
    // Disconnect URL
    $disconnect_url = wp_nonce_url(
        admin_url('options-general.php?page=ttp-voice-widget&action=disconnect'),
        'ttp_disconnect'
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('TalkToPC Voice Widget', 'ttp-voice-widget'); ?></h1>
        
        <?php settings_errors(); ?>
        
        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings saved successfully!', 'ttp-voice-widget'); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['connected'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Successfully connected to TalkToPC!', 'ttp-voice-widget'); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['disconnected'])): ?>
            <div class="notice notice-info is-dismissible">
                <p><?php esc_html_e('Disconnected from TalkToPC.', 'ttp-voice-widget'); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Module 1: Connection -->
        <div class="ttp-card">
            <h2><?php esc_html_e('Account Connection', 'ttp-voice-widget'); ?></h2>
            
            <?php if ($is_connected): ?>
                <div class="ttp-connected-status">
                    <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                    <strong><?php esc_html_e('Connected', 'ttp-voice-widget'); ?></strong>
                    <?php if ($user_email): ?>
                        <span class="ttp-email">(<?php echo esc_html($user_email); ?>)</span>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($disconnect_url); ?>" class="button button-link-delete" style="margin-left: 10px;">
                        <?php esc_html_e('Disconnect', 'ttp-voice-widget'); ?>
                    </a>
                </div>
            <?php else: ?>
                <p><?php esc_html_e('Connect your TalkToPC account to get started.', 'ttp-voice-widget'); ?></p>
                <a href="<?php echo esc_url($connect_url); ?>" class="button button-primary button-hero">
                    <?php esc_html_e('Connect to TalkToPC', 'ttp-voice-widget'); ?>
                </a>
                <p class="description" style="margin-top: 10px;">
                    <?php esc_html_e("Don't have an account? You'll be able to create one.", 'ttp-voice-widget'); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <?php if ($is_connected): ?>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <!-- Module 2: Agent Selection -->
            <div class="ttp-card">
                <h2><?php esc_html_e('Select Agent', 'ttp-voice-widget'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ttp_agent_select"><?php esc_html_e('Agent', 'ttp-voice-widget'); ?></label></th>
                        <td>
                            <select id="ttp_agent_select" name="ttp_agent_id" class="regular-text">
                                <option value=""><?php esc_html_e('Loading agents...', 'ttp-voice-widget'); ?></option>
                            </select>
                            <span class="spinner" id="ttp-agents-loading" style="float: none; margin: 0 0 0 5px;"></span>
                            <input type="hidden" name="ttp_agent_name" id="ttp_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
                        </td>
                    </tr>
                </table>
                
                <div id="ttp-create-agent" style="margin-top: 5px; display: none;">
                    <button type="button" class="button" id="ttp-show-create-agent">
                        <?php esc_html_e('+ Create New Agent', 'ttp-voice-widget'); ?>
                    </button>
                    <div id="ttp-create-agent-form" style="display: none; margin-top: 10px;">
                        <input type="text" id="ttp-new-agent-name" placeholder="<?php esc_attr_e('Agent name', 'ttp-voice-widget'); ?>" class="regular-text">
                        <button type="button" class="button button-primary" id="ttp-create-agent-btn">
                            <?php esc_html_e('Create', 'ttp-voice-widget'); ?>
                        </button>
                        <button type="button" class="button" id="ttp-cancel-create-agent">
                            <?php esc_html_e('Cancel', 'ttp-voice-widget'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Module 3: Agent Override Settings -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">
                    <?php esc_html_e('Agent Settings (Override)', 'ttp-voice-widget'); ?>
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </h2>
                <div class="ttp-collapsible-content">
                    <p class="description"><?php esc_html_e('These settings override the agent defaults. Leave empty to use agent defaults.', 'ttp-voice-widget'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ttp_override_prompt"><?php esc_html_e('System Prompt', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <textarea id="ttp_override_prompt" name="ttp_override_prompt" rows="4" class="large-text"><?php echo esc_textarea(get_option('ttp_override_prompt')); ?></textarea>
                                <p class="description"><?php esc_html_e('Override the agent system prompt', 'ttp-voice-widget'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_override_first_message"><?php esc_html_e('First Message', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="text" id="ttp_override_first_message" name="ttp_override_first_message" value="<?php echo esc_attr(get_option('ttp_override_first_message')); ?>" class="large-text" placeholder="Hello! How can I help you today?">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_override_voice"><?php esc_html_e('Voice', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_override_voice" name="ttp_override_voice" class="regular-text">
                                    <option value=""><?php esc_html_e('-- Use agent default --', 'ttp-voice-widget'); ?></option>
                                </select>
                                <span id="ttp-voice-loading" class="spinner" style="float: none;"></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_override_voice_speed"><?php esc_html_e('Voice Speed', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="number" id="ttp_override_voice_speed" name="ttp_override_voice_speed" value="<?php echo esc_attr(get_option('ttp_override_voice_speed')); ?>" class="small-text" min="0.5" max="2.0" step="0.1" placeholder="1.0">
                                <p class="description"><?php esc_html_e('0.5 to 2.0 (1.0 = normal speed)', 'ttp-voice-widget'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_override_language"><?php esc_html_e('Language', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_override_language" name="ttp_override_language">
                                    <option value=""><?php esc_html_e('-- Use agent default --', 'ttp-voice-widget'); ?></option>
                                    <option value="en" <?php selected(get_option('ttp_override_language'), 'en'); ?>>English</option>
                                    <option value="he" <?php selected(get_option('ttp_override_language'), 'he'); ?>>עברית (Hebrew)</option>
                                    <option value="es" <?php selected(get_option('ttp_override_language'), 'es'); ?>>Español</option>
                                    <option value="fr" <?php selected(get_option('ttp_override_language'), 'fr'); ?>>Français</option>
                                    <option value="de" <?php selected(get_option('ttp_override_language'), 'de'); ?>>Deutsch</option>
                                    <option value="ar" <?php selected(get_option('ttp_override_language'), 'ar'); ?>>العربية (Arabic)</option>
                                    <option value="ru" <?php selected(get_option('ttp_override_language'), 'ru'); ?>>Русский</option>
                                    <option value="zh" <?php selected(get_option('ttp_override_language'), 'zh'); ?>>中文</option>
                                    <option value="ja" <?php selected(get_option('ttp_override_language'), 'ja'); ?>>日本語</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_override_temperature"><?php esc_html_e('Temperature', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="number" id="ttp_override_temperature" name="ttp_override_temperature" value="<?php echo esc_attr(get_option('ttp_override_temperature')); ?>" class="small-text" min="0" max="2" step="0.1" placeholder="0.8">
                                <p class="description"><?php esc_html_e('0 to 2.0 (higher = more creative)', 'ttp-voice-widget'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Module 4: Widget Appearance -->
            <div class="ttp-card ttp-collapsible">
                <h2 class="ttp-collapsible-header">
                    <?php esc_html_e('Widget Appearance', 'ttp-voice-widget'); ?>
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </h2>
                <div class="ttp-collapsible-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ttp_mode"><?php esc_html_e('Widget Mode', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_mode" name="ttp_mode">
                                    <option value="unified" <?php selected(get_option('ttp_mode', 'unified'), 'unified'); ?>><?php esc_html_e('Unified (Voice & Text)', 'ttp-voice-widget'); ?></option>
                                    <option value="voice-only" <?php selected(get_option('ttp_mode'), 'voice-only'); ?>><?php esc_html_e('Voice Only', 'ttp-voice-widget'); ?></option>
                                    <option value="text-only" <?php selected(get_option('ttp_mode'), 'text-only'); ?>><?php esc_html_e('Text Only', 'ttp-voice-widget'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_position"><?php esc_html_e('Position', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_position" name="ttp_position">
                                    <option value="bottom-right" <?php selected(get_option('ttp_position', 'bottom-right'), 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'ttp-voice-widget'); ?></option>
                                    <option value="bottom-left" <?php selected(get_option('ttp_position'), 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'ttp-voice-widget'); ?></option>
                                    <option value="top-right" <?php selected(get_option('ttp_position'), 'top-right'); ?>><?php esc_html_e('Top Right', 'ttp-voice-widget'); ?></option>
                                    <option value="top-left" <?php selected(get_option('ttp_position'), 'top-left'); ?>><?php esc_html_e('Top Left', 'ttp-voice-widget'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_direction"><?php esc_html_e('Text Direction', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_direction" name="ttp_direction">
                                    <option value="ltr" <?php selected(get_option('ttp_direction', 'ltr'), 'ltr'); ?>><?php esc_html_e('Left to Right', 'ttp-voice-widget'); ?></option>
                                    <option value="rtl" <?php selected(get_option('ttp_direction'), 'rtl'); ?>><?php esc_html_e('Right to Left', 'ttp-voice-widget'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_button_size"><?php esc_html_e('Button Size', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_button_size" name="ttp_button_size">
                                    <option value="small" <?php selected(get_option('ttp_button_size'), 'small'); ?>><?php esc_html_e('Small', 'ttp-voice-widget'); ?></option>
                                    <option value="medium" <?php selected(get_option('ttp_button_size', 'medium'), 'medium'); ?>><?php esc_html_e('Medium', 'ttp-voice-widget'); ?></option>
                                    <option value="large" <?php selected(get_option('ttp_button_size'), 'large'); ?>><?php esc_html_e('Large', 'ttp-voice-widget'); ?></option>
                                    <option value="xl" <?php selected(get_option('ttp_button_size'), 'xl'); ?>><?php esc_html_e('Extra Large', 'ttp-voice-widget'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_button_shape"><?php esc_html_e('Button Shape', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <select id="ttp_button_shape" name="ttp_button_shape">
                                    <option value="circle" <?php selected(get_option('ttp_button_shape', 'circle'), 'circle'); ?>><?php esc_html_e('Circle', 'ttp-voice-widget'); ?></option>
                                    <option value="square" <?php selected(get_option('ttp_button_shape'), 'square'); ?>><?php esc_html_e('Square', 'ttp-voice-widget'); ?></option>
                                    <option value="rounded" <?php selected(get_option('ttp_button_shape'), 'rounded'); ?>><?php esc_html_e('Rounded', 'ttp-voice-widget'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_button_bg_color"><?php esc_html_e('Button Color', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="color" id="ttp_button_bg_color" name="ttp_button_bg_color" value="<?php echo esc_attr(get_option('ttp_button_bg_color', '#FFFFFF')); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_header_title"><?php esc_html_e('Header Title', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="text" id="ttp_header_title" name="ttp_header_title" value="<?php echo esc_attr(get_option('ttp_header_title')); ?>" class="regular-text" placeholder="Chat Assistant">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_header_bg_color"><?php esc_html_e('Header Background', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="color" id="ttp_header_bg_color" name="ttp_header_bg_color" value="<?php echo esc_attr(get_option('ttp_header_bg_color', '#7C3AED')); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ttp_header_text_color"><?php esc_html_e('Header Text Color', 'ttp-voice-widget'); ?></label></th>
                            <td>
                                <input type="color" id="ttp_header_text_color" name="ttp_header_text_color" value="<?php echo esc_attr(get_option('ttp_header_text_color', '#FFFFFF')); ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php submit_button(esc_html__('Save Settings', 'ttp-voice-widget')); ?>
            
        </form>
        
        <!-- Status -->
        <?php if (get_option('ttp_agent_id')): ?>
        <div class="ttp-card ttp-status-card">
            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
            <strong><?php esc_html_e('Widget is active!', 'ttp-voice-widget'); ?></strong>
            <?php esc_html_e('Visit your site to see the voice widget.', 'ttp-voice-widget'); ?>
        </div>
        <?php endif; ?>
        
        <?php endif; // end if connected ?>
        
    </div>
    
    <style>
        .ttp-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .ttp-card h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .ttp-connected-status {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .ttp-email {
            color: #666;
        }
        .ttp-collapsible-header {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ttp-collapsible-content {
            display: none;
            padding-top: 15px;
        }
        .ttp-collapsible.open .ttp-collapsible-content {
            display: block;
        }
        .ttp-collapsible.open .dashicons {
            transform: rotate(180deg);
        }
        .ttp-status-card {
            background: #d4edda;
            border-color: #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ttp-loading {
            display: flex;
            align-items: center;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        var ajaxNonce = '<?php echo wp_create_nonce('ttp_ajax_nonce'); ?>';
        var currentAgentId = '<?php echo esc_js($current_agent_id); ?>';
        var currentVoice = '<?php echo esc_js(get_option('ttp_override_voice')); ?>';
        
        // Collapsible sections
        $('.ttp-collapsible-header').on('click', function() {
            $(this).closest('.ttp-collapsible').toggleClass('open');
        });
        
        // Fetch agents on load
        <?php if ($is_connected): ?>
        fetchAgents();
        fetchVoices();
        <?php endif; ?>
        
        function fetchAgents() {
            $('#ttp-agents-loading').addClass('is-active');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ttp_fetch_agents',
                    nonce: ajaxNonce
                },
                success: function(response) {
                    // API returns {success: true, data: [...]} wrapped by wp_send_json_success
                    var agents = null;
                    if (response.success && response.data) {
                        // Handle both direct array and wrapped response
                        if (Array.isArray(response.data)) {
                            agents = response.data;
                        } else if (response.data.success && Array.isArray(response.data.data)) {
                            agents = response.data.data;
                        } else if (Array.isArray(response.data.data)) {
                            agents = response.data.data;
                        }
                    }
                    $('#ttp-agents-loading').removeClass('is-active');
                    if (agents && agents.length > 0) {
                        renderAgents(agents);
                    } else {
                        $('#ttp_agent_select').html('<option value="">No agents found</option>');
                    }
                    $('#ttp-create-agent').show();
                },
                error: function() {
                    $('#ttp-agents-loading').removeClass('is-active');
                    $('#ttp_agent_select').html('<option value="">Failed to load agents</option>');
                    $('#ttp-create-agent').show();
                }
            });
        }
        
        function renderAgents(agents) {
            var $select = $('#ttp_agent_select');
            $select.empty();
            $select.append('<option value="">-- Select an agent --</option>');
            
            agents.forEach(function(agent) {
                var agentId = agent.agentId || agent.id; // Support both formats
                var isSelected = agentId === currentAgentId;
                $select.append('<option value="' + agentId + '"' + (isSelected ? ' selected' : '') + '>' + agent.name + '</option>');
            });
            
            // Handle selection change
            $select.off('change').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                $('#ttp_agent_name').val(selectedOption.text() !== '-- Select an agent --' ? selectedOption.text() : '');
            });
        }
        
        function fetchVoices() {
            $('#ttp-voice-loading').addClass('is-active');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ttp_fetch_voices',
                    nonce: ajaxNonce
                },
                success: function(response) {
                    $('#ttp-voice-loading').removeClass('is-active');
                    var voices = null;
                    if (response.success && response.data) {
                        // Handle both direct array and wrapped response
                        if (Array.isArray(response.data)) {
                            voices = response.data;
                        } else if (response.data.success && Array.isArray(response.data.data)) {
                            voices = response.data.data;
                        } else if (Array.isArray(response.data.data)) {
                            voices = response.data.data;
                        }
                    }
                    if (voices && voices.length > 0) {
                        var $select = $('#ttp_override_voice');
                        voices.forEach(function(voice) {
                            var voiceId = voice.voiceId || voice.id; // Support both formats
                            var isSelected = voiceId === currentVoice;
                            $select.append('<option value="' + voiceId + '"' + (isSelected ? ' selected' : '') + '>' + voice.name + '</option>');
                        });
                    }
                },
                error: function() {
                    $('#ttp-voice-loading').removeClass('is-active');
                }
            });
        }
        
        // Show/hide create agent form
        $('#ttp-show-create-agent').on('click', function() {
            $(this).hide();
            $('#ttp-create-agent-form').show();
            $('#ttp-new-agent-name').focus();
        });
        
        $('#ttp-cancel-create-agent').on('click', function() {
            $('#ttp-create-agent-form').hide();
            $('#ttp-show-create-agent').show();
            $('#ttp-new-agent-name').val('');
        });
        
        // Create agent
        $('#ttp-create-agent-btn').on('click', function() {
            var name = $('#ttp-new-agent-name').val().trim();
            if (!name) {
                alert('Please enter an agent name');
                return;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('Creating...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ttp_create_agent',
                    nonce: ajaxNonce,
                    agent_name: name
                },
                success: function(response) {
                    if (response.success) {
                        // Get new agent data
                        var agentData = response.data.data || response.data;
                        var newAgentId = agentData.agentId || agentData.id;
                        var newAgentName = agentData.name || name;
                        
                        // Update current selection
                        if (newAgentId) {
                            currentAgentId = newAgentId;
                        }
                        
                        // Refresh agents list (will auto-select new agent)
                        fetchAgents();
                        
                        // Reset form
                        $('#ttp-new-agent-name').val('');
                        $('#ttp-create-agent-form').hide();
                        $('#ttp-show-create-agent').show();
                        $('#ttp_agent_name').val(newAgentName);
                    } else {
                        var errMsg = (response.data && response.data.message) || 'Failed to create agent';
                        alert('Error: ' + errMsg);
                    }
                },
                error: function() {
                    alert('Failed to create agent. Please try again.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Create');
                }
            });
        });
    });
    </script>
    <?php
}

// =============================================================================
// FRONTEND WIDGET
// =============================================================================

add_action('wp_enqueue_scripts', function() {
    $api_key = get_option('ttp_api_key');
    $app_id = get_option('ttp_app_id');
    $agent_id = get_option('ttp_agent_id');
    
    // Need at minimum api_key and agent_id
    if (empty($api_key) || empty($agent_id)) return;
    
    wp_enqueue_script('ttp-agent-widget', 'https://cdn.talktopc.com/agent-widget.js', [], TTP_VERSION, true);
    
    // Build config
    $config = [
        'agentId' => $agent_id,
    ];
    
    if (!empty($app_id)) {
        $config['appId'] = $app_id;
    }
    
    // Position
    $position = get_option('ttp_position', 'bottom-right');
    if (!empty($position)) {
        $config['position'] = $position;
    }
    
    // Direction
    $direction = get_option('ttp_direction', 'ltr');
    if (!empty($direction)) {
        $config['direction'] = $direction;
    }
    
    // Button
    $button = [];
    if ($size = get_option('ttp_button_size')) $button['size'] = $size;
    if ($shape = get_option('ttp_button_shape')) $button['shape'] = $shape;
    if ($bg = get_option('ttp_button_bg_color')) $button['backgroundColor'] = $bg;
    if (!empty($button)) $config['button'] = $button;
    
    // Header
    $header = [];
    if ($title = get_option('ttp_header_title')) $header['title'] = $title;
    if ($bg = get_option('ttp_header_bg_color')) $header['backgroundColor'] = $bg;
    if ($text = get_option('ttp_header_text_color')) $header['textColor'] = $text;
    if (!empty($header)) $config['header'] = $header;
    
    // Behavior
    $behavior = [];
    if ($mode = get_option('ttp_mode')) $behavior['mode'] = $mode;
    if (!empty($behavior)) $config['behavior'] = $behavior;
    
    // Agent Settings Override
    $override = [];
    if ($prompt = get_option('ttp_override_prompt')) $override['prompt'] = $prompt;
    if ($first = get_option('ttp_override_first_message')) $override['firstMessage'] = $first;
    if ($voice = get_option('ttp_override_voice')) $override['voiceId'] = $voice;
    if ($speed = get_option('ttp_override_voice_speed')) $override['voiceSpeed'] = floatval($speed);
    if ($lang = get_option('ttp_override_language')) $override['language'] = $lang;
    if ($temp = get_option('ttp_override_temperature')) $override['temperature'] = floatval($temp);
    if (!empty($override)) $config['agentSettingsOverride'] = $override;
    
    // Initialize widget
    $inline_script = sprintf(
        '(function() {
            function initWidget() {
                if (typeof TTPAgentSDK !== "undefined" && TTPAgentSDK.TTPChatWidget) {
                    new TTPAgentSDK.TTPChatWidget(%s);
                } else {
                    setTimeout(initWidget, 100);
                }
            }
            initWidget();
        })();',
        wp_json_encode($config)
    );
    
    wp_add_inline_script('ttp-agent-widget', $inline_script);
});

// Add settings link on plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=ttp-voice-widget')) . '">' . esc_html__('Settings', 'ttp-voice-widget') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});