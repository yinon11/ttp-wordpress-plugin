<?php
/**
 * Frontend Widget
 * 
 * Handles:
 * - Enqueuing the widget script from CDN
 * - Building the configuration object from all settings
 * - Injecting the initialization script
 * 
 * FIX: Page rules now properly match with type casting
 */

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function() {
    $api_key = get_option('talktopc_api_key');
    $default_agent_id = get_option('talktopc_agent_id');
    
    if (empty($api_key)) return;
    
    // Get agent for current page (check rules first)
    $agent_config = talktopc_get_agent_for_current_page();
    
    // Debug logging (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('TTP Widget - Agent Config: ' . print_r($agent_config, true));
    }
    
    // Don't render if disabled
    if ($agent_config['is_disabled'] || empty($agent_config['agent_id'])) return;
    
    $agent_id = $agent_config['agent_id'];
    
    // Enqueue widget script from CDN
    wp_enqueue_script('talktopc-agent-widget', 'https://cdn.talktopc.com/agent-widget.js', [], TALKTOPC_VERSION, true);
    
    // Build configuration object (pass agent_id from page rules)
    $config = talktopc_build_widget_config($agent_id);
    
    // Create nonce for signed URL requests
    $nonce = wp_create_nonce('talktopc_widget_nonce');
    
    // Build initialization script
    $script = sprintf(
        '(function(){var c=%s,u=%s,n=%s;function f(){var x=new XMLHttpRequest();x.open("POST",u,true);x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");x.onreadystatechange=function(){if(x.readyState===4&&x.status===200){try{var r=JSON.parse(x.responseText);if(r.success&&r.data.signedUrl){c.signedUrl=r.data.signedUrl;i();}}catch(e){console.error("TTP Widget error",e);}}};x.send("action=talktopc_get_signed_url&nonce="+n);}function i(){if(typeof TTPAgentSDK!=="undefined"&&TTPAgentSDK.TTPChatWidget){new TTPAgentSDK.TTPChatWidget(c);}else{setTimeout(i,100);}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",f);}else{f();}})();',
        wp_json_encode($config),
        wp_json_encode(admin_url('admin-ajax.php')),
        wp_json_encode($nonce)
    );
    
    wp_add_inline_script('talktopc-agent-widget', $script);
});

/**
 * Build widget configuration from all settings
 */
function talktopc_build_widget_config($agent_id = null) {
    $config = [];
    
    // ==========================================================================
    // REQUIRED
    // ==========================================================================
    $config['appId'] = get_option('talktopc_app_id');
    $config['agentId'] = $agent_id !== null ? $agent_id : get_option('talktopc_agent_id');
    
    // ==========================================================================
    // BASIC OPTIONS (Widget UI only - NOT agent settings)
    // ==========================================================================
    if ($v = get_option('talktopc_direction')) $config['direction'] = $v;
    if ($v = get_option('talktopc_position')) $config['position'] = $v;
    
    // ==========================================================================
    // BUTTON
    // ==========================================================================
    $button = [];
    if ($v = get_option('talktopc_button_size')) $button['size'] = $v;
    if ($v = get_option('talktopc_button_shape')) $button['shape'] = $v;
    if ($v = get_option('talktopc_button_bg_color')) $button['backgroundColor'] = $v;
    if ($v = get_option('talktopc_button_hover_color')) $button['hoverColor'] = $v;
    $button['shadow'] = get_option('talktopc_button_shadow', '1') === '1';
    if ($v = get_option('talktopc_button_shadow_color')) $button['shadowColor'] = $v;
    if (!empty($button)) $config['button'] = $button;
    
    // ==========================================================================
    // ICON
    // ==========================================================================
    $icon = [];
    if ($v = get_option('talktopc_icon_type')) $icon['type'] = $v;
    if ($v = get_option('talktopc_icon_custom_image')) $icon['customImage'] = $v;
    if ($v = get_option('talktopc_icon_emoji')) $icon['emoji'] = $v;
    if ($v = get_option('talktopc_icon_text')) $icon['text'] = $v;
    if ($v = get_option('talktopc_icon_size')) $icon['size'] = $v;
    if ($v = get_option('talktopc_icon_bg_color')) $icon['backgroundColor'] = $v;
    if (!empty($icon)) $config['icon'] = $icon;
    
    // ==========================================================================
    // PANEL
    // ==========================================================================
    $panel = [];
    if ($v = get_option('talktopc_panel_width')) $panel['width'] = intval($v);
    if ($v = get_option('talktopc_panel_height')) $panel['height'] = intval($v);
    if ($v = get_option('talktopc_panel_border_radius')) $panel['borderRadius'] = intval($v);
    if ($v = get_option('talktopc_panel_bg_color')) $panel['backgroundColor'] = $v;
    if ($v = get_option('talktopc_panel_border')) $panel['border'] = $v;
    if ($v = get_option('talktopc_panel_backdrop_filter')) $panel['backdropFilter'] = $v;
    if (!empty($panel)) $config['panel'] = $panel;
    
    // ==========================================================================
    // HEADER
    // ==========================================================================
    $header = [];
    if ($v = get_option('talktopc_header_title')) $header['title'] = $v;
    $header['showTitle'] = get_option('talktopc_header_show_title', '1') === '1';
    if ($v = get_option('talktopc_header_bg_color')) $header['backgroundColor'] = $v;
    if ($v = get_option('talktopc_header_text_color')) $header['textColor'] = $v;
    $header['showCloseButton'] = get_option('talktopc_header_show_close', '1') === '1';
    if (!empty($header)) $config['header'] = $header;
    
    // ==========================================================================
    // FOOTER (TTP Branding)
    // ==========================================================================
    $footer = ['show' => true]; // Always show for free tier
    if ($v = get_option('talktopc_footer_bg_color')) $footer['backgroundColor'] = $v;
    if ($v = get_option('talktopc_footer_text_color')) $footer['textColor'] = $v;
    if ($v = get_option('talktopc_footer_hover_color')) $footer['hoverColor'] = $v;
    $config['footer'] = $footer;
    
    // ==========================================================================
    // MESSAGES
    // ==========================================================================
    $messages = [];
    if ($v = get_option('talktopc_msg_user_bg')) $messages['userBackgroundColor'] = $v;
    if ($v = get_option('talktopc_msg_agent_bg')) $messages['agentBackgroundColor'] = $v;
    if ($v = get_option('talktopc_msg_system_bg')) $messages['systemBackgroundColor'] = $v;
    if ($v = get_option('talktopc_msg_error_bg')) $messages['errorBackgroundColor'] = $v;
    if ($v = get_option('talktopc_msg_text_color')) $messages['textColor'] = $v;
    if ($v = get_option('talktopc_msg_font_size')) $messages['fontSize'] = $v;
    if ($v = get_option('talktopc_msg_border_radius')) $messages['borderRadius'] = intval($v);
    if (!empty($messages)) $config['messages'] = $messages;
    
    // ==========================================================================
    // LANDING SCREEN
    // ==========================================================================
    $landing = [];
    if ($v = get_option('talktopc_landing_bg_color')) $landing['backgroundColor'] = $v;
    if ($v = get_option('talktopc_landing_logo')) $landing['logo'] = $v;
    if ($v = get_option('talktopc_landing_title')) $landing['title'] = $v;
    if ($v = get_option('talktopc_landing_title_color')) $landing['titleColor'] = $v;
    if ($v = get_option('talktopc_landing_card_bg_color')) $landing['modeCardBackgroundColor'] = $v;
    if ($v = get_option('talktopc_landing_card_border_color')) $landing['modeCardBorderColor'] = $v;
    if ($v = get_option('talktopc_landing_card_hover_border_color')) $landing['modeCardHoverBorderColor'] = $v;
    if ($v = get_option('talktopc_landing_card_icon_bg_color')) $landing['modeCardIconBackgroundColor'] = $v;
    if ($v = get_option('talktopc_landing_card_title_color')) $landing['modeCardTitleColor'] = $v;
    if ($v = get_option('talktopc_landing_voice_icon')) $landing['voiceCardIcon'] = $v;
    if ($v = get_option('talktopc_landing_voice_title')) $landing['voiceCardTitle'] = $v;
    if ($v = get_option('talktopc_landing_text_icon')) $landing['textCardIcon'] = $v;
    if ($v = get_option('talktopc_landing_text_title')) $landing['textCardTitle'] = $v;
    if (!empty($landing)) $config['landing'] = $landing;
    
    // ==========================================================================
    // VOICE INTERFACE
    // ==========================================================================
    $voice = [
        // Audio output settings (required for proper playback)
        'outputContainer' => 'raw',
        'outputEncoding' => 'pcm',
        'outputSampleRate' => 44100,
        'outputChannels' => 1,
        'outputBitDepth' => 16
    ];
    // Microphone
    if ($v = get_option('talktopc_voice_mic_color')) $voice['micButtonColor'] = $v;
    if ($v = get_option('talktopc_voice_mic_active_color')) $voice['micButtonActiveColor'] = $v;
    // Avatar
    if ($v = get_option('talktopc_voice_avatar_color')) $voice['avatarBackgroundColor'] = $v;
    if ($v = get_option('talktopc_voice_avatar_active_color')) $voice['avatarActiveBackgroundColor'] = $v;
    // Status
    if ($v = get_option('talktopc_voice_status_title_color')) $voice['statusTitleColor'] = $v;
    if ($v = get_option('talktopc_voice_status_subtitle_color')) $voice['statusSubtitleColor'] = $v;
    // Start Call
    if ($v = get_option('talktopc_voice_start_title')) $voice['startCallTitle'] = $v;
    if ($v = get_option('talktopc_voice_start_subtitle')) $voice['startCallSubtitle'] = $v;
    if ($v = get_option('talktopc_voice_start_btn_text')) $voice['startCallButtonText'] = $v;
    if ($v = get_option('talktopc_voice_start_btn_color')) $voice['startCallButtonColor'] = $v;
    if ($v = get_option('talktopc_voice_start_btn_text_color')) $voice['startCallButtonTextColor'] = $v;
    // Transcript
    if ($v = get_option('talktopc_voice_transcript_bg_color')) $voice['transcriptBackgroundColor'] = $v;
    if ($v = get_option('talktopc_voice_transcript_text_color')) $voice['transcriptTextColor'] = $v;
    if ($v = get_option('talktopc_voice_transcript_label_color')) $voice['transcriptLabelColor'] = $v;
    // Controls
    if ($v = get_option('talktopc_voice_control_btn_color')) $voice['controlButtonColor'] = $v;
    if ($v = get_option('talktopc_voice_control_btn_secondary_color')) $voice['controlButtonSecondaryColor'] = $v;
    if ($v = get_option('talktopc_voice_end_btn_color')) $voice['endCallButtonColor'] = $v;
    $config['voice'] = $voice;
    
    // ==========================================================================
    // TEXT INTERFACE
    // ==========================================================================
    $text = [];
    // Send Button
    if ($v = get_option('talktopc_text_send_btn_color')) $text['sendButtonColor'] = $v;
    if ($v = get_option('talktopc_text_send_btn_hover_color')) $text['sendButtonHoverColor'] = $v;
    if ($v = get_option('talktopc_text_send_btn_text')) $text['sendButtonText'] = $v;
    if ($v = get_option('talktopc_text_send_btn_text_color')) $text['sendButtonTextColor'] = $v;
    // Input Field
    if ($v = get_option('talktopc_text_input_placeholder')) $text['inputPlaceholder'] = $v;
    if ($v = get_option('talktopc_text_input_bg_color')) $text['inputBackgroundColor'] = $v;
    if ($v = get_option('talktopc_text_input_text_color')) $text['inputTextColor'] = $v;
    if ($v = get_option('talktopc_text_input_border_color')) $text['inputBorderColor'] = $v;
    if ($v = get_option('talktopc_text_input_focus_color')) $text['inputFocusColor'] = $v;
    if ($v = get_option('talktopc_text_input_border_radius')) $text['inputBorderRadius'] = intval($v);
    if (!empty($text)) $config['text'] = $text;
    
    // ==========================================================================
    // TOOLTIPS
    // ==========================================================================
    $tooltips = [];
    if ($v = get_option('talktopc_tooltip_new_chat')) $tooltips['newChat'] = $v;
    if ($v = get_option('talktopc_tooltip_back')) $tooltips['back'] = $v;
    if ($v = get_option('talktopc_tooltip_close')) $tooltips['close'] = $v;
    if ($v = get_option('talktopc_tooltip_mute')) $tooltips['mute'] = $v;
    if ($v = get_option('talktopc_tooltip_speaker')) $tooltips['speaker'] = $v;
    if ($v = get_option('talktopc_tooltip_end_call')) $tooltips['endCall'] = $v;
    if (!empty($tooltips)) $config['tooltips'] = $tooltips;
    
    // ==========================================================================
    // ANIMATION
    // ==========================================================================
    $animation = [];
    $animation['enableHover'] = get_option('talktopc_anim_enable_hover', '1') === '1';
    $animation['enablePulse'] = get_option('talktopc_anim_enable_pulse', '1') === '1';
    $animation['enableSlide'] = get_option('talktopc_anim_enable_slide', '1') === '1';
    if ($v = get_option('talktopc_anim_duration')) $animation['duration'] = floatval($v);
    if (!empty($animation)) $config['animation'] = $animation;
    
    // ==========================================================================
    // ACCESSIBILITY
    // ==========================================================================
    $accessibility = [];
    if ($v = get_option('talktopc_a11y_aria_label')) $accessibility['ariaLabel'] = $v;
    if ($v = get_option('talktopc_a11y_aria_description')) $accessibility['ariaDescription'] = $v;
    $accessibility['keyboardNavigation'] = get_option('talktopc_a11y_keyboard_nav', '1') === '1';
    if (!empty($accessibility)) $config['accessibility'] = $accessibility;
    
    // ==========================================================================
    // BEHAVIOR
    // ==========================================================================
    $behavior = [];
    if ($v = get_option('talktopc_mode')) $behavior['mode'] = $v;
    if (get_option('talktopc_auto_open') === '1') $behavior['autoOpen'] = true;
    if ($v = get_option('talktopc_welcome_message')) {
        $behavior['showWelcomeMessage'] = true;
        $behavior['welcomeMessage'] = $v;
    }
    if (!empty($behavior)) $config['behavior'] = $behavior;
    
    // ==========================================================================
    // AGENT SETTINGS OVERRIDE
    // ==========================================================================
    // NOTE: We no longer send overrides by default. The agent should use its 
    // own settings from the TalkToPC backend. Only send overrides if explicitly
    // configured in WordPress (future feature: add a "customize agent" checkbox)
    //
    // If you need per-page overrides, use Page Rules with custom settings.
    // ==========================================================================
    
    return $config;
}

/**
 * Get agent configuration for current page based on page rules
 * 
 * FIX: Properly cast target_id to int for page/post/category matching
 */
function talktopc_get_agent_for_current_page() {
    $rules = json_decode(get_option('talktopc_page_rules', '[]'), true);
    $default_agent_id = get_option('talktopc_agent_id', '');
    $default_agent_name = get_option('talktopc_agent_name', '');
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG && !empty($rules)) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('TTP Page Rules Check - Current URL: ' . $request_uri);
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
        error_log('TTP Page Rules - Rules: ' . print_r($rules, true));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log('TTP Page Rules - is_page(): ' . (is_page() ? 'yes' : 'no'));
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log('TTP Page Rules - is_single(): ' . (is_single() ? 'yes' : 'no'));
        if (is_page() || is_single()) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('TTP Page Rules - Current post ID: ' . get_the_ID());
        }
    }
    
    foreach ($rules as $rule) {
        if (talktopc_rule_matches_current_page($rule)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r
                error_log('TTP Page Rules - MATCHED rule: ' . print_r($rule, true));
            }
            return [
                'agent_id' => $rule['agent_id'],
                'agent_name' => $rule['agent_name'] ?? '',
                'is_disabled' => ($rule['agent_id'] === 'none')
            ];
        }
    }
    
    return [
        'agent_id' => $default_agent_id,
        'agent_name' => $default_agent_name,
        'is_disabled' => ($default_agent_id === 'none' || empty($default_agent_id))
    ];
}

/**
 * Check if a rule matches the current page
 * 
 * FIX: Cast target_id to int for numeric comparisons
 */
function talktopc_rule_matches_current_page($rule) {
    $type = $rule['type'] ?? '';
    $target_id = $rule['target_id'] ?? '';
    
    if (empty($target_id)) return false;
    
    // Debug logging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log("TTP Rule Check - Type: {$type}, Target: {$target_id}");
    }
    
    switch ($type) {
        case 'page':
            // FIX: Cast to int for is_page() check
            $target_int = intval($target_id);
            $matches = is_page($target_int);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log("TTP Rule Check - is_page({$target_int}): " . ($matches ? 'yes' : 'no'));
            }
            return $matches;
            
        case 'post':
            // FIX: Cast to int for is_single() check
            $target_int = intval($target_id);
            return is_single($target_int);
            
        case 'post_type':
            // post_type target_id is a string like 'post', 'product'
            return is_singular($target_id) || is_post_type_archive($target_id);
            
        case 'category':
            // FIX: Cast to int for category checks
            $target_int = intval($target_id);
            return is_category($target_int) || (is_single() && has_category($target_int));
            
        case 'product_cat':
            if (function_exists('is_product_category')) {
                // FIX: Cast to int for product category checks
                $target_int = intval($target_id);
                return is_product_category($target_int) || (is_singular('product') && has_term($target_int, 'product_cat'));
            }
            return false;
            
        default:
            return false;
    }
}