<?php
/**
 * Page Rules Scripts
 */

if (!defined('ABSPATH')) exit;


/**
 * Enqueue page rules scripts using WordPress enqueue functions
 * 
 * WordPress Plugin Review: Uses wp_add_inline_script() instead of inline <script> tags
 */
function talktopc_enqueue_page_rules_scripts($hook) {
    // Only load on page rules page
    // WordPress hook format: {parent-slug}_page_{submenu-slug}
    if (strpos($hook, 'talktopc-page-rules') === false) {
        return;
    }
    
    // Get PHP variables for JavaScript
    $rules_json = get_option('talktopc_page_rules', '[]');
    $rules = json_decode($rules_json, true) ?: [];
    
    // Register dummy script handle (required for wp_add_inline_script)
    wp_register_script('talktopc-page-rules', false, ['jquery'], TALKTOPC_VERSION, true);
    wp_enqueue_script('talktopc-page-rules');
    
    // Pass PHP variables to JavaScript
    wp_localize_script('talktopc-page-rules', 'talktopcPageRules', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('talktopc_ajax_nonce'),
        'rules' => $rules,
    ]);
    
    // Add inline script
    $js = <<<'JS'
    // Global variables for rule management
    var ttpAjaxNonce = talktopcPageRules.nonce;
    var ttpRules = talktopcPageRules.rules || [];
    var ttpAgentsList = [];
    
    jQuery(document).ready(function($) {
        var ajaxNonce = ttpAjaxNonce;
        var rules = ttpRules;
        
        // Fetch agents for dropdowns
        $.post(talktopcPageRules.ajaxUrl, { action: "talktopc_fetch_agents", nonce: ajaxNonce }, function(r) {
            if (r.success && r.data) {
                // Handle different response formats: array or {data: [...]}
                ttpAgentsList = Array.isArray(r.data) ? r.data : (r.data.data || []);
                agentsList = ttpAgentsList;
                populateAgentSelects();
            }
        });
        
        function populateAgentSelects() {
            $('.rule-agent-select-field').each(function() {
                var select = $(this);
                var index = parseInt(select.data('index') || select.closest('.rule-card').data('index'));
                var rule = ttpRules[index];
                var currentAgentId = rule ? (rule.agent_id || '') : '';
                
                select.html('<option value="">-- Select Agent --</option>');
                ttpAgentsList.forEach(function(agent) {
                    var agentId = agent.agentId || agent.id;
                    var agentName = agent.name || '';
                    var selected = agentId === currentAgentId ? ' selected' : '';
                    select.append('<option value="' + agentId + '"' + selected + '>' + agentName + '</option>');
                });
                select.append('<option value="none"' + (currentAgentId === 'none' ? ' selected' : '') + '>🚫 No Widget (disable)</option>');
            });
            
            // Populate modal select
            $('#modalAgentSelect').html('<option value="">-- Select Agent --</option>');
            ttpAgentsList.forEach(function(agent) {
                var agentId = agent.agentId || agent.id;
                var agentName = agent.name || '';
                $('#modalAgentSelect').append('<option value="' + agentId + '">' + agentName + '</option>');
            });
            $('#modalAgentSelect').append('<option value="none">🚫 No Widget (disable)</option>');
        }
        
        // Fetch pages list for modal
        var pagesData = null;
        $.post(talktopcPageRules.ajaxUrl, { action: "talktopc_get_pages_list", nonce: ajaxNonce }, function(r) {
            if (r.success) {
                pagesData = r.data;
                renderPageSelector();
            }
        });
        
        function renderPageSelector() {
            if (!pagesData) return;
            var html = '';
            
            if (pagesData.pages && pagesData.pages.length) {
                html += '<h4>📄 Pages</h4><div class="page-list">';
                pagesData.pages.forEach(function(page) {
                    html += '<label class="page-item"><input type="radio" name="target" value="' + page.id + '" data-type="' + page.type + '" data-name="' + page.name + '"><span class="icon">' + page.icon + '</span><span class="name">' + page.name + '</span></label>';
                });
                html += '</div>';
            }
            
            if (pagesData.post_types && pagesData.post_types.length) {
                html += '<h4>📝 Post Types</h4><div class="page-list">';
                pagesData.post_types.forEach(function(pt) {
                    html += '<label class="page-item"><input type="radio" name="target" value="' + pt.id + '" data-type="' + pt.type + '" data-name="' + pt.name + '"><span class="icon">' + pt.icon + '</span><span class="name">' + pt.name + '</span><span class="type-badge">' + (pt.badge || '') + '</span></label>';
                });
                html += '</div>';
            }
            
            if (pagesData.categories && pagesData.categories.length) {
                html += '<h4>📁 Categories</h4><div class="page-list">';
                pagesData.categories.forEach(function(cat) {
                    html += '<label class="page-item"><input type="radio" name="target" value="' + cat.id + '" data-type="' + cat.type + '" data-name="' + cat.name + '"><span class="icon">' + cat.icon + '</span><span class="name">' + cat.name + '</span>' + (cat.badge ? '<span class="type-badge">' + cat.badge + '</span>' : '') + '</label>';
                });
                html += '</div>';
            }
            
            $('#pageSelector').html(html);
            populateAgentSelects();
        }
        
        // Page search
        $('#pageSearch').on('input', function() {
            var search = $(this).val().toLowerCase();
            $('.page-item').each(function() {
                var name = $(this).find('.name').text().toLowerCase();
                $(this).toggle(name.indexOf(search) > -1);
            });
        });
        
        // Handle rule agent selection changes
        $(document).on('change', '.rule-agent-select-field', function() {
            var index = parseInt($(this).data('index') || $(this).closest('.rule-card').data('index'));
            var agentId = $(this).val();
            updateRuleAgent(index, agentId);
        });
        
        // Handle rule settings save buttons
        $(document).on('click', '.rule-save-btn', function() {
            var index = parseInt($(this).data('index') || $(this).closest('.rule-card').data('index'));
            saveRuleSettings(index);
        });
    });
    
    function toggleRule(header) {
        header.closest('.rule-card').classList.toggle('open');
    }
    
    function updateRuleAgent(index, agentId) {
        if (index < 0 || index >= ttpRules.length) return;
        
        var rule = ttpRules[index];
        rule.agent_id = agentId;
        
        // Find agent name
        var agentName = '🚫 No Widget';
        if (agentId && agentId !== 'none') {
            var agent = ttpAgentsList.find(function(a) {
                return (a.agentId || a.id) === agentId;
            });
            if (agent) agentName = agent.name || '';
        }
        rule.agent_name = agentName;
        
        // Update card visual state
        var card = document.querySelector('.rule-card[data-index="' + index + '"]');
        if (card) {
            if (agentId === 'none') {
                card.classList.add('disabled-rule');
            } else {
                card.classList.remove('disabled-rule');
            }
        }
        
        // Save immediately
        saveRules();
    }
    
    function saveRules() {
        jQuery.post(talktopcPageRules.ajaxUrl, {
            action: "talktopc_save_page_rules",
            nonce: ttpAjaxNonce,
            rules: JSON.stringify(ttpRules)
        }, function(r) {
            if (r.success) {
                // Show save status
                var statusEl = document.getElementById('rulesSaveStatus');
                if (statusEl) {
                    statusEl.textContent = '✓ Saved';
                    statusEl.classList.add('saved');
                    setTimeout(function() {
                        statusEl.textContent = '';
                        statusEl.classList.remove('saved');
                    }, 2000);
                }
            }
        });
    }
    
    function deleteRule(index) {
        if (!confirm("Delete this rule?")) return;
        ttpRules = ttpRules.filter(function(r, i) { return i !== index; });
        jQuery.post(talktopcPageRules.ajaxUrl, {
            action: "talktopc_save_page_rules",
            nonce: ttpAjaxNonce,
            rules: JSON.stringify(ttpRules)
        }, function(r) {
            if (r.success) location.reload();
        });
    }
    
    function openAddRuleModal() {
        document.getElementById('addRuleModal').classList.remove('hidden');
    }
    
    function closeAddRuleModal() {
        document.getElementById('addRuleModal').classList.add('hidden');
    }
    
    function saveNewRule() {
        var selected = document.querySelector('input[name="target"]:checked');
        var agentId = document.getElementById('modalAgentSelect').value;
        if (!selected || !agentId) {
            alert('Please select a page and agent');
            return;
        }
        
        // Find agent name
        var agentName = '🚫 No Widget';
        if (agentId && agentId !== 'none') {
            var agent = ttpAgentsList.find(function(a) {
                return (a.agentId || a.id) === agentId;
            });
            if (agent) agentName = agent.name || '';
        }
        
        var newRule = {
            id: 'rule_' + Date.now(),
            type: selected.dataset.type,
            target_id: selected.value,
            target_name: selected.dataset.name,
            agent_id: agentId,
            agent_name: agentName
        };
        
        ttpRules.push(newRule);
        
        jQuery.post(talktopcPageRules.ajaxUrl, {
            action: "talktopc_save_page_rules",
            nonce: ttpAjaxNonce,
            rules: JSON.stringify(ttpRules)
        }, function(r) {
            if (r.success) location.reload();
        });
    }
    
    function saveRuleSettings(index) {
        if (index < 0 || index >= ttpRules.length) return;
        
        var card = document.querySelector('.rule-card[data-index="' + index + '"]');
        if (!card) return;
        
        var prompt = card.querySelector('.rule-prompt-field')?.value || '';
        var firstMessage = card.querySelector('.rule-first-message-field')?.value || '';
        
        // Update rule (for now, we'll just save the rules array)
        // In the future, you might want to store rule-specific settings separately
        saveRules();
        
        // Show save status
        var statusEl = document.getElementById('ruleSaveStatus' + index);
        if (statusEl) {
            statusEl.textContent = '✓ Saved';
            statusEl.classList.add('saved');
            setTimeout(function() {
                statusEl.textContent = '';
                statusEl.classList.remove('saved');
            }, 2000);
        }
    }
    
    // Initialize drag & drop for rule reordering
    function initDragDrop() {
        var rulesList = document.getElementById('rulesList');
        if (!rulesList || typeof Sortable === 'undefined') {
            // Sortable.js not loaded - skip drag & drop
            return;
        }
        
        new Sortable(rulesList, {
            handle: '.rule-drag-handle',
            animation: 150,
            onEnd: function(evt) {
                // Reorder rules array based on new DOM order
                var newRules = [];
                rulesList.querySelectorAll('.rule-card').forEach(function(card) {
                    var index = parseInt(card.dataset.index);
                    if (index >= 0 && index < ttpRules.length) {
                        newRules.push(ttpRules[index]);
                    }
                });
                ttpRules = newRules;
                saveRules();
            }
        });
    }
    
    // Initialize on page load
    initDragDrop();
    
    // Close modal on overlay click
    document.getElementById("addRuleModal")?.addEventListener("click", function(e) {
        if (e.target === this) closeAddRuleModal();
    });
JS;
    
    // Replace all ajaxurl references with talktopcPageRules.ajaxUrl
    $js = str_replace('ajaxurl', 'talktopcPageRules.ajaxUrl', $js);
    
    wp_add_inline_script('talktopc-page-rules', $js);
}
add_action('admin_enqueue_scripts', 'talktopc_enqueue_page_rules_scripts');

/**
 * Render page rules scripts (deprecated - kept for backwards compatibility)
 * 
 * @deprecated Use talktopc_enqueue_page_rules_scripts() instead
 */
function talktopc_render_page_rules_scripts() {
    // This function is deprecated but kept for backwards compatibility
    // Scripts are now enqueued via admin_enqueue_scripts hook
}