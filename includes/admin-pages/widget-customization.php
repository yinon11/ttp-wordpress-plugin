<?php
/**
 * Customization Page
 * 
 * Live customization interface for widget appearance
 */

if (!defined('ABSPATH')) exit;

function talktopc_render_widget_customization_page() {
    // Get current settings to populate the preview
    $current_settings = talktopc_get_all_widget_settings();
    
    // Enqueue required scripts and styles
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    $script_url = plugins_url('includes/admin-scripts/widget-customization.js', TALKTOPC_PLUGIN_DIR . 'talktopc.php');
    wp_enqueue_script('talktopc-widget-customization', $script_url, ['jquery', 'wp-color-picker'], TALKTOPC_VERSION, true);
    
    // Pass settings to JavaScript
    wp_localize_script('talktopc-widget-customization', 'talktopcWidgetSettings', [
        'settings' => $current_settings,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('talktopc_customization_nonce'),
        'saveAction' => 'talktopc_save_widget_customization'
    ]);
    
    ?>
    <div class="wrap talktopc-admin-wrap talktopc-customization-wrap">
        <div class="wp-header">
            <h1>Customization</h1>
        </div>
        
        <div class="talktopc-customization-container">
            <div class="talktopc-preview-section">
                <h2>Preview</h2>
                <div class="talktopc-preview-area" id="previewArea">
                    <div style="position: relative; min-height: 800px; width: 100%; padding-bottom: 200px;">
                        <div class="mock-widget" id="mockWidget" data-element-type="position" style="position: absolute; bottom: 60px; right: 60px;">
                            <div class="mock-widget-panel" id="mockPanel">
                                <!-- Panel content will be dynamically generated -->
                            </div>
                            <button class="mock-widget-button" id="mockButton">🎤</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="talktopc-customization-panel">
                <h2>Customization</h2>
                
                <!-- Deployment Status Indicator (will be populated by JavaScript) -->
                <div id="deploymentStatusIndicator" style="display: none;"></div>
                
                <div class="talktopc-info-box" id="instructionsBox" style="background: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
                    <h3 style="font-size: 24px; font-weight: 700; color: #0c4a6e; margin: 0 0 16px 0; text-align: center;">
                        How to Use
                    </h3>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="font-size: 20px; font-weight: 600; color: #0369a1; margin-bottom: 8px;">
                            👆 Single Click = Customize
                        </div>
                        <div style="font-size: 20px; font-weight: 600; color: #0369a1;">
                            👆👆 Double Click = Interact Normally
                        </div>
                    </div>
                    <div style="font-size: 14px; color: #475569; line-height: 1.6;">
                        <div style="margin-bottom: 8px;">
                            <strong>Single Click:</strong> Select any widget element to customize its appearance. Changes apply instantly.
                        </div>
                        <div>
                            <strong>Double Click:</strong> Interact with elements normally (open widget, switch views, etc.)
                        </div>
                    </div>
                </div>
                
                <form method="post" action="options.php" id="customizationForm">
                    <?php settings_fields('talktopc_settings'); ?>
                    
                    <div id="customizationControls" style="flex: 0 0 auto; overflow: visible; margin-bottom: 16px;">
                        <!-- Controls will be dynamically generated based on selected element -->
                        <div class="customization-group">
                            <h3>Select an Element</h3>
                            <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                                Click on any widget element in the preview to start customizing.
                            </p>
                        </div>
                    </div>
                    
                    <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 16px; margin-top: auto;">
                        <div class="button-group" style="flex-shrink: 0;">
                            <button type="button" class="button button-primary" id="saveCustomizationBtn">Save Changes</button>
                            <button type="button" class="button button-secondary" id="resetBtn">Reset to Defaults</button>
                            <button type="button" class="button button-secondary" id="togglePanelBtn">Toggle Panel</button>
                        </div>
                        
                        <div id="saveStatus" class="save-status" style="display: none;"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        .talktopc-customization-wrap {
            max-width: 1800px;
        }
        
        .talktopc-customization-container {
            display: grid;
            grid-template-columns: minmax(600px, 1fr) 420px;
            gap: 24px;
            align-items: start;
        }
        
        .talktopc-preview-section {
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
        
        .talktopc-preview-section h2 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            flex-shrink: 0;
        }
        
        .talktopc-preview-area {
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
        
        .talktopc-customization-panel {
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
        
        .talktopc-customization-panel h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            flex-shrink: 0;
        }
        
        .talktopc-info-box {
            background: #f0f6fc;
            border: 1px solid #c3e6fb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
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
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .save-status {
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .save-status.success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .save-status.error {
            background: #fee2e2;
            color: #991b1b;
        }
        
        @media (max-width: 1400px) {
            .talktopc-customization-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
}

/**
 * Get all widget settings for JavaScript
 */
function talktopc_get_all_widget_settings() {
    return [
        'button' => [
            'size' => get_option('talktopc_button_size', 'medium'),
            'shape' => get_option('talktopc_button_shape', 'circle'),
            'backgroundColor' => get_option('talktopc_button_bg_color', '#FFFFFF'),
            'hoverColor' => get_option('talktopc_button_hover_color', '#D3D3D3'),
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
            'width' => intval(get_option('talktopc_panel_width', 350)),
            'height' => intval(get_option('talktopc_panel_height', 550)),
            'borderRadius' => intval(get_option('talktopc_panel_border_radius', 12)),
            'backgroundColor' => get_option('talktopc_panel_bg_color', '#FFFFFF'),
            'border' => get_option('talktopc_panel_border', '1px solid #E5E7EB'),
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
        ],
        'landing' => [
            'logo' => get_option('talktopc_landing_logo', '🤖'),
            'logoType' => 'icon', // Default to icon
            'logoIcon' => get_option('talktopc_landing_logo', '🤖'),
            'logoImageUrl' => '',
            'title' => get_option('talktopc_landing_title', 'Welcome to AI Assistant'),
            'titleColor' => get_option('talktopc_landing_title_color', '#1e293b'),
            'subtitle' => 'Choose how you\'d like to interact',
            'subtitleColor' => get_option('talktopc_landing_subtitle_color', '#64748b'),
            'voiceCardTitle' => get_option('talktopc_landing_voice_title', 'Voice Call'),
            'voiceCardDesc' => get_option('talktopc_landing_voice_desc', 'Start a voice conversation'),
            'textCardTitle' => get_option('talktopc_landing_text_title', 'Text Chat'),
            'textCardDesc' => get_option('talktopc_landing_text_desc', 'Chat via text messages'),
            'modeCardBackgroundColor' => get_option('talktopc_landing_card_bg_color', '#FFFFFF'),
        ],
        'voice' => [
            'micButtonColor' => get_option('talktopc_voice_mic_color', '#7C3AED'),
            'micButtonActiveColor' => get_option('talktopc_voice_mic_active_color', '#EF4444'),
            'avatarBackgroundColor' => get_option('talktopc_voice_avatar_color', '#667eea'),
            'startCallButtonText' => get_option('talktopc_voice_start_btn_text', 'Start Call'),
            'startCallButtonColor' => get_option('talktopc_voice_start_btn_color', '#667eea'),
            'startCallButtonTextColor' => get_option('talktopc_voice_start_btn_text_color', '#FFFFFF'),
            'statusTitleColor' => get_option('talktopc_voice_status_title_color', '#1e293b'),
            'waveformType' => 'waveform',
            'waveformIcon' => '🎤',
            'waveformImageUrl' => '',
            'avatarType' => 'icon',
            'avatarIcon' => '🤖',
            'avatarImageUrl' => '',
        ],
        'position' => [
            'vertical' => strpos(get_option('talktopc_position', 'bottom-right'), 'bottom') !== false ? 'bottom' : 'top',
            'horizontal' => strpos(get_option('talktopc_position', 'bottom-right'), 'right') !== false ? 'right' : 'left',
        ],
        'direction' => get_option('talktopc_direction', 'ltr'),
    ];
}
