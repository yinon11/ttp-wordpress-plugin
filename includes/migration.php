<?php
/**
 * Migration Script
 * 
 * Migrates data from old database structure to current WordPress Options API
 * 
 * Usage:
 * 1. Place this file in includes/ directory
 * 2. Access: /wp-admin/admin.php?page=talktopc&migrate=1
 * 3. Or run via WP-CLI: wp eval-file includes/migration.php
 */

if (!defined('ABSPATH')) exit;

/**
 * Check if old custom table exists
 */
function talktopc_check_old_table() {
    global $wpdb;
    
    // Common old table names - adjust if yours is different
    $possible_tables = [
        $wpdb->prefix . 'talktopc_settings',
        $wpdb->prefix . 'talktopc_config',
        $wpdb->prefix . 'ttp_settings',
        $wpdb->prefix . 'ttp_config',
    ];
    
    foreach ($possible_tables as $table) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration utility needs direct query
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table
        ));
        
        if ($table_exists) {
            return $table;
        }
    }
    
    return false;
}

/**
 * Get table structure to understand what columns exist
 */
function talktopc_get_table_structure($table_name) {
    global $wpdb;
    
    // Validate table name format (alphanumeric, underscore, hyphen)
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $table_name)) {
        return [];
    }
    
    // Use esc_sql for table name (table names can't use prepare placeholders)
    $table_name = esc_sql($table_name);
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Table names cannot be prepared, migration utility needs direct query
    $columns = $wpdb->get_results("DESCRIBE `{$table_name}`", ARRAY_A);
    return $columns;
}

/**
 * Migrate from custom table to WordPress options
 * 
 * This function maps old table columns to new WordPress option names
 * Adjust the mapping based on your actual table structure
 */
function talktopc_migrate_from_table($table_name) {
    global $wpdb;
    
    // Validate table name format
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $table_name)) {
        return ['migrated' => 0, 'skipped' => 0, 'sources' => []];
    }
    
    // Use esc_sql for table name (table names can't use prepare placeholders)
    $table_name = esc_sql($table_name);
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Table names cannot be prepared, migration utility needs direct query
    $rows = $wpdb->get_results("SELECT * FROM `{$table_name}`", ARRAY_A);
    
    if (empty($rows)) {
        return [
            'success' => false,
            'message' => 'No data found in old table'
        ];
    }
    
    // Get table structure to understand columns
    $columns = talktopc_get_table_structure($table_name);
    $column_names = array_column($columns, 'Field');
    
    // Mapping: old_column_name => new_option_name
    // Adjust this mapping based on your actual table structure
    $mapping = [
        // Button settings
        'button_size' => 'talktopc_button_size',
        'button_shape' => 'talktopc_button_shape',
        'button_bg_color' => 'talktopc_button_bg_color',
        'button_hover_color' => 'talktopc_button_hover_color',
        
        // Icon settings
        'icon_type' => 'talktopc_icon_type',
        'icon_custom_image' => 'talktopc_icon_custom_image',
        'icon_emoji' => 'talktopc_icon_emoji',
        'icon_text' => 'talktopc_icon_text',
        
        // Panel settings
        'panel_width' => 'talktopc_panel_width',
        'panel_height' => 'talktopc_panel_height',
        'panel_border_radius' => 'talktopc_panel_border_radius',
        'panel_bg_color' => 'talktopc_panel_bg_color',
        
        // Header settings
        'header_title' => 'talktopc_header_title',
        'header_bg_color' => 'talktopc_header_bg_color',
        'header_text_color' => 'talktopc_header_text_color',
        
        // Agent settings
        'agent_id' => 'talktopc_agent_id',
        'agent_name' => 'talktopc_agent_name',
        
        // Connection settings
        'api_key' => 'talktopc_api_key',
        'app_id' => 'talktopc_app_id',
        'user_email' => 'talktopc_user_email',
        
        // Add more mappings as needed based on your table structure
    ];
    
    $migrated = 0;
    $skipped = 0;
    $errors = [];
    
    foreach ($rows as $row) {
        foreach ($mapping as $old_key => $new_option_name) {
            // Check if column exists in table
            if (!in_array($old_key, $column_names)) {
                continue;
            }
            
            // Skip if value is empty or null
            if (empty($row[$old_key]) && $row[$old_key] !== '0') {
                continue;
            }
            
            $value = $row[$old_key];
            
            // Sanitize based on option type
            if (strpos($new_option_name, '_color') !== false) {
                $value = sanitize_hex_color($value);
            } elseif (strpos($new_option_name, '_width') !== false || 
                      strpos($new_option_name, '_height') !== false ||
                      strpos($new_option_name, '_radius') !== false) {
                $value = absint($value);
            } else {
                $value = sanitize_text_field($value);
            }
            
            // Only update if option doesn't already exist (preserve existing settings)
            $existing = get_option($new_option_name);
            if ($existing === false) {
                update_option($new_option_name, $value);
                $migrated++;
            } else {
                $skipped++;
            }
        }
    }
    
    return [
        'success' => true,
        'migrated' => $migrated,
        'skipped' => $skipped,
        'message' => sprintf(
            'Migration complete: %d options migrated, %d skipped (already exist)',
            $migrated,
            $skipped
        )
    ];
}

/**
 * Automatic migration function - runs on plugin upgrade
 * Checks for old data sources and migrates if found
 */
function talktopc_auto_migrate() {
    global $wpdb;
    
    $results = [
        'migrated' => 0,
        'skipped' => 0,
        'sources' => []
    ];
    
    // Check for old custom tables
    $old_table = talktopc_check_old_table();
    if ($old_table) {
        $migration_result = talktopc_migrate_from_table($old_table);
        if ($migration_result['success']) {
            $results['migrated'] += $migration_result['migrated'];
            $results['skipped'] += $migration_result['skipped'];
            $results['sources'][] = 'table: ' . $old_table;
        }
    }
    
    // Check for common JSON file locations (optional - only if you had JSON storage)
    // Uncomment and adjust paths if needed:
    /*
    $possible_json_paths = [
        WP_CONTENT_DIR . '/talktopc-settings.json',
        ABSPATH . 'wp-content/talktopc-settings.json',
    ];
    
    foreach ($possible_json_paths as $json_path) {
        if (file_exists($json_path)) {
            $migration_result = talktopc_migrate_from_json($json_path);
            if ($migration_result['success']) {
                $results['migrated'] += $migration_result['migrated'];
                $results['skipped'] += $migration_result['skipped'];
                $results['sources'][] = 'json: ' . $json_path;
            }
            break; // Only migrate from first found JSON file
        }
    }
    */
    
    return [
        'success' => true,
        'migrated' => $results['migrated'],
        'skipped' => $results['skipped'],
        'sources' => $results['sources'],
        'message' => sprintf(
            'Migration complete: %d options migrated, %d skipped',
            $results['migrated'],
            $results['skipped']
        )
    ];
}

/**
 * Migrate from JSON file or other source
 */
function talktopc_migrate_from_json($json_file_path) {
    if (!file_exists($json_file_path)) {
        return [
            'success' => false,
            'message' => 'JSON file not found'
        ];
    }
    
    $json_content = file_get_contents($json_file_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ];
    }
    
    // Map JSON keys to WordPress options
    // Adjust based on your JSON structure
    $mapping = [
        'button.size' => 'talktopc_button_size',
        'button.shape' => 'talktopc_button_shape',
        'button.backgroundColor' => 'talktopc_button_bg_color',
        'button.hoverColor' => 'talktopc_button_hover_color',
        // Add more mappings as needed
    ];
    
    $migrated = 0;
    
    foreach ($mapping as $json_path => $option_name) {
        $keys = explode('.', $json_path);
        $value = $data;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                continue 2; // Skip this mapping
            }
            $value = $value[$key];
        }
        
        if (!empty($value)) {
            update_option($option_name, sanitize_text_field($value));
            $migrated++;
        }
    }
    
    return [
        'success' => true,
        'migrated' => $migrated,
        'message' => sprintf('Migrated %d options from JSON', $migrated)
    ];
}

/**
 * Admin page to run migration
 */
// Manual migration page (fallback - only shown if needed)
// Most users won't need this as migration runs automatically
add_action('admin_menu', function() {
    // Only add migration menu if there's an old table detected
    // This keeps the menu clean for most users
    $old_table = talktopc_check_old_table();
    if ($old_table) {
        add_submenu_page(
            'talktopc',
            'Migration',
            'Migration',
            'manage_options',
            'talktopc-migration',
            'talktopc_render_migration_page'
        );
    }
}, 999); // Add at the end

function talktopc_render_migration_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    global $wpdb;
    
    $result = null;
    
    // Handle "Find Tables" request
    if (isset($_POST['find_tables']) && check_admin_referer('talktopc_migrate')) {
        // Find all tables that might be related
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration utility needs direct query
        $all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
        $possible_tables = [];
        foreach ($all_tables as $table) {
            $table_name = $table[0];
            if (stripos($table_name, 'talktopc') !== false || 
                stripos($table_name, 'ttp') !== false ||
                stripos($table_name, 'widget') !== false) {
                $possible_tables[] = $table_name;
            }
        }
    }
    
    // Handle "View Table Structure" request
    $view_table = isset($_GET['view_table']) ? sanitize_text_field(wp_unslash($_GET['view_table'])) : $view_table_post;
    $table_structure = null;
    $sample_data = null;
    if ($view_table) {
        // Verify table exists
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration utility needs direct query
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $view_table));
        if ($table_exists) {
            $table_structure = talktopc_get_table_structure($view_table);
            // Validate table name before query
            if (preg_match('/^[a-zA-Z0-9_\-]+$/', $view_table)) {
                $view_table_escaped = esc_sql($view_table);
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Table names cannot be prepared, migration utility
                $sample_data = $wpdb->get_results("SELECT * FROM `{$view_table_escaped}` LIMIT 1", ARRAY_A);
            } else {
                $sample_data = [];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'Table not found: ' . $view_table
            ];
        }
    }
    
    // Handle "View Table Structure" button (not migrating yet)
    $view_table_post = null;
    if (isset($_POST['view_structure']) && check_admin_referer('talktopc_migrate')) {
        $view_table_post = isset($_POST['table_name']) ? sanitize_text_field(wp_unslash($_POST['table_name'])) : null;
    }
    
    // Handle migration request
    if (isset($_POST['migrate_table']) && check_admin_referer('talktopc_migrate')) {
        $table_name = isset($_POST['table_name']) ? sanitize_text_field(wp_unslash($_POST['table_name'])) : '';
        if (!empty($table_name)) {
            $result = talktopc_migrate_from_table($table_name);
        }
    }
    
    if (isset($_POST['migrate_json']) && check_admin_referer('talktopc_migrate')) {
        $json_path = isset($_POST['json_path']) ? sanitize_text_field(wp_unslash($_POST['json_path'])) : '';
        if (!empty($json_path)) {
            $result = talktopc_migrate_from_json($json_path);
        }
    }
    
    // Check for old table
    $old_table = talktopc_check_old_table();
    if ($old_table && !$view_table) {
        $table_structure = talktopc_get_table_structure($old_table);
    }
    
    ?>
    <div class="wrap">
        <h1>TalkToPC Migration</h1>
        
        <?php if ($result): ?>
            <div class="notice notice-<?php echo esc_attr($result['success'] ? 'success' : 'error'); ?> is-dismissible">
                <p><strong><?php echo esc_html($result['success'] ? 'Success!' : 'Error:'); ?></strong> <?php echo esc_html($result['message']); ?></p>
                <?php if (isset($result['migrated'])): ?>
                    <p>✅ Migrated: <strong><?php echo esc_html($result['migrated']); ?></strong> options</p>
                <?php endif; ?>
                <?php if (isset($result['skipped'])): ?>
                    <p>⏭️ Skipped: <strong><?php echo esc_html($result['skipped']); ?></strong> options (already exist - not overwritten)</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="card" style="background: #e7f5ff; border-left: 4px solid #0ea5e9; padding: 20px; margin: 20px 0;">
            <h2 style="margin-top: 0;">📋 Current Storage Method</h2>
            <p>The plugin uses <strong>WordPress Options API</strong> (stored in <code>wp_options</code> table).</p>
            <p>All settings are prefixed with <code>talktopc_</code> (e.g., <code>talktopc_button_size</code>).</p>
        </div>
        
        <div class="card" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0;">
            <h2 style="margin-top: 0;">🔍 Step 1: Find Your Old Table</h2>
            <p>First, let's find your old database table. Click the button below to search for tables that might contain your old settings.</p>
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('talktopc_migrate'); ?>
                <button type="submit" name="find_tables" class="button button-primary">
                    🔍 Search for Old Tables
                </button>
            </form>
            
            <?php if (isset($possible_tables) && !empty($possible_tables)): ?>
                <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 4px;">
                    <h3>Found <?php echo count($possible_tables); ?> possible table(s):</h3>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <?php foreach ($possible_tables as $table): ?>
                            <li style="margin: 8px 0;">
                                <code><?php echo esc_html($table); ?></code>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=talktopc-migration&view_table=' . urlencode($table))); ?>" 
                                   class="button button-small" style="margin-left: 10px;">
                                    View Structure
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (isset($possible_tables) && empty($possible_tables)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #f8d7da; border-radius: 4px; color: #721c24;">
                    <p><strong>No matching tables found.</strong> Your old data might be stored differently, or the table might have a different name.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($view_table || ($old_table && $table_structure)): 
            $table_to_show = $view_table ? $view_table : $old_table;
        ?>
            <div class="card" style="background: #d1fae5; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0;">
                <h2 style="margin-top: 0;">📊 Step 2: Review Table Structure</h2>
                <p>Found table: <code style="background: white; padding: 4px 8px; border-radius: 3px;"><?php echo esc_html($table_to_show); ?></code></p>
                
                <h3>Table Columns:</h3>
                <table class="widefat" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Column Name</th>
                            <th>Type</th>
                            <th>Sample Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table_structure as $col): ?>
                            <tr>
                                <td><code><?php echo esc_html($col['Field']); ?></code></td>
                                <td><?php echo esc_html($col['Type']); ?></td>
                                <td>
                                    <?php if (isset($sample_data) && isset($sample_data[0][$col['Field']])): ?>
                                        <code style="font-size: 11px;"><?php echo esc_html(substr($sample_data[0][$col['Field']], 0, 50)); ?></code>
                                    <?php else: ?>
                                        <em>No sample data</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 4px;">
                    <h3>⚠️ Important: Column Mapping</h3>
                    <p>Before migrating, you need to map your table columns to WordPress options. The migration script will try to automatically map common column names, but you may need to customize it.</p>
                    <p><strong>Next step:</strong> Click "Migrate" below to attempt automatic migration. If it doesn't work correctly, we'll help you customize the mapping.</p>
                </div>
                
                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field('talktopc_migrate'); ?>
                    <input type="hidden" name="table_name" value="<?php echo esc_attr($table_to_show); ?>">
                    <button type="submit" name="migrate_table" class="button button-primary button-large">
                        ✅ Migrate from <?php echo esc_html($table_to_show); ?>
                    </button>
                </form>
            </div>
        <?php elseif (!isset($possible_tables)): ?>
            <div class="card" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0;">
                <h2 style="margin-top: 0;">📝 Step 2: Enter Your Table Name Manually</h2>
                <p>If you know your table name, enter it below:</p>
                
                <form method="post" style="margin-top: 15px;">
                    <?php wp_nonce_field('talktopc_migrate'); ?>
                    <label style="display: block; margin-bottom: 10px;">
                        <strong>Table Name (with WordPress prefix):</strong><br>
                        <input type="text" name="table_name" class="regular-text" 
                               value="<?php echo esc_attr($wpdb->prefix . 'talktopc_settings'); ?>"
                               placeholder="<?php echo esc_attr($wpdb->prefix); ?>talktopc_settings"
                               style="margin-top: 5px;">
                        <br>
                        <small style="color: #666;">Example: <?php echo esc_html($wpdb->prefix); ?>talktopc_settings</small>
                    </label>
                    <button type="submit" name="view_structure" class="button button-secondary">
                        👁️ View Table Structure First
                    </button>
                    <button type="submit" name="migrate_table" class="button button-primary" style="margin-left: 10px;">
                        ✅ Migrate Now
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="card" style="margin-top: 20px;">
            <h2>📄 Alternative: Migrate from JSON File</h2>
            <p>If your old settings are stored in a JSON file instead of a database table:</p>
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('talktopc_migrate'); ?>
                <label style="display: block; margin-bottom: 10px;">
                    <strong>JSON File Path (absolute path on server):</strong><br>
                    <input type="text" name="json_path" class="large-text" 
                           placeholder="/var/www/html/wp-content/uploads/settings.json"
                           style="margin-top: 5px;">
                    <br>
                    <small style="color: #666;">Must be an absolute path accessible by WordPress</small>
                </label>
                <button type="submit" name="migrate_json" class="button button-secondary">
                    📄 Migrate from JSON
                </button>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px; background: #f8f9fa;">
            <h2>📚 Need Help?</h2>
            <p><strong>If automatic migration doesn't work:</strong></p>
            <ol style="margin-left: 20px;">
                <li>Share your table structure (column names) with me</li>
                <li>I'll help you customize the mapping in the migration script</li>
                <li>Or you can manually map each column using the guide below</li>
            </ol>
            
            <h3 style="margin-top: 20px;">All Available WordPress Options:</h3>
            <p>See the complete list in <code>talktopc.php</code> function <code>talktopc_get_all_option_names()</code></p>
            <p>Or check your database: <code>wp_options</code> table → filter for <code>talktopc_*</code> options.</p>
        </div>
    </div>
    <?php
}
