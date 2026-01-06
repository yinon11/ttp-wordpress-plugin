<?php
/**
 * OAuth Handlers
 * 
 * Secure OAuth flow (WordPress.org security compliant):
 * 1. User clicks "Connect" → verifies nonce, generates secret, stores in transient, redirects to TalkToPC
 * 2. User authorizes on TalkToPC → TalkToPC redirects back to authenticated admin callback
 * 3. We verify nonce, capability, and secret matches, store credentials
 * 4. Auto-fetch agents and create one if none exist
 * 
 * Security:
 * - Requires admin login and capability check
 * - Nonce verification prevents CSRF attacks
 * - One-time secret prevents unauthorized credential injection
 * - Secret expires after 5 minutes
 * - hash_equals() prevents timing attacks
 * - No public endpoints - all flows require authentication
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// CONNECT - Generate secret and redirect to TalkToPC
// =============================================================================
add_action('admin_post_talktopc_connect', 'talktopc_handle_connect');

function talktopc_handle_connect() {
    // Verify nonce
    check_admin_referer('talktopc_connect_action');
    
    // Verify user has admin permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'talktopc'));
    }
    
    // Generate one-time secret (32 chars, alphanumeric)
    $secret = wp_generate_password(32, false);
    
    // Store secret in transient (expires in 5 minutes)
    set_transient('talktopc_connect_secret', $secret, 300);
    
    // Build redirect URI (where TalkToPC will redirect back with credentials)
    $redirect_uri = admin_url('admin.php?page=talktopc&action=talktopc_oauth_callback');
    
    // Create nonce for callback verification
    $nonce = wp_create_nonce('talktopc_connect_action');
    
    // Build TalkToPC authorization URL
    $connect_url = TALKTOPC_CONNECT_URL . '?' . http_build_query([
        'redirect_uri' => $redirect_uri,
        'secret'       => $secret,
        'site_url'     => home_url(),
        'site_name'    => get_bloginfo('name'),
        '_wpnonce'     => $nonce,
    ]);
    
    // Allow TalkToPC domain for redirect
    add_filter('allowed_redirect_hosts', function($hosts) {
        $hosts[] = wp_parse_url(TALKTOPC_CONNECT_URL, PHP_URL_HOST);
        return $hosts;
    });
    
    // Redirect to TalkToPC (external redirect requires wp_redirect, not wp_safe_redirect)
    // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Intentional external redirect to TalkToPC OAuth
    wp_redirect($connect_url);
    exit;
}

// =============================================================================
// OAUTH CALLBACK - Handle redirect from TalkToPC with credentials
// =============================================================================
add_action('admin_init', 'talktopc_handle_oauth_callback');

function talktopc_handle_oauth_callback() {
    // 1. Early return - strict check
    if (
        empty($_GET['page']) || 
        empty($_GET['action']) ||
        sanitize_text_field(wp_unslash($_GET['page'])) !== 'talktopc' ||
        sanitize_text_field(wp_unslash($_GET['action'])) !== 'talktopc_oauth_callback'
    ) {
        return;
    }
    
    // 2. Capability check
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'talktopc'));
    }
    
    // 3. Nonce check (reuse same nonce from Connect flow)
    check_admin_referer('talktopc_connect_action');
    
    // 4. Sanitize with intent - OAuth redirect uses GET params
    // This is acceptable in authenticated admin context with nonce + capability checks
    $api_key = sanitize_text_field(wp_unslash($_GET['api_key'] ?? ''));
    $app_id  = sanitize_text_field(wp_unslash($_GET['app_id'] ?? ''));
    $email   = sanitize_email(wp_unslash($_GET['email'] ?? ''));
    $secret  = sanitize_text_field(wp_unslash($_GET['secret'] ?? ''));
    
    // 5. Validate required fields
    if (empty($api_key) || empty($app_id) || empty($secret)) {
        wp_die(__('Missing required fields.', 'talktopc'));
    }
    
    // 6. Validate secret against stored transient
    $stored_secret = get_transient('talktopc_connect_secret');
    if (!$stored_secret || !hash_equals($stored_secret, $secret)) {
        wp_die(__('Invalid or expired session. Please try connecting again.', 'talktopc'));
    }
    
    // 7. Delete transient immediately (one-time use)
    delete_transient('talktopc_connect_secret');
    
    // 8. Store credentials
    update_option('talktopc_api_key', $api_key);
    update_option('talktopc_app_id', $app_id);
    if ($email) {
        update_option('talktopc_user_email', $email);
    }
    update_option('talktopc_connected_at', current_time('mysql'));
    
    // 9. Set flag for agent setup (non-blocking)
    // Agent will be created via AJAX when dashboard loads
    set_transient('talktopc_needs_agent_setup', true, 300);
    
    // 10. Redirect to dashboard IMMEDIATELY
    wp_safe_redirect(admin_url('admin.php?page=talktopc&connected=1'));
    exit;
}

// =============================================================================
// AUTO-SETUP AGENT - Fetch agents, create if none exist
// =============================================================================
function talktopc_auto_setup_agent($api_key) {
    $result = [
        'agents_fetched' => false,
        'agent_created' => false,
        'agent_id' => null,
        'agent_name' => null,
        'error' => null
    ];
    
    // Step 1: Fetch existing agents
    $agents = talktopc_fetch_agents_sync($api_key);
    
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
            update_option('talktopc_agent_id', $agent_id);
            update_option('talktopc_agent_name', $agent_name);
            
            $result['agent_id'] = $agent_id;
            $result['agent_name'] = $agent_name;
        }
        
        return $result;
    }
    
    // Step 3: No agents exist - set flag and create one with AI-generated prompt
    set_transient('talktopc_agent_creating', true, 180); // 3 minute timeout
    
    $agent_name = get_bloginfo('name') . ' Assistant';
    $created_agent = talktopc_create_agent_sync($api_key, $agent_name, true);
    
    // Clear flag when done
    delete_transient('talktopc_agent_creating');
    
    if ($created_agent === false) {
        $result['error'] = 'Failed to create agent';
        return $result;
    }
    
    $result['agent_created'] = true;
    
    $agent_id = $created_agent['agentId'] ?? $created_agent['id'] ?? null;
    $agent_name = $created_agent['name'] ?? $agent_name;
    
    if ($agent_id) {
        update_option('talktopc_agent_id', $agent_id);
        update_option('talktopc_agent_name', $agent_name);
        
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
function talktopc_fetch_agents_sync($api_key) {
    $response = wp_remote_get(TALKTOPC_API_URL . '/api/public/wordpress/agents', [
        'headers' => [
            'X-API-Key' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TalkToPC OAuth: Failed to fetch agents - ' . $response->get_error_message());
        }
        return false;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TalkToPC OAuth: Failed to fetch agents - HTTP ' . $status_code);
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
function talktopc_create_agent_sync($api_key, $agent_name, $auto_generate = true) {
    // Build agent data
    $agent_data = [
        'name' => $agent_name,
        'site_url' => home_url(),
        'site_name' => get_bloginfo('name')
    ];
    
    // Collect site content for AI prompt generation
    if ($auto_generate && function_exists('talktopc_collect_site_content')) {
        $agent_data['site_content'] = talktopc_collect_site_content();
        
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
    
    $response = wp_remote_post(TALKTOPC_API_URL . '/api/public/wordpress/agents', [
        'headers' => $headers,
        'body' => $body,
        'timeout' => $timeout
    ]);
    
    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TalkToPC OAuth: Failed to create agent - ' . $response->get_error_message());
        }
        return false;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $error_msg = $response_body['error'] ?? 'Unknown error';
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
            error_log('TalkToPC OAuth: Failed to create agent - HTTP ' . $status_code . ' - ' . $error_msg);
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
    if (!wp_verify_nonce($nonce, 'talktopc_disconnect')) {
        add_settings_error('talktopc_settings', 'invalid_nonce', 'Invalid security token.');
        return;
    }
    
    // Verify user has admin permissions
    if (!current_user_can('manage_options')) {
        add_settings_error('talktopc_settings', 'unauthorized', 'Unauthorized.');
        return;
    }
    
    // Delete ALL plugin settings for clean slate on reconnect
    $all_options = talktopc_get_all_option_names();
    foreach ($all_options as $option) {
        delete_option($option);
    }
    
    // Also clear any setup transients
    delete_transient('talktopc_agent_creating');
    delete_transient('talktopc_connect_secret');
    
    // Redirect to settings page with disconnected message
    wp_safe_redirect(admin_url('admin.php?page=talktopc&disconnected=1'));
    exit;
});