<?php
/**
 * Dashboard Scripts
 * 
 * FIXES:
 * - Issue #1: Keep existing saved agent visible while loading, only update dropdown options
 */

if (!defined('ABSPATH')) exit;


/**
 * Enqueue dashboard scripts using WordPress enqueue functions
 * 
 * WordPress Plugin Review: Uses wp_add_inline_script() instead of inline <script> tags
 */
function talktopc_enqueue_dashboard_scripts($hook) {
    // Only load on dashboard page
    if ($hook !== 'toplevel_page_talktopc') {
        return;
    }
    
    // Get PHP variables for JavaScript
    $current_agent_id = get_option('talktopc_agent_id', '');
    $current_voice = get_option('talktopc_override_voice', '');
    $current_language = get_option('talktopc_override_language', '');
    $needs_agent_setup = get_transient('talktopc_needs_agent_setup');
    
    // Register dummy script handle (required for wp_add_inline_script)
    wp_register_script('talktopc-dashboard', false, ['jquery'], TALKTOPC_VERSION, true);
    wp_enqueue_script('talktopc-dashboard');
    
    // Pass PHP variables to JavaScript
    wp_localize_script('talktopc-dashboard', 'talktopcDashboard', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('talktopc_ajax_nonce'),
        'version' => TALKTOPC_VERSION,
        'currentAgentId' => $current_agent_id,
        'currentVoice' => $current_voice,
        'currentLanguage' => $current_language,
        'needsAgentSetup' => !empty($needs_agent_setup),
    ]);
    
    // Add inline script
    $js = <<<'JS'
    (function($) {
        'use strict';
        
        // Variables scoped to IIFE
        var agentsData = {};
        var voicesData = [];
        var languageMap = {};
        var isBackgroundSetup = false;
        
        $(document).ready(function() {
        var ajaxNonce = talktopcDashboard.nonce;
        var currentAgentId = talktopcDashboard.currentAgentId;
        var currentVoice = talktopcDashboard.currentVoice;
        var currentLanguage = talktopcDashboard.currentLanguage;
        
        // FIX #1: If we have a saved agent, show settings immediately (don't wait for AJAX)
        if (currentAgentId && currentAgentId !== 'none') {
            $('#agentSettings').removeClass('collapsed').show();
        }
        
        // Check if we need to set up agent (non-blocking AJAX)
        if (talktopcDashboard.needsAgentSetup) {
            // Show popup IMMEDIATELY - don't wait for AJAX
            showSetupInProgress();
            
            // Then trigger AJAX agent creation in the background
            $.ajax({
                url: talktopcDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'talktopc_auto_setup_agent',
                    nonce: ajaxNonce
                },
                success: function(response) {
                    if (response.success) {
                        // Check if agent was actually created or if agents already existed
                        if (response.data && response.data.created === true) {
                            // Agent is being created - popup already showing, start polling
                            checkSetupStatus();
                        } else {
                            // Agents already exist - hide popup and reload to show agents
                            hideSetupInProgress();
                            fetchVoices(function() { fetchAgents(); });
                        }
                    } else {
                        // Show error - hide popup and show error notice
                        hideSetupInProgress();
                        var $notice = $('<div class="notice notice-error is-dismissible" style="margin: 10px 0;"><p><strong>Error:</strong> ' + (response.data?.message || 'Failed to set up agent') + '</p></div>');
                        $('.talktopc-admin-wrap .wp-header').after($notice);
                        fetchVoices(function() { fetchAgents(); });
                    }
                },
                error: function() {
                    // Show error - hide popup and show error notice
                    hideSetupInProgress();
                    var $notice = $('<div class="notice notice-error is-dismissible" style="margin: 10px 0;"><p><strong>Error:</strong> Could not connect to server. Please try again.</p></div>');
                    $('.talktopc-admin-wrap .wp-header').after($notice);
                    fetchVoices(function() { fetchAgents(); });
                }
            });
        } else {
            // Not setting up agent - check setup status first before loading data
            checkSetupStatus();
        }
        
        // === SETUP STATUS CHECK ===
        function checkSetupStatus() {
            $.post(talktopcDashboard.ajaxUrl, { action: 'talktopc_get_setup_status', nonce: ajaxNonce }, function(r) {
                if (r.success && r.data.creating) {
                    // Still creating agent - show overlay and poll
                    showSetupInProgress();
                } else {
                    // Not creating - load data normally
                    hideSetupInProgress();
                    fetchVoices(function() { fetchAgents(); });
                }
            }).fail(function() {
                // On error, just load normally
                fetchVoices(function() { fetchAgents(); });
            });
        }
        
        function showSetupInProgress() {
            // Show overlay if not exists and not in background mode
            if ($('#talktopc-setup-overlay').length === 0 && !isBackgroundSetup) {
                var overlay = $(
                    '<div id="talktopc-setup-overlay" class="talktopc-setup-overlay">' +
                        '<div class="talktopc-setup-modal">' +
                            '<div class="talktopc-setup-spinner"></div>' +
                            '<h2>🤖 Creating your AI assistant...</h2>' +
                            '<p>We\'re analyzing your website content and generating a personalized AI assistant.</p>' +
                            '<p class="talktopc-setup-note">This usually takes 30-60 seconds.</p>' +
                            '<button type="button" class="button" id="talktopc-run-background-btn">Run in Background</button>' +
                        '</div>' +
                    '</div>'
                );
                $('body').append(overlay);
                
                // Handle "Run in Background" button (use event delegation to avoid multiple handlers)
                $(document).off('click', '#talktopc-run-background-btn').on('click', '#talktopc-run-background-btn', function(e) {
                    e.preventDefault();
                    isBackgroundSetup = true;
                    $('#talktopc-setup-overlay').remove();
                    showBackgroundBanner();
                    // Load voices but keep agents area disabled
                    fetchVoices(function() {
                        // Show loading state for agents
                        $('#defaultAgentSelect').html('<option value="">Setting up...</option>').prop('disabled', true);
                        $('#agentSelectorArea').hide();
                        $('#createAgentBtn').prop('disabled', true);
                    });
                });
            }
            
            // Poll every 3 seconds
            setTimeout(function() {
                $.post(talktopcDashboard.ajaxUrl, { action: 'talktopc_get_setup_status', nonce: ajaxNonce }, function(r) {
                    if (r.success && r.data.creating) {
                        // Still creating - keep polling
                        showSetupInProgress();
                    } else {
                        // Done!
                        if (isBackgroundSetup) {
                            // Remove banner and reload agents
                            $('#talktopc-background-setup-banner').remove();
                            $('#defaultAgentSelect').prop('disabled', false);
                            $('#agentSelectorArea').show();
                            $('#createAgentBtn').prop('disabled', false);
                            fetchAgents();
                            // Show success notice
                            var $notice = $('<div class="notice notice-success is-dismissible" style="margin: 10px 0;"><p>✅ Your AI assistant is ready!</p></div>');
                            $('.talktopc-admin-wrap .wp-header').after($notice);
                        } else {
                            // Reload page to show everything fresh
                            window.location.reload();
                        }
                    }
                }).fail(function() {
                    // On error, reload anyway
                    if (!isBackgroundSetup) {
                        window.location.reload();
                    }
                });
            }, 3000);
        }
        
        function showBackgroundBanner() {
            // Remove any existing banner first
            $('#talktopc-background-setup-banner').remove();
            
            var banner = $(
                '<div id="talktopc-background-setup-banner" class="talktopc-background-setup-banner">' +
                    '<div class="talktopc-banner-spinner"></div>' +
                    '<div class="talktopc-banner-text">' +
                        '<strong>Creating your AI assistant...</strong>' +
                        '<span>This is running in the background. You can explore the settings while you wait.</span>' +
                    '</div>' +
                '</div>'
            );
            
            // Insert banner after settings_errors or after header, before first card
            var $adminWrap = $('.talktopc-admin-wrap');
            if ($adminWrap.length) {
                // Try to insert after settings_errors or after header
                var $afterElement = $adminWrap.find('.settings-error, .wp-header').last();
                if ($afterElement.length) {
                    $afterElement.after(banner);
                } else {
                    // Insert before first card
                    var $firstCard = $adminWrap.find('.card').first();
                    if ($firstCard.length) {
                        $firstCard.before(banner);
                    } else {
                        // Insert at the beginning of the wrap
                        $adminWrap.prepend(banner);
                    }
                }
            } else {
                // Fallback: insert at top of body
                $('body').prepend(banner);
            }
            
            // Make sure banner is visible
            banner.show();
        }
        
        function hideSetupInProgress() {
            $('#talktopc-setup-overlay').remove();
            $('#talktopc-background-setup-banner').remove();
        }
        
        // === FETCH CREDITS ===
        // Start in loading state
        $('#talktopcCreditsBox').addClass('loading');
        
        $.post(ajaxurl, { action: 'talktopc_fetch_credits', nonce: ajaxNonce }, function(r) {
            $('#talktopcCreditsBox').removeClass('loading');
            
            if (r.success && r.data) {
                // Use remainingBrowserMinutes if available, fallback to credits
                var minutes = r.data.remainingBrowserMinutes !== undefined 
                    ? r.data.remainingBrowserMinutes 
                    : r.data.credits;
                updateCreditsDisplay(minutes, r.data);
            } else {
                showCreditsError(r.data?.message || 'Unable to load credits');
            }
        }).fail(function() {
            $('#talktopcCreditsBox').removeClass('loading');
            showCreditsError('Connection failed');
        });
        
        function updateCreditsDisplay(creditsValue, fullData) {
            // Parse credits - remove commas if string
            var credits = typeof creditsValue === 'string' 
                ? parseInt(creditsValue.replace(/,/g, ''), 10) 
                : parseInt(creditsValue, 10);
            
            if (isNaN(credits)) credits = 0;
            
            var $box = $('#talktopcCreditsBox');
            var $icon = $('#talktopcCreditsIcon');
            var $title = $('#talktopcCreditsTitle');
            var $amount = $('#talktopcCreditsAmount');
            var $unit = $('#talktopcCreditsUnit');
            var $label = $('#talktopcCreditsLabel');
            var $warning = $('#talktopcCreditsWarning');
            var $button = $('#talktopcCreditsButton');
            var $hint = $('#talktopcCreditsHint');
            
            // Reset classes
            $box.removeClass('loading low-credits critical-credits no-credits error');
            
            // Format number with commas
            var formattedCredits = credits.toLocaleString();
            
            // Calculate hours
            var hours = Math.floor(credits / 60);
            var conversations = Math.floor(credits / 3); // ~3 min avg per conversation
            
            if (credits === 0) {
                // STATE: No Credits
                $box.addClass('no-credits');
                $icon.text('😴');
                $title.text('No Credits Available');
                $amount.text('0');
                $unit.text('minutes');
                $label.text('Your voice widget is currently disabled');
                $warning.html('ℹ️ <span>Visitors cannot use voice chat until you add credits</span>').addClass('visible');
                $button.text('🛒 Buy Credits Now');
                $hint.text('');
            } else if (credits < 10) {
                // STATE: Critical
                $box.addClass('critical-credits');
                $icon.text('🚨');
                $title.text('Credits Almost Depleted!');
                $amount.text(formattedCredits);
                $unit.text('minutes');
                $label.text('Widget will stop working when depleted');
                $warning.removeClass('visible').html('');
                $button.text('🛒 Buy Credits Now');
                $hint.text('');
            } else if (credits < 100) {
                // STATE: Low
                $box.addClass('low-credits');
                $icon.text('⚠️');
                $title.text('Credits Running Low');
                $amount.text(formattedCredits);
                $unit.text('minutes');
                $label.text('Voice conversation time remaining');
                $warning.html('⏱️ <span>Estimated ~' + conversations + ' conversations left</span>').addClass('visible');
                $button.text('🛒 Buy Credits Now');
                $hint.text('');
            } else {
                // STATE: Healthy
                $icon.text('🎙️');
                $title.text('Available Credits');
                $amount.text(formattedCredits);
                $unit.text('minutes');
                $label.text('Voice conversation time remaining');
                $warning.removeClass('visible').html('');
                $button.text('Buy More →');
                $hint.text('~' + hours + ' hours of conversations');
            }
        }
        
        function showCreditsError(message) {
            var $box = $('#talktopcCreditsBox');
            $box.removeClass('loading').addClass('error');
            $('#talktopcCreditsIcon').text('⚡');
            $('#talktopcCreditsTitle').text('Credits');
            $('#talktopcCreditsAmount').text('—');
            $('#talktopcCreditsUnit').html('<button class="retry-btn" onclick="location.reload()">↻ Retry</button>');
            $('#talktopcCreditsLabel').text(message);
            $('#talktopcCreditsWarning').removeClass('visible').html('');
            $('#talktopcCreditsHint').text('');
            $('#talktopcCreditsButton').text('Buy More →');
        }
        
        // === AGENTS ===
        function fetchAgents() {
            $.post(ajaxurl, { action: 'talktopc_fetch_agents', nonce: ajaxNonce }, function(r) {
                var agents = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || [])) : [];
                
                // Populate dropdown
                populateAgentsDropdown(agents);
                
                // FIX #1: Hide loading indicator once agents are loaded
                $('#agentLoadingIndicator').addClass('hidden');
            }).fail(function() {
                $('#agentLoadingIndicator').addClass('hidden');
            });
        }
        
        function populateAgentsDropdown(agents) {
            agentsData = {};
            agents.forEach(function(a) { var id = a.agentId || a.id; agentsData[id] = a; });
            
            var $s = $('#defaultAgentSelect');
            
            // FIX #1: Store current selection before clearing
            var previousSelection = $s.val() || currentAgentId;
            
            // Clear and rebuild options
            $s.empty().append('<option value="">-- Select Agent --</option>');
            
            if (agents.length === 0) {
                // No agents yet - show message
                $s.append('<option value="" disabled>No agents yet - create one below</option>');
                return;
            }
            
            agents.forEach(function(a) {
                var id = a.agentId || a.id;
                // FIX #1: Use previousSelection to maintain selection
                var isSelected = (id === previousSelection);
                $s.append('<option value="'+id+'"'+(isSelected?' selected':'')+'>'+a.name+'</option>');
            });
            $s.append('<option value="none"'+(previousSelection === 'none' ? ' selected' : '')+'>🚫 Widget Disabled</option>');
            
            $s.off('change').on('change', function() {
                var selectedId = $(this).val();
                var selectedName = $(this).find('option:selected').text();
                
                // Always save the selection (including "none" for disabled)
                if (selectedId) {
                    $.post(ajaxurl, {
                        action: 'talktopc_save_agent_selection',
                        nonce: ajaxNonce,
                        agent_id: selectedId,
                        agent_name: selectedName
                    }, function(r) {
                        if (r.success && selectedId === 'none') {
                            // Show confirmation for disabled state
                            var $notice = $('<div class="notice notice-warning is-dismissible" style="margin: 10px 0;"><p>✓ Widget disabled. The voice widget will not appear on your website.</p></div>');
                            $('#agentSelectorArea').after($notice);
                            setTimeout(function() { $notice.fadeOut(function() { $(this).remove(); }); }, 3000);
                        }
                    });
                }
                
                // Show/hide settings panel based on selection
                if (selectedId === 'none' || !selectedId) {
                    $('#agentSettings').addClass('collapsed').hide();
                } else {
                    $('#agentSettings').removeClass('collapsed').show();
                    if (selectedId && agentsData[selectedId]) {
                        populateAgentSettings(agentsData[selectedId]);
                    }
                }
            });
            
            // FIX #1: If we have the saved agent in the fetched data, populate its settings
            if (previousSelection && agentsData[previousSelection]) {
                populateAgentSettings(agentsData[previousSelection]);
                $('#agentSettings').removeClass('collapsed').show();
            } else if (agents.length > 0 && !previousSelection) {
                // Auto-select first agent if none selected
                var first = agents[0], firstId = first.agentId || first.id;
                $s.val(firstId);
                populateAgentSettings(first);
                autoSaveSettings(firstId, first.name);
                $('#agentSettings').removeClass('collapsed').show();
                return;
            }
        }
        
        function populateAgentSettings(agent) {
            var config = {};
            if (agent.configuration && agent.configuration.value) {
                try { 
                    var parsed = JSON.parse(agent.configuration.value); 
                    config = typeof parsed === 'string' ? JSON.parse(parsed) : parsed; 
                } catch (e) { 
                    config = agent.configuration; 
                }
            } else if (agent.configuration && typeof agent.configuration === 'object') { 
                config = agent.configuration; 
            } else { 
                config = agent; 
            }
            
            // Always populate fields with agent data
            var agentPrompt = config.systemPrompt || config.prompt || '';
            $('#talktopc_override_prompt').val(agentPrompt);
            
            var firstMessage = config.firstMessage || '';
            $('#talktopc_override_first_message').val(firstMessage);
            
            var voiceId = config.voiceId || '';
            var voiceSpeed = config.voiceSpeed || 1.0;
            
            // If voice has a default speed, use it
            if (voiceId && voicesData.length > 0) {
                var voice = voicesData.find(function(v) { return (v.voiceId || v.id) === voiceId; });
                if (voice && voice.defaultVoiceSpeed && (!voiceSpeed || voiceSpeed == 1)) {
                    voiceSpeed = voice.defaultVoiceSpeed;
                }
            }
            
            var lang = config.agentLanguage || config.language || '';
            
            // Set language first (this filters voices)
            if (lang) {
                $('#talktopc_override_language').val(lang);
                populateVoicesDropdown(lang);
            }
            
            // Then set voice (after dropdown is populated)
            $('#talktopc_override_voice').val(voiceId);
            $('#talktopc_override_voice_speed').val(voiceSpeed);
            
            $('#talktopc_override_temperature').val(config.temperature || '0.7');
            $('#talktopc_override_max_tokens').val(config.maxTokens || '1000');
            $('#talktopc_override_max_call_duration').val(config.maxCallDuration || '300');
        }
        
        function autoSaveSettings(agentId, agentName) {
            if (!agentId) return;
            var $notice = $('<div class="notice notice-info" id="talktopc-autosave-notice" style="margin: 10px 0; padding: 10px;"><p>⏳ Auto-saving...</p></div>');
            $('.talktopc-admin-wrap .wp-header').after($notice);
            
            $.post(ajaxurl, { action: 'talktopc_save_agent_selection', nonce: ajaxNonce, agent_id: agentId, agent_name: agentName }, function(r) {
                if (r.success) {
                    $('#talktopc-autosave-notice').removeClass('notice-info').addClass('notice-success').html('<p>✅ Saved! Reloading...</p>');
                    setTimeout(function() { window.location.reload(); }, 300);
                } else {
                    $('#talktopc-autosave-notice').removeClass('notice-info').addClass('notice-error').html('<p>❌ Failed. Save manually.</p>');
                }
            });
        }
        
        // === VOICES ===
        function fetchVoices(callback) {
            $.post(ajaxurl, { action: 'talktopc_fetch_voices', nonce: ajaxNonce }, function(r) {
                voicesData = r.success && r.data ? (Array.isArray(r.data) ? r.data : (r.data.data || r.data.voices || [])) : [];
                
                var langNames = {
                    'en':'English','en-US':'English (US)','en-GB':'English (UK)',
                    'es':'Spanish','fr':'French','de':'German',
                    'he':'Hebrew','he-IL':'Hebrew',
                    'ar':'Arabic','zh':'Chinese','ja':'Japanese',
                    'pt':'Portuguese','ru':'Russian','it':'Italian',
                    'nl':'Dutch','ko':'Korean','pl':'Polish',
                    'tr':'Turkish','hi':'Hindi','sv':'Swedish'
                };
                languageMap = {};
                voicesData.forEach(function(v) { 
                    (v.languages || []).forEach(function(l) { 
                        if (!languageMap[l]) languageMap[l] = langNames[l] || l; 
                    }); 
                });
                
                var $lang = $('#talktopc_override_language');
                $lang.find('option:not(:first)').remove();
                Object.keys(languageMap).sort(function(a,b) { 
                    return languageMap[a].localeCompare(languageMap[b]); 
                }).forEach(function(code) {
                    $lang.append('<option value="'+code+'"'+(code===currentLanguage?' selected':'')+'>'+languageMap[code]+'</option>');
                });
                
                populateVoicesDropdown(currentLanguage);
                $lang.off('change').on('change', function() { 
                    populateVoicesDropdown($(this).val()); 
                });
                if (callback) callback();
            });
        }
        
        function populateVoicesDropdown(filterLang) {
            var $v = $('#talktopc_override_voice');
            $v.find('option:not(:first)').remove();
            var filtered = filterLang ? voicesData.filter(function(v) {
                return (v.languages || []).some(function(l) { 
                    return l === filterLang || l.startsWith(filterLang + '-') || filterLang.startsWith(l + '-'); 
                });
            }) : voicesData;
            
            filtered.forEach(function(v) {
                var id = v.voiceId || v.id;
                $v.append('<option value="'+id+'" data-default-speed="'+(v.defaultVoiceSpeed||1.0)+'"'+(id===currentVoice?' selected':'')+'>'+v.name+'</option>');
            });
        }
        
        $('#talktopc_override_voice').on('change', function() {
            var speed = $(this).find('option:selected').data('default-speed');
            if (speed) $('#talktopc_override_voice_speed').val(speed);
        });
        
        // === GENERATE PROMPT ===
        $('#talktopcGeneratePrompt').on('click', function() {
            var $btn = $(this), $ta = $('#talktopc_override_prompt');
            if ($ta.val().trim() !== '' && !confirm('Replace current prompt?')) return;
            
            $btn.prop('disabled', true).text('Generating...');
            
            $.post(talktopcDashboard.ajaxUrl, { action: 'talktopc_generate_prompt', nonce: ajaxNonce }, function(r) {
                if (r.success && r.data.prompt) {
                    $ta.val(r.data.prompt).css('background-color', '#e8f5e9');
                    setTimeout(function() { $ta.css('background-color', ''); }, 2000);
                } else {
                    var $notice = $('<div class="notice notice-error is-dismissible" style="margin: 10px 0;"><p><strong>Error:</strong> ' + (r.data?.message || 'Failed to generate prompt') + '</p></div>');
                    $('.talktopc-admin-wrap .wp-header').after($notice);
                }
                $btn.prop('disabled', false).text('🔄 Generate from Site Content');
            });
        });
        
        // === CREATE AGENT ===
        $('#createAgentBtn').on('click', function() {
            var name = $('#newAgentName').val().trim();
            if (!name) {
                $('#newAgentName').closest('.create-agent-section').find('.create-agent-error').remove();
                $('#newAgentName').closest('.create-agent-inline').after('<div class="create-agent-error notice notice-error" style="margin: 10px 0;"><p>Please enter an agent name.</p></div>');
                return;
            }
            var $btn = $(this).prop('disabled', true).text('Creating...');
            
            $.post(talktopcDashboard.ajaxUrl, {
                action: 'talktopc_create_agent',
                nonce: ajaxNonce,
                agent_name: name,
                auto_generate_prompt: 'true'
            }, function(r) {
                if (r.success) {
                    location.reload();
                } else {
                    var $notice = $('<div class="notice notice-error is-dismissible" style="margin: 10px 0;"><p><strong>Error:</strong> ' + (r.data?.message || 'Failed to create agent') + '</p></div>');
                    $('.talktopc-admin-wrap .wp-header').after($notice);
                }
                $btn.prop('disabled', false).text('Create Agent');
            });
        });
        
        // === EDIT MODE TOGGLE ===
        var originalValues = {}; // Store original values for cancel
        
        function enterEditMode() {
            // Store original values
            originalValues = {
                prompt: $('#talktopc_override_prompt').val(),
                firstMessage: $('#talktopc_override_first_message').val(),
                voice: $('#talktopc_override_voice').val(),
                voiceSpeed: $('#talktopc_override_voice_speed').val(),
                language: $('#talktopc_override_language').val(),
                temperature: $('#talktopc_override_temperature').val(),
                maxTokens: $('#talktopc_override_max_tokens').val(),
                maxCallDuration: $('#talktopc_override_max_call_duration').val()
            };
            
            // Enable all fields
            $('#agentSettingsForm input, #agentSettingsForm textarea, #agentSettingsForm select').prop('disabled', false);
            $('#agentSettingsForm').addClass('edit-mode');
            
            // Show edit-only elements (Generate button, Save area)
            $('.edit-only').show();
            
            // Toggle buttons
            $('#editAgentSettingsBtn').hide();
            $('#cancelEditBtn').show();
        }
        
        function exitEditMode(revert) {
            if (revert) {
                // Restore original values
                $('#talktopc_override_prompt').val(originalValues.prompt);
                $('#talktopc_override_first_message').val(originalValues.firstMessage);
                $('#talktopc_override_voice').val(originalValues.voice);
                $('#talktopc_override_voice_speed').val(originalValues.voiceSpeed);
                $('#talktopc_override_language').val(originalValues.language);
                $('#talktopc_override_temperature').val(originalValues.temperature);
                $('#talktopc_override_max_tokens').val(originalValues.maxTokens);
                $('#talktopc_override_max_call_duration').val(originalValues.maxCallDuration);
            }
            
            // Disable all fields
            $('#agentSettingsForm input, #agentSettingsForm textarea, #agentSettingsForm select').prop('disabled', true);
            $('#agentSettingsForm').removeClass('edit-mode');
            
            // Hide edit-only elements
            $('.edit-only').hide();
            
            // Toggle buttons
            $('#editAgentSettingsBtn').show();
            $('#cancelEditBtn').hide();
            
            // Clear any status messages
            $('#agentSaveStatus').text('').removeClass('saved error');
        }
        
        // Edit button click
        $('#editAgentSettingsBtn').on('click', function(e) {
            e.stopPropagation();
            enterEditMode();
        });
        
        // Cancel button clicks (both in header and in save area)
        $('#cancelEditBtn, #cancelEditBtn2').on('click', function(e) {
            e.stopPropagation();
            exitEditMode(true); // true = revert changes
        });
        
        // === SAVE AGENT SETTINGS ===
        // Saves to both WordPress (for fast UI cache) and TalkToPC backend (actual DB)
        $('#saveAgentSettingsBtn').on('click', function() {
            var $btn = $(this);
            var $status = $('#agentSaveStatus');
            var selectedAgentId = $('#defaultAgentSelect').val();
            
            if (!selectedAgentId || selectedAgentId === 'none') {
                $status.text('Please select an agent first').addClass('error');
                return;
            }
            
            // Collect form data
            var formData = {
                system_prompt: $('#talktopc_override_prompt').val(),
                first_message: $('#talktopc_override_first_message').val(),
                voice_id: $('#talktopc_override_voice').val(),
                voice_speed: $('#talktopc_override_voice_speed').val(),
                language: $('#talktopc_override_language').val(),
                temperature: $('#talktopc_override_temperature').val(),
                max_tokens: $('#talktopc_override_max_tokens').val(),
                max_call_duration: $('#talktopc_override_max_call_duration').val()
            };
            
            $btn.prop('disabled', true).text('Saving...');
            $status.text('').removeClass('saved error');
            
            // Step 1: Save to WordPress options (for fast UI cache)
            $.post(ajaxurl, {
                action: 'talktopc_save_agent_settings_local',
                nonce: ajaxNonce,
                ...formData
            }, function(localResult) {
                // Step 2: Save to TalkToPC backend (actual DB)
                $.post(ajaxurl, {
                    action: 'talktopc_update_agent',
                    nonce: ajaxNonce,
                    agent_id: selectedAgentId,
                    ...formData
                }, function(r) {
                    if (r.success) {
                        $status.text('✓ Saved').addClass('saved');
                        // Exit edit mode after successful save
                        setTimeout(function() {
                            exitEditMode(false); // false = don't revert, keep new values
                        }, 1000);
                    } else {
                        $status.text('⚠ Failed: ' + (r.data?.message || 'Unknown error')).addClass('error');
                        $btn.prop('disabled', false).text('Save Agent Settings');
                    }
                }).fail(function() {
                    $status.text('⚠ Backend unreachable').addClass('error');
                    $btn.prop('disabled', false).text('Save Agent Settings');
                });
            }).fail(function() {
                $status.text('✗ Failed to save').addClass('error');
                $btn.prop('disabled', false).text('Save Agent Settings');
            });
        });
    });
    
        function toggleAgentSettings() {
            var el = document.getElementById("agentSettings");
            el.classList.toggle("collapsed");
            if (el.classList.contains("collapsed")) {
                el.style.display = "none";
            } else {
                el.style.display = "block";
            }
        }
        
        // Expose function needed for onclick handler
        window.toggleAgentSettings = toggleAgentSettings;
    })(jQuery);
JS;
    
    // Replace all ajaxurl references with talktopcDashboard.ajaxUrl
    $js = str_replace('ajaxurl', 'talktopcDashboard.ajaxUrl', $js);
    
    wp_add_inline_script('talktopc-dashboard', $js);
}
add_action('admin_enqueue_scripts', 'talktopc_enqueue_dashboard_scripts');

/**
 * Render dashboard scripts (deprecated - kept for backwards compatibility)
 * 
 * @deprecated Use talktopc_enqueue_dashboard_scripts() instead
 * @param string $current_agent_id Current agent ID (unused, kept for compatibility)
 */
function talktopc_render_dashboard_scripts($current_agent_id = '') {
    // This function is deprecated but kept for backwards compatibility
    // Scripts are now enqueued via admin_enqueue_scripts hook
}