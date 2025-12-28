<?php
/**
 * Plugin Name: TalkToPC Voice Widget
 * Description: Add AI voice conversations to your WordPress site. Let visitors talk to your AI agent with natural voice interactions.
 * Version: 1.0.0
 * Author: TalkToPC
 * Author URI: https://talktopc.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ttp-voice-widget
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

// Add settings page
add_action('admin_menu', function() {
    add_options_page(
        'TalkToPC Voice Widget',
        'TalkToPC Widget',
        'manage_options',
        'ttp-voice-widget',
        'ttp_settings_page'
    );
});

// Sanitize callbacks
function ttp_sanitize_text($input) {
    return sanitize_text_field($input);
}

function ttp_sanitize_color($input) {
    if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $input)) {
        return $input;
    }
    return '';
}

function ttp_sanitize_number($input) {
    return absint($input);
}

// Register all settings
add_action('admin_init', function() {
    // Required
    register_setting('ttp_settings', 'ttp_app_id', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => ''));
    register_setting('ttp_settings', 'ttp_agent_id', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => ''));
    
    // Button
    register_setting('ttp_settings', 'ttp_button_size', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'medium'));
    register_setting('ttp_settings', 'ttp_button_shape', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'circle'));
    register_setting('ttp_settings', 'ttp_button_bg_color', array('sanitize_callback' => 'ttp_sanitize_color', 'default' => '#FFFFFF'));
    register_setting('ttp_settings', 'ttp_button_hover_color', array('sanitize_callback' => 'ttp_sanitize_color', 'default' => '#F5F5F5'));
    
    // Icon
    register_setting('ttp_settings', 'ttp_icon_type', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'custom'));
    register_setting('ttp_settings', 'ttp_icon_custom_image', array('sanitize_callback' => 'esc_url_raw', 'default' => ''));
    register_setting('ttp_settings', 'ttp_icon_emoji', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => '🎤'));
    register_setting('ttp_settings', 'ttp_icon_text', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'AI'));
    
    // Header
    register_setting('ttp_settings', 'ttp_header_title', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => ''));
    register_setting('ttp_settings', 'ttp_header_bg_color', array('sanitize_callback' => 'ttp_sanitize_color', 'default' => '#7C3AED'));
    register_setting('ttp_settings', 'ttp_header_text_color', array('sanitize_callback' => 'ttp_sanitize_color', 'default' => '#FFFFFF'));
    
    // Panel
    register_setting('ttp_settings', 'ttp_panel_width', array('sanitize_callback' => 'ttp_sanitize_number', 'default' => 350));
    register_setting('ttp_settings', 'ttp_panel_height', array('sanitize_callback' => 'ttp_sanitize_number', 'default' => 500));
    
    // Position
    register_setting('ttp_settings', 'ttp_position', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'bottom-right'));
    
    // Behavior
    register_setting('ttp_settings', 'ttp_mode', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'unified'));
    register_setting('ttp_settings', 'ttp_welcome_message', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => ''));
    register_setting('ttp_settings', 'ttp_language', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'en'));
    register_setting('ttp_settings', 'ttp_direction', array('sanitize_callback' => 'ttp_sanitize_text', 'default' => 'ltr'));
});

// Settings page HTML
function ttp_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p><?php esc_html_e('Configure your TalkToPC Voice Widget.', 'ttp-voice-widget'); ?> 
           <a href="https://talktopc.com" target="_blank"><?php esc_html_e('Get your credentials', 'ttp-voice-widget'); ?></a>
        </p>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <!-- Required Settings -->
            <h2 class="title"><?php esc_html_e('Required Settings', 'ttp-voice-widget'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ttp_app_id"><?php esc_html_e('App ID', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="text" id="ttp_app_id" name="ttp_app_id" value="<?php echo esc_attr(get_option('ttp_app_id')); ?>" class="regular-text" placeholder="app_xxxxxxxx" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_agent_id"><?php esc_html_e('Agent ID', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="text" id="ttp_agent_id" name="ttp_agent_id" value="<?php echo esc_attr(get_option('ttp_agent_id')); ?>" class="regular-text" placeholder="agent_xxxxxxxx" />
                    </td>
                </tr>
            </table>
            
            <!-- Behavior Settings -->
            <h2 class="title"><?php esc_html_e('Behavior', 'ttp-voice-widget'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ttp_mode"><?php esc_html_e('Widget Mode', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_mode" name="ttp_mode">
                            <option value="unified" <?php selected(get_option('ttp_mode', 'unified'), 'unified'); ?>><?php esc_html_e('Unified (Voice & Text)', 'ttp-voice-widget'); ?></option>
                            <option value="voice-only" <?php selected(get_option('ttp_mode'), 'voice-only'); ?>><?php esc_html_e('Voice Only', 'ttp-voice-widget'); ?></option>
                            <option value="text-only" <?php selected(get_option('ttp_mode'), 'text-only'); ?>><?php esc_html_e('Text Only', 'ttp-voice-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_language"><?php esc_html_e('Language', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_language" name="ttp_language">
                            <option value="en" <?php selected(get_option('ttp_language', 'en'), 'en'); ?>>English</option>
                            <option value="he" <?php selected(get_option('ttp_language'), 'he'); ?>>עברית (Hebrew)</option>
                            <option value="es" <?php selected(get_option('ttp_language'), 'es'); ?>>Español</option>
                            <option value="fr" <?php selected(get_option('ttp_language'), 'fr'); ?>>Français</option>
                            <option value="de" <?php selected(get_option('ttp_language'), 'de'); ?>>Deutsch</option>
                            <option value="ar" <?php selected(get_option('ttp_language'), 'ar'); ?>>العربية (Arabic)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_direction"><?php esc_html_e('Text Direction', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_direction" name="ttp_direction">
                            <option value="ltr" <?php selected(get_option('ttp_direction', 'ltr'), 'ltr'); ?>><?php esc_html_e('Left to Right', 'ttp-voice-widget'); ?></option>
                            <option value="rtl" <?php selected(get_option('ttp_direction'), 'rtl'); ?>><?php esc_html_e('Right to Left', 'ttp-voice-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_welcome_message"><?php esc_html_e('Welcome Message', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="text" id="ttp_welcome_message" name="ttp_welcome_message" value="<?php echo esc_attr(get_option('ttp_welcome_message')); ?>" class="large-text" placeholder="Hello! How can I help you today?" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_position"><?php esc_html_e('Position', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_position" name="ttp_position">
                            <option value="bottom-right" <?php selected(get_option('ttp_position', 'bottom-right'), 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'ttp-voice-widget'); ?></option>
                            <option value="bottom-left" <?php selected(get_option('ttp_position'), 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'ttp-voice-widget'); ?></option>
                            <option value="top-right" <?php selected(get_option('ttp_position'), 'top-right'); ?>><?php esc_html_e('Top Right', 'ttp-voice-widget'); ?></option>
                            <option value="top-left" <?php selected(get_option('ttp_position'), 'top-left'); ?>><?php esc_html_e('Top Left', 'ttp-voice-widget'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <!-- Button Settings -->
            <h2 class="title"><?php esc_html_e('Floating Button', 'ttp-voice-widget'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ttp_button_size"><?php esc_html_e('Size', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_button_size" name="ttp_button_size">
                            <option value="small" <?php selected(get_option('ttp_button_size'), 'small'); ?>><?php esc_html_e('Small', 'ttp-voice-widget'); ?></option>
                            <option value="medium" <?php selected(get_option('ttp_button_size', 'medium'), 'medium'); ?>><?php esc_html_e('Medium', 'ttp-voice-widget'); ?></option>
                            <option value="large" <?php selected(get_option('ttp_button_size'), 'large'); ?>><?php esc_html_e('Large', 'ttp-voice-widget'); ?></option>
                            <option value="xl" <?php selected(get_option('ttp_button_size'), 'xl'); ?>><?php esc_html_e('Extra Large', 'ttp-voice-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_button_shape"><?php esc_html_e('Shape', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_button_shape" name="ttp_button_shape">
                            <option value="circle" <?php selected(get_option('ttp_button_shape', 'circle'), 'circle'); ?>><?php esc_html_e('Circle', 'ttp-voice-widget'); ?></option>
                            <option value="square" <?php selected(get_option('ttp_button_shape'), 'square'); ?>><?php esc_html_e('Square', 'ttp-voice-widget'); ?></option>
                            <option value="rounded" <?php selected(get_option('ttp_button_shape'), 'rounded'); ?>><?php esc_html_e('Rounded', 'ttp-voice-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_button_bg_color"><?php esc_html_e('Background Color', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="color" id="ttp_button_bg_color" name="ttp_button_bg_color" value="<?php echo esc_attr(get_option('ttp_button_bg_color', '#FFFFFF')); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_button_hover_color"><?php esc_html_e('Hover Color', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="color" id="ttp_button_hover_color" name="ttp_button_hover_color" value="<?php echo esc_attr(get_option('ttp_button_hover_color', '#F5F5F5')); ?>" />
                    </td>
                </tr>
            </table>
            
            <!-- Icon Settings -->
            <h2 class="title"><?php esc_html_e('Icon', 'ttp-voice-widget'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ttp_icon_type"><?php esc_html_e('Icon Type', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <select id="ttp_icon_type" name="ttp_icon_type">
                            <option value="custom" <?php selected(get_option('ttp_icon_type', 'custom'), 'custom'); ?>><?php esc_html_e('Custom Image', 'ttp-voice-widget'); ?></option>
                            <option value="microphone" <?php selected(get_option('ttp_icon_type'), 'microphone'); ?>><?php esc_html_e('Microphone', 'ttp-voice-widget'); ?></option>
                            <option value="emoji" <?php selected(get_option('ttp_icon_type'), 'emoji'); ?>><?php esc_html_e('Emoji', 'ttp-voice-widget'); ?></option>
                            <option value="text" <?php selected(get_option('ttp_icon_type'), 'text'); ?>><?php esc_html_e('Text', 'ttp-voice-widget'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_icon_custom_image"><?php esc_html_e('Custom Image URL', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="url" id="ttp_icon_custom_image" name="ttp_icon_custom_image" value="<?php echo esc_attr(get_option('ttp_icon_custom_image')); ?>" class="regular-text" placeholder="https://example.com/icon.png" />
                        <p class="description"><?php esc_html_e('Used when Icon Type is "Custom Image"', 'ttp-voice-widget'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_icon_emoji"><?php esc_html_e('Emoji', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="text" id="ttp_icon_emoji" name="ttp_icon_emoji" value="<?php echo esc_attr(get_option('ttp_icon_emoji', '🎤')); ?>" class="small-text" />
                        <p class="description"><?php esc_html_e('Used when Icon Type is "Emoji"', 'ttp-voice-widget'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_icon_text"><?php esc_html_e('Text', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="text" id="ttp_icon_text" name="ttp_icon_text" value="<?php echo esc_attr(get_option('ttp_icon_text', 'AI')); ?>" class="small-text" maxlength="4" />
                        <p class="description"><?php esc_html_e('Used when Icon Type is "Text" (max 4 characters)', 'ttp-voice-widget'); ?></p>
                    </td>
                </tr>
            </table>
            
            <!-- Header Settings -->
            <h2 class="title"><?php esc_html_e('Panel Header', 'ttp-voice-widget'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ttp_header_title"><?php esc_html_e('Title', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="text" id="ttp_header_title" name="ttp_header_title" value="<?php echo esc_attr(get_option('ttp_header_title')); ?>" class="regular-text" placeholder="Chat Assistant" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_header_bg_color"><?php esc_html_e('Background Color', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="color" id="ttp_header_bg_color" name="ttp_header_bg_color" value="<?php echo esc_attr(get_option('ttp_header_bg_color', '#7C3AED')); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_header_text_color"><?php esc_html_e('Text Color', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="color" id="ttp_header_text_color" name="ttp_header_text_color" value="<?php echo esc_attr(get_option('ttp_header_text_color', '#FFFFFF')); ?>" />
                    </td>
                </tr>
            </table>
            
            <!-- Panel Settings -->
            <h2 class="title"><?php esc_html_e('Panel Size', 'ttp-voice-widget'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ttp_panel_width"><?php esc_html_e('Width (px)', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="number" id="ttp_panel_width" name="ttp_panel_width" value="<?php echo esc_attr(get_option('ttp_panel_width', 350)); ?>" class="small-text" min="250" max="600" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ttp_panel_height"><?php esc_html_e('Height (px)', 'ttp-voice-widget'); ?></label></th>
                    <td>
                        <input type="number" id="ttp_panel_height" name="ttp_panel_height" value="<?php echo esc_attr(get_option('ttp_panel_height', 500)); ?>" class="small-text" min="300" max="800" />
                    </td>
                </tr>
            </table>
            
            <?php submit_button(esc_html__('Save Settings', 'ttp-voice-widget')); ?>
        </form>
        
        <?php if (get_option('ttp_app_id') && get_option('ttp_agent_id')): ?>
        <div class="notice notice-success" style="padding: 10px;">
            <strong><?php esc_html_e('Widget is active!', 'ttp-voice-widget'); ?></strong> <?php esc_html_e('Visit your site to see the voice widget.', 'ttp-voice-widget'); ?>
        </div>
        <?php else: ?>
        <div class="notice notice-warning" style="padding: 10px;">
            <strong><?php esc_html_e('Widget not configured.', 'ttp-voice-widget'); ?></strong> <?php esc_html_e('Please enter your App ID and Agent ID above.', 'ttp-voice-widget'); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// Enqueue and initialize widget
add_action('wp_enqueue_scripts', function() {
    $app_id = get_option('ttp_app_id');
    $agent_id = get_option('ttp_agent_id');
    
    if (empty($app_id) || empty($agent_id)) return;
    
    wp_enqueue_script('ttp-agent-widget', 'https://cdn.talktopc.com/agent-widget.js', array(), '1.0.0', true);
    
    // Build config object matching SDK structure
    $config = array(
        'agentId' => $agent_id,
        'appId' => $app_id,
    );
    
    // Position (shorthand)
    $position = get_option('ttp_position', 'bottom-right');
    if (!empty($position)) {
        $config['position'] = $position;
    }
    
    // Language & Direction
    $language = get_option('ttp_language', 'en');
    if (!empty($language)) {
        $config['language'] = $language;
    }
    
    $direction = get_option('ttp_direction', 'ltr');
    if (!empty($direction)) {
        $config['direction'] = $direction;
    }
    
    // Button settings
    $button = array();
    $button_size = get_option('ttp_button_size', 'medium');
    if (!empty($button_size)) $button['size'] = $button_size;
    
    $button_shape = get_option('ttp_button_shape', 'circle');
    if (!empty($button_shape)) $button['shape'] = $button_shape;
    
    $button_bg = get_option('ttp_button_bg_color');
    if (!empty($button_bg)) $button['backgroundColor'] = $button_bg;
    
    $button_hover = get_option('ttp_button_hover_color');
    if (!empty($button_hover)) $button['hoverColor'] = $button_hover;
    
    if (!empty($button)) {
        $config['button'] = $button;
    }
    
    // Icon settings
    $icon = array();
    $icon_type = get_option('ttp_icon_type', 'custom');
    if (!empty($icon_type)) $icon['type'] = $icon_type;
    
    $icon_custom = get_option('ttp_icon_custom_image');
    if (!empty($icon_custom)) $icon['customImage'] = $icon_custom;
    
    $icon_emoji = get_option('ttp_icon_emoji');
    if (!empty($icon_emoji)) $icon['emoji'] = $icon_emoji;
    
    $icon_text = get_option('ttp_icon_text');
    if (!empty($icon_text)) $icon['text'] = $icon_text;
    
    if (!empty($icon)) {
        $config['icon'] = $icon;
    }
    
    // Header settings
    $header = array();
    $header_title = get_option('ttp_header_title');
    if (!empty($header_title)) $header['title'] = $header_title;
    
    $header_bg = get_option('ttp_header_bg_color');
    if (!empty($header_bg)) $header['backgroundColor'] = $header_bg;
    
    $header_text = get_option('ttp_header_text_color');
    if (!empty($header_text)) $header['textColor'] = $header_text;
    
    if (!empty($header)) {
        $config['header'] = $header;
    }
    
    // Panel settings
    $panel = array();
    $panel_width = get_option('ttp_panel_width');
    if (!empty($panel_width)) $panel['width'] = intval($panel_width);
    
    $panel_height = get_option('ttp_panel_height');
    if (!empty($panel_height)) $panel['height'] = intval($panel_height);
    
    if (!empty($panel)) {
        $config['panel'] = $panel;
    }
    
    // Behavior settings
    $behavior = array();
    $mode = get_option('ttp_mode', 'unified');
    if (!empty($mode)) $behavior['mode'] = $mode;
    
    $welcome = get_option('ttp_welcome_message');
    if (!empty($welcome)) {
        $behavior['showWelcomeMessage'] = true;
        $behavior['welcomeMessage'] = $welcome;
    }
    
    if (!empty($behavior)) {
        $config['behavior'] = $behavior;
    }
    
    // Initialize widget
    $inline_script = sprintf(
        '(function() {
            function initWidget() {
                if (typeof TTPAgentSDK !== "undefined" && TTPAgentSDK.TTPChatWidget) {
                    new TTPAgentSDK.TTPChatWidget(%s);
                } else {
                    setTimeout(initWidget, 100);
                }
            }
            initWidget();
        })();',
        wp_json_encode($config)
    );
    
    wp_add_inline_script('ttp-agent-widget', $inline_script);
});

// Add settings link on plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=ttp-voice-widget')) . '">' . esc_html__('Settings', 'ttp-voice-widget') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
