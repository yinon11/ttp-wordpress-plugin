<?php
/**
 * Page Rules Page
 */

if (!defined('ABSPATH')) exit;


function talktopc_render_page_rules_page() {
    $default_agent_id = get_option('talktopc_agent_id', '');
    $default_agent_name = get_option('talktopc_agent_name', 'Default Agent');
    $rules_json = get_option('talktopc_page_rules', '[]');
    $rules = json_decode($rules_json, true);
    if (!is_array($rules)) $rules = [];
    
    talktopc_render_admin_styles();
    ?>
    <div class="wrap ttp-admin-wrap">
        <div class="wp-header">
            <h1>Page Rules</h1>
        </div>
        
        <div class="help-box">
            <strong>📄 What are Page Rules?</strong>
            Page rules let you show different agents on different pages. For example, show a Support agent on the Contact page, or disable the widget on Checkout.
            Rules are checked from top to bottom — the first matching rule wins.
        </div>
        
        <!-- Current default reminder -->
        <div class="default-agent-bar">
            <span class="icon">🤖</span>
            <div class="text">
                <strong>Default: <?php echo esc_html($default_agent_name ?: 'None'); ?></strong>
                <span>Used on pages without specific rules • <a href="<?php echo esc_url(admin_url('admin.php?page=talktopc')); ?>">Change default →</a></span>
            </div>
        </div>
        
        <div class="card">
            <h2><span class="icon">📋</span> Page-Specific Rules</h2>
            <span class="save-status" id="rulesSaveStatus" style="float: right; margin-top: -30px;"></span>
            
            <div id="rulesList">
                <?php if (empty($rules)): ?>
                    <div class="empty-state">
                        <div class="icon">📄</div>
                        <h3>No rules yet</h3>
                        <p>Create your first page rule to show different agents on different pages.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rules as $index => $rule): ?>
                        <?php talktopc_render_rule_card($rule, $index); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="add-rule-btn" onclick="openAddRuleModal()">
                <span>➕</span>
                <span>Add Page Rule</span>
            </div>
            
            <p style="font-size: 12px; color: #646970; margin-top: 15px;">
                💡 <strong>Tip:</strong> Drag rules to change priority. First matching rule wins.
            </p>
        </div>
    </div>
    
    <!-- Add Rule Modal -->
    <div class="modal-overlay hidden" id="addRuleModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Page Rule</h3>
                <button class="modal-close" onclick="closeAddRuleModal()">×</button>
            </div>
            <div class="modal-body">
                <input type="text" class="page-selector-search" id="pageSearch" placeholder="🔍 Search pages, posts, categories...">
                
                <div class="page-selector" id="pageSelector">
                    <!-- Will be populated by JS -->
                </div>
                
                <div class="agent-selector-modal">
                    <label>Agent for this page:</label>
                    <select id="modalAgentSelect">
                        <option value="">-- Select Agent --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="button" onclick="closeAddRuleModal()">Cancel</button>
                <button class="button button-primary" onclick="saveNewRule()">Add Rule</button>
            </div>
        </div>
    </div>
    
    <?php
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    talktopc_render_common_scripts();
    talktopc_render_page_rules_scripts();
}

/**
 * Render a single rule card
 */
function talktopc_render_rule_card($rule, $index) {
    $is_disabled = ($rule['agent_id'] ?? '') === 'none';
    $icon = talktopc_get_rule_icon($rule['type'] ?? 'page');
    ?>
    <div class="rule-card <?php echo $is_disabled ? 'disabled-rule' : ''; ?>" data-index="<?php echo esc_attr($index); ?>">
        <div class="rule-header" onclick="toggleRule(this)">
            <span class="rule-drag-handle" title="Drag to reorder">⋮⋮</span>
            <span class="rule-icon"><?php echo esc_html($icon); ?></span>
            <div class="rule-target">
                <div class="name"><?php echo esc_html($rule['target_name'] ?? 'Unknown'); ?></div>
                <div class="type"><?php echo esc_html(ucfirst($rule['type'] ?? 'page')); ?></div>
            </div>
            <div class="rule-agent-select" onclick="event.stopPropagation()">
                <select class="rule-agent-select-field" data-index="<?php echo esc_attr($index); ?>" onchange="updateRuleAgent(<?php echo esc_attr($index); ?>, this.value)">
                    <option value="">-- Select Agent --</option>
                </select>
            </div>
            <div class="rule-actions">
                <button class="rule-expand">▼</button>
                <button class="rule-delete" onclick="deleteRule(<?php echo esc_attr($index); ?>)" title="Delete rule">✕</button>
            </div>
        </div>
        <div class="rule-settings">
            <div class="form-row">
                <label>System Prompt</label>
                <div class="field">
                    <textarea class="rule-prompt-field" data-index="<?php echo esc_attr($index); ?>" rows="3" placeholder="Optional: Override agent prompt for this page"></textarea>
                </div>
            </div>
            <div class="form-row">
                <label>First Message</label>
                <div class="field">
                    <input type="text" class="rule-first-message-field" data-index="<?php echo esc_attr($index); ?>" placeholder="Optional: Override first message">
                </div>
            </div>
            <div class="save-area" style="border: none; padding-top: 15px; margin-top: 10px;">
                <button class="button button-primary button-small rule-save-btn" data-index="<?php echo esc_attr($index); ?>" onclick="saveRuleSettings(<?php echo esc_attr($index); ?>)">Save Agent Settings</button>
                <span class="save-status" id="ruleSaveStatus<?php echo esc_attr($index); ?>"></span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get icon for rule type
 */
function talktopc_get_rule_icon($type) {
    $icons = [
        'page' => '📄',
        'post' => '📝',
        'post_type' => '📝',
        'category' => '📁',
        'product_cat' => '🛍️'
    ];
    return $icons[$type] ?? '📄';
}
