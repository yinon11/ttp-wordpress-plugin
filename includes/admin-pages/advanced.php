<?php
/**
 * Advanced Page
 * 
 * FIX #2: Include hidden fields to preserve agent_id and agent_name when saving
 * FIX #4: Add note about custom CSS usage
 */

if (!defined('ABSPATH')) exit;


function ttp_render_advanced_page() {
    ttp_render_admin_styles();
    
    // FIX #2: Get current agent values to preserve them
    $current_agent_id = get_option('ttp_agent_id', '');
    $current_agent_name = get_option('ttp_agent_name', '');
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Advanced Settings</h1>
        </div>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <!-- FIX #2: Hidden fields to preserve agent selection -->
            <input type="hidden" name="ttp_agent_id" value="<?php echo esc_attr($current_agent_id); ?>">
            <input type="hidden" name="ttp_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
            
            <?php ttp_render_animation_settings(); ?>
            <?php ttp_render_accessibility_settings(); ?>
            <?php ttp_render_tooltip_settings(); ?>
            
            <!-- Custom CSS Section -->
            <div class="card">
                <h2><span class="icon">🎨</span> Custom CSS</h2>
                <div class="form-row">
                    <label for="ttp_custom_css">Custom Styles</label>
                    <div class="field">
                        <textarea name="ttp_custom_css" id="ttp_custom_css" rows="10" style="font-family: monospace; width: 100%;"><?php echo esc_textarea(get_option('ttp_custom_css')); ?></textarea>
                        <p class="description">
                            Add custom CSS to style the widget. Use <code>.ttp-widget</code> as the root selector.<br>
                            <strong>Example:</strong> <code>.ttp-widget .ttp-button { box-shadow: none; }</code>
                        </p>
                        <!-- FIX #4: Help text explaining how custom CSS works -->
                        <div class="ttp-css-help" style="margin-top: 12px; padding: 12px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 2px;">
                            <strong>💡 How Custom CSS Works:</strong>
                            <ul style="margin: 8px 0 0 20px; list-style: disc;">
                                <li>Your CSS is passed to the widget as <code>customStyles</code></li>
                                <li>The widget injects it into a <code>&lt;style&gt;</code> tag inside its shadow DOM</li>
                                <li>Use browser DevTools to inspect the widget and find correct selectors</li>
                                <li>If styles aren't applying, check if the widget uses Shadow DOM (you may need <code>::part()</code> selectors)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="save-area">
                <button type="submit" class="button button-primary">Save Advanced Settings</button>
            </div>
        </form>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    ttp_render_common_scripts();
}