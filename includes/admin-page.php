<?php
/**
 * Admin Settings Page
 * 
 * Renders the full admin UI for the plugin settings.
 * Includes all HTML, JavaScript, and CSS for:
 * - Connection status
 * - Agent selection/creation
 * - Agent settings override
 * - Widget appearance customization
 */

if (!defined('ABSPATH')) exit;

/**
 * Main settings page function (called from admin menu)
 */
function ttp_settings_page() {
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
    
    ?>
    <div class="wrap ttp-settings-wrap">
        <h1><?php esc_html_e('TalkToPC Voice Widget', 'talktopc'); ?> <small style="font-size: 12px; color: #666;">v<?php echo esc_html(TTP_VERSION); ?></small></h1>
        
        <?php settings_errors(); ?>
        <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag ?>
        <?php if (isset($_GET['settings-updated'])): ?><div class="notice notice-success is-dismissible"><p>Settings saved!</p></div><?php endif; ?>
        <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag ?>
        <?php if (isset($_GET['connected'])): ?><div class="notice notice-success is-dismissible"><p>Connected to TalkToPC!</p></div><?php endif; ?>
        <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag ?>
        <?php if (isset($_GET['disconnected'])): ?><div class="notice notice-info is-dismissible"><p>Disconnected.</p></div><?php endif; ?>
        
        <!-- Connection Card -->
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
            
            <?php ttp_render_agent_selection($current_agent_id, $current_agent_name); ?>
            <?php ttp_render_agent_overrides(); ?>
            <?php ttp_render_behavior_settings(); ?>
            <?php ttp_render_button_settings(); ?>
            <?php ttp_render_icon_settings(); ?>
            <?php ttp_render_panel_settings(); ?>
            <?php ttp_render_header_settings(); ?>
            <?php ttp_render_footer_settings(); ?>
            <?php ttp_render_landing_settings(); ?>
            <?php ttp_render_message_settings(); ?>
            <?php ttp_render_voice_settings(); ?>
            <?php ttp_render_text_settings(); ?>
            <?php ttp_render_tooltip_settings(); ?>
            <?php ttp_render_animation_settings(); ?>
            <?php ttp_render_accessibility_settings(); ?>
            <?php ttp_render_custom_css(); ?>
            
            <?php submit_button('Save Settings'); ?>
        </form>
        
        <?php ttp_render_advanced_features(); ?>
        
        <?php if (!empty($current_agent_id)): ?>
        <div class="ttp-card ttp-status-card">
            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
            <strong>Widget is active!</strong> Visit your site to see the voice widget.
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php ttp_render_admin_styles(); ?>
    <?php if ($is_connected): ttp_render_admin_scripts($current_agent_id); endif; ?>
    <?php
}

// =============================================================================
// SETTINGS SECTION RENDERERS
// =============================================================================

function ttp_render_agent_selection($current_agent_id, $current_agent_name) {
    ?>
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
    <?php
}

function ttp_render_agent_overrides() {
    ?>
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
                    </td></tr>
                <tr><th><label for="ttp_override_first_message">First Message</label></th>
                    <td><input type="text" id="ttp_override_first_message" name="ttp_override_first_message" value="<?php echo esc_attr(get_option('ttp_override_first_message')); ?>" class="large-text"></td></tr>
                <tr><th><label for="ttp_override_voice">Voice</label></th>
                    <td><select id="ttp_override_voice" name="ttp_override_voice" class="regular-text"><option value="">-- Use agent default --</option></select><span id="ttp-voice-loading" class="spinner"></span></td></tr>
                <tr><th><label for="ttp_override_voice_speed">Voice Speed</label></th>
                    <td><input type="number" id="ttp_override_voice_speed" name="ttp_override_voice_speed" value="<?php echo esc_attr(get_option('ttp_override_voice_speed')); ?>" class="small-text" min="0.5" max="2.0" step="0.1"> <span class="description">0.5 to 2.0</span></td></tr>
                <tr><th><label for="ttp_override_language">Language</label></th>
                    <td><select id="ttp_override_language" name="ttp_override_language" class="regular-text"><option value="">-- All languages --</option></select><span id="ttp-language-loading" class="spinner"></span></td></tr>
                <tr><th><label for="ttp_override_temperature">Temperature</label></th>
                    <td><input type="number" id="ttp_override_temperature" name="ttp_override_temperature" value="<?php echo esc_attr(get_option('ttp_override_temperature')); ?>" class="small-text" min="0" max="2" step="0.1"> <span class="description">0 to 2</span></td></tr>
                <tr><th><label for="ttp_override_max_tokens">Max Tokens</label></th>
                    <td><input type="number" id="ttp_override_max_tokens" name="ttp_override_max_tokens" value="<?php echo esc_attr(get_option('ttp_override_max_tokens')); ?>" class="small-text" min="50" max="4000"></td></tr>
                <tr><th><label for="ttp_override_max_call_duration">Max Call Duration</label></th>
                    <td><input type="number" id="ttp_override_max_call_duration" name="ttp_override_max_call_duration" value="<?php echo esc_attr(get_option('ttp_override_max_call_duration')); ?>" class="small-text" min="30" max="3600"> <span class="description">seconds</span></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_behavior_settings() {
    ?>
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
    <?php
}

function ttp_render_button_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Floating Button <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
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
                <tr><th><label for="ttp_button_shadow_color">Shadow Color</label></th>
                    <td><input type="text" id="ttp_button_shadow_color" name="ttp_button_shadow_color" value="<?php echo esc_attr(get_option('ttp_button_shadow_color', 'rgba(0,0,0,0.15)')); ?>" class="regular-text" placeholder="rgba(0,0,0,0.15)"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_icon_settings() {
    ?>
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
                <tr><th><label for="ttp_icon_bg_color">Icon Background</label></th>
                    <td><input type="text" id="ttp_icon_bg_color" name="ttp_icon_bg_color" value="<?php echo esc_attr(get_option('ttp_icon_bg_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_panel_settings() {
    ?>
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
                <tr><th><label for="ttp_panel_border">Border</label></th>
                    <td><input type="text" id="ttp_panel_border" name="ttp_panel_border" value="<?php echo esc_attr(get_option('ttp_panel_border', '1px solid #E5E7EB')); ?>" class="regular-text" placeholder="1px solid #E5E7EB">
                        <p class="description">CSS border value. Examples: <code>1px solid #E5E7EB</code>, <code>none</code></p></td></tr>
                <tr><th><label for="ttp_panel_backdrop_filter">Backdrop Filter</label></th>
                    <td><input type="text" id="ttp_panel_backdrop_filter" name="ttp_panel_backdrop_filter" value="<?php echo esc_attr(get_option('ttp_panel_backdrop_filter')); ?>" class="regular-text" placeholder="blur(10px)">
                        <p class="description">CSS backdrop-filter. Example: <code>blur(10px)</code></p></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_header_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Header Settings <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <table class="form-table">
                <tr><th><label for="ttp_header_title">Title</label></th>
                    <td><input type="text" id="ttp_header_title" name="ttp_header_title" value="<?php echo esc_attr(get_option('ttp_header_title')); ?>" class="regular-text" placeholder="Chat Assistant"></td></tr>
                <tr><th>Show Title</th>
                    <td><label><input type="checkbox" name="ttp_header_show_title" value="1" <?php checked(get_option('ttp_header_show_title', '1'), '1'); ?>> Display title in header</label></td></tr>
                <tr><th><label for="ttp_header_bg_color">Background Color</label></th>
                    <td><input type="text" id="ttp_header_bg_color" name="ttp_header_bg_color" value="<?php echo esc_attr(get_option('ttp_header_bg_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="ttp_header_text_color">Text Color</label></th>
                    <td><input type="text" id="ttp_header_text_color" name="ttp_header_text_color" value="<?php echo esc_attr(get_option('ttp_header_text_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th>Close Button</th>
                    <td><label><input type="checkbox" name="ttp_header_show_close" value="1" <?php checked(get_option('ttp_header_show_close', '1'), '1'); ?>> Show close button</label></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_footer_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Footer Branding <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <p class="description"><strong>⚡ Powered by TalkToPC</strong> — Displayed at the bottom of the chat panel.</p>
            <div class="ttp-footer-preview" style="margin: 15px 0; padding: 8px 16px; background: <?php echo esc_attr(get_option('ttp_footer_bg_color', '#f9fafb')); ?>; border: 1px solid #e5e7eb; border-radius: 4px; text-align: center;">
                <span style="font-size: 11px; color: <?php echo esc_attr(get_option('ttp_footer_text_color', '#9ca3af')); ?>;">⚡ Powered by <strong>TalkToPC</strong></span>
            </div>
            <table class="form-table">
                <tr><th><label for="ttp_footer_bg_color">Background Color</label></th>
                    <td><input type="text" id="ttp_footer_bg_color" name="ttp_footer_bg_color" value="<?php echo esc_attr(get_option('ttp_footer_bg_color')); ?>" class="ttp-color-picker" data-default-color="#f9fafb"></td></tr>
                <tr><th><label for="ttp_footer_text_color">Text Color</label></th>
                    <td><input type="text" id="ttp_footer_text_color" name="ttp_footer_text_color" value="<?php echo esc_attr(get_option('ttp_footer_text_color')); ?>" class="ttp-color-picker" data-default-color="#9ca3af"></td></tr>
                <tr><th><label for="ttp_footer_hover_color">Hover Color</label></th>
                    <td><input type="text" id="ttp_footer_hover_color" name="ttp_footer_hover_color" value="<?php echo esc_attr(get_option('ttp_footer_hover_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_landing_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Landing Screen <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <p class="description">Shown in unified mode when user first opens widget.</p>
            <table class="form-table">
                <tr><th><label for="ttp_landing_bg_color">Background</label></th>
                    <td><input type="text" id="ttp_landing_bg_color" name="ttp_landing_bg_color" value="<?php echo esc_attr(get_option('ttp_landing_bg_color')); ?>" class="large-text" placeholder="linear-gradient(180deg, #f8fafc 0%, #e0e7ff 100%)">
                        <p class="description">CSS color or gradient</p></td></tr>
                <tr><th><label for="ttp_landing_logo">Logo (emoji/text)</label></th>
                    <td><input type="text" id="ttp_landing_logo" name="ttp_landing_logo" value="<?php echo esc_attr(get_option('ttp_landing_logo')); ?>" class="small-text" placeholder="🤖"></td></tr>
                <tr><th><label for="ttp_landing_title">Title</label></th>
                    <td><input type="text" id="ttp_landing_title" name="ttp_landing_title" value="<?php echo esc_attr(get_option('ttp_landing_title')); ?>" class="regular-text" placeholder="How would you like to chat?"></td></tr>
                <tr><th><label for="ttp_landing_title_color">Title Color</label></th>
                    <td><input type="text" id="ttp_landing_title_color" name="ttp_landing_title_color" value="<?php echo esc_attr(get_option('ttp_landing_title_color')); ?>" class="ttp-color-picker" data-default-color="#1e293b"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Mode Selection Cards</h4>
            <table class="form-table">
                <tr><th><label for="ttp_landing_card_bg_color">Card Background</label></th>
                    <td><input type="text" id="ttp_landing_card_bg_color" name="ttp_landing_card_bg_color" value="<?php echo esc_attr(get_option('ttp_landing_card_bg_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="ttp_landing_card_border_color">Card Border</label></th>
                    <td><input type="text" id="ttp_landing_card_border_color" name="ttp_landing_card_border_color" value="<?php echo esc_attr(get_option('ttp_landing_card_border_color')); ?>" class="ttp-color-picker" data-default-color="#E2E8F0"></td></tr>
                <tr><th><label for="ttp_landing_card_hover_border_color">Card Hover Border</label></th>
                    <td><input type="text" id="ttp_landing_card_hover_border_color" name="ttp_landing_card_hover_border_color" value="<?php echo esc_attr(get_option('ttp_landing_card_hover_border_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="ttp_landing_card_icon_bg_color">Card Icon Background</label></th>
                    <td><input type="text" id="ttp_landing_card_icon_bg_color" name="ttp_landing_card_icon_bg_color" value="<?php echo esc_attr(get_option('ttp_landing_card_icon_bg_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="ttp_landing_card_title_color">Card Title Color</label></th>
                    <td><input type="text" id="ttp_landing_card_title_color" name="ttp_landing_card_title_color" value="<?php echo esc_attr(get_option('ttp_landing_card_title_color')); ?>" class="ttp-color-picker" data-default-color="#111827"></td></tr>
                <tr><th><label for="ttp_landing_voice_icon">Voice Card Icon</label></th>
                    <td><input type="text" id="ttp_landing_voice_icon" name="ttp_landing_voice_icon" value="<?php echo esc_attr(get_option('ttp_landing_voice_icon', '🎤')); ?>" class="small-text" placeholder="🎤"></td></tr>
                <tr><th><label for="ttp_landing_voice_title">Voice Card Title</label></th>
                    <td><input type="text" id="ttp_landing_voice_title" name="ttp_landing_voice_title" value="<?php echo esc_attr(get_option('ttp_landing_voice_title')); ?>" class="regular-text" placeholder="Voice Call"></td></tr>
                <tr><th><label for="ttp_landing_text_icon">Text Card Icon</label></th>
                    <td><input type="text" id="ttp_landing_text_icon" name="ttp_landing_text_icon" value="<?php echo esc_attr(get_option('ttp_landing_text_icon', '💬')); ?>" class="small-text" placeholder="💬"></td></tr>
                <tr><th><label for="ttp_landing_text_title">Text Card Title</label></th>
                    <td><input type="text" id="ttp_landing_text_title" name="ttp_landing_text_title" value="<?php echo esc_attr(get_option('ttp_landing_text_title')); ?>" class="regular-text" placeholder="Text Chat"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_message_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Message Styling <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <table class="form-table">
                <tr><th><label for="ttp_msg_user_bg">User Message Background</label></th>
                    <td><input type="text" id="ttp_msg_user_bg" name="ttp_msg_user_bg" value="<?php echo esc_attr(get_option('ttp_msg_user_bg')); ?>" class="ttp-color-picker" data-default-color="#E5E7EB"></td></tr>
                <tr><th><label for="ttp_msg_agent_bg">Agent Message Background</label></th>
                    <td><input type="text" id="ttp_msg_agent_bg" name="ttp_msg_agent_bg" value="<?php echo esc_attr(get_option('ttp_msg_agent_bg')); ?>" class="ttp-color-picker" data-default-color="#F3F4F6"></td></tr>
                <tr><th><label for="ttp_msg_system_bg">System Message Background</label></th>
                    <td><input type="text" id="ttp_msg_system_bg" name="ttp_msg_system_bg" value="<?php echo esc_attr(get_option('ttp_msg_system_bg')); ?>" class="ttp-color-picker" data-default-color="#DCFCE7"></td></tr>
                <tr><th><label for="ttp_msg_error_bg">Error Message Background</label></th>
                    <td><input type="text" id="ttp_msg_error_bg" name="ttp_msg_error_bg" value="<?php echo esc_attr(get_option('ttp_msg_error_bg')); ?>" class="ttp-color-picker" data-default-color="#FEE2E2"></td></tr>
                <tr><th><label for="ttp_msg_text_color">Text Color</label></th>
                    <td><input type="text" id="ttp_msg_text_color" name="ttp_msg_text_color" value="<?php echo esc_attr(get_option('ttp_msg_text_color')); ?>" class="ttp-color-picker" data-default-color="#1F2937"></td></tr>
                <tr><th><label for="ttp_msg_font_size">Font Size</label></th>
                    <td><input type="text" id="ttp_msg_font_size" name="ttp_msg_font_size" value="<?php echo esc_attr(get_option('ttp_msg_font_size', '14px')); ?>" class="small-text" placeholder="14px"></td></tr>
                <tr><th><label for="ttp_msg_border_radius">Border Radius (px)</label></th>
                    <td><input type="number" id="ttp_msg_border_radius" name="ttp_msg_border_radius" value="<?php echo esc_attr(get_option('ttp_msg_border_radius', 8)); ?>" class="small-text" min="0" max="20"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_voice_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Voice Interface <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <h4>Microphone Button</h4>
            <table class="form-table">
                <tr><th><label for="ttp_voice_mic_color">Mic Button Color</label></th>
                    <td><input type="text" id="ttp_voice_mic_color" name="ttp_voice_mic_color" value="<?php echo esc_attr(get_option('ttp_voice_mic_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="ttp_voice_mic_active_color">Mic Active Color</label></th>
                    <td><input type="text" id="ttp_voice_mic_active_color" name="ttp_voice_mic_active_color" value="<?php echo esc_attr(get_option('ttp_voice_mic_active_color')); ?>" class="ttp-color-picker" data-default-color="#EF4444"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Avatar</h4>
            <table class="form-table">
                <tr><th><label for="ttp_voice_avatar_color">Avatar Background</label></th>
                    <td><input type="text" id="ttp_voice_avatar_color" name="ttp_voice_avatar_color" value="<?php echo esc_attr(get_option('ttp_voice_avatar_color')); ?>" class="ttp-color-picker" data-default-color="#667eea"></td></tr>
                <tr><th><label for="ttp_voice_avatar_active_color">Avatar Active Background</label></th>
                    <td><input type="text" id="ttp_voice_avatar_active_color" name="ttp_voice_avatar_active_color" value="<?php echo esc_attr(get_option('ttp_voice_avatar_active_color')); ?>" class="ttp-color-picker" data-default-color="#667eea"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Status Text</h4>
            <table class="form-table">
                <tr><th><label for="ttp_voice_status_title_color">Status Title Color</label></th>
                    <td><input type="text" id="ttp_voice_status_title_color" name="ttp_voice_status_title_color" value="<?php echo esc_attr(get_option('ttp_voice_status_title_color')); ?>" class="ttp-color-picker" data-default-color="#1e293b"></td></tr>
                <tr><th><label for="ttp_voice_status_subtitle_color">Status Subtitle Color</label></th>
                    <td><input type="text" id="ttp_voice_status_subtitle_color" name="ttp_voice_status_subtitle_color" value="<?php echo esc_attr(get_option('ttp_voice_status_subtitle_color')); ?>" class="ttp-color-picker" data-default-color="#64748b"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Start Call Button</h4>
            <table class="form-table">
                <tr><th><label for="ttp_voice_start_title">Title Text</label></th>
                    <td><input type="text" id="ttp_voice_start_title" name="ttp_voice_start_title" value="<?php echo esc_attr(get_option('ttp_voice_start_title')); ?>" class="regular-text" placeholder="Ready to chat?"></td></tr>
                <tr><th><label for="ttp_voice_start_subtitle">Subtitle Text</label></th>
                    <td><input type="text" id="ttp_voice_start_subtitle" name="ttp_voice_start_subtitle" value="<?php echo esc_attr(get_option('ttp_voice_start_subtitle')); ?>" class="regular-text" placeholder="Speak naturally"></td></tr>
                <tr><th><label for="ttp_voice_start_btn_text">Button Text</label></th>
                    <td><input type="text" id="ttp_voice_start_btn_text" name="ttp_voice_start_btn_text" value="<?php echo esc_attr(get_option('ttp_voice_start_btn_text')); ?>" class="regular-text" placeholder="Start Call"></td></tr>
                <tr><th><label for="ttp_voice_start_btn_color">Button Color</label></th>
                    <td><input type="text" id="ttp_voice_start_btn_color" name="ttp_voice_start_btn_color" value="<?php echo esc_attr(get_option('ttp_voice_start_btn_color')); ?>" class="ttp-color-picker" data-default-color="#667eea"></td></tr>
                <tr><th><label for="ttp_voice_start_btn_text_color">Button Text Color</label></th>
                    <td><input type="text" id="ttp_voice_start_btn_text_color" name="ttp_voice_start_btn_text_color" value="<?php echo esc_attr(get_option('ttp_voice_start_btn_text_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Transcript</h4>
            <table class="form-table">
                <tr><th><label for="ttp_voice_transcript_bg_color">Background</label></th>
                    <td><input type="text" id="ttp_voice_transcript_bg_color" name="ttp_voice_transcript_bg_color" value="<?php echo esc_attr(get_option('ttp_voice_transcript_bg_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="ttp_voice_transcript_text_color">Text Color</label></th>
                    <td><input type="text" id="ttp_voice_transcript_text_color" name="ttp_voice_transcript_text_color" value="<?php echo esc_attr(get_option('ttp_voice_transcript_text_color')); ?>" class="ttp-color-picker" data-default-color="#1e293b"></td></tr>
                <tr><th><label for="ttp_voice_transcript_label_color">Label Color</label></th>
                    <td><input type="text" id="ttp_voice_transcript_label_color" name="ttp_voice_transcript_label_color" value="<?php echo esc_attr(get_option('ttp_voice_transcript_label_color')); ?>" class="ttp-color-picker" data-default-color="#94a3b8"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Control Buttons</h4>
            <table class="form-table">
                <tr><th><label for="ttp_voice_control_btn_color">Control Button Color</label></th>
                    <td><input type="text" id="ttp_voice_control_btn_color" name="ttp_voice_control_btn_color" value="<?php echo esc_attr(get_option('ttp_voice_control_btn_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="ttp_voice_control_btn_secondary_color">Secondary Color</label></th>
                    <td><input type="text" id="ttp_voice_control_btn_secondary_color" name="ttp_voice_control_btn_secondary_color" value="<?php echo esc_attr(get_option('ttp_voice_control_btn_secondary_color')); ?>" class="ttp-color-picker" data-default-color="#64748b"></td></tr>
                <tr><th><label for="ttp_voice_end_btn_color">End Call Button</label></th>
                    <td><input type="text" id="ttp_voice_end_btn_color" name="ttp_voice_end_btn_color" value="<?php echo esc_attr(get_option('ttp_voice_end_btn_color')); ?>" class="ttp-color-picker" data-default-color="#EF4444"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_text_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Text Interface <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <h4>Send Button</h4>
            <table class="form-table">
                <tr><th><label for="ttp_text_send_btn_color">Button Color</label></th>
                    <td><input type="text" id="ttp_text_send_btn_color" name="ttp_text_send_btn_color" value="<?php echo esc_attr(get_option('ttp_text_send_btn_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="ttp_text_send_btn_hover_color">Hover Color</label></th>
                    <td><input type="text" id="ttp_text_send_btn_hover_color" name="ttp_text_send_btn_hover_color" value="<?php echo esc_attr(get_option('ttp_text_send_btn_hover_color')); ?>" class="ttp-color-picker" data-default-color="#6D28D9"></td></tr>
                <tr><th><label for="ttp_text_send_btn_text">Button Text</label></th>
                    <td><input type="text" id="ttp_text_send_btn_text" name="ttp_text_send_btn_text" value="<?php echo esc_attr(get_option('ttp_text_send_btn_text', '➤')); ?>" class="small-text" placeholder="➤"></td></tr>
                <tr><th><label for="ttp_text_send_btn_text_color">Text Color</label></th>
                    <td><input type="text" id="ttp_text_send_btn_text_color" name="ttp_text_send_btn_text_color" value="<?php echo esc_attr(get_option('ttp_text_send_btn_text_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Input Field</h4>
            <table class="form-table">
                <tr><th><label for="ttp_text_input_placeholder">Placeholder</label></th>
                    <td><input type="text" id="ttp_text_input_placeholder" name="ttp_text_input_placeholder" value="<?php echo esc_attr(get_option('ttp_text_input_placeholder')); ?>" class="regular-text" placeholder="Type your message..."></td></tr>
                <tr><th><label for="ttp_text_input_bg_color">Background Color</label></th>
                    <td><input type="text" id="ttp_text_input_bg_color" name="ttp_text_input_bg_color" value="<?php echo esc_attr(get_option('ttp_text_input_bg_color')); ?>" class="ttp-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="ttp_text_input_text_color">Text Color</label></th>
                    <td><input type="text" id="ttp_text_input_text_color" name="ttp_text_input_text_color" value="<?php echo esc_attr(get_option('ttp_text_input_text_color')); ?>" class="ttp-color-picker" data-default-color="#1F2937"></td></tr>
                <tr><th><label for="ttp_text_input_border_color">Border Color</label></th>
                    <td><input type="text" id="ttp_text_input_border_color" name="ttp_text_input_border_color" value="<?php echo esc_attr(get_option('ttp_text_input_border_color')); ?>" class="ttp-color-picker" data-default-color="#E5E7EB"></td></tr>
                <tr><th><label for="ttp_text_input_focus_color">Focus Color</label></th>
                    <td><input type="text" id="ttp_text_input_focus_color" name="ttp_text_input_focus_color" value="<?php echo esc_attr(get_option('ttp_text_input_focus_color')); ?>" class="ttp-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="ttp_text_input_border_radius">Border Radius (px)</label></th>
                    <td><input type="number" id="ttp_text_input_border_radius" name="ttp_text_input_border_radius" value="<?php echo esc_attr(get_option('ttp_text_input_border_radius', 20)); ?>" class="small-text" min="0" max="30"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_tooltip_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Tooltips <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <p class="description">Custom tooltip text for buttons. Leave empty to use defaults.</p>
            <table class="form-table">
                <tr><th><label for="ttp_tooltip_new_chat">New Chat</label></th>
                    <td><input type="text" id="ttp_tooltip_new_chat" name="ttp_tooltip_new_chat" value="<?php echo esc_attr(get_option('ttp_tooltip_new_chat')); ?>" class="regular-text" placeholder="New Chat"></td></tr>
                <tr><th><label for="ttp_tooltip_back">Back</label></th>
                    <td><input type="text" id="ttp_tooltip_back" name="ttp_tooltip_back" value="<?php echo esc_attr(get_option('ttp_tooltip_back')); ?>" class="regular-text" placeholder="Back"></td></tr>
                <tr><th><label for="ttp_tooltip_close">Close</label></th>
                    <td><input type="text" id="ttp_tooltip_close" name="ttp_tooltip_close" value="<?php echo esc_attr(get_option('ttp_tooltip_close')); ?>" class="regular-text" placeholder="Close"></td></tr>
                <tr><th><label for="ttp_tooltip_mute">Mute</label></th>
                    <td><input type="text" id="ttp_tooltip_mute" name="ttp_tooltip_mute" value="<?php echo esc_attr(get_option('ttp_tooltip_mute')); ?>" class="regular-text" placeholder="Mute"></td></tr>
                <tr><th><label for="ttp_tooltip_speaker">Speaker</label></th>
                    <td><input type="text" id="ttp_tooltip_speaker" name="ttp_tooltip_speaker" value="<?php echo esc_attr(get_option('ttp_tooltip_speaker')); ?>" class="regular-text" placeholder="Speaker"></td></tr>
                <tr><th><label for="ttp_tooltip_end_call">End Call</label></th>
                    <td><input type="text" id="ttp_tooltip_end_call" name="ttp_tooltip_end_call" value="<?php echo esc_attr(get_option('ttp_tooltip_end_call')); ?>" class="regular-text" placeholder="End Call"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_animation_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Animation <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <table class="form-table">
                <tr><th>Enable Hover</th>
                    <td><label><input type="checkbox" name="ttp_anim_enable_hover" value="1" <?php checked(get_option('ttp_anim_enable_hover', '1'), '1'); ?>> Enable hover animations</label></td></tr>
                <tr><th>Enable Pulse</th>
                    <td><label><input type="checkbox" name="ttp_anim_enable_pulse" value="1" <?php checked(get_option('ttp_anim_enable_pulse', '1'), '1'); ?>> Enable pulse animation when recording</label></td></tr>
                <tr><th>Enable Slide</th>
                    <td><label><input type="checkbox" name="ttp_anim_enable_slide" value="1" <?php checked(get_option('ttp_anim_enable_slide', '1'), '1'); ?>> Enable slide animations</label></td></tr>
                <tr><th><label for="ttp_anim_duration">Duration (seconds)</label></th>
                    <td><input type="number" id="ttp_anim_duration" name="ttp_anim_duration" value="<?php echo esc_attr(get_option('ttp_anim_duration', '0.3')); ?>" class="small-text" min="0.1" max="1" step="0.1"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_accessibility_settings() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Accessibility <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <table class="form-table">
                <tr><th><label for="ttp_a11y_aria_label">Aria Label</label></th>
                    <td><input type="text" id="ttp_a11y_aria_label" name="ttp_a11y_aria_label" value="<?php echo esc_attr(get_option('ttp_a11y_aria_label')); ?>" class="regular-text" placeholder="Chat Assistant"></td></tr>
                <tr><th><label for="ttp_a11y_aria_description">Aria Description</label></th>
                    <td><input type="text" id="ttp_a11y_aria_description" name="ttp_a11y_aria_description" value="<?php echo esc_attr(get_option('ttp_a11y_aria_description')); ?>" class="large-text" placeholder="Click to open chat assistant"></td></tr>
                <tr><th>Keyboard Navigation</th>
                    <td><label><input type="checkbox" name="ttp_a11y_keyboard_nav" value="1" <?php checked(get_option('ttp_a11y_keyboard_nav', '1'), '1'); ?>> Enable keyboard navigation (ESC to close)</label></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function ttp_render_custom_css() {
    ?>
    <div class="ttp-card ttp-collapsible">
        <h2 class="ttp-collapsible-header">Custom CSS <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="ttp-collapsible-content">
            <p class="description">Add custom CSS to further customize the widget.</p>
            <textarea id="ttp_custom_css" name="ttp_custom_css" rows="8" class="large-text code" placeholder="#text-chat-button { /* your styles */ }"><?php echo esc_textarea(get_option('ttp_custom_css')); ?></textarea>
        </div>
    </div>
    <?php
}

function ttp_render_advanced_features() {
    ?>
    <div class="ttp-card ttp-advanced-features">
        <div class="ttp-advanced-header">
            <span class="dashicons dashicons-admin-generic"></span>
            <h2>Advanced Features</h2>
        </div>
        <p class="ttp-advanced-subtitle">Configure these options in the TalkToPC app</p>
        
        <!-- Call Management -->
        <div class="ttp-feature-group">
            <h4>📞 Call Management</h4>
            <div class="ttp-feature-list">
                <div class="ttp-feature-item">
                    <span class="ttp-feature-icon">🎙️</span>
                    <span class="ttp-feature-name">Call Recording</span>
                    <span class="ttp-feature-status">Configure in app</span>
                </div>
                <a href="https://talktopc.com/agents/conversations" target="_blank" class="ttp-feature-item ttp-feature-clickable">
                    <span class="ttp-feature-icon">🔊</span>
                    <span class="ttp-feature-name">Export Calls (Audio)</span>
                    <span class="ttp-feature-action">Open →</span>
                </a>
                <a href="https://talktopc.com/agents/conversations" target="_blank" class="ttp-feature-item ttp-feature-clickable">
                    <span class="ttp-feature-icon">📝</span>
                    <span class="ttp-feature-name">Export Transcripts</span>
                    <span class="ttp-feature-action">Open →</span>
                </a>
            </div>
        </div>
        
        <!-- AI Configuration -->
        <div class="ttp-feature-group">
            <h4>🤖 AI Configuration</h4>
            <div class="ttp-feature-list">
                <div class="ttp-feature-item">
                    <span class="ttp-feature-icon">🧠</span>
                    <span class="ttp-feature-name">LLM Model</span>
                    <span class="ttp-feature-status">Configure in app</span>
                </div>
                <div class="ttp-feature-item">
                    <span class="ttp-feature-icon">🎛️</span>
                    <span class="ttp-feature-name">Temperature</span>
                    <span class="ttp-feature-status">Configure in app</span>
                </div>
                <div class="ttp-feature-item">
                    <span class="ttp-feature-icon">🔧</span>
                    <span class="ttp-feature-name">Add Tools</span>
                    <span class="ttp-feature-status">Configure in app</span>
                </div>
            </div>
        </div>
        
        <!-- Analytics -->
        <div class="ttp-feature-group">
            <h4>📊 Analytics</h4>
            <div class="ttp-feature-list">
                <div class="ttp-feature-item">
                    <span class="ttp-feature-icon">📊</span>
                    <span class="ttp-feature-name">Dashboard</span>
                    <span class="ttp-feature-status">View stats & analytics</span>
                </div>
            </div>
        </div>
        
        <!-- CTA Button -->
        <div class="ttp-feature-cta">
            <a href="https://talktopc.com/agents" target="_blank" class="button button-primary button-hero">
                🚀 Open TTP App to Configure
            </a>
        </div>
    </div>
    <?php
}

// =============================================================================
// STYLES
// =============================================================================
function ttp_render_admin_styles() {
    ?>
    <style>
        .ttp-settings-wrap { max-width: 800px; }
        .ttp-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; }
        .ttp-card h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .ttp-card h4 { margin: 0 0 10px 0; color: #1d2327; font-size: 13px; }
        .ttp-connected-status { display: flex; align-items: center; gap: 5px; }
        .ttp-email { color: #666; }
        .ttp-collapsible-header { cursor: pointer; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0 !important; border-bottom: none !important; }
        .ttp-collapsible-content { display: none; padding-top: 15px; border-top: 1px solid #eee; margin-top: 15px; }
        .ttp-collapsible.open .ttp-collapsible-content { display: block; }
        .ttp-collapsible.open .dashicons { transform: rotate(180deg); }
        .ttp-status-card { display: flex; align-items: center; gap: 8px; background: #d4edda; border-color: #c3e6cb; }
        .form-table th { width: 200px; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .dashicons.spin { display: inline-block; animation: spin 1s linear infinite; }
        
        /* Setup in progress overlay */
        .ttp-setup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100000;
        }
        
        .ttp-setup-modal {
            background: #fff;
            padding: 40px 50px;
            border-radius: 8px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .ttp-setup-modal h2 {
            margin: 20px 0 10px;
            color: #1d2327;
            border: none;
            padding: 0;
        }
        
        .ttp-setup-modal p {
            color: #666;
            margin: 0 0 10px;
            line-height: 1.5;
        }
        
        .ttp-setup-modal .ttp-setup-note {
            font-size: 12px;
            color: #999;
            margin-top: 15px;
        }
        
        .ttp-setup-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #7C3AED;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        .ttp-setup-modal .button {
            margin-top: 20px;
        }
        
        .ttp-disabled {
            pointer-events: none;
            opacity: 0.5;
        }
        
        /* Background setup banner */
        .ttp-background-setup-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .ttp-background-setup-banner .ttp-banner-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            flex-shrink: 0;
        }
        
        .ttp-background-setup-banner .ttp-banner-text {
            flex: 1;
        }
        
        .ttp-background-setup-banner .ttp-banner-text strong {
            display: block;
            margin-bottom: 2px;
        }
        
        .ttp-background-setup-banner .ttp-banner-text span {
            font-size: 12px;
            opacity: 0.9;
        }
        
        /* Advanced Features Section */
        .ttp-advanced-features {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 24px;
            margin: 20px 0;
        }
        
        .ttp-advanced-features h2 {
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 18px;
        }
        
        .ttp-advanced-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }
        
        .ttp-advanced-header .dashicons {
            color: #7c3aed;
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
        
        .ttp-advanced-subtitle {
            color: #64748b;
            margin: 0 0 20px 0;
            font-size: 14px;
        }
        
        .ttp-feature-group {
            margin-bottom: 20px;
        }
        
        .ttp-feature-group h4 {
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin: 0 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .ttp-feature-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .ttp-feature-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        
        .ttp-feature-item.ttp-feature-clickable:hover {
            background: #f1f5f9;
            border-color: #7c3aed;
        }
        
        .ttp-feature-icon {
            font-size: 18px;
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }
        
        .ttp-feature-name {
            flex: 1;
            font-size: 14px;
            color: #334155;
            font-weight: 500;
        }
        
        .ttp-feature-status {
            font-size: 13px;
            color: #94a3b8;
            font-style: italic;
        }
        
        .ttp-feature-action {
            font-size: 13px;
            color: #7c3aed;
            font-weight: 500;
        }
        
        .ttp-feature-cta {
            margin-top: 24px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        
        .ttp-feature-cta .button-hero {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            border: none;
            padding: 12px 32px !important;
            font-size: 15px !important;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
            transition: all 0.2s ease;
        }
        
        .ttp-feature-cta .button-hero:hover {
            background: linear-gradient(135deg, #6d28d9 0%, #4c1d95 100%);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
            transform: translateY(-1px);
        }
    </style>
    <?php
}

// =============================================================================
// JAVASCRIPT
// =============================================================================
function ttp_render_admin_scripts($current_agent_id) {
    $current_voice = get_option('ttp_override_voice');
    $current_language = get_option('ttp_override_language');
    ?>
    <script>
    console.log('🔧 TTP Voice Widget v<?php echo esc_js(TTP_VERSION); ?> loaded');
    var agentsData = {};
    var voicesData = [];
    var languageMap = {};
    var isBackgroundSetup = false;
    
    jQuery(document).ready(function($) {
        var ajaxNonce = '<?php echo esc_js(wp_create_nonce("ttp_ajax_nonce")); ?>';
        var currentAgentId = '<?php echo esc_js($current_agent_id); ?>';
        var currentVoice = '<?php echo esc_js($current_voice); ?>';
        var currentLanguage = '<?php echo esc_js($current_language); ?>';
        
        // Initialize color pickers and collapsible sections
        $('.ttp-color-picker').wpColorPicker();
        $('.ttp-collapsible-header').on('click', function() { $(this).closest('.ttp-collapsible').toggleClass('open'); });
        
        // Icon type toggle
        $('#ttp_icon_type').on('change', function() {
            var type = $(this).val();
            $('.ttp-icon-custom-row, .ttp-icon-emoji-row, .ttp-icon-text-row').hide();
            if (type === 'custom') $('.ttp-icon-custom-row').show();
            else if (type === 'emoji') $('.ttp-icon-emoji-row').show();
            else if (type === 'text') $('.ttp-icon-text-row').show();
        }).trigger('change');
        
        // Check setup status first before loading data
        checkSetupStatus();
        
        // === SETUP STATUS CHECK ===
        function checkSetupStatus() {
            $.post(ajaxurl, { action: 'ttp_get_setup_status', nonce: ajaxNonce }, function(r) {
                if (r.success && r.data.creating) {
                    // Still creating agent - show overlay and poll
                    showSetupInProgress();
                } else {
                    // Not creating - load data normally
                    hideSetupInProgress();
                    fetchVoices(function() { fetchAgents(); });
                }
            }).fail(function() {
                // On error, just load normally
                fetchVoices(function() { fetchAgents(); });
            });
        }
        
        function showSetupInProgress() {
            // Show overlay if not exists and not in background mode
            if ($('#ttp-setup-overlay').length === 0 && !isBackgroundSetup) {
                var overlay = $(
                    '<div id="ttp-setup-overlay" class="ttp-setup-overlay">' +
                        '<div class="ttp-setup-modal">' +
                            '<div class="ttp-setup-spinner"></div>' +
                            '<h2>🤖 Creating your AI assistant...</h2>' +
                            '<p>We\'re analyzing your website content and generating a personalized AI assistant.</p>' +
                            '<p class="ttp-setup-note">This usually takes 30-60 seconds.</p>' +
                            '<button type="button" class="button" id="ttp-run-background-btn">Run in Background</button>' +
                        '</div>' +
                    '</div>'
                );
                $('body').append(overlay);
                
                // Handle "Run in Background" button
                $('#ttp-run-background-btn').on('click', function() {
                    isBackgroundSetup = true;
                    $('#ttp-setup-overlay').remove();
                    showBackgroundBanner();
                    // Load voices but keep agents area disabled
                    fetchVoices(function() {
                        // Show loading state for agents
                        $('#ttp_agent_select').html('<option value="">Setting up...</option>').prop('disabled', true);
                        $('#ttp-create-agent').hide();
                    });
                });
            }
            
            // Poll every 3 seconds
            setTimeout(function() {
                $.post(ajaxurl, { action: 'ttp_get_setup_status', nonce: ajaxNonce }, function(r) {
                    if (r.success && r.data.creating) {
                        // Still creating - keep polling
                        showSetupInProgress();
                    } else {
                        // Done!
                        if (isBackgroundSetup) {
                            // Remove banner and reload agents
                            $('#ttp-background-setup-banner').remove();
                            $('#ttp_agent_select').prop('disabled', false);
                            fetchAgents();
                            // Show success notice
                            var $notice = $('<div class="notice notice-success is-dismissible" style="margin: 10px 0;"><p>✅ Your AI assistant is ready!</p></div>');
                            $('.wrap h1').after($notice);
                        } else {
                            // Reload page to show everything fresh
                            window.location.reload();
                        }
                    }
                }).fail(function() {
                    // On error, reload anyway
                    if (!isBackgroundSetup) {
                        window.location.reload();
                    }
                });
            }, 3000);
        }
        
        function showBackgroundBanner() {
            if ($('#ttp-background-setup-banner').length === 0) {
                var banner = $(
                    '<div id="ttp-background-setup-banner" class="ttp-background-setup-banner">' +
                        '<div class="ttp-banner-spinner"></div>' +
                        '<div class="ttp-banner-text">' +
                            '<strong>Creating your AI assistant...</strong>' +
                            '<span>This is running in the background. You can explore the settings while you wait.</span>' +
                        '</div>' +
                    '</div>'
                );
                $('.ttp-card').first().before(banner);
            }
        }
        
        function hideSetupInProgress() {
            $('#ttp-setup-overlay').remove();
            $('#ttp-background-setup-banner').remove();
        }
        
        // === AGENTS ===
        function fetchAgents() {
            $('#ttp-agents-loading').addClass('is-active');
            $.post(ajaxurl, { action: 'ttp_fetch_agents', nonce: ajaxNonce }, function(r) {
                var agents = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || [])) : [];
                $('#ttp-agents-loading').removeClass('is-active');
                
                // Just populate dropdown - don't auto-create
                populateAgentsDropdown(agents);
            });
        }
        
        function populateAgentsDropdown(agents) {
            agentsData = {};
            agents.forEach(function(a) { var id = a.agentId || a.id; agentsData[id] = a; });
            
            var $s = $('#ttp_agent_select').empty().append('<option value="">-- Select an agent --</option>');
            
            if (agents.length === 0) {
                // No agents yet - show message and create button
                $s.append('<option value="" disabled>No agents yet - create one below</option>');
                $('#ttp-create-agent').show();
                return;
            }
            
            agents.forEach(function(a) {
                var id = a.agentId || a.id;
                $s.append('<option value="'+id+'"'+(id===currentAgentId?' selected':'')+'>'+a.name+'</option>');
            });
            
            $s.off('change').on('change', function() {
                var selectedId = $(this).val();
                $('#ttp_agent_name').val($(this).find('option:selected').text());
                if (selectedId && agentsData[selectedId]) populateAgentSettings(agentsData[selectedId]);
            });
            
            if (currentAgentId && agentsData[currentAgentId]) {
                populateAgentSettings(agentsData[currentAgentId]);
            } else if (agents.length > 0 && !currentAgentId) {
                // Auto-select first agent if none selected
                var first = agents[0], firstId = first.agentId || first.id;
                $s.val(firstId);
                $('#ttp_agent_name').val(first.name);
                populateAgentSettings(first);
                autoSaveSettings(firstId, first.name);
                return;
            }
            $('#ttp-create-agent').show();
        }
        
        function populateAgentSettings(agent) {
            var config = {};
            if (agent.configuration && agent.configuration.value) {
                try { var parsed = JSON.parse(agent.configuration.value); config = typeof parsed === 'string' ? JSON.parse(parsed) : parsed; }
                catch (e) { config = agent.configuration; }
            } else if (agent.configuration && typeof agent.configuration === 'object') { config = agent.configuration; }
            else { config = agent; }
            
            $('#ttp_override_prompt').val(config.systemPrompt || config.prompt || '');
            $('#ttp_override_first_message').val(config.firstMessage || '');
            var voiceId = config.voiceId || '';
            $('#ttp_override_voice').val(voiceId);
            
            var voiceSpeed = config.voiceSpeed;
            if (voiceId && voicesData.length > 0) {
                var voice = voicesData.find(function(v) { return (v.voiceId || v.id) === voiceId; });
                if (voice && voice.defaultVoiceSpeed && (!voiceSpeed || voiceSpeed == 1)) voiceSpeed = voice.defaultVoiceSpeed;
            }
            $('#ttp_override_voice_speed').val(voiceSpeed || '');
            
            var lang = config.agentLanguage || config.language || '';
            $('#ttp_override_language').val(lang);
            if (lang) { populateVoicesDropdown(lang); if (voiceId) $('#ttp_override_voice').val(voiceId); }
            
            $('#ttp_override_temperature').val(config.temperature || '');
            $('#ttp_override_max_tokens').val(config.maxTokens || '');
            $('#ttp_override_max_call_duration').val(config.maxCallDuration || '');
        }
        
        function autoSaveSettings(agentId, agentName) {
            if (!agentId) return;
            var $notice = $('<div class="notice notice-info" id="ttp-autosave-notice" style="margin: 10px 0; padding: 10px;"><p>⏳ Auto-saving...</p></div>');
            $('.wrap h1').after($notice);
            
            $.post(ajaxurl, { action: 'ttp_save_agent_selection', nonce: ajaxNonce, agent_id: agentId, agent_name: agentName }, function(r) {
                if (r.success) {
                    $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-success').html('<p>✅ Saved! Reloading...</p>');
                    setTimeout(function() { window.location.reload(); }, 300);
                } else {
                    $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-error').html('<p>❌ Failed. Save manually.</p>');
                }
            });
        }
        
        // === VOICES ===
        function fetchVoices(callback) {
            $('#ttp-voice-loading, #ttp-language-loading').addClass('is-active');
            $.post(ajaxurl, { action: 'ttp_fetch_voices', nonce: ajaxNonce }, function(r) {
                $('#ttp-voice-loading, #ttp-language-loading').removeClass('is-active');
                voicesData = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || [])) : [];
                
                var langNames = {'en':'English','en-US':'English (US)','en-GB':'English (UK)','es':'Spanish','fr':'French','de':'German','he':'Hebrew','he-IL':'Hebrew','ar':'Arabic','zh':'Chinese','ja':'Japanese','pt':'Portuguese','ru':'Russian','it':'Italian','nl':'Dutch','ko':'Korean','pl':'Polish','tr':'Turkish','hi':'Hindi','sv':'Swedish'};
                languageMap = {};
                voicesData.forEach(function(v) { (v.languages || []).forEach(function(l) { if (!languageMap[l]) languageMap[l] = langNames[l] || l; }); });
                
                var $lang = $('#ttp_override_language');
                $lang.find('option:not(:first)').remove();
                Object.keys(languageMap).sort(function(a,b) { return languageMap[a].localeCompare(languageMap[b]); }).forEach(function(code) {
                    $lang.append('<option value="'+code+'"'+(code===currentLanguage?' selected':'')+'>'+languageMap[code]+'</option>');
                });
                
                populateVoicesDropdown(currentLanguage);
                $lang.off('change').on('change', function() { populateVoicesDropdown($(this).val()); });
                if (callback) callback();
            });
        }
        
        function populateVoicesDropdown(filterLang) {
            var $v = $('#ttp_override_voice');
            $v.find('option:not(:first)').remove();
            var filtered = filterLang ? voicesData.filter(function(v) {
                return (v.languages || []).some(function(l) { return l === filterLang || l.startsWith(filterLang + '-') || filterLang.startsWith(l + '-'); });
            }) : voicesData;
            
            filtered.forEach(function(v) {
                var id = v.voiceId || v.id;
                $v.append('<option value="'+id+'" data-default-speed="'+(v.defaultVoiceSpeed||1.0)+'"'+(id===currentVoice?' selected':'')+'>'+v.name+'</option>');
            });
        }
        
        $('#ttp_override_voice').on('change', function() {
            var speed = $(this).find('option:selected').data('default-speed');
            if (speed) $('#ttp_override_voice_speed').val(speed);
        });
        
        // === CREATE AGENT ===
        $('#ttp-show-create-agent').on('click', function() { $(this).hide(); $('#ttp-create-agent-form').show(); });
        $('#ttp-cancel-create-agent').on('click', function() { $('#ttp-create-agent-form').hide(); $('#ttp-show-create-agent').show(); });
        $('#ttp-create-agent-btn').on('click', function() {
            var name = $('#ttp-new-agent-name').val().trim();
            if (!name) { alert('Enter agent name'); return; }
            var $btn = $(this).prop('disabled', true).text('Creating...');
            
            var postData = { action: 'ttp_create_agent', nonce: ajaxNonce, agent_name: name };
            var voiceId = $('#ttp_override_voice').val();
            if (voiceId) postData.voice_id = voiceId;
            var voiceSpeed = $('#ttp_override_voice_speed').val();
            if (voiceSpeed) postData.voice_speed = voiceSpeed;
            var language = $('#ttp_override_language').val();
            if (language) postData.language = language;
            var temperature = $('#ttp_override_temperature').val();
            if (temperature) postData.temperature = temperature;
            var maxTokens = $('#ttp_override_max_tokens').val();
            if (maxTokens) postData.max_tokens = maxTokens;
            var maxCallDuration = $('#ttp_override_max_call_duration').val();
            if (maxCallDuration) postData.max_call_duration = maxCallDuration;
            var systemPrompt = $('#ttp_override_prompt').val();
            if (systemPrompt) postData.system_prompt = systemPrompt;
            var firstMessage = $('#ttp_override_first_message').val();
            if (firstMessage) postData.first_message = firstMessage;
            
            $.post(ajaxurl, postData, function(r) {
                if (r.success) {
                    var agent = r.data.data || r.data, agentId = agent.agentId || agent.id;
                    agentsData[agentId] = agent;
                    $('#ttp_agent_select').append('<option value="'+agentId+'">'+agent.name+'</option>').val(agentId);
                    $('#ttp_agent_name').val(agent.name);
                    populateAgentSettings(agent);
                    $('#ttp-new-agent-name').val('');
                    $('#ttp-create-agent-form').hide();
                    $('#ttp-show-create-agent').show();
                } else { alert('Error: ' + (r.data?.message || 'Failed')); }
                $btn.prop('disabled', false).text('Create');
            });
        });
        
        // === GENERATE PROMPT ===
        $('#ttp-generate-prompt-btn').on('click', function() {
            var $btn = $(this), $status = $('#ttp-generate-prompt-status'), $ta = $('#ttp_override_prompt');
            if ($ta.val().trim() !== '' && !confirm('Replace current prompt?')) return;
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Generating...');
            $status.text('Scanning website...');
            
            $.post(ajaxurl, { action: 'ttp_generate_prompt', nonce: ajaxNonce }, function(r) {
                if (r.success) {
                    $ta.val(r.data.prompt).css('background-color', '#e8f5e9');
                    setTimeout(function() { $ta.css('background-color', ''); }, 2000);
                    var s = r.data.stats, parts = [];
                    if (s.pages > 0) parts.push(s.pages + ' pages');
                    if (s.posts > 0) parts.push(s.posts + ' posts');
                    if (s.products > 0) parts.push(s.products + ' products');
                    $status.html('<span style="color:green;">✓ From: ' + parts.join(', ') + '</span>');
                } else {
                    $status.html('<span style="color:red;">Error: ' + (r.data?.message || 'Failed') + '</span>');
                }
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-site" style="vertical-align:middle;margin-right:4px;"></span> Generate from Site Content');
            });
        });
    });
    </script>
    <?php
}