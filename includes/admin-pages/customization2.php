<?php
/**
 * Customization2 Page - Based on SDK widget-customization.html
 * 
 * Live customization interface for widget appearance
 * Matches widget-customization.html from SDK
 */

if (!defined('ABSPATH')) exit;

/**
 * Get all widget settings from WordPress options
 * Returns an array of all widget customization settings
 * 
 * @return array Widget settings array
 */
function talktopc_get_all_widget_settings() {
    return [
        'button' => [
            'size' => get_option('talktopc_button_size', 'medium'),
            'shape' => get_option('talktopc_button_shape', 'circle'),
            'backgroundColor' => get_option('talktopc_button_bg_color', '#FFFFFF'),
            'hoverColor' => get_option('talktopc_button_hover_color', '#D3D3D3'),
            'shadow' => get_option('talktopc_button_shadow', '1') === '1',
            'shadowColor' => get_option('talktopc_button_shadow_color', 'rgba(0,0,0,0.15)'),
        ],
        'icon' => [
            'type' => get_option('talktopc_icon_type', 'custom'),
            'customImage' => get_option('talktopc_icon_custom_image', 'https://talktopc.com/logo192.png'),
            'emoji' => get_option('talktopc_icon_emoji', ''),
            'text' => get_option('talktopc_icon_text', ''),
            'size' => get_option('talktopc_icon_size', 'medium'),
            'backgroundColor' => get_option('talktopc_icon_bg_color', '#FFFFFF'),
        ],
        'panel' => [
            'width' => intval(get_option('talktopc_panel_width', 360)),
            'height' => intval(get_option('talktopc_panel_height', 550)),
            'borderRadius' => intval(get_option('talktopc_panel_border_radius', 24)),
            'backgroundColor' => get_option('talktopc_panel_bg_color', '#FFFFFF'),
            'border' => get_option('talktopc_panel_border', '1px solid #E5E7EB'),
        ],
        'position' => [
            'vertical' => strpos(get_option('talktopc_position', 'bottom-right'), 'bottom') !== false ? 'bottom' : 'top',
            'horizontal' => strpos(get_option('talktopc_position', 'bottom-right'), 'right') !== false ? 'right' : 'left',
            'offset' => [
                'x' => intval(get_option('talktopc_position_offset_x', 20)),
                'y' => intval(get_option('talktopc_position_offset_y', 20))
            ]
        ],
        'header' => [
            'title' => get_option('talktopc_header_title', 'Chat Assistant'),
            'backgroundColor' => get_option('talktopc_header_bg_color', '#7C3AED'),
            'textColor' => get_option('talktopc_header_text_color', '#FFFFFF'),
            'showCloseButton' => get_option('talktopc_header_show_close', '1') === '1',
        ],
        'messages' => [
            'userBackgroundColor' => get_option('talktopc_msg_user_bg', '#E5E7EB'),
            'agentBackgroundColor' => get_option('talktopc_msg_agent_bg', '#F3F4F6'),
            'textColor' => get_option('talktopc_msg_text_color', '#1F2937'),
            'fontSize' => get_option('talktopc_msg_font_size', '14px'),
            'borderRadius' => intval(get_option('talktopc_msg_border_radius', 16)),
        ],
        'text' => [
            'sendButtonText' => get_option('talktopc_text_send_btn_text', '→'),
            'sendButtonColor' => get_option('talktopc_text_send_btn_color', '#7C3AED'),
            'sendButtonHoverColor' => get_option('talktopc_text_send_btn_hover_color', '#6D28D9'),
            'inputPlaceholder' => get_option('talktopc_text_input_placeholder', 'Type your message...'),
            'inputFocusColor' => get_option('talktopc_text_input_focus_color', '#7C3AED'),
        ],
        'voice' => [
            'micButtonColor' => get_option('talktopc_voice_mic_color', '#7C3AED'),
            'micButtonActiveColor' => get_option('talktopc_voice_mic_active_color', '#EF4444'),
            'avatarBackgroundColor' => get_option('talktopc_voice_avatar_color', '#667eea'),
            'startCallButtonText' => get_option('talktopc_voice_start_btn_text', 'Start Call'),
            'startCallButtonColor' => get_option('talktopc_voice_start_btn_color', '#667eea'),
            'startCallButtonTextColor' => get_option('talktopc_voice_start_btn_text_color', '#FFFFFF'),
            'statusTitleColor' => get_option('talktopc_voice_status_title_color', '#1e293b'),
            'statusSubtitleColor' => get_option('talktopc_voice_status_subtitle_color', '#64748b'),
            'liveDotColor' => get_option('talktopc_voice_live_dot_color', '#10b981'),
            'liveTextColor' => get_option('talktopc_voice_live_text_color', '#10b981'),
            'waveformType' => 'waveform',
            'waveformIcon' => '🎤',
            'waveformImageUrl' => '',
            'avatarType' => 'icon',
            'avatarIcon' => '🤖',
            'avatarImageUrl' => '',
        ],
        'landing' => [
            'logo' => get_option('talktopc_landing_logo', '🤖'),
            'logoType' => 'icon',
            'logoIcon' => get_option('talktopc_landing_logo', '🤖'),
            'logoImageUrl' => '',
            'title' => get_option('talktopc_landing_title', 'Welcome to AI Assistant'),
            'titleColor' => get_option('talktopc_landing_title_color', '#1e293b'),
            'subtitle' => 'Choose how you\'d like to interact',
            'subtitleColor' => get_option('talktopc_landing_subtitle_color', '#64748b'),
            'voiceCardTitle' => get_option('talktopc_landing_voice_title', 'Voice Call'),
            'textCardTitle' => get_option('talktopc_landing_text_title', 'Text Chat'),
            'modeCardBackgroundColor' => get_option('talktopc_landing_card_bg_color', '#FFFFFF'),
        ],
        'direction' => get_option('talktopc_direction', 'ltr'),
    ];
}

function talktopc_render_customization2_page() {
    // Get current settings to populate the preview
    $current_settings = talktopc_get_all_widget_settings();
    
    // Enqueue required scripts and styles
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    $script_path = TALKTOPC_PLUGIN_DIR . 'includes/admin-scripts/customization2.js';
    $script_url = plugins_url('includes/admin-scripts/customization2.js', TALKTOPC_PLUGIN_DIR . 'talktopc.php');
    $script_version = file_exists($script_path) ? TALKTOPC_VERSION . '-' . filemtime($script_path) : TALKTOPC_VERSION;
    wp_enqueue_script('talktopc-customization2', $script_url, ['jquery', 'wp-color-picker'], $script_version, true);
    
    // Pass settings to JavaScript
    wp_localize_script('talktopc-customization2', 'talktopcWidgetSettings2', [
        'settings' => $current_settings,
        'agentId' => get_option('talktopc_agent_id', 'your_agent_id'),
        'appId' => get_option('talktopc_app_id', 'your_app_id'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('talktopc_customization2_nonce'),
        'saveAction' => 'talktopc_save_widget_customization2'
    ]);
    
    ?>
    <div class="wrap" style="max-width: 100%; margin: 0; padding: 0;">
        <div class="header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin: -20px -20px 20px -20px; position: relative;">
            <h1 style="font-size: 36px; font-weight: 700; margin-bottom: 8px; margin-top: 0;">🎨 Widget Live Customization</h1>
            <div style="margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 24px; flex-wrap: wrap;">
                <div id="modeInstructions" style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: 12px; font-size: 15px; font-weight: 500;">
                    <span id="instructionText">✏️ <strong>Single click</strong> to customize • <strong>Double click</strong> to interact</span>
                </div>
            </div>
        </div>

        <div class="container" style="max-width: 1800px; margin: 0 auto; padding: 40px 20px; display: grid; grid-template-columns: minmax(600px, 1fr) 420px; gap: 24px; align-items: start;">
            <div class="preview-section" style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); min-height: 600px; height: 100%; position: relative; overflow: visible; display: flex; flex-direction: column;">
                <div style="display: flex; align-items: center; margin-bottom: 20px; flex-shrink: 0; position: relative;">
                    <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Preview</h2>
                    <span style="font-size: 14px; color: #6b7280; font-weight: 500; position: absolute; left: 50%; transform: translateX(-50%);">✏️ Single click to customize • Double click to interact</span>
                </div>
                <div class="preview-area" id="previewArea" style="background: #f3f4f6; border-radius: 12px; height: 600px; position: relative; overflow: scroll !important; border: 2px dashed #d1d5db; padding: 60px; box-sizing: border-box; min-width: 500px; flex: 1; scrollbar-width: auto; -webkit-overflow-scrolling: touch;">
                    <div style="position: relative; min-height: 800px; width: 100%; padding-bottom: 200px;">
                        <div class="mock-widget" id="mockWidget" data-element-type="position" style="position: absolute; bottom: 60px; right: 60px; z-index: 1000; min-width: 360px; transform: translateZ(0); cursor: pointer; padding: 4px; border-radius: 8px; transition: background 0.2s;">
                            <div class="mock-widget-panel open" id="mockPanel" style="position: absolute; bottom: 90px; right: 0; width: 360px; height: 550px; background: white; border-radius: 24px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); display: flex; overflow: hidden; flex-direction: column; z-index: 1;">
                                <!-- Panel content will be dynamically generated -->
                            </div>
                            <button class="mock-widget-button" id="mockButton" style="position: relative; width: 60px; height: 60px; border-radius: 50%; background: #FFFFFF; border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 24px; transition: all 0.3s; z-index: 2;">🎤</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="customization-panel" style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); overflow-y: visible; overflow-x: hidden; display: flex; flex-direction: column; position: relative; min-height: fit-content;">
                <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb; flex-shrink: 0;">Customization</h2>
                
                <div class="info-box" id="instructionsBox" style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                    <p id="instructionsText" style="font-size: 14px; color: #1e40af; line-height: 1.6; margin: 0;">
                        <strong>How to use:</strong><br>
                        <span style="display: block; margin-top: 8px;">
                            <strong>Single Click:</strong> Select any widget element to customize its appearance. Changes apply instantly.<br>
                            <strong>Double Click:</strong> Interact with elements normally (open widget, switch views, etc.)
                        </span>
                        <span style="display: block; margin-top: 12px; font-size: 13px; color: #1e40af;">
                            💡 <strong>Tip:</strong> Single click any element to see customization options. Double click to use it normally.
                        </span>
                    </p>
                </div>

                <div id="customizationControls" style="flex: 0 0 auto; overflow: visible; margin-bottom: 16px;">
                    <!-- Controls will be dynamically generated based on selected element -->
                    <div class="customization-group" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <span style="width: 4px; height: 16px; background: #667eea; border-radius: 2px;"></span>
                            Select an Element
                        </h3>
                        <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                            Click on any widget element in the preview to start customizing.
                        </p>
                    </div>
                </div>

                <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 16px; margin-top: auto;">
                    <div class="button-group" style="display: flex; gap: 12px; margin-top: 24px; flex-shrink: 0;">
                        <button type="button" class="btn btn-primary" id="saveCustomizationBtn" style="flex: 1; padding: 12px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: #667eea; color: white;">Save Changes</button>
                        <button type="button" class="btn btn-secondary" id="resetBtn" style="flex: 1; padding: 12px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: #e5e7eb; color: #374151;">Reset to Defaults</button>
                    </div>

                    <div id="saveStatus" style="display: none; padding: 10px; border-radius: 4px; font-size: 13px;"></div>

                    <div class="code-output" style="background: #1f2937; color: #f9fafb; padding: 20px; border-radius: 8px; font-family: 'Monaco', 'Courier New', monospace; font-size: 13px; overflow-x: auto; overflow-y: visible; flex-shrink: 0; position: relative; border: 2px solid #374151; min-height: 400px; height: auto; max-height: none; margin-bottom: 0;">
                        <h3 style="color: #f9fafb; margin-bottom: 12px; font-size: 16px; font-weight: 700;">Configuration Code:</h3>
                        <pre id="configCode" style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 13px; line-height: 1.6;">// Select an element to see its configuration</pre>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <style>
        /* CSS styles matching SDK example - embedded directly */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 40px 20px;
            display: grid;
            grid-template-columns: minmax(600px, 1fr) 420px;
            gap: 24px;
            align-items: start;
        }

        .preview-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            min-height: 600px;
            height: 100%;
            position: relative;
            overflow: visible;
            display: flex;
            flex-direction: column;
        }

        .preview-section h2 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            flex-shrink: 0;
        }

        .preview-area {
            background: #f3f4f6;
            border-radius: 12px;
            height: 600px;
            position: relative;
            overflow: scroll !important;
            border: 2px dashed #d1d5db;
            padding: 60px;
            box-sizing: border-box;
            min-width: 500px;
            flex: 1;
            scrollbar-width: auto;
            -webkit-overflow-scrolling: touch;
        }

        .preview-area > div {
            min-height: 800px;
            position: relative;
        }

        .customization-panel {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow-y: visible;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            min-height: fit-content;
        }

        .customization-panel h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            flex-shrink: 0;
        }

        .customization-group {
            margin-bottom: 24px;
        }

        .customization-group h3 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .customization-group h3::before {
            content: '';
            width: 4px;
            height: 16px;
            background: #667eea;
            border-radius: 2px;
        }

        .control-item {
            margin-bottom: 16px;
        }

        .control-item label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .control-item input[type="text"],
        .control-item input[type="number"],
        .control-item select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .control-item input[type="text"]:focus,
        .control-item input[type="number"]:focus,
        .control-item select:focus {
            outline: none;
            border-color: #667eea;
        }

        .control-item input[type="color"] {
            width: 100%;
            height: 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
        }

        .control-item input[type="checkbox"] {
            margin-right: 8px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-shrink: 0;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .info-box p {
            font-size: 14px;
            color: #1e40af;
            line-height: 1.6;
        }

        .code-output {
            background: #1f2937;
            color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            overflow-y: visible;
            flex-shrink: 0;
            position: relative;
            border: 2px solid #374151;
            min-height: 400px;
            height: auto;
            max-height: none;
        }

        .code-output pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            font-size: 13px;
        }

        .code-output h3 {
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 700;
        }

        .element-highlight {
            outline: 2px solid #667eea;
            outline-offset: 2px;
            cursor: pointer;
            transition: outline-color 0.2s;
        }

        .element-highlight:hover {
            outline-color: #ef4444;
        }

        .mock-widget {
            position: absolute;
            bottom: 60px;
            right: 60px;
            z-index: 1000;
            min-width: 360px;
            transform: translateZ(0);
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .mock-widget:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .mock-widget.element-highlight {
            background: rgba(102, 126, 234, 0.1);
            outline: 2px solid #667eea;
            outline-offset: 4px;
        }

        .mock-widget-button {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #FFFFFF;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s;
            z-index: 2;
        }

        .mock-widget-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .mock-widget-panel {
            position: absolute;
            bottom: 90px;
            right: 0;
            width: 360px;
            height: 550px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            flex-direction: column;
            z-index: 1;
        }

        .mock-widget-panel.open {
            display: flex !important;
        }

        .mock-panel-header {
            background: #7C3AED;
            color: white;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
        }

        .mock-panel-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .mock-panel-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .mock-panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .mock-landing-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 40px 20px;
            text-align: center;
        }

        .mock-landing-logo {
            font-size: 64px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        
        .mock-landing-logo img {
            display: block;
            max-width: 64px;
            max-height: 64px;
            object-fit: contain;
        }

        .mock-landing-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .mock-landing-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
        }

        .mock-mode-cards {
            display: flex;
            gap: 16px;
            width: 100%;
        }

        .mock-mode-card {
            flex: 1;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .mock-mode-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .mock-mode-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7C3AED;
        }
        
        .mock-mode-icon svg {
            width: 32px;
            height: 32px;
        }

        .mock-mode-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .mock-text-interface {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .mock-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mock-message {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
        }

        .mock-message.user {
            align-self: flex-end;
            background: #E5E7EB;
            color: #1F2937;
        }

        .mock-message.agent {
            align-self: flex-start;
            background: #F3F4F6;
            color: #1F2937;
        }

        .mock-input-area {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
        }

        .mock-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
        }

        .mock-input:focus {
            border-color: #667eea;
        }

        .mock-send-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #7C3AED;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: background 0.2s;
        }

        .mock-send-button:hover {
            background: #6D28D9;
        }

        .mock-voice-interface {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow-y: auto;
        }

        .mock-voice-section {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .mock-voice-timer {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            color: #64748b;
        }

        .mock-timer-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
        }

        .mock-waveform {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            height: 60px;
            margin-bottom: 16px;
        }

        .mock-waveform-bar {
            width: 3px;
            background: #7C3AED;
            border-radius: 2px;
            animation: waveformAnimation 0.8s ease-in-out infinite;
        }

        .mock-waveform-bar:nth-child(1) { height: 12px; animation-delay: 0s; }
        .mock-waveform-bar:nth-child(2) { height: 20px; animation-delay: 0.05s; }
        .mock-waveform-bar:nth-child(3) { height: 28px; animation-delay: 0.1s; }
        .mock-waveform-bar:nth-child(4) { height: 36px; animation-delay: 0.15s; }
        .mock-waveform-bar:nth-child(5) { height: 44px; animation-delay: 0.2s; }
        .mock-waveform-bar:nth-child(6) { height: 50px; animation-delay: 0.25s; }
        .mock-waveform-bar:nth-child(7) { height: 54px; animation-delay: 0.3s; }
        .mock-waveform-bar:nth-child(8) { height: 56px; animation-delay: 0.35s; }
        .mock-waveform-bar:nth-child(9) { height: 54px; animation-delay: 0.4s; }
        .mock-waveform-bar:nth-child(10) { height: 50px; animation-delay: 0.45s; }
        .mock-waveform-bar:nth-child(11) { height: 44px; animation-delay: 0.5s; }
        .mock-waveform-bar:nth-child(12) { height: 36px; animation-delay: 0.55s; }
        .mock-waveform-bar:nth-child(13) { height: 28px; animation-delay: 0.6s; }
        .mock-waveform-bar:nth-child(14) { height: 20px; animation-delay: 0.65s; }
        .mock-waveform-bar:nth-child(15) { height: 12px; animation-delay: 0.7s; }

        @keyframes waveformAnimation {
            0%, 100% { 
                transform: scaleY(0.3);
                opacity: 0.7;
            }
            50% { 
                transform: scaleY(1);
                opacity: 1;
            }
        }

        .mock-voice-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #64748b;
        }

        .mock-status-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .mock-voice-controls {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .mock-voice-section-compact {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .mock-compact-row {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: nowrap;
        }

        .mock-compact-waveform {
            display: flex;
            align-items: center;
            gap: 2px;
            height: 32px;
            flex-shrink: 0;
        }

        .mock-compact-waveform .mock-waveform-bar {
            width: 2px;
            background: #7C3AED;
            border-radius: 1px;
        }

        .mock-compact-waveform .mock-waveform-bar:nth-child(1) { height: 8px; animation-delay: 0s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(2) { height: 12px; animation-delay: 0.05s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(3) { height: 16px; animation-delay: 0.1s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(4) { height: 20px; animation-delay: 0.15s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(5) { height: 24px; animation-delay: 0.2s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(6) { height: 28px; animation-delay: 0.25s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(7) { height: 30px; animation-delay: 0.3s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(8) { height: 32px; animation-delay: 0.35s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(9) { height: 30px; animation-delay: 0.4s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(10) { height: 28px; animation-delay: 0.45s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(11) { height: 24px; animation-delay: 0.5s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(12) { height: 20px; animation-delay: 0.55s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(13) { height: 16px; animation-delay: 0.6s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(14) { height: 12px; animation-delay: 0.65s; }
        .mock-compact-waveform .mock-waveform-bar:nth-child(15) { height: 8px; animation-delay: 0.7s; }

        .mock-compact-timer {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #64748b;
            flex-shrink: 0;
        }

        .mock-compact-timer .mock-timer-dot {
            width: 6px;
            height: 6px;
        }

        .mock-compact-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #10b981;
            flex-shrink: 0;
        }

        .mock-compact-status .mock-status-dot {
            width: 6px;
            height: 6px;
        }

        .mock-compact-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .mock-compact-controls .mock-control-btn {
            width: 40px;
            height: 40px;
        }

        .mock-control-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .mock-control-btn.secondary {
            background: white;
            color: #374151;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .mock-control-btn.danger {
            background: #ef4444;
            color: white;
            width: 56px;
            height: 56px;
        }

        .mock-control-btn svg {
            width: 20px;
            height: 20px;
        }

        .mock-conversation-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding: 16px;
        }

        .mock-conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mock-conversation-toggle {
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            color: #6b7280;
            font-size: 12px;
        }

        .mock-live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #10b981;
        }

        .mock-live-dot {
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }

        .mock-live-transcript-collapsed {
            padding: 0;
        }

        .mock-live-text-collapsed {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            min-height: 44px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .mock-conversation-history {
            display: none;
            flex-direction: column;
            gap: 16px;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .mock-conversation-history.expanded {
            display: flex !important;
        }

        .mock-history-message {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .mock-history-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
            color: white;
            overflow: hidden;
        }

        .mock-history-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .mock-history-bubble {
            background: #f3f4f6;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            color: #1f2937;
            line-height: 1.5;
            max-width: calc(100% - 44px);
        }

        .mock-live-message-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-top: 8px;
        }

        .mock-live-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            margin-right: 8px;
            text-transform: uppercase;
        }

        .mock-voice-input-area {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .mock-voice-input-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .mock-voice-text-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
        }

        .mock-voice-text-input:focus {
            border-color: #7C3AED;
        }

        .mock-voice-send-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #7C3AED;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .mock-voice-send-btn:hover {
            background: #6D28D9;
        }

        .mock-powered-by {
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mock-powered-by strong {
            color: #7C3AED;
        }

        .preview-area::-webkit-scrollbar {
            width: 14px;
            height: 14px;
        }

        .preview-area::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 7px;
            margin: 4px;
        }

        .preview-area::-webkit-scrollbar-thumb {
            background: #6b7280;
            border-radius: 7px;
            border: 2px solid #e5e7eb;
        }

        .preview-area::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }

        .code-output::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .code-output::-webkit-scrollbar-track {
            background: #374151;
            border-radius: 4px;
        }

        .code-output::-webkit-scrollbar-thumb {
            background: #6b7280;
            border-radius: 4px;
        }

        .code-output::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        @media (max-width: 1400px) {
            .container {
                grid-template-columns: 1fr 380px;
            }
        }

        @media (max-width: 1200px) {
            .container {
                grid-template-columns: 1fr;
            }
        }

        /* WordPress admin overrides */
        .wrap {
            max-width: 100% !important;
        }
    </style>
    <?php
}
