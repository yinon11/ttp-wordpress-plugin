<?php
/**
 * OAuth Handlers
 * 
 * Secure OAuth flow:
 * 1. User clicks "Connect" → generates secret, stores in transient, redirects to TalkToPC
 * 2. User authorizes on TalkToPC → TalkToPC POSTs credentials to our endpoint
 * 3. We verify the secret matches, store credentials
 * 4. Auto-fetch agents and create one if none exist
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
    
    // Allow TalkToPC domain for redirect
    add_filter('allowed_redirect_hosts', function($hosts) {
        $hosts[] = wp_parse_url(TTP_CONNECT_URL, PHP_URL_HOST);
        return $hosts;
    });
    
    // Redirect to TalkToPC (external redirect requires wp_redirect, not wp_safe_redirect)
    // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Intentional external redirect to TalkToPC OAuth
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
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- REQUEST_METHOD is always set by server
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : '';
    if ($request_method !== 'POST') {
        wp_send_json_error(['message' => 'Method not allowed'], 405);
    }
    
    // Note: Nonce verification is intentionally skipped for this endpoint.
    // This is an OAuth callback endpoint that receives POST data from TalkToPC's server,
    // not from a WordPress form. Security is handled via the one-time secret mechanism:
    // 1. Secret is generated and stored in transient when user initiates connection
    // 2. Secret is sent to TalkToPC and returned with credentials
    // 3. Secret is verified with hash_equals() and immediately deleted
    // phpcs:disable WordPress.Security.NonceVerification.Missing
    
    // Get POST data
    $received_secret = isset($_POST['secret']) ? sanitize_text_field(wp_unslash($_POST['secret'])) : '';
    $api_key         = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
    $app_id          = isset($_POST['app_id']) ? sanitize_text_field(wp_unslash($_POST['app_id'])) : '';
    $email           = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    
    // phpcs:enable WordPress.Security.NonceVerification.Missing
    
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
    
    // =================================================================
    // AUTO-SETUP: Fetch agents and create one if none exist
    // =================================================================
    $setup_result = ttp_auto_setup_agent($api_key);
    
    // Return success with setup info
    wp_send_json_success([
        'message' => 'Credentials stored successfully',
        'setup' => $setup_result
    ]);
}

// =============================================================================
// AUTO-SETUP AGENT - Fetch agents, create if none exist
// =============================================================================
function ttp_auto_setup_agent($api_key) {
    $result = [
        'agents_fetched' => false,
        'agent_created' => false,
        'agent_id' => null,
        'agent_name' => null,
        'error' => null
    ];
    
    // Step 1: Fetch existing agents
    $agents = ttp_fetch_agents_sync($api_key);
    
    if ($agents === false) {
        $result['error'] = 'Failed to fetch agents';
        return $result;
    }
    
    $result['agents_fetched'] = true;
    
    // Step 2: If agents exist, use the first one
    if (!empty($agents)) {
        $first_agent = $agents[0];
        $agent_id = $first_agent['agentId'] ?? $first_agent['id'] ?? null;
        $agent_name = $first_agent['name'] ?? 'Agent';
        
        if ($agent_id) {
            update_option('ttp_agent_id', $agent_id);
            update_option('ttp_agent_name', $agent_name);
            
            $result['agent_id'] = $agent_id;
            $result['agent_name'] = $agent_name;
        }
        
        return $result;
    }
    
    // Step 3: No agents exist - set flag and create one with AI-generated prompt
    set_transient('ttp_agent_creating', true, 180); // 3 minute timeout
    
    $agent_name = get_bloginfo('name') . ' Assistant';
    $created_agent = ttp_create_agent_sync($api_key, $agent_name, true);
    
    // Clear flag when done
    delete_transient('ttp_agent_creating');
    
    if ($created_agent === false) {
        $result['error'] = 'Failed to create agent';
        return $result;
    }
    
    $result['agent_created'] = true;
    
    $agent_id = $created_agent['agentId'] ?? $created_agent['id'] ?? null;
    $agent_name = $created_agent['name'] ?? $agent_name;
    
    if ($agent_id) {
        update_option('ttp_agent_id', $agent_id);
        update_option('ttp_agent_name', $agent_name);
        
        $result['agent_id'] = $agent_id;
        $result['agent_name'] = $agent_name;
    }
    
    return $result;
}

// =============================================================================
// SYNC API HELPERS - Same logic as AJAX handlers but synchronous
// =============================================================================

/**
 * Fetch agents from TalkToPC API (synchronous)
 * 
 * @param string $api_key The API key
 * @return array|false Array of agents or false on error
 */
function ttp_fetch_agents_sync($api_key) {
    $response = wp_remote_get(TTP_API_URL . '/api/public/wordpress/agents', [
        'headers' => [
            'X-API-Key' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TTP OAuth: Failed to fetch agents - ' . $response->get_error_message());
        }
        return false;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TTP OAuth: Failed to fetch agents - HTTP ' . $status_code);
        }
        return false;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Handle different response formats
    if (is_array($body)) {
        // Direct array of agents
        if (isset($body[0])) {
            return $body;
        }
        // Wrapped in data key
        if (isset($body['data']) && is_array($body['data'])) {
            return $body['data'];
        }
    }
    
    return [];
}

/**
 * Create agent via TalkToPC API (synchronous)
 * 
 * @param string $api_key The API key
 * @param string $agent_name Name for the agent
 * @param bool $auto_generate Whether to auto-generate prompt from site content
 * @return array|false Created agent data or false on error
 */
function ttp_create_agent_sync($api_key, $agent_name, $auto_generate = true) {
    // Build agent data
    $agent_data = [
        'name' => $agent_name,
        'site_url' => home_url(),
        'site_name' => get_bloginfo('name')
    ];
    
    // Collect site content for AI prompt generation
    if ($auto_generate && function_exists('ttp_collect_site_content')) {
        $agent_data['site_content'] = ttp_collect_site_content();
        
        // Use detected content language
        $detected_lang = $agent_data['site_content']['site']['language'] ?? 'en_US';
        $lang_code = explode('_', $detected_lang)[0];
        $agent_data['language'] = $lang_code;
    }
    
    // Prepare request
    $json_body = wp_json_encode($agent_data);
    $headers = [
        'X-API-Key' => $api_key,
        'Content-Type' => 'application/json'
    ];
    $body = $json_body;
    $timeout = 30;
    
    // Use gzip compression for large payloads
    if ($auto_generate && !empty($agent_data['site_content'])) {
        $body = gzencode($json_body, 9);
        $headers['Content-Encoding'] = 'gzip';
        $timeout = 120; // Longer timeout for AI generation
    }
    
    $response = wp_remote_post(TTP_API_URL . '/api/public/wordpress/agents', [
        'headers' => $headers,
        'body' => $body,
        'timeout' => $timeout
    ]);
    
    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TTP OAuth: Failed to create agent - ' . $response->get_error_message());
        }
        return false;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $error_msg = $response_body['error'] ?? 'Unknown error';
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TTP OAuth: Failed to create agent - HTTP ' . $status_code . ' - ' . $error_msg);
        }
        return false;
    }
    
    // Handle different response formats
    if (isset($response_body['data'])) {
        return $response_body['data'];
    }
    
    return $response_body;
}

// =============================================================================
// DISCONNECT - Clear all settings
// =============================================================================
add_action('admin_init', function() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below before any action
    if (!isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'talktopc') {
        return;
    }
    
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below before any action
    if (!isset($_GET['action']) || sanitize_text_field(wp_unslash($_GET['action'])) !== 'disconnect') {
        return;
    }
    
    // Verify security nonce
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (!wp_verify_nonce($nonce, 'ttp_disconnect')) {
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
    
    // Also clear any setup transients
    delete_transient('ttp_agent_creating');
    delete_transient('ttp_connect_secret');
    
    // Redirect to settings page with disconnected message
    wp_safe_redirect(admin_url('admin.php?page=talktopc&disconnected=1'));
    exit;
});