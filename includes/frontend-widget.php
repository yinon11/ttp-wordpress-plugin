<?php
/**
 * Frontend Widget
 * 
 * Handles:
 * - Enqueuing the widget script from CDN
 * - Building the configuration object from all settings
 * - Injecting the initialization script
 */

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function() {
    $api_key = get_option('ttp_api_key');
    $agent_id = get_option('ttp_agent_id');
    
    // Don't load widget if not configured
    if (empty($api_key) || empty($agent_id)) return;
    
    // Enqueue widget script from CDN
    wp_enqueue_script('ttp-agent-widget', 'https://cdn.talktopc.com/agent-widget.js', [], TTP_VERSION, true);
    
    // Build configuration object
    $config = ttp_build_widget_config();
    
    // Create nonce for signed URL requests
    $nonce = wp_create_nonce('ttp_widget_nonce');
    
    // Build initialization script
    $script = sprintf(
        '(function(){var c=%s,u=%s,n=%s;function f(){var x=new XMLHttpRequest();x.open("POST",u,true);x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");x.onreadystatechange=function(){if(x.readyState===4&&x.status===200){try{var r=JSON.parse(x.responseText);if(r.success&&r.data.signedUrl){c.signedUrl=r.data.signedUrl;i();}}catch(e){console.error("TTP Widget error",e);}}};x.send("action=ttp_get_signed_url&nonce="+n);}function i(){if(typeof TTPAgentSDK!=="undefined"&&TTPAgentSDK.TTPChatWidget){new TTPAgentSDK.TTPChatWidget(c);}else{setTimeout(i,100);}}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",f);}else{f();}})();',
        wp_json_encode($config),
        wp_json_encode(admin_url('admin-ajax.php')),
        wp_json_encode($nonce)
    );
    
    wp_add_inline_script('ttp-agent-widget', $script);
});

/**
 * Build widget configuration from all settings
 */
function ttp_build_widget_config() {
    $config = [];
    
    // Direction & Language
    if ($v = get_option('ttp_direction')) $config['direction'] = $v;
    if ($v = get_option('ttp_override_language')) $config['language'] = $v;
    if ($v = get_option('ttp_position')) $config['position'] = $v;
    
    // Button
    $button = [];
    if ($v = get_option('ttp_button_size')) $button['size'] = $v;
    if ($v = get_option('ttp_button_shape')) $button['shape'] = $v;
    if ($v = get_option('ttp_button_bg_color')) $button['backgroundColor'] = $v;
    if ($v = get_option('ttp_button_hover_color')) $button['hoverColor'] = $v;
    $button['shadow'] = get_option('ttp_button_shadow', '1') === '1';
    if (!empty($button)) $config['button'] = $button;
    
    // Icon
    $icon = [];
    if ($v = get_option('ttp_icon_type')) $icon['type'] = $v;
    if ($v = get_option('ttp_icon_custom_image')) $icon['customImage'] = $v;
    if ($v = get_option('ttp_icon_emoji')) $icon['emoji'] = $v;
    if ($v = get_option('ttp_icon_text')) $icon['text'] = $v;
    if ($v = get_option('ttp_icon_size')) $icon['size'] = $v;
    if (!empty($icon)) $config['icon'] = $icon;
    
    // Panel
    $panel = [];
    if ($v = get_option('ttp_panel_width')) $panel['width'] = intval($v);
    if ($v = get_option('ttp_panel_height')) $panel['height'] = intval($v);
    if ($v = get_option('ttp_panel_border_radius')) $panel['borderRadius'] = intval($v);
    if ($v = get_option('ttp_panel_bg_color')) $panel['backgroundColor'] = $v;
    if (!empty($panel)) $config['panel'] = $panel;
    
    // Header
    $header = [];
    if ($v = get_option('ttp_header_title')) $header['title'] = $v;
    if ($v = get_option('ttp_header_bg_color')) $header['backgroundColor'] = $v;
    if ($v = get_option('ttp_header_text_color')) $header['textColor'] = $v;
    $header['showCloseButton'] = get_option('ttp_header_show_close', '1') === '1';
    if (!empty($header)) $config['header'] = $header;
    
    // Voice (with audio fix)
    $voice = [
        'outputContainer' => 'raw',
        'outputEncoding' => 'pcm',
        'outputSampleRate' => 44100,
        'outputChannels' => 1,
        'outputBitDepth' => 16
    ];
    if ($v = get_option('ttp_voice_mic_color')) $voice['micButtonColor'] = $v;
    if ($v = get_option('ttp_voice_mic_active_color')) $voice['micButtonActiveColor'] = $v;
    if ($v = get_option('ttp_voice_avatar_color')) {
        $voice['avatarBackgroundColor'] = $v;
        $voice['avatarActiveBackgroundColor'] = $v;
    }
    if ($v = get_option('ttp_voice_start_btn_color')) $voice['startCallButtonColor'] = $v;
    if ($v = get_option('ttp_voice_end_btn_color')) $voice['endCallButtonColor'] = $v;
    $config['voice'] = $voice;
    
    // Text
    $text = [];
    if ($v = get_option('ttp_text_send_btn_color')) $text['sendButtonColor'] = $v;
    if ($v = get_option('ttp_text_input_placeholder')) $text['inputPlaceholder'] = $v;
    if ($v = get_option('ttp_text_input_focus_color')) $text['inputFocusColor'] = $v;
    if (!empty($text)) $config['text'] = $text;
    
    // Messages
    $messages = [];
    if ($v = get_option('ttp_msg_user_bg')) $messages['userBackgroundColor'] = $v;
    if ($v = get_option('ttp_msg_agent_bg')) $messages['agentBackgroundColor'] = $v;
    if ($v = get_option('ttp_msg_text_color')) $messages['textColor'] = $v;
    if (!empty($messages)) $config['messages'] = $messages;
    
    // Landing
    $landing = [];
    if ($v = get_option('ttp_landing_logo')) $landing['logo'] = $v;
    if ($v = get_option('ttp_landing_title')) $landing['title'] = $v;
    if ($v = get_option('ttp_landing_title_color')) $landing['titleColor'] = $v;
    if (!empty($landing)) $config['landing'] = $landing;
    
    // Behavior
    $behavior = [];
    if ($v = get_option('ttp_mode')) $behavior['mode'] = $v;
    if (get_option('ttp_auto_open') === '1') $behavior['autoOpen'] = true;
    if ($v = get_option('ttp_welcome_message')) {
        $behavior['showWelcomeMessage'] = true;
        $behavior['welcomeMessage'] = $v;
    }
    if (!empty($behavior)) $config['behavior'] = $behavior;
    
    // Agent Settings Override
    $override = [];
    if ($v = get_option('ttp_override_prompt')) $override['prompt'] = $v;
    if ($v = get_option('ttp_override_first_message')) $override['firstMessage'] = $v;
    if ($v = get_option('ttp_override_voice')) $override['voiceId'] = $v;
    if ($v = get_option('ttp_override_voice_speed')) $override['voiceSpeed'] = floatval($v);
    if ($v = get_option('ttp_override_language')) $override['language'] = $v;
    if ($v = get_option('ttp_override_temperature')) $override['temperature'] = floatval($v);
    if ($v = get_option('ttp_override_max_tokens')) $override['maxTokens'] = intval($v);
    if ($v = get_option('ttp_override_max_call_duration')) $override['maxCallDuration'] = intval($v);
    if (!empty($override)) $config['agentSettingsOverride'] = $override;
    
    // Custom CSS
    if ($css = get_option('ttp_custom_css')) $config['customStyles'] = $css;
    
    return $config;
}
