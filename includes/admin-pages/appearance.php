<?php
/**
 * Appearance Page
 * 
 * FIX #2: Include hidden fields to preserve agent_id and agent_name when saving
 */

if (!defined('ABSPATH')) exit;


function ttp_render_appearance_page() {
    ttp_render_admin_styles();
    
    // FIX #2: Get current agent values to preserve them
    $current_agent_id = get_option('ttp_agent_id', '');
    $current_agent_name = get_option('ttp_agent_name', '');
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Appearance</h1>
        </div>
        
        <form method="post" action="options.php">
            <?php settings_fields('ttp_settings'); ?>
            
            <!-- FIX #2: Hidden fields to preserve agent selection -->
            <input type="hidden" name="ttp_agent_id" value="<?php echo esc_attr($current_agent_id); ?>">
            <input type="hidden" name="ttp_agent_name" value="<?php echo esc_attr($current_agent_name); ?>">
            
            <div class="card">
                <h2><span class="icon">🔘</span> Floating Button</h2>
                <div class="form-row">
                    <label>Position</label>
                    <div class="field">
                        <select name="ttp_position">
                            <option value="bottom-right" <?php selected(get_option('ttp_position', 'bottom-right'), 'bottom-right'); ?>>Bottom Right</option>
                            <option value="bottom-left" <?php selected(get_option('ttp_position'), 'bottom-left'); ?>>Bottom Left</option>
                            <option value="top-right" <?php selected(get_option('ttp_position'), 'top-right'); ?>>Top Right</option>
                            <option value="top-left" <?php selected(get_option('ttp_position'), 'top-left'); ?>>Top Left</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label>Size</label>
                    <div class="field">
                        <select name="ttp_button_size">
                            <option value="small" <?php selected(get_option('ttp_button_size', 'medium'), 'small'); ?>>Small</option>
                            <option value="medium" <?php selected(get_option('ttp_button_size', 'medium'), 'medium'); ?>>Medium</option>
                            <option value="large" <?php selected(get_option('ttp_button_size', 'medium'), 'large'); ?>>Large</option>
                            <option value="extra-large" <?php selected(get_option('ttp_button_size', 'medium'), 'extra-large'); ?>>Extra Large</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label>Shape</label>
                    <div class="field">
                        <select name="ttp_button_shape">
                            <option value="circle" <?php selected(get_option('ttp_button_shape', 'circle'), 'circle'); ?>>Circle</option>
                            <option value="rounded" <?php selected(get_option('ttp_button_shape', 'circle'), 'rounded'); ?>>Rounded</option>
                            <option value="square" <?php selected(get_option('ttp_button_shape', 'circle'), 'square'); ?>>Square</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label>Background</label>
                    <div class="field">
                        <div class="color-picker-row">
                            <div class="color-preview ttp-color-picker-preview" style="background: <?php echo esc_attr(get_option('ttp_button_bg_color', '#7C3AED')); ?>;"></div>
                            <input type="text" name="ttp_button_bg_color" class="ttp-color-picker" value="<?php echo esc_attr(get_option('ttp_button_bg_color', '#7C3AED')); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <?php ttp_render_icon_settings(); ?>
            <?php ttp_render_panel_settings(); ?>
            <?php ttp_render_header_settings(); ?>
            <?php ttp_render_footer_settings(); ?>
            <?php ttp_render_landing_settings(); ?>
            
            <div class="save-area">
                <button type="submit" class="button button-primary">Save Appearance</button>
            </div>
        </form>
    </div>
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    ttp_render_common_scripts();
}