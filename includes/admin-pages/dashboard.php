<?php
/**
 * Dashboard Page
 * 
 * FIXES:
 * - Issue #1: Show saved agent immediately from PHP, then update from AJAX
 * - Issue #3: Better aligned Agent Settings UI with CSS grid
 */

if (!defined('ABSPATH')) exit;


function talktopc_render_dashboard_page() {
    $is_connected = !empty(get_option('talktopc_api_key'));
    $user_email = get_option('talktopc_user_email', '');
    $current_agent_id = get_option('talktopc_agent_id', '');
    $current_agent_name = get_option('talktopc_agent_name', '');
    
    // Build OAuth URLs
    $connect_url = admin_url('admin-post.php?action=talktopc_connect');
    $connect_url = wp_nonce_url($connect_url, 'talktopc_connect_action');
    $disconnect_url = wp_nonce_url(admin_url('admin.php?page=talktopc&action=disconnect'), 'talktopc_disconnect');
    
    // Enqueue WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Styles and scripts are now enqueued via admin_enqueue_scripts hook
    // No need to call render functions here
    ?>
    <div class="wrap talktopc-admin-wrap">
        <div class="wp-header">
            <h1>Dashboard</h1>
            <span class="version">v<?php echo esc_html(TALKTOPC_VERSION); ?></span>
        </div>
        
        <?php settings_errors(); ?>
        
        <?php 
        // Check if agent setup is needed (from OAuth callback)
        $needs_agent_setup = false;
        if ($is_connected) {
            $needs_agent_setup = get_transient('talktopc_needs_agent_setup');
            if ($needs_agent_setup) {
                delete_transient('talktopc_needs_agent_setup');
            }
        }
        ?>
        
        <?php if ($is_connected): ?>
            <!-- Credits Box - Dynamic states based on credit amount -->
            <div class="credits-box" id="talktopcCreditsBox">
                <div class="credits-left">
                    <div class="credits-icon" id="talktopcCreditsIcon">💳</div>
                    <div class="credits-info">
                        <h3 id="talktopcCreditsTitle">Available Credits</h3>
                        <div class="credits-amount">
                            <span class="amount" id="talktopcCreditsAmount">
                                <span class="spinner"></span> Loading...
                            </span>
                            <span class="unit" id="talktopcCreditsUnit"></span>
                        </div>
                        <div class="credits-label" id="talktopcCreditsLabel">Checking your account...</div>
                        <div class="credits-warning" id="talktopcCreditsWarning"></div>
                    </div>
                </div>
                <div class="credits-right">
                    <a href="https://talktopc.com/upgrade" target="_blank" class="button" id="talktopcCreditsButton">Buy More →</a>
                    <div class="credits-hint" id="talktopcCreditsHint"></div>
                </div>
            </div>
            
            <!-- Connection Status -->
            <div class="card">
                <h2><span class="icon">🔗</span> Account</h2>
                <div class="status-connected">
                    <span class="status-dot"></span>
                    <span>Connected</span>
                    <?php if ($user_email): ?><span class="status-email">• <?php echo esc_html($user_email); ?></span><?php endif; ?>
                    <a href="<?php echo esc_url($disconnect_url); ?>" class="button-link-delete" style="margin-left: auto;">Disconnect</a>
                </div>
            </div>
            
            <!-- Default Agent -->
            <div class="card">
                <h2><span class="icon">🤖</span> Your AI Agent</h2>
                
                <div id="agentSelectorArea">
                <div class="agent-selector-big">
                    <label>Select Agent</label>
                    <select id="defaultAgentSelect" name="talktopc_agent_id">
                        <option value="">-- Select Agent --</option>
                        <?php 
                        // FIX #1: Show saved agent immediately from PHP
                        if ($current_agent_id && $current_agent_id !== 'none'): 
                        ?>
                            <option value="<?php echo esc_attr($current_agent_id); ?>" selected>
                                <?php echo esc_html($current_agent_name ?: 'Loading...'); ?>
                            </option>
                        <?php elseif ($current_agent_id === 'none'): ?>
                            <option value="none" selected>🚫 Widget Disabled</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($current_agent_id && $current_agent_id !== 'none'): ?>
                        <span class="agent-loading-indicator" id="agentLoadingIndicator">
                            <span class="spinner"></span> Syncing agents...
                        </span>
                    <?php endif; ?>
                    <p class="description">
                        This agent appears on all pages of your website.
                        <a href="<?php echo esc_url(admin_url('admin.php?page=talktopc-page-rules')); ?>">Need different agents on different pages? →</a>
                    </p>
                </div>
                
                <!-- Agent Settings (collapsible) -->
                <div class="agent-settings <?php echo ($current_agent_id && $current_agent_id !== 'none') ? '' : 'collapsed'; ?>" id="agentSettings">
                    <div class="agent-settings-header">
                        <div class="header-left" onclick="toggleAgentSettings()">
                            <h3><span>⚙️</span> Agent Settings</h3>
                            <span class="arrow">▼</span>
                        </div>
                        <div class="header-right">
                            <button type="button" class="button button-small" id="editAgentSettingsBtn">✏️ Edit</button>
                            <button type="button" class="button button-small" id="cancelEditBtn" style="display: none;">✖️ Cancel</button>
                        </div>
                    </div>
                    <div class="agent-settings-body">
                        <!-- Settings saved via AJAX to both WordPress (cache) and TalkToPC backend (DB) -->
                        <div id="agentSettingsForm">
                            
                            <!-- FIX #3: Better aligned form layout -->
                            <div class="agent-form-grid">
                                <div class="form-row full-width">
                                    <label for="talktopc_override_prompt">System Prompt</label>
                                    <div class="field">
                                        <textarea id="talktopc_override_prompt" rows="5" disabled><?php echo esc_textarea(get_option('talktopc_override_prompt', '')); ?></textarea>
                                        <p class="field-action edit-only" style="display: none;">
                                            <button type="button" class="button button-small" id="talktopcGeneratePrompt">🔄 Generate from Site Content</button>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="form-row full-width">
                                    <label for="talktopc_override_first_message">First Message</label>
                                    <div class="field">
                                        <input type="text" id="talktopc_override_first_message" value="<?php echo esc_attr(get_option('talktopc_override_first_message', '')); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="form-row full-width">
                                    <label for="talktopc_override_voice">Voice</label>
                                    <div class="field">
                                        <select id="talktopc_override_voice" disabled>
                                            <option value="">-- Select Voice --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="section-title">Advanced</h3>
                            
                            <!-- FIX #3: CSS Grid for aligned inline fields -->
                            <div class="advanced-settings-grid">
                                <div class="grid-field">
                                    <label for="talktopc_override_voice_speed">Speed</label>
                                    <input type="number" id="talktopc_override_voice_speed" value="<?php echo esc_attr(get_option('talktopc_override_voice_speed', '1.0')); ?>" step="0.1" min="0.5" max="2.0" disabled>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_language">Language</label>
                                    <select id="talktopc_override_language" disabled>
                                        <option value="">-- Select Language --</option>
                                    </select>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_temperature">Temperature</label>
                                    <input type="number" id="talktopc_override_temperature" value="<?php echo esc_attr(get_option('talktopc_override_temperature', '0.7')); ?>" step="0.1" min="0" max="2" disabled>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_max_tokens">Max Tokens</label>
                                    <input type="number" id="talktopc_override_max_tokens" value="<?php echo esc_attr(get_option('talktopc_override_max_tokens', '1000')); ?>" disabled>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="talktopc_override_max_call_duration">Max Duration</label>
                                    <div class="input-with-suffix">
                                        <input type="number" id="talktopc_override_max_call_duration" value="<?php echo esc_attr(get_option('talktopc_override_max_call_duration', '300')); ?>" disabled>
                                        <span class="suffix">sec</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="save-area edit-only" style="display: none;">
                                <button type="button" class="button button-primary" id="saveAgentSettingsBtn">Save Agent Settings</button>
                                <button type="button" class="button" id="cancelEditBtn2">Cancel</button>
                                <span class="save-status" id="agentSaveStatus"></span>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                
                <!-- Create New Agent -->
                <div class="create-agent-section">
                    <h3>➕ Create New Agent</h3>
                    <p>Need another agent? Create one here or in the TalkToPC app.</p>
                    <div class="create-agent-inline">
                        <input type="text" id="newAgentName" placeholder="Agent name (e.g., Hebrew Support)">
                        <button type="button" class="button button-primary" id="createAgentBtn">Create Agent</button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="card">
                <h2><span class="icon">🚀</span> TalkToPC App</h2>
                <p style="color: #646970; margin-bottom: 15px; font-size: 13px;">Advanced features available in the TalkToPC web app:</p>
                <div class="quick-links">
                    <a href="https://talktopc.com/agents" target="_blank" class="quick-link">
                        <span class="icon">📊</span>
                        <span class="text">Analytics Dashboard</span>
                    </a>
                    <a href="https://talktopc.com/agents/conversations" target="_blank" class="quick-link">
                        <span class="icon">🎙️</span>
                        <span class="text">Call Recording & Transcripts</span>
                    </a>
                    <a href="https://talktopc.com/agents/tools" target="_blank" class="quick-link">
                        <span class="icon">🔧</span>
                        <span class="text">Tools & Integrations</span>
                    </a>
                    <a href="https://talktopc.com/agents/usage" target="_blank" class="quick-link">
                        <span class="icon">📈</span>
                        <span class="text">Usage Tracking</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><span class="icon">🔗</span> Account Connection</h2>
                <p>Connect your TalkToPC account to get started.</p>
                <a href="<?php echo esc_url($connect_url); ?>" class="button button-primary button-hero"><?php echo esc_html__('Connect to TalkToPC', 'talktopc'); ?></a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    // Scripts and styles are now enqueued via admin_enqueue_scripts hook
    // No need to call render functions here
}

/**
 * FIX #3: Additional CSS for better Agent Settings alignment
 */
/**
 * Get agent settings CSS content
 * 
 * WordPress Plugin Review: Returns CSS as string for wp_add_inline_style()
 * 
 * @return string CSS content
 */
function talktopc_get_agent_settings_styles_css() {
    return '
    /* Agent loading indicator */
    .agent-loading-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 10px;
        font-size: 12px;
        color: #646970;
    }
    .agent-loading-indicator .spinner {
        width: 14px;
        height: 14px;
        border: 2px solid #ddd;
        border-top-color: #7C3AED;
        border-radius: 50%;
        animation: talktopc-spin 0.8s linear infinite;
    }
    .agent-loading-indicator.hidden {
        display: none;
    }
    
    /* Agent settings header with Edit button */
    .agent-settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f6f7f7;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .agent-settings-header .header-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .agent-settings-header .header-left h3 {
        margin: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .agent-settings-header .header-right {
        display: flex;
        gap: 8px;
    }
    
    .agent-settings-header .header-right .button {
        margin: 0;
    }
    
    /* Disabled state for fields */
    #agentSettingsForm input:disabled,
    #agentSettingsForm textarea:disabled,
    #agentSettingsForm select:disabled {
        background-color: #f6f7f7;
        color: #50575e;
        cursor: not-allowed;
        opacity: 0.8;
    }
    
    /* Edit mode styling */
    #agentSettingsForm.edit-mode input:not(:disabled),
    #agentSettingsForm.edit-mode textarea:not(:disabled),
    #agentSettingsForm.edit-mode select:not(:disabled) {
        background-color: #fff;
        border-color: #8c8f94;
    }
    
    #agentSettingsForm.edit-mode input:focus,
    #agentSettingsForm.edit-mode textarea:focus,
    #agentSettingsForm.edit-mode select:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: none;
    }
    
    /* ============================================
       Agent Settings Form - Clean Layout
       ============================================ */
    
    /* Form grid container */
    .agent-form-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    /* Each form row: label on left, field on right */
    .agent-form-grid .form-row {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    
    .agent-form-grid .form-row label {
        width: 130px;
        min-width: 130px;
        font-weight: 500;
        font-size: 13px;
        color: #1d2327;
        padding-top: 10px;
        text-align: right;
    }
    
    .agent-form-grid .form-row .field {
        flex: 1;
        max-width: 700px;
    }
    
    /* Textarea styling */
    .agent-form-grid .field textarea {
        width: 100%;
        min-height: 120px;
        padding: 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
    }
    
    /* Text input styling */
    .agent-form-grid .field input[type="text"] {
        width: 100%;
        max-width: 500px;
        padding: 10px 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
    }
    
    /* Select styling */
    .agent-form-grid .field select {
        width: 100%;
        max-width: 300px;
        padding: 10px 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .agent-form-grid .field-action {
        margin-top: 10px;
    }
    
    /* Section title */
    .agent-settings-body .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #1d2327;
        margin: 28px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    /* ============================================
       Advanced Settings - 5-column Grid
       ============================================ */
    .advanced-settings-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
        max-width: 100%;
    }
    
    .advanced-settings-grid .grid-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .advanced-settings-grid .grid-field label {
        font-size: 13px;
        font-weight: 500;
        color: #1d2327;
    }
    
    .advanced-settings-grid .grid-field input,
    .advanced-settings-grid .grid-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #8c8f94;
        border-radius: 4px;
        font-size: 14px;
        background: #fff;
    }
    
    .advanced-settings-grid .grid-field input:focus,
    .advanced-settings-grid .grid-field select:focus {
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
        outline: none;
    }
    
    /* Input with suffix (e.g., "sec") */
    .input-with-suffix {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .input-with-suffix input {
        flex: 1;
        min-width: 0;
    }
    .input-with-suffix .suffix {
        font-size: 13px;
        color: #646970;
        white-space: nowrap;
    }
    
    /* Save area */
    .agent-settings-body .save-area {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    /* Responsive: Stack on smaller screens */
    @media (max-width: 900px) {
        .advanced-settings-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 600px) {
        .agent-form-grid .form-row {
            flex-direction: column;
        }
        .agent-form-grid .form-row label {
            width: 100%;
            text-align: left;
            padding-top: 0;
            margin-bottom: 6px;
        }
        .advanced-settings-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    ';
}

/**
 * Enqueue agent settings styles using WordPress enqueue functions
 * 
 * WordPress Plugin Review: Uses wp_add_inline_style() instead of inline <style> tags
 */
function talktopc_enqueue_agent_settings_styles($hook) {
    // Only load on dashboard page
    if ($hook !== 'toplevel_page_talktopc') {
        return;
    }
    
    // Register dummy stylesheet handle
    wp_register_style('talktopc-agent-settings', false, [], TALKTOPC_VERSION);
    wp_enqueue_style('talktopc-agent-settings');
    
    // Add inline styles
    wp_add_inline_style('talktopc-agent-settings', talktopc_get_agent_settings_styles_css());
}
add_action('admin_enqueue_scripts', 'talktopc_enqueue_agent_settings_styles');

/**
 * Render agent settings styles (deprecated - kept for backwards compatibility)
 * 
 * @deprecated Use talktopc_enqueue_agent_settings_styles() instead
 */
function talktopc_render_agent_settings_styles() {
    // This function is deprecated but kept for backwards compatibility
    // Styles are now enqueued via admin_enqueue_scripts hook
}