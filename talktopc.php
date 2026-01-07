<?php
/**
 * Plugin Name: TalkToPC Voice Widget
 * Plugin URI: https://wordpress.org/plugins/talktopc/
 * Description: Add AI voice conversations to your WordPress site.
 * Version: 1.9.79
 * Author: TalkToPC
 * Author URI: https://talktopc.com
 * License: GPL-2.0-or-later
 * Text Domain: talktopc
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * 
 * =============================================================================
 * PLUGIN STRUCTURE (for AI agents)
 * =============================================================================
 * 
 * Main Files:
 *   - talktopc.php    → THIS FILE: Entry point, constants, includes
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
define('TALKTOPC_API_URL', 'https://backend.talktopc.com');
define('TALKTOPC_CONNECT_URL', 'https://talktopc.com/connect/wordpress');
define('TALKTOPC_VERSION', '1.9.79');
define('TALKTOPC_PLUGIN_DIR', plugin_dir_path(__FILE__));

// =============================================================================
// INCLUDES
// =============================================================================
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-settings.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-page.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/oauth.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/frontend-widget.php';

// =============================================================================
// UNINSTALL CLEANUP
// =============================================================================
register_uninstall_hook(__FILE__, 'talktopc_uninstall_cleanup');

function talktopc_uninstall_cleanup() {
    $all_options = talktopc_get_all_option_names();
    foreach ($all_options as $option) {
        delete_option($option);
    }
}

/**
 * Get all plugin option names (used for cleanup and disconnect)
 */
function talktopc_get_all_option_names() {
    return [
        // =========================
        // CONNECTION
        // =========================
        'talktopc_api_key', 'talktopc_app_id', 'talktopc_user_email', 'talktopc_feature_banner_dismissed',
        'talktopc_connected_time', 'talktopc_review_dismissed', 'talktopc_review_done',
        
        // =========================
        // AGENT
        // =========================
        'talktopc_agent_id', 'talktopc_agent_name',
        
        // =========================
        // PAGE RULES
        // =========================
        'talktopc_page_rules',
        
        // =========================
        // AGENT OVERRIDES
        // =========================
        'talktopc_override_prompt', 'talktopc_override_first_message', 'talktopc_override_voice',
        'talktopc_override_voice_speed', 'talktopc_override_language', 'talktopc_override_temperature',
        'talktopc_override_max_tokens', 'talktopc_override_max_call_duration',
        
        // =========================
        // BEHAVIOR
        // =========================
        'talktopc_mode', 'talktopc_direction', 'talktopc_auto_open', 'talktopc_welcome_message',
        
        // =========================
        // BUTTON
        // =========================
        'talktopc_position', 'talktopc_button_size', 'talktopc_button_shape', 'talktopc_button_bg_color',
        'talktopc_button_hover_color', 'talktopc_button_shadow', 'talktopc_button_shadow_color',
        
        // =========================
        // ICON
        // =========================
        'talktopc_icon_type', 'talktopc_icon_custom_image', 'talktopc_icon_emoji', 'talktopc_icon_text',
        'talktopc_icon_size', 'talktopc_icon_bg_color',
        
        // =========================
        // PANEL
        // =========================
        'talktopc_panel_width', 'talktopc_panel_height', 'talktopc_panel_border_radius',
        'talktopc_panel_bg_color', 'talktopc_panel_border', 'talktopc_panel_backdrop_filter',
        
        // =========================
        // HEADER
        // =========================
        'talktopc_header_title', 'talktopc_header_show_title', 'talktopc_header_bg_color',
        'talktopc_header_text_color', 'talktopc_header_show_close',
        
        // =========================
        // FOOTER (TTP Branding)
        // =========================
        'talktopc_footer_bg_color', 'talktopc_footer_text_color', 'talktopc_footer_hover_color',
        
        // =========================
        // MESSAGES
        // =========================
        'talktopc_msg_user_bg', 'talktopc_msg_agent_bg', 'talktopc_msg_system_bg', 'talktopc_msg_error_bg',
        'talktopc_msg_text_color', 'talktopc_msg_font_size', 'talktopc_msg_border_radius',
        
        // =========================
        // LANDING SCREEN
        // =========================
        'talktopc_landing_bg_color', 'talktopc_landing_logo', 'talktopc_landing_title',
        'talktopc_landing_title_color', 'talktopc_landing_card_bg_color', 'talktopc_landing_card_border_color',
        'talktopc_landing_card_hover_border_color', 'talktopc_landing_card_icon_bg_color',
        'talktopc_landing_card_title_color', 'talktopc_landing_voice_icon', 'talktopc_landing_text_icon',
        'talktopc_landing_voice_title', 'talktopc_landing_text_title',
        
        // =========================
        // VOICE INTERFACE
        // =========================
        'talktopc_voice_mic_color', 'talktopc_voice_mic_active_color',
        'talktopc_voice_avatar_color', 'talktopc_voice_avatar_active_color',
        'talktopc_voice_status_title_color', 'talktopc_voice_status_subtitle_color',
        'talktopc_voice_start_title', 'talktopc_voice_start_subtitle', 'talktopc_voice_start_btn_text',
        'talktopc_voice_start_btn_color', 'talktopc_voice_start_btn_text_color',
        'talktopc_voice_transcript_bg_color', 'talktopc_voice_transcript_text_color',
        'talktopc_voice_transcript_label_color',
        'talktopc_voice_control_btn_color', 'talktopc_voice_control_btn_secondary_color',
        'talktopc_voice_end_btn_color',
        
        // =========================
        // TEXT INTERFACE
        // =========================
        'talktopc_text_send_btn_color', 'talktopc_text_send_btn_hover_color',
        'talktopc_text_send_btn_text', 'talktopc_text_send_btn_text_color',
        'talktopc_text_input_placeholder', 'talktopc_text_input_border_color',
        'talktopc_text_input_focus_color', 'talktopc_text_input_bg_color',
        'talktopc_text_input_text_color', 'talktopc_text_input_border_radius',
        
        // =========================
        // TOOLTIPS
        // =========================
        'talktopc_tooltip_new_chat', 'talktopc_tooltip_back', 'talktopc_tooltip_close',
        'talktopc_tooltip_mute', 'talktopc_tooltip_speaker', 'talktopc_tooltip_end_call',
        
        // =========================
        // ANIMATION
        // =========================
        'talktopc_anim_enable_hover', 'talktopc_anim_enable_pulse', 'talktopc_anim_enable_slide',
        'talktopc_anim_duration',
        
        // =========================
        // ACCESSIBILITY
        // =========================
        'talktopc_a11y_aria_label', 'talktopc_a11y_aria_description', 'talktopc_a11y_keyboard_nav'
    ];
}

// =============================================================================
// PLUGIN ACTION LINKS
// =============================================================================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    array_unshift($links, '<a href="' . admin_url('admin.php?page=talktopc') . '">Settings</a>');
    return $links;
});