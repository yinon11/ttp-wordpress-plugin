<?php
/**
 * OAuth Callback & Disconnect Handlers
 * 
 * Handles:
 * - OAuth callback from TalkToPC (receives API key)
 * - Disconnect action (clears all settings)
 * 
 * Flow:
 * 1. User clicks "Connect to TalkToPC" → redirects to talktopc.com/connect/wordpress
 * 2. User authorizes → React frontend creates API key via /api/developers/api-keys
 * 3. Redirects back here with api_key in URL
 * 4. We save the key directly (no secondary key creation needed)
 */

if (!defined('ABSPATH')) exit;

add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ttp-voice-widget') return;
    
    // ==========================================================================
    // OAUTH CALLBACK
    // ==========================================================================
    if (isset($_GET['api_key']) && isset($_GET['state'])) {
        // Verify security nonce
        if (!wp_verify_nonce(wp_unslash($_GET['state']), 'ttp_connect')) {
            add_settings_error('ttp_settings', 'invalid_state', 'Invalid security token. Please try again.');
            return;
        }
        
        // The API key from the authorization flow is already site-specific
        // It was created by the React frontend with created_for = this site's hostname
        // No need to create another one - just use it directly
        $api_key = sanitize_text_field(wp_unslash($_GET['api_key']));
        $app_id = isset($_GET['app_id']) ? sanitize_text_field(wp_unslash($_GET['app_id'])) : '';
        $user_email = isset($_GET['email']) ? sanitize_email(wp_unslash($_GET['email'])) : '';
        
        // Save connection details
        update_option('ttp_api_key', $api_key);
        if ($app_id) update_option('ttp_app_id', $app_id);
        if ($user_email) update_option('ttp_user_email', $user_email);
        
        // Redirect to settings page with success message
        wp_safe_redirect(admin_url('admin.php?page=ttp-voice-widget&connected=1'));
        exit;
    }
    
    // ==========================================================================
    // DISCONNECT
    // ==========================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'disconnect') {
        // Verify security nonce
        if (!wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'ttp_disconnect')) {
            add_settings_error('ttp_settings', 'invalid_nonce', 'Invalid security token.');
            return;
        }
        
        // Delete ALL plugin settings for clean slate on reconnect
        // The API key on the backend will be automatically replaced on next connect
        // (createApiKey in backend deletes existing key with same created_for)
        $all_options = ttp_get_all_option_names();
        foreach ($all_options as $option) {
            delete_option($option);
        }
        
        // Redirect to settings page with disconnected message
        wp_safe_redirect(admin_url('admin.php?page=ttp-voice-widget&disconnected=1'));
        exit;
    }
});
