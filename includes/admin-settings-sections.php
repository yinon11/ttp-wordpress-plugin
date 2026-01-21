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

function talktopc_render_agent_selection($current_agent_id, $current_agent_name) {
    ?>
    <div class="talktopc-card">
        <h2>Select Agent</h2>
        <table class="form-table">
            <tr>
                <th><label for="talktopc_agent_select">Agent</label></th>
                <td>
                    <select id="talktopc_agent_select" name="talktopc_agent_id" class="regular-text">
                        <option value="">Loading agents...</option>
                    </select>
                    <span class="spinner" id="talktopc-agents-loading"></span>
                    <input type="hidden" name="talktopc_agent_name" id="talktopc_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
                </td>
            </tr>
        </table>
        <div id="talktopc-create-agent" style="display: none;">
            <button type="button" class="button" id="talktopc-show-create-agent">+ Create New Agent</button>
            <div id="talktopc-create-agent-form" style="display: none; margin-top: 10px;">
                <input type="text" id="talktopc-new-agent-name" placeholder="Agent name" class="regular-text">
                <button type="button" class="button button-primary" id="talktopc-create-agent-btn">Create</button>
                <button type="button" class="button" id="talktopc-cancel-create-agent">Cancel</button>
            </div>
        </div>
    </div>
    <?php
}

function talktopc_render_agent_overrides() {
    ?>
    <div class="talktopc-card talktopc-collapsible open">
        <h2 class="talktopc-collapsible-header">Agent Settings (Override) <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <p class="description">Override agent defaults. Leave empty to use agent settings.</p>
            <table class="form-table">
                <tr><th><label for="talktopc_override_prompt">System Prompt</label></th>
                    <td>
                        <textarea id="talktopc_override_prompt" name="talktopc_override_prompt" rows="6" class="large-text"><?php echo esc_textarea(get_option('talktopc_override_prompt')); ?></textarea>
                        <p style="margin-top: 8px;">
                            <button type="button" class="button" id="talktopc-generate-prompt-btn">
                                <span class="dashicons dashicons-admin-site" style="vertical-align: middle; margin-right: 4px;"></span>
                                Generate from Site Content
                            </button>
                            <span id="talktopc-generate-prompt-status" style="margin-left: 10px;"></span>
                        </p>
                    </td></tr>
                <tr><th><label for="talktopc_override_first_message">First Message</label></th>
                    <td><input type="text" id="talktopc_override_first_message" name="talktopc_override_first_message" value="<?php echo esc_attr(get_option('talktopc_override_first_message')); ?>" class="large-text"></td></tr>
                <tr><th><label for="talktopc_override_voice">Voice</label></th>
                    <td><select id="talktopc_override_voice" name="talktopc_override_voice" class="regular-text"><option value="">-- Use agent default --</option></select><span id="talktopc-voice-loading" class="spinner"></span></td></tr>
                <tr><th><label for="talktopc_override_voice_speed">Voice Speed</label></th>
                    <td><input type="number" id="talktopc_override_voice_speed" name="talktopc_override_voice_speed" value="<?php echo esc_attr(get_option('talktopc_override_voice_speed')); ?>" class="small-text" min="0.5" max="2.0" step="0.1"> <span class="description">0.5 to 2.0</span></td></tr>
                <tr><th><label for="talktopc_override_language">Language</label></th>
                    <td><select id="talktopc_override_language" name="talktopc_override_language" class="regular-text"><option value="">-- All languages --</option></select><span id="talktopc-language-loading" class="spinner"></span></td></tr>
                <tr><th><label for="talktopc_override_temperature">Temperature</label></th>
                    <td><input type="number" id="talktopc_override_temperature" name="talktopc_override_temperature" value="<?php echo esc_attr(get_option('talktopc_override_temperature')); ?>" class="small-text" min="0" max="2" step="0.1"> <span class="description">0 to 2</span></td></tr>
                <tr><th><label for="talktopc_override_max_tokens">Max Tokens</label></th>
                    <td><input type="number" id="talktopc_override_max_tokens" name="talktopc_override_max_tokens" value="<?php echo esc_attr(get_option('talktopc_override_max_tokens')); ?>" class="small-text" min="50" max="4000"></td></tr>
                <tr><th><label for="talktopc_override_max_call_duration">Max Call Duration</label></th>
                    <td><input type="number" id="talktopc_override_max_call_duration" name="talktopc_override_max_call_duration" value="<?php echo esc_attr(get_option('talktopc_override_max_call_duration')); ?>" class="small-text" min="30" max="3600"> <span class="description">seconds</span></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_behavior_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Behavior <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_mode">Widget Mode</label></th>
                    <td><select id="talktopc_mode" name="talktopc_mode">
                        <option value="unified" <?php selected(get_option('talktopc_mode', 'unified'), 'unified'); ?>>Unified (Voice + Text)</option>
                        <option value="voice-only" <?php selected(get_option('talktopc_mode'), 'voice-only'); ?>>Voice Only</option>
                        <option value="text-only" <?php selected(get_option('talktopc_mode'), 'text-only'); ?>>Text Only</option>
                    </select></td></tr>
                <tr><th><label for="talktopc_direction">Text Direction</label></th>
                    <td><select id="talktopc_direction" name="talktopc_direction">
                        <option value="ltr" <?php selected(get_option('talktopc_direction', 'ltr'), 'ltr'); ?>>Left to Right</option>
                        <option value="rtl" <?php selected(get_option('talktopc_direction'), 'rtl'); ?>>Right to Left</option>
                    </select></td></tr>
                <tr><th>Auto Open</th>
                    <td><label><input type="checkbox" name="talktopc_auto_open" value="1" <?php checked(get_option('talktopc_auto_open'), '1'); ?>> Open widget on page load</label></td></tr>
                <tr><th><label for="talktopc_welcome_message">Welcome Message</label></th>
                    <td><input type="text" id="talktopc_welcome_message" name="talktopc_welcome_message" value="<?php echo esc_attr(get_option('talktopc_welcome_message')); ?>" class="large-text" placeholder="Hello! How can I help you today?"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_button_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Floating Button <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_position">Position</label></th>
                    <td><select id="talktopc_position" name="talktopc_position">
                        <option value="bottom-right" <?php selected(get_option('talktopc_position', 'bottom-right'), 'bottom-right'); ?>>Bottom Right</option>
                        <option value="bottom-left" <?php selected(get_option('talktopc_position'), 'bottom-left'); ?>>Bottom Left</option>
                        <option value="top-right" <?php selected(get_option('talktopc_position'), 'top-right'); ?>>Top Right</option>
                        <option value="top-left" <?php selected(get_option('talktopc_position'), 'top-left'); ?>>Top Left</option>
                    </select></td></tr>
                <tr><th><label for="talktopc_button_size">Size</label></th>
                    <td><select id="talktopc_button_size" name="talktopc_button_size">
                        <option value="small" <?php selected(get_option('talktopc_button_size'), 'small'); ?>>Small</option>
                        <option value="medium" <?php selected(get_option('talktopc_button_size', 'medium'), 'medium'); ?>>Medium</option>
                        <option value="large" <?php selected(get_option('talktopc_button_size'), 'large'); ?>>Large</option>
                        <option value="xl" <?php selected(get_option('talktopc_button_size'), 'xl'); ?>>Extra Large</option>
                    </select></td></tr>
                <tr><th><label for="talktopc_button_shape">Shape</label></th>
                    <td><select id="talktopc_button_shape" name="talktopc_button_shape">
                        <option value="circle" <?php selected(get_option('talktopc_button_shape', 'circle'), 'circle'); ?>>Circle</option>
                        <option value="rounded" <?php selected(get_option('talktopc_button_shape'), 'rounded'); ?>>Rounded</option>
                        <option value="square" <?php selected(get_option('talktopc_button_shape'), 'square'); ?>>Square</option>
                    </select></td></tr>
                <tr><th><label for="talktopc_button_bg_color">Background Color</label></th>
                    <td><input type="text" id="talktopc_button_bg_color" name="talktopc_button_bg_color" value="<?php echo esc_attr(get_option('talktopc_button_bg_color', '#FFFFFF')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="talktopc_button_hover_color">Hover Color</label></th>
                    <td><input type="text" id="talktopc_button_hover_color" name="talktopc_button_hover_color" value="<?php echo esc_attr(get_option('talktopc_button_hover_color', '#D3D3D3')); ?>" class="talktopc-color-picker" data-default-color="#D3D3D3"></td></tr>
                <tr><th>Shadow</th>
                    <td><label><input type="checkbox" name="talktopc_button_shadow" value="1" <?php checked(get_option('talktopc_button_shadow', '1'), '1'); ?>> Enable button shadow</label></td></tr>
                <tr><th><label for="talktopc_button_shadow_color">Shadow Color</label></th>
                    <td><input type="text" id="talktopc_button_shadow_color" name="talktopc_button_shadow_color" value="<?php echo esc_attr(get_option('talktopc_button_shadow_color', 'rgba(0,0,0,0.15)')); ?>" class="regular-text" placeholder="rgba(0,0,0,0.15)"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_icon_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Button Icon <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_icon_type">Icon Type</label></th>
                    <td><select id="talktopc_icon_type" name="talktopc_icon_type">
                        <option value="custom" <?php selected(get_option('talktopc_icon_type', 'custom'), 'custom'); ?>>Custom Image</option>
                        <option value="microphone" <?php selected(get_option('talktopc_icon_type'), 'microphone'); ?>>Microphone</option>
                        <option value="emoji" <?php selected(get_option('talktopc_icon_type'), 'emoji'); ?>>Emoji</option>
                        <option value="text" <?php selected(get_option('talktopc_icon_type'), 'text'); ?>>Text</option>
                    </select></td></tr>
                <tr class="talktopc-icon-custom-row"><th><label for="talktopc_icon_custom_image">Image URL</label></th>
                    <td><input type="url" id="talktopc_icon_custom_image" name="talktopc_icon_custom_image" value="<?php echo esc_attr(get_option('talktopc_icon_custom_image')); ?>" class="large-text" placeholder="https://talktopc.com/logo192.png"></td></tr>
                <tr class="talktopc-icon-emoji-row" style="display:none;"><th><label for="talktopc_icon_emoji">Emoji</label></th>
                    <td><input type="text" id="talktopc_icon_emoji" name="talktopc_icon_emoji" value="<?php echo esc_attr(get_option('talktopc_icon_emoji', '🎤')); ?>" class="small-text"></td></tr>
                <tr class="talktopc-icon-text-row" style="display:none;"><th><label for="talktopc_icon_text">Text</label></th>
                    <td><input type="text" id="talktopc_icon_text" name="talktopc_icon_text" value="<?php echo esc_attr(get_option('talktopc_icon_text', 'AI')); ?>" class="small-text" maxlength="4"></td></tr>
                <tr><th><label for="talktopc_icon_size">Icon Size</label></th>
                    <td><select id="talktopc_icon_size" name="talktopc_icon_size">
                        <option value="small" <?php selected(get_option('talktopc_icon_size'), 'small'); ?>>Small</option>
                        <option value="medium" <?php selected(get_option('talktopc_icon_size', 'medium'), 'medium'); ?>>Medium</option>
                        <option value="large" <?php selected(get_option('talktopc_icon_size'), 'large'); ?>>Large</option>
                        <option value="xl" <?php selected(get_option('talktopc_icon_size'), 'xl'); ?>>Extra Large</option>
                    </select></td></tr>
                <tr><th><label for="talktopc_icon_bg_color">Icon Background</label></th>
                    <td><input type="text" id="talktopc_icon_bg_color" name="talktopc_icon_bg_color" value="<?php echo esc_attr(get_option('talktopc_icon_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_panel_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Panel Settings <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_panel_width">Width (px)</label></th>
                    <td><input type="number" id="talktopc_panel_width" name="talktopc_panel_width" value="<?php echo esc_attr(get_option('talktopc_panel_width', 350)); ?>" class="small-text" min="280" max="600"></td></tr>
                <tr><th><label for="talktopc_panel_height">Height (px)</label></th>
                    <td><input type="number" id="talktopc_panel_height" name="talktopc_panel_height" value="<?php echo esc_attr(get_option('talktopc_panel_height', 500)); ?>" class="small-text" min="300" max="800"></td></tr>
                <tr><th><label for="talktopc_panel_border_radius">Border Radius (px)</label></th>
                    <td><input type="number" id="talktopc_panel_border_radius" name="talktopc_panel_border_radius" value="<?php echo esc_attr(get_option('talktopc_panel_border_radius', 12)); ?>" class="small-text" min="0" max="30"></td></tr>
                <tr><th><label for="talktopc_panel_bg_color">Background Color</label></th>
                    <td><input type="text" id="talktopc_panel_bg_color" name="talktopc_panel_bg_color" value="<?php echo esc_attr(get_option('talktopc_panel_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="talktopc_panel_border">Border</label></th>
                    <td><input type="text" id="talktopc_panel_border" name="talktopc_panel_border" value="<?php echo esc_attr(get_option('talktopc_panel_border', '1px solid #E5E7EB')); ?>" class="regular-text" placeholder="1px solid #E5E7EB">
                        <p class="description">CSS border value. Examples: <code>1px solid #E5E7EB</code>, <code>none</code></p></td></tr>
                <tr><th><label for="talktopc_panel_backdrop_filter">Backdrop Filter</label></th>
                    <td><input type="text" id="talktopc_panel_backdrop_filter" name="talktopc_panel_backdrop_filter" value="<?php echo esc_attr(get_option('talktopc_panel_backdrop_filter')); ?>" class="regular-text" placeholder="blur(10px)">
                        <p class="description">CSS backdrop-filter. Example: <code>blur(10px)</code></p></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_header_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Header Settings <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_header_title">Title</label></th>
                    <td><input type="text" id="talktopc_header_title" name="talktopc_header_title" value="<?php echo esc_attr(get_option('talktopc_header_title')); ?>" class="regular-text" placeholder="Chat Assistant"></td></tr>
                <tr><th>Show Title</th>
                    <td><label><input type="checkbox" name="talktopc_header_show_title" value="1" <?php checked(get_option('talktopc_header_show_title', '1'), '1'); ?>> Display title in header</label></td></tr>
                <tr><th><label for="talktopc_header_bg_color">Background Color</label></th>
                    <td><input type="text" id="talktopc_header_bg_color" name="talktopc_header_bg_color" value="<?php echo esc_attr(get_option('talktopc_header_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="talktopc_header_text_color">Text Color</label></th>
                    <td><input type="text" id="talktopc_header_text_color" name="talktopc_header_text_color" value="<?php echo esc_attr(get_option('talktopc_header_text_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th>Close Button</th>
                    <td><label><input type="checkbox" name="talktopc_header_show_close" value="1" <?php checked(get_option('talktopc_header_show_close', '1'), '1'); ?>> Show close button</label></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_footer_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Footer Branding <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <p class="description"><strong>⚡ Powered by TalkToPC</strong> — Displayed at the bottom of the chat panel.</p>
            <div class="talktopc-footer-preview" style="margin: 15px 0; padding: 8px 16px; background: <?php echo esc_attr(get_option('talktopc_footer_bg_color', '#f9fafb')); ?>; border: 1px solid #e5e7eb; border-radius: 4px; text-align: center;">
                <span style="font-size: 11px; color: <?php echo esc_attr(get_option('talktopc_footer_text_color', '#9ca3af')); ?>;">⚡ Powered by <strong>TalkToPC</strong></span>
            </div>
            <table class="form-table">
                <tr><th><label for="talktopc_footer_bg_color">Background Color</label></th>
                    <td><input type="text" id="talktopc_footer_bg_color" name="talktopc_footer_bg_color" value="<?php echo esc_attr(get_option('talktopc_footer_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#f9fafb"></td></tr>
                <tr><th><label for="talktopc_footer_text_color">Text Color</label></th>
                    <td><input type="text" id="talktopc_footer_text_color" name="talktopc_footer_text_color" value="<?php echo esc_attr(get_option('talktopc_footer_text_color')); ?>" class="talktopc-color-picker" data-default-color="#9ca3af"></td></tr>
                <tr><th><label for="talktopc_footer_hover_color">Hover Color</label></th>
                    <td><input type="text" id="talktopc_footer_hover_color" name="talktopc_footer_hover_color" value="<?php echo esc_attr(get_option('talktopc_footer_hover_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_landing_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Landing Screen <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <p class="description">Shown in unified mode when user first opens widget.</p>
            <table class="form-table">
                <tr><th><label for="talktopc_landing_bg_color">Background</label></th>
                    <td><input type="text" id="talktopc_landing_bg_color" name="talktopc_landing_bg_color" value="<?php echo esc_attr(get_option('talktopc_landing_bg_color')); ?>" class="large-text" placeholder="linear-gradient(180deg, #f8fafc 0%, #e0e7ff 100%)">
                        <p class="description">CSS color or gradient</p></td></tr>
                <tr><th><label for="talktopc_landing_logo">Logo (emoji/text)</label></th>
                    <td><input type="text" id="talktopc_landing_logo" name="talktopc_landing_logo" value="<?php echo esc_attr(get_option('talktopc_landing_logo')); ?>" class="small-text" placeholder="🤖"></td></tr>
                <tr><th><label for="talktopc_landing_title">Title</label></th>
                    <td><input type="text" id="talktopc_landing_title" name="talktopc_landing_title" value="<?php echo esc_attr(get_option('talktopc_landing_title')); ?>" class="regular-text" placeholder="How would you like to chat?"></td></tr>
                <tr><th><label for="talktopc_landing_title_color">Title Color</label></th>
                    <td><input type="text" id="talktopc_landing_title_color" name="talktopc_landing_title_color" value="<?php echo esc_attr(get_option('talktopc_landing_title_color')); ?>" class="talktopc-color-picker" data-default-color="#1e293b"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Mode Selection Cards</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_landing_card_bg_color">Card Background</label></th>
                    <td><input type="text" id="talktopc_landing_card_bg_color" name="talktopc_landing_card_bg_color" value="<?php echo esc_attr(get_option('talktopc_landing_card_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="talktopc_landing_card_border_color">Card Border</label></th>
                    <td><input type="text" id="talktopc_landing_card_border_color" name="talktopc_landing_card_border_color" value="<?php echo esc_attr(get_option('talktopc_landing_card_border_color')); ?>" class="talktopc-color-picker" data-default-color="#E2E8F0"></td></tr>
                <tr><th><label for="talktopc_landing_card_hover_border_color">Card Hover Border</label></th>
                    <td><input type="text" id="talktopc_landing_card_hover_border_color" name="talktopc_landing_card_hover_border_color" value="<?php echo esc_attr(get_option('talktopc_landing_card_hover_border_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="talktopc_landing_card_icon_bg_color">Card Icon Background</label></th>
                    <td><input type="text" id="talktopc_landing_card_icon_bg_color" name="talktopc_landing_card_icon_bg_color" value="<?php echo esc_attr(get_option('talktopc_landing_card_icon_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="talktopc_landing_card_title_color">Card Title Color</label></th>
                    <td><input type="text" id="talktopc_landing_card_title_color" name="talktopc_landing_card_title_color" value="<?php echo esc_attr(get_option('talktopc_landing_card_title_color')); ?>" class="talktopc-color-picker" data-default-color="#111827"></td></tr>
                <tr><th><label for="talktopc_landing_voice_icon">Voice Card Icon</label></th>
                    <td><input type="text" id="talktopc_landing_voice_icon" name="talktopc_landing_voice_icon" value="<?php echo esc_attr(get_option('talktopc_landing_voice_icon', '🎤')); ?>" class="small-text" placeholder="🎤"></td></tr>
                <tr><th><label for="talktopc_landing_voice_title">Voice Card Title</label></th>
                    <td><input type="text" id="talktopc_landing_voice_title" name="talktopc_landing_voice_title" value="<?php echo esc_attr(get_option('talktopc_landing_voice_title')); ?>" class="regular-text" placeholder="Voice Call"></td></tr>
                <tr><th><label for="talktopc_landing_text_icon">Text Card Icon</label></th>
                    <td><input type="text" id="talktopc_landing_text_icon" name="talktopc_landing_text_icon" value="<?php echo esc_attr(get_option('talktopc_landing_text_icon', '💬')); ?>" class="small-text" placeholder="💬"></td></tr>
                <tr><th><label for="talktopc_landing_text_title">Text Card Title</label></th>
                    <td><input type="text" id="talktopc_landing_text_title" name="talktopc_landing_text_title" value="<?php echo esc_attr(get_option('talktopc_landing_text_title')); ?>" class="regular-text" placeholder="Text Chat"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_message_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Message Styling <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_msg_user_bg">User Message Background</label></th>
                    <td><input type="text" id="talktopc_msg_user_bg" name="talktopc_msg_user_bg" value="<?php echo esc_attr(get_option('talktopc_msg_user_bg')); ?>" class="talktopc-color-picker" data-default-color="#E5E7EB"></td></tr>
                <tr><th><label for="talktopc_msg_agent_bg">Agent Message Background</label></th>
                    <td><input type="text" id="talktopc_msg_agent_bg" name="talktopc_msg_agent_bg" value="<?php echo esc_attr(get_option('talktopc_msg_agent_bg')); ?>" class="talktopc-color-picker" data-default-color="#F3F4F6"></td></tr>
                <tr><th><label for="talktopc_msg_system_bg">System Message Background</label></th>
                    <td><input type="text" id="talktopc_msg_system_bg" name="talktopc_msg_system_bg" value="<?php echo esc_attr(get_option('talktopc_msg_system_bg')); ?>" class="talktopc-color-picker" data-default-color="#DCFCE7"></td></tr>
                <tr><th><label for="talktopc_msg_error_bg">Error Message Background</label></th>
                    <td><input type="text" id="talktopc_msg_error_bg" name="talktopc_msg_error_bg" value="<?php echo esc_attr(get_option('talktopc_msg_error_bg')); ?>" class="talktopc-color-picker" data-default-color="#FEE2E2"></td></tr>
                <tr><th><label for="talktopc_msg_text_color">Text Color</label></th>
                    <td><input type="text" id="talktopc_msg_text_color" name="talktopc_msg_text_color" value="<?php echo esc_attr(get_option('talktopc_msg_text_color')); ?>" class="talktopc-color-picker" data-default-color="#1F2937"></td></tr>
                <tr><th><label for="talktopc_msg_font_size">Font Size</label></th>
                    <td><input type="text" id="talktopc_msg_font_size" name="talktopc_msg_font_size" value="<?php echo esc_attr(get_option('talktopc_msg_font_size', '14px')); ?>" class="small-text" placeholder="14px"></td></tr>
                <tr><th><label for="talktopc_msg_border_radius">Border Radius (px)</label></th>
                    <td><input type="number" id="talktopc_msg_border_radius" name="talktopc_msg_border_radius" value="<?php echo esc_attr(get_option('talktopc_msg_border_radius', 8)); ?>" class="small-text" min="0" max="20"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_voice_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Voice Interface <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <h4>Microphone Button</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_voice_mic_color">Mic Button Color</label></th>
                    <td><input type="text" id="talktopc_voice_mic_color" name="talktopc_voice_mic_color" value="<?php echo esc_attr(get_option('talktopc_voice_mic_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="talktopc_voice_mic_active_color">Mic Active Color</label></th>
                    <td><input type="text" id="talktopc_voice_mic_active_color" name="talktopc_voice_mic_active_color" value="<?php echo esc_attr(get_option('talktopc_voice_mic_active_color')); ?>" class="talktopc-color-picker" data-default-color="#EF4444"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Avatar</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_voice_avatar_color">Avatar Background</label></th>
                    <td><input type="text" id="talktopc_voice_avatar_color" name="talktopc_voice_avatar_color" value="<?php echo esc_attr(get_option('talktopc_voice_avatar_color')); ?>" class="talktopc-color-picker" data-default-color="#667eea"></td></tr>
                <tr><th><label for="talktopc_voice_avatar_active_color">Avatar Active Background</label></th>
                    <td><input type="text" id="talktopc_voice_avatar_active_color" name="talktopc_voice_avatar_active_color" value="<?php echo esc_attr(get_option('talktopc_voice_avatar_active_color')); ?>" class="talktopc-color-picker" data-default-color="#667eea"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Status Text</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_voice_status_title_color">Status Title Color</label></th>
                    <td><input type="text" id="talktopc_voice_status_title_color" name="talktopc_voice_status_title_color" value="<?php echo esc_attr(get_option('talktopc_voice_status_title_color')); ?>" class="talktopc-color-picker" data-default-color="#1e293b"></td></tr>
                <tr><th><label for="talktopc_voice_status_subtitle_color">Status Subtitle Color</label></th>
                    <td><input type="text" id="talktopc_voice_status_subtitle_color" name="talktopc_voice_status_subtitle_color" value="<?php echo esc_attr(get_option('talktopc_voice_status_subtitle_color')); ?>" class="talktopc-color-picker" data-default-color="#64748b"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Start Call Button</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_voice_start_title">Title Text</label></th>
                    <td><input type="text" id="talktopc_voice_start_title" name="talktopc_voice_start_title" value="<?php echo esc_attr(get_option('talktopc_voice_start_title')); ?>" class="regular-text" placeholder="Ready to chat?"></td></tr>
                <tr><th><label for="talktopc_voice_start_subtitle">Subtitle Text</label></th>
                    <td><input type="text" id="talktopc_voice_start_subtitle" name="talktopc_voice_start_subtitle" value="<?php echo esc_attr(get_option('talktopc_voice_start_subtitle')); ?>" class="regular-text" placeholder="Speak naturally"></td></tr>
                <tr><th><label for="talktopc_voice_start_btn_text">Button Text</label></th>
                    <td><input type="text" id="talktopc_voice_start_btn_text" name="talktopc_voice_start_btn_text" value="<?php echo esc_attr(get_option('talktopc_voice_start_btn_text')); ?>" class="regular-text" placeholder="Start Call"></td></tr>
                <tr><th><label for="talktopc_voice_start_btn_color">Button Color</label></th>
                    <td><input type="text" id="talktopc_voice_start_btn_color" name="talktopc_voice_start_btn_color" value="<?php echo esc_attr(get_option('talktopc_voice_start_btn_color')); ?>" class="talktopc-color-picker" data-default-color="#667eea"></td></tr>
                <tr><th><label for="talktopc_voice_start_btn_text_color">Button Text Color</label></th>
                    <td><input type="text" id="talktopc_voice_start_btn_text_color" name="talktopc_voice_start_btn_text_color" value="<?php echo esc_attr(get_option('talktopc_voice_start_btn_text_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Transcript</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_voice_transcript_bg_color">Background</label></th>
                    <td><input type="text" id="talktopc_voice_transcript_bg_color" name="talktopc_voice_transcript_bg_color" value="<?php echo esc_attr(get_option('talktopc_voice_transcript_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="talktopc_voice_transcript_text_color">Text Color</label></th>
                    <td><input type="text" id="talktopc_voice_transcript_text_color" name="talktopc_voice_transcript_text_color" value="<?php echo esc_attr(get_option('talktopc_voice_transcript_text_color')); ?>" class="talktopc-color-picker" data-default-color="#1e293b"></td></tr>
                <tr><th><label for="talktopc_voice_transcript_label_color">Label Color</label></th>
                    <td><input type="text" id="talktopc_voice_transcript_label_color" name="talktopc_voice_transcript_label_color" value="<?php echo esc_attr(get_option('talktopc_voice_transcript_label_color')); ?>" class="talktopc-color-picker" data-default-color="#94a3b8"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Control Buttons</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_voice_control_btn_color">Control Button Color</label></th>
                    <td><input type="text" id="talktopc_voice_control_btn_color" name="talktopc_voice_control_btn_color" value="<?php echo esc_attr(get_option('talktopc_voice_control_btn_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="talktopc_voice_control_btn_secondary_color">Secondary Color</label></th>
                    <td><input type="text" id="talktopc_voice_control_btn_secondary_color" name="talktopc_voice_control_btn_secondary_color" value="<?php echo esc_attr(get_option('talktopc_voice_control_btn_secondary_color')); ?>" class="talktopc-color-picker" data-default-color="#64748b"></td></tr>
                <tr><th><label for="talktopc_voice_end_btn_color">End Call Button</label></th>
                    <td><input type="text" id="talktopc_voice_end_btn_color" name="talktopc_voice_end_btn_color" value="<?php echo esc_attr(get_option('talktopc_voice_end_btn_color')); ?>" class="talktopc-color-picker" data-default-color="#EF4444"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_text_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Text Interface <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <h4>Send Button</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_text_send_btn_color">Button Color</label></th>
                    <td><input type="text" id="talktopc_text_send_btn_color" name="talktopc_text_send_btn_color" value="<?php echo esc_attr(get_option('talktopc_text_send_btn_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="talktopc_text_send_btn_hover_color">Hover Color</label></th>
                    <td><input type="text" id="talktopc_text_send_btn_hover_color" name="talktopc_text_send_btn_hover_color" value="<?php echo esc_attr(get_option('talktopc_text_send_btn_hover_color')); ?>" class="talktopc-color-picker" data-default-color="#6D28D9"></td></tr>
                <tr><th><label for="talktopc_text_send_btn_text">Button Text</label></th>
                    <td><input type="text" id="talktopc_text_send_btn_text" name="talktopc_text_send_btn_text" value="<?php echo esc_attr(get_option('talktopc_text_send_btn_text', '➤')); ?>" class="small-text" placeholder="➤"></td></tr>
                <tr><th><label for="talktopc_text_send_btn_text_color">Text Color</label></th>
                    <td><input type="text" id="talktopc_text_send_btn_text_color" name="talktopc_text_send_btn_text_color" value="<?php echo esc_attr(get_option('talktopc_text_send_btn_text_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
            </table>
            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Input Field</h4>
            <table class="form-table">
                <tr><th><label for="talktopc_text_input_placeholder">Placeholder</label></th>
                    <td><input type="text" id="talktopc_text_input_placeholder" name="talktopc_text_input_placeholder" value="<?php echo esc_attr(get_option('talktopc_text_input_placeholder')); ?>" class="regular-text" placeholder="Type your message..."></td></tr>
                <tr><th><label for="talktopc_text_input_bg_color">Background Color</label></th>
                    <td><input type="text" id="talktopc_text_input_bg_color" name="talktopc_text_input_bg_color" value="<?php echo esc_attr(get_option('talktopc_text_input_bg_color')); ?>" class="talktopc-color-picker" data-default-color="#FFFFFF"></td></tr>
                <tr><th><label for="talktopc_text_input_text_color">Text Color</label></th>
                    <td><input type="text" id="talktopc_text_input_text_color" name="talktopc_text_input_text_color" value="<?php echo esc_attr(get_option('talktopc_text_input_text_color')); ?>" class="talktopc-color-picker" data-default-color="#1F2937"></td></tr>
                <tr><th><label for="talktopc_text_input_border_color">Border Color</label></th>
                    <td><input type="text" id="talktopc_text_input_border_color" name="talktopc_text_input_border_color" value="<?php echo esc_attr(get_option('talktopc_text_input_border_color')); ?>" class="talktopc-color-picker" data-default-color="#E5E7EB"></td></tr>
                <tr><th><label for="talktopc_text_input_focus_color">Focus Color</label></th>
                    <td><input type="text" id="talktopc_text_input_focus_color" name="talktopc_text_input_focus_color" value="<?php echo esc_attr(get_option('talktopc_text_input_focus_color')); ?>" class="talktopc-color-picker" data-default-color="#7C3AED"></td></tr>
                <tr><th><label for="talktopc_text_input_border_radius">Border Radius (px)</label></th>
                    <td><input type="number" id="talktopc_text_input_border_radius" name="talktopc_text_input_border_radius" value="<?php echo esc_attr(get_option('talktopc_text_input_border_radius', 20)); ?>" class="small-text" min="0" max="30"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_tooltip_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Tooltips <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <p class="description">Custom tooltip text for buttons. Leave empty to use defaults.</p>
            <table class="form-table">
                <tr><th><label for="talktopc_tooltip_new_chat">New Chat</label></th>
                    <td><input type="text" id="talktopc_tooltip_new_chat" name="talktopc_tooltip_new_chat" value="<?php echo esc_attr(get_option('talktopc_tooltip_new_chat')); ?>" class="regular-text" placeholder="New Chat"></td></tr>
                <tr><th><label for="talktopc_tooltip_back">Back</label></th>
                    <td><input type="text" id="talktopc_tooltip_back" name="talktopc_tooltip_back" value="<?php echo esc_attr(get_option('talktopc_tooltip_back')); ?>" class="regular-text" placeholder="Back"></td></tr>
                <tr><th><label for="talktopc_tooltip_close">Close</label></th>
                    <td><input type="text" id="talktopc_tooltip_close" name="talktopc_tooltip_close" value="<?php echo esc_attr(get_option('talktopc_tooltip_close')); ?>" class="regular-text" placeholder="Close"></td></tr>
                <tr><th><label for="talktopc_tooltip_mute">Mute</label></th>
                    <td><input type="text" id="talktopc_tooltip_mute" name="talktopc_tooltip_mute" value="<?php echo esc_attr(get_option('talktopc_tooltip_mute')); ?>" class="regular-text" placeholder="Mute"></td></tr>
                <tr><th><label for="talktopc_tooltip_speaker">Speaker</label></th>
                    <td><input type="text" id="talktopc_tooltip_speaker" name="talktopc_tooltip_speaker" value="<?php echo esc_attr(get_option('talktopc_tooltip_speaker')); ?>" class="regular-text" placeholder="Speaker"></td></tr>
                <tr><th><label for="talktopc_tooltip_end_call">End Call</label></th>
                    <td><input type="text" id="talktopc_tooltip_end_call" name="talktopc_tooltip_end_call" value="<?php echo esc_attr(get_option('talktopc_tooltip_end_call')); ?>" class="regular-text" placeholder="End Call"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_animation_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Animation <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th>Enable Hover</th>
                    <td><label><input type="checkbox" name="talktopc_anim_enable_hover" value="1" <?php checked(get_option('talktopc_anim_enable_hover', '1'), '1'); ?>> Enable hover animations</label></td></tr>
                <tr><th>Enable Pulse</th>
                    <td><label><input type="checkbox" name="talktopc_anim_enable_pulse" value="1" <?php checked(get_option('talktopc_anim_enable_pulse', '1'), '1'); ?>> Enable pulse animation when recording</label></td></tr>
                <tr><th>Enable Slide</th>
                    <td><label><input type="checkbox" name="talktopc_anim_enable_slide" value="1" <?php checked(get_option('talktopc_anim_enable_slide', '1'), '1'); ?>> Enable slide animations</label></td></tr>
                <tr><th><label for="talktopc_anim_duration">Duration (seconds)</label></th>
                    <td><input type="number" id="talktopc_anim_duration" name="talktopc_anim_duration" value="<?php echo esc_attr(get_option('talktopc_anim_duration', '0.3')); ?>" class="small-text" min="0.1" max="1" step="0.1"></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_accessibility_settings() {
    ?>
    <div class="talktopc-card talktopc-collapsible">
        <h2 class="talktopc-collapsible-header">Accessibility <span class="dashicons dashicons-arrow-down-alt2"></span></h2>
        <div class="talktopc-collapsible-content">
            <table class="form-table">
                <tr><th><label for="talktopc_a11y_aria_label">Aria Label</label></th>
                    <td><input type="text" id="talktopc_a11y_aria_label" name="talktopc_a11y_aria_label" value="<?php echo esc_attr(get_option('talktopc_a11y_aria_label')); ?>" class="regular-text" placeholder="Chat Assistant"></td></tr>
                <tr><th><label for="talktopc_a11y_aria_description">Aria Description</label></th>
                    <td><input type="text" id="talktopc_a11y_aria_description" name="talktopc_a11y_aria_description" value="<?php echo esc_attr(get_option('talktopc_a11y_aria_description')); ?>" class="large-text" placeholder="Click to open chat assistant"></td></tr>
                <tr><th>Keyboard Navigation</th>
                    <td><label><input type="checkbox" name="talktopc_a11y_keyboard_nav" value="1" <?php checked(get_option('talktopc_a11y_keyboard_nav', '1'), '1'); ?>> Enable keyboard navigation (ESC to close)</label></td></tr>
            </table>
        </div>
    </div>
    <?php
}

function talktopc_render_advanced_features() {
    ?>
    <div class="talktopc-card talktopc-advanced-features">
        <div class="talktopc-advanced-header">
            <span class="dashicons dashicons-admin-generic"></span>
            <h2>Advanced Features</h2>
        </div>
        <p class="talktopc-advanced-subtitle">Configure these options in the TalkToPC app</p>
        
        <!-- Call Management -->
        <div class="talktopc-feature-group">
            <h4>📞 Call Management</h4>
            <div class="talktopc-feature-list">
                <div class="talktopc-feature-item">
                    <span class="talktopc-feature-icon">🎙️</span>
                    <span class="talktopc-feature-name">Call Recording</span>
                    <span class="talktopc-feature-status">Configure in app</span>
                </div>
                <a href="https://talktopc.com/agents/conversations" target="_blank" class="talktopc-feature-item talktopc-feature-clickable">
                    <span class="talktopc-feature-icon">🔊</span>
                    <span class="talktopc-feature-name">Export Calls (Audio)</span>
                    <span class="talktopc-feature-action">Open →</span>
                </a>
                <a href="https://talktopc.com/agents/conversations" target="_blank" class="talktopc-feature-item talktopc-feature-clickable">
                    <span class="talktopc-feature-icon">📝</span>
                    <span class="talktopc-feature-name">Export Transcripts</span>
                    <span class="talktopc-feature-action">Open →</span>
                </a>
            </div>
        </div>
        
        <!-- AI Configuration -->
        <div class="talktopc-feature-group">
            <h4>🤖 AI Configuration</h4>
            <div class="talktopc-feature-list">
                <div class="talktopc-feature-item">
                    <span class="talktopc-feature-icon">🧠</span>
                    <span class="talktopc-feature-name">LLM Model</span>
                    <span class="talktopc-feature-status">Configure in app</span>
                </div>
                <div class="talktopc-feature-item">
                    <span class="talktopc-feature-icon">🎛️</span>
                    <span class="talktopc-feature-name">Temperature</span>
                    <span class="talktopc-feature-status">Configure in app</span>
                </div>
                <div class="talktopc-feature-item">
                    <span class="talktopc-feature-icon">🔧</span>
                    <span class="talktopc-feature-name">Add Tools</span>
                    <span class="talktopc-feature-status">Configure in app</span>
                </div>
            </div>
        </div>
        
        <!-- Analytics -->
        <div class="talktopc-feature-group">
            <h4>📊 Analytics</h4>
            <div class="talktopc-feature-list">
                <div class="talktopc-feature-item">
                    <span class="talktopc-feature-icon">📊</span>
                    <span class="talktopc-feature-name">Dashboard</span>
                    <span class="talktopc-feature-status">View stats & analytics</span>
                </div>
            </div>
        </div>
        
        <!-- CTA Button -->
        <div class="talktopc-feature-cta">
            <a href="https://talktopc.com/agents" target="_blank" class="button button-primary button-hero">
                🚀 Open TTP App to Configure
            </a>
        </div>
    </div>
    <?php
}
