<?php
/**
 * OAuth Handlers
 * 
 * Secure OAuth flow:
 * 1. User clicks "Connect" → generates secret, stores in transient, redirects to TalkToPC
 * 2. User authorizes on TalkToPC → TalkToPC POSTs credentials to our endpoint
 * 3. We verify the secret matches, store credentials
 * 
 * Security:
 * - One-time secret prevents unauthorized credential injection
 * - Secret expires after 5 minutes
 * - hash_equals() prevents timing attacks
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// CONNECT - Generate secret and redirect to TalkToPC
// =============================================================================
add_action('admin_post_ttp_connect', 'ttp_handle_connect');

function ttp_handle_connect() {
    // Verify user has admin permissions
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 'Error', ['response' => 403]);
    }
    
    // Generate one-time secret (32 chars, alphanumeric)
    $secret = wp_generate_password(32, false);
    
    // Store secret in transient (expires in 5 minutes)
    set_transient('ttp_connect_secret', $secret, 300);
    
    // Build redirect URI (where TalkToPC will POST the credentials)
    $redirect_uri = admin_url('admin-ajax.php?action=ttp_receive_credentials');
    
    // Build TalkToPC authorization URL
    $connect_url = TTP_CONNECT_URL . '?' . http_build_query([
        'redirect_uri' => $redirect_uri,
        'secret'       => $secret,
        'site_url'     => home_url(),
        'site_name'    => get_bloginfo('name'),
    ]);
    
    // Redirect to TalkToPC
    wp_redirect($connect_url);
    exit;
}

// =============================================================================
// RECEIVE CREDENTIALS - Endpoint for TalkToPC to POST credentials
// =============================================================================
add_action('wp_ajax_ttp_receive_credentials', 'ttp_receive_credentials');
add_action('wp_ajax_nopriv_ttp_receive_credentials', 'ttp_receive_credentials');

function ttp_receive_credentials() {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error(['message' => 'Method not allowed'], 405);
    }
    
    // Get POST data
    $received_secret = isset($_POST['secret']) ? sanitize_text_field(wp_unslash($_POST['secret'])) : '';
    $api_key         = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
    $app_id          = isset($_POST['app_id']) ? sanitize_text_field(wp_unslash($_POST['app_id'])) : '';
    $email           = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    
    // Validate required fields
    if (empty($received_secret) || empty($api_key) || empty($app_id)) {
        wp_send_json_error(['message' => 'Missing required fields'], 400);
    }
    
    // Get stored secret
    $stored_secret = get_transient('ttp_connect_secret');
    
    if (!$stored_secret) {
        wp_send_json_error(['message' => 'No pending connection or secret expired'], 400);
    }
    
    // Constant-time comparison to prevent timing attacks
    if (!hash_equals($stored_secret, $received_secret)) {
        wp_send_json_error(['message' => 'Invalid secret'], 403);
    }
    
    // Delete transient immediately (one-time use)
    delete_transient('ttp_connect_secret');
    
    // Store credentials securely
    update_option('ttp_api_key', $api_key);
    update_option('ttp_app_id', $app_id);
    if ($email) {
        update_option('ttp_user_email', $email);
    }
    update_option('ttp_connected_at', current_time('mysql'));
    
    // Return success
    wp_send_json_success(['message' => 'Credentials stored successfully']);
}

// =============================================================================
// DISCONNECT - Clear all settings
// =============================================================================
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ttp-voice-widget') return;
    
    if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
        // Verify security nonce
        if (!wp_verify_nonce(isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '', 'ttp_disconnect')) {
            add_settings_error('ttp_settings', 'invalid_nonce', 'Invalid security token.');
            return;
        }
        
        // Verify user has admin permissions
        if (!current_user_can('manage_options')) {
            add_settings_error('ttp_settings', 'unauthorized', 'Unauthorized.');
            return;
        }
        
        // Delete ALL plugin settings for clean slate on reconnect
        $all_options = ttp_get_all_option_names();
        foreach ($all_options as $option) {
            delete_option($option);
        }
        
        // Redirect to settings page with disconnected message
        wp_safe_redirect(admin_url('admin.php?page=ttp-voice-widget&disconnected=1'));
        exit;
    }
});