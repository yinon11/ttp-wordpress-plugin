<?php
/**
 * Plugin Name: TalkToPC Voice Widget
 * Description: Add AI voice conversations to your WordPress site.
 * Version: 1.9.20
 * Author: TalkToPC
 * Author URI: https://talktopc.com
 * License: GPL-2.0-or-later
 * Text Domain: ttp-voice-widget
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * 
 * =============================================================================
 * PLUGIN STRUCTURE (for AI agents)
 * =============================================================================
 * 
 * Main Files:
 *   - ttp-voice-widget.php    → THIS FILE: Entry point, constants, includes
 *   - README-STRUCTURE.md     → Guide for understanding the codebase
 * 
 * Includes:
 *   - includes/admin-settings.php   → Settings registration & sanitizers
 *   - includes/admin-page.php       → Admin UI (settings page HTML/JS/CSS)
 *   - includes/oauth.php            → OAuth callback & disconnect handlers
 *   - includes/ajax-handlers.php    → All AJAX endpoints (agents, voices, etc.)
 *   - includes/frontend-widget.php  → Frontend widget script & config
 * 
 * To modify specific functionality, ask to see the relevant include file.
 * =============================================================================
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// CONSTANTS
// =============================================================================
define('TTP_API_URL', 'https://backend.talktopc.com');
define('TTP_CONNECT_URL', 'https://talktopc.com/connect/wordpress');
define('TTP_VERSION', '1.9.20');
define('TTP_PLUGIN_DIR', plugin_dir_path(__FILE__));

// =============================================================================
// INCLUDES
// =============================================================================
require_once TTP_PLUGIN_DIR . 'includes/admin-settings.php';
require_once TTP_PLUGIN_DIR . 'includes/admin-page.php';
require_once TTP_PLUGIN_DIR . 'includes/oauth.php';
require_once TTP_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once TTP_PLUGIN_DIR . 'includes/frontend-widget.php';

// =============================================================================
// UNINSTALL CLEANUP
// =============================================================================
register_uninstall_hook(__FILE__, 'ttp_uninstall_cleanup');

function ttp_uninstall_cleanup() {
    $all_options = ttp_get_all_option_names();
    foreach ($all_options as $option) {
        delete_option($option);
    }
}

/**
 * Get all plugin option names (used for cleanup and disconnect)
 */
function ttp_get_all_option_names() {
    return [
        // =========================
        // CONNECTION
        // =========================
        'ttp_api_key', 'ttp_app_id', 'ttp_user_email',
        
        // =========================
        // AGENT
        // =========================
        'ttp_agent_id', 'ttp_agent_name',
        
        // =========================
        // AGENT OVERRIDES
        // =========================
        'ttp_override_prompt', 'ttp_override_first_message', 'ttp_override_voice',
        'ttp_override_voice_speed', 'ttp_override_language', 'ttp_override_temperature',
        'ttp_override_max_tokens', 'ttp_override_max_call_duration',
        
        // =========================
        // BEHAVIOR
        // =========================
        'ttp_mode', 'ttp_direction', 'ttp_auto_open', 'ttp_welcome_message',
        
        // =========================
        // BUTTON
        // =========================
        'ttp_position', 'ttp_button_size', 'ttp_button_shape', 'ttp_button_bg_color',
        'ttp_button_hover_color', 'ttp_button_shadow', 'ttp_button_shadow_color',
        
        // =========================
        // ICON
        // =========================
        'ttp_icon_type', 'ttp_icon_custom_image', 'ttp_icon_emoji', 'ttp_icon_text',
        'ttp_icon_size', 'ttp_icon_bg_color',
        
        // =========================
        // PANEL
        // =========================
        'ttp_panel_width', 'ttp_panel_height', 'ttp_panel_border_radius',
        'ttp_panel_bg_color', 'ttp_panel_border', 'ttp_panel_backdrop_filter',
        
        // =========================
        // HEADER
        // =========================
        'ttp_header_title', 'ttp_header_show_title', 'ttp_header_bg_color',
        'ttp_header_text_color', 'ttp_header_show_close',
        
        // =========================
        // FOOTER (TTP Branding)
        // =========================
        'ttp_footer_bg_color', 'ttp_footer_text_color', 'ttp_footer_hover_color',
        
        // =========================
        // MESSAGES
        // =========================
        'ttp_msg_user_bg', 'ttp_msg_agent_bg', 'ttp_msg_system_bg', 'ttp_msg_error_bg',
        'ttp_msg_text_color', 'ttp_msg_font_size', 'ttp_msg_border_radius',
        
        // =========================
        // LANDING SCREEN
        // =========================
        'ttp_landing_bg_color', 'ttp_landing_logo', 'ttp_landing_title',
        'ttp_landing_title_color', 'ttp_landing_card_bg_color', 'ttp_landing_card_border_color',
        'ttp_landing_card_hover_border_color', 'ttp_landing_card_icon_bg_color',
        'ttp_landing_card_title_color', 'ttp_landing_voice_icon', 'ttp_landing_text_icon',
        'ttp_landing_voice_title', 'ttp_landing_text_title',
        
        // =========================
        // VOICE INTERFACE
        // =========================
        'ttp_voice_mic_color', 'ttp_voice_mic_active_color',
        'ttp_voice_avatar_color', 'ttp_voice_avatar_active_color',
        'ttp_voice_status_title_color', 'ttp_voice_status_subtitle_color',
        'ttp_voice_start_title', 'ttp_voice_start_subtitle', 'ttp_voice_start_btn_text',
        'ttp_voice_start_btn_color', 'ttp_voice_start_btn_text_color',
        'ttp_voice_transcript_bg_color', 'ttp_voice_transcript_text_color',
        'ttp_voice_transcript_label_color',
        'ttp_voice_control_btn_color', 'ttp_voice_control_btn_secondary_color',
        'ttp_voice_end_btn_color',
        
        // =========================
        // TEXT INTERFACE
        // =========================
        'ttp_text_send_btn_color', 'ttp_text_send_btn_hover_color',
        'ttp_text_send_btn_text', 'ttp_text_send_btn_text_color',
        'ttp_text_input_placeholder', 'ttp_text_input_border_color',
        'ttp_text_input_focus_color', 'ttp_text_input_bg_color',
        'ttp_text_input_text_color', 'ttp_text_input_border_radius',
        
        // =========================
        // TOOLTIPS
        // =========================
        'ttp_tooltip_new_chat', 'ttp_tooltip_back', 'ttp_tooltip_close',
        'ttp_tooltip_mute', 'ttp_tooltip_speaker', 'ttp_tooltip_end_call',
        
        // =========================
        // ANIMATION
        // =========================
        'ttp_anim_enable_hover', 'ttp_anim_enable_pulse', 'ttp_anim_enable_slide',
        'ttp_anim_duration',
        
        // =========================
        // ACCESSIBILITY
        // =========================
        'ttp_a11y_aria_label', 'ttp_a11y_aria_description', 'ttp_a11y_keyboard_nav',
        
        // =========================
        // CUSTOM CSS
        // =========================
        'ttp_custom_css'
    ];
}

// =============================================================================
// PLUGIN ACTION LINKS
// =============================================================================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    array_unshift($links, '<a href="' . admin_url('admin.php?page=ttp-voice-widget') . '">Settings</a>');
    return $links;
});