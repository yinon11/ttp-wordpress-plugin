<?php
/**
 * Admin Settings Page Router
 * 
 * Main entry point for admin pages. Routes to appropriate page renderers.
 */

if (!defined('ABSPATH')) exit;

// Include all required files
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-styles.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-helpers.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-settings-sections.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-pages/dashboard.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-pages/page-rules.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-pages/widget-customization.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-scripts/common.js.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-scripts/dashboard.js.php';
require_once TALKTOPC_PLUGIN_DIR . 'includes/admin-scripts/page-rules.js.php';

/**
 * Remove third-party admin notices from TalkToPC settings page
 * This prevents other plugins (like MonsterInsights) from showing their
 * review nags and promotional notices inside our settings panel.
 */
add_action('admin_head', function() {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'toplevel_page_talktopc' || 
                    strpos($screen->id, 'talktopc') !== false)) {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        // Re-add settings errors so our own notices still work
        add_action('admin_notices', 'settings_errors');
    }
}, 1);

/**
 * Main settings page function (called from admin menu) - routes to dashboard
 */
function talktopc_settings_page() {
    talktopc_render_dashboard_page();
}
