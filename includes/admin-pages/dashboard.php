<?php
/**
 * Dashboard Page
 * 
 * FIXES:
 * - Issue #1: Show saved agent immediately from PHP, then update from AJAX
 * - Issue #3: Better aligned Agent Settings UI with CSS grid
 */

if (!defined('ABSPATH')) exit;


function ttp_render_dashboard_page() {
    $is_connected = !empty(get_option('ttp_api_key'));
    $user_email = get_option('ttp_user_email', '');
    $current_agent_id = get_option('ttp_agent_id', '');
    $current_agent_name = get_option('ttp_agent_name', '');
    
    // Build OAuth URLs
    $connect_url = admin_url('admin-post.php?action=ttp_connect');
    $disconnect_url = wp_nonce_url(admin_url('admin.php?page=talktopc&action=disconnect'), 'ttp_disconnect');
    
    // Enqueue WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    ttp_render_admin_styles();
    ttp_render_agent_settings_styles(); // New: Additional styles for agent settings
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Dashboard</h1>
            <span class="version">v<?php echo esc_html(TTP_VERSION); ?></span>
        </div>
        
        <?php settings_errors(); ?>
        
        <?php if ($is_connected): ?>
            <!-- Credits Box -->
            <div class="credits-box" id="ttpCreditsBox">
                <div class="credits-info">
                    <h3>Available Credits</h3>
                    <div class="amount" id="ttpCreditsAmount">Loading...</div>
                    <div class="label">voice minutes remaining</div>
                </div>
                <a href="https://talktopc.com/upgrade" target="_blank" class="button">Buy More →</a>
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
                    <select id="defaultAgentSelect" name="ttp_agent_id">
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
                    <div class="agent-settings-header" onclick="toggleAgentSettings()">
                        <h3><span>⚙️</span> Agent Settings</h3>
                        <span class="arrow">▼</span>
                    </div>
                    <div class="agent-settings-body">
                        <!-- Settings saved via AJAX to both WordPress (cache) and TalkToPC backend (DB) -->
                        <div id="agentSettingsForm">
                            
                            <!-- FIX #3: Better aligned form layout -->
                            <div class="agent-form-grid">
                                <div class="form-row full-width">
                                    <label for="ttp_override_prompt">System Prompt</label>
                                    <div class="field">
                                        <textarea id="ttp_override_prompt" rows="5"><?php echo esc_textarea(get_option('ttp_override_prompt', '')); ?></textarea>
                                        <p class="field-action">
                                            <button type="button" class="button button-small" id="ttpGeneratePrompt">🔄 Generate from Site Content</button>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="form-row full-width">
                                    <label for="ttp_override_first_message">First Message</label>
                                    <div class="field">
                                        <input type="text" id="ttp_override_first_message" value="<?php echo esc_attr(get_option('ttp_override_first_message', '')); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row full-width">
                                    <label for="ttp_override_voice">Voice</label>
                                    <div class="field">
                                        <select id="ttp_override_voice">
                                            <option value="">-- Select Voice --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="section-title">Advanced</h3>
                            
                            <!-- FIX #3: CSS Grid for aligned inline fields -->
                            <div class="advanced-settings-grid">
                                <div class="grid-field">
                                    <label for="ttp_override_voice_speed">Speed</label>
                                    <input type="number" id="ttp_override_voice_speed" value="<?php echo esc_attr(get_option('ttp_override_voice_speed', '1.0')); ?>" step="0.1" min="0.5" max="2.0">
                                </div>
                                
                                <div class="grid-field">
                                    <label for="ttp_override_language">Language</label>
                                    <select id="ttp_override_language">
                                        <option value="">-- Select Language --</option>
                                    </select>
                                </div>
                                
                                <div class="grid-field">
                                    <label for="ttp_override_temperature">Temperature</label>
                                    <input type="number" id="ttp_override_temperature" value="<?php echo esc_attr(get_option('ttp_override_temperature', '0.7')); ?>" step="0.1" min="0" max="2">
                                </div>
                                
                                <div class="grid-field">
                                    <label for="ttp_override_max_tokens">Max Tokens</label>
                                    <input type="number" id="ttp_override_max_tokens" value="<?php echo esc_attr(get_option('ttp_override_max_tokens', '1000')); ?>">
                                </div>
                                
                                <div class="grid-field">
                                    <label for="ttp_override_max_call_duration">Max Duration</label>
                                    <div class="input-with-suffix">
                                        <input type="number" id="ttp_override_max_call_duration" value="<?php echo esc_attr(get_option('ttp_override_max_call_duration', '300')); ?>">
                                        <span class="suffix">sec</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="save-area">
                                <button type="button" class="button button-primary" id="saveAgentSettingsBtn">Save Agent Settings</button>
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
                    <a href="https://app.talktopc.com/analytics" target="_blank" class="quick-link">
                        <span class="icon">📊</span>
                        <span class="text">Analytics Dashboard</span>
                    </a>
                    <a href="https://app.talktopc.com/recordings" target="_blank" class="quick-link">
                        <span class="icon">🎙️</span>
                        <span class="text">Call Recording</span>
                    </a>
                    <a href="https://app.talktopc.com/conversations" target="_blank" class="quick-link">
                        <span class="icon">📝</span>
                        <span class="text">Conversation History</span>
                    </a>
                    <a href="https://app.talktopc.com/integrations" target="_blank" class="quick-link">
                        <span class="icon">🔧</span>
                        <span class="text">Tools & Integrations</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2><span class="icon">🔗</span> Account Connection</h2>
                <p>Connect your TalkToPC account to get started.</p>
                <a href="<?php echo esc_url($connect_url); ?>" class="button button-primary button-hero">Connect to TalkToPC</a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    ttp_render_common_scripts();
    ttp_render_dashboard_scripts($current_agent_id);
}

/**
 * FIX #3: Additional CSS for better Agent Settings alignment
 */
function ttp_render_agent_settings_styles() {
    ?>
    <style>
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
        animation: ttp-spin 0.8s linear infinite;
    }
    .agent-loading-indicator.hidden {
        display: none;
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
    </style>
    <?php
}