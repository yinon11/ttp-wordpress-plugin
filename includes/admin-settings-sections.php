<?php
/**
 * Admin Settings Sections
 * 
 * All render_*_settings() functions for form sections
 */

if (!defined('ABSPATH')) exit;

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
