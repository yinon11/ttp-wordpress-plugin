<?php
/**
 * Dashboard Scripts
 * 
 * FIXES:
 * - Issue #1: Keep existing saved agent visible while loading, only update dropdown options
 */

if (!defined('ABSPATH')) exit;


function ttp_render_dashboard_scripts($current_agent_id) {
    $ajax_nonce = wp_create_nonce('ttp_ajax_nonce');
    $current_voice = get_option('ttp_override_voice');
    $current_language = get_option('ttp_override_language');
    ?>
    <script>
    console.log('🔧 TTP Voice Widget v<?php echo esc_js(TTP_VERSION); ?> loaded');
    var agentsData = {};
    var voicesData = [];
    var languageMap = {};
    var isBackgroundSetup = false;
    
    jQuery(document).ready(function($) {
        var ajaxNonce = '<?php echo esc_js($ajax_nonce); ?>';
        var currentAgentId = '<?php echo esc_js($current_agent_id); ?>';
        var currentVoice = '<?php echo esc_js($current_voice); ?>';
        var currentLanguage = '<?php echo esc_js($current_language); ?>';
        
        // FIX #1: If we have a saved agent, show settings immediately (don't wait for AJAX)
        if (currentAgentId && currentAgentId !== 'none') {
            $('#agentSettings').removeClass('collapsed').show();
        }
        
        // Check setup status first before loading data
        checkSetupStatus();
        
        // === SETUP STATUS CHECK ===
        function checkSetupStatus() {
            $.post(ajaxurl, { action: 'ttp_get_setup_status', nonce: ajaxNonce }, function(r) {
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
            if ($('#ttp-setup-overlay').length === 0 && !isBackgroundSetup) {
                var overlay = $(
                    '<div id="ttp-setup-overlay" class="ttp-setup-overlay">' +
                        '<div class="ttp-setup-modal">' +
                            '<div class="ttp-setup-spinner"></div>' +
                            '<h2>🤖 Creating your AI assistant...</h2>' +
                            '<p>We\'re analyzing your website content and generating a personalized AI assistant.</p>' +
                            '<p class="ttp-setup-note">This usually takes 30-60 seconds.</p>' +
                            '<button type="button" class="button" id="ttp-run-background-btn">Run in Background</button>' +
                        '</div>' +
                    '</div>'
                );
                $('body').append(overlay);
                
                // Handle "Run in Background" button
                $('#ttp-run-background-btn').on('click', function() {
                    isBackgroundSetup = true;
                    $('#ttp-setup-overlay').remove();
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
                $.post(ajaxurl, { action: 'ttp_get_setup_status', nonce: ajaxNonce }, function(r) {
                    if (r.success && r.data.creating) {
                        // Still creating - keep polling
                        showSetupInProgress();
                    } else {
                        // Done!
                        if (isBackgroundSetup) {
                            // Remove banner and reload agents
                            $('#ttp-background-setup-banner').remove();
                            $('#defaultAgentSelect').prop('disabled', false);
                            $('#agentSelectorArea').show();
                            $('#createAgentBtn').prop('disabled', false);
                            fetchAgents();
                            // Show success notice
                            var $notice = $('<div class="notice notice-success is-dismissible" style="margin: 10px 0;"><p>✅ Your AI assistant is ready!</p></div>');
                            $('.ttp-admin-wrap .wp-header').after($notice);
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
            if ($('#ttp-background-setup-banner').length === 0) {
                var banner = $(
                    '<div id="ttp-background-setup-banner" class="ttp-background-setup-banner">' +
                        '<div class="ttp-banner-spinner"></div>' +
                        '<div class="ttp-banner-text">' +
                            '<strong>Creating your AI assistant...</strong>' +
                            '<span>This is running in the background. You can explore the settings while you wait.</span>' +
                        '</div>' +
                    '</div>'
                );
                $('.ttp-admin-wrap .card').first().before(banner);
            }
        }
        
        function hideSetupInProgress() {
            $('#ttp-setup-overlay').remove();
            $('#ttp-background-setup-banner').remove();
        }
        
        // === FETCH CREDITS ===
        // Start in loading state
        $('#ttpCreditsBox').addClass('loading');
        
        $.post(ajaxurl, { action: 'ttp_fetch_credits', nonce: ajaxNonce }, function(r) {
            $('#ttpCreditsBox').removeClass('loading');
            
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
            $('#ttpCreditsBox').removeClass('loading');
            showCreditsError('Connection failed');
        });
        
        function updateCreditsDisplay(creditsValue, fullData) {
            // Parse credits - remove commas if string
            var credits = typeof creditsValue === 'string' 
                ? parseInt(creditsValue.replace(/,/g, ''), 10) 
                : parseInt(creditsValue, 10);
            
            if (isNaN(credits)) credits = 0;
            
            var $box = $('#ttpCreditsBox');
            var $icon = $('#ttpCreditsIcon');
            var $title = $('#ttpCreditsTitle');
            var $amount = $('#ttpCreditsAmount');
            var $unit = $('#ttpCreditsUnit');
            var $label = $('#ttpCreditsLabel');
            var $warning = $('#ttpCreditsWarning');
            var $button = $('#ttpCreditsButton');
            var $hint = $('#ttpCreditsHint');
            
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
            var $box = $('#ttpCreditsBox');
            $box.removeClass('loading').addClass('error');
            $('#ttpCreditsIcon').text('⚡');
            $('#ttpCreditsTitle').text('Credits');
            $('#ttpCreditsAmount').text('—');
            $('#ttpCreditsUnit').html('<button class="retry-btn" onclick="location.reload()">↻ Retry</button>');
            $('#ttpCreditsLabel').text(message);
            $('#ttpCreditsWarning').removeClass('visible').html('');
            $('#ttpCreditsHint').text('');
            $('#ttpCreditsButton').text('Buy More →');
        }
        
        // === AGENTS ===
        function fetchAgents() {
            $.post(ajaxurl, { action: 'ttp_fetch_agents', nonce: ajaxNonce }, function(r) {
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
                if (selectedId === 'none' || !selectedId) {
                    $('#agentSettings').addClass('collapsed').hide();
                } else {
                    $('#agentSettings').removeClass('collapsed').show();
                    // Save the selection via AJAX
                    $.post(ajaxurl, {
                        action: 'ttp_save_agent_selection',
                        nonce: ajaxNonce,
                        agent_id: selectedId,
                        agent_name: $(this).find('option:selected').text()
                    });
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
            $('#ttp_override_prompt').val(agentPrompt);
            
            var firstMessage = config.firstMessage || '';
            $('#ttp_override_first_message').val(firstMessage);
            
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
                $('#ttp_override_language').val(lang);
                populateVoicesDropdown(lang);
            }
            
            // Then set voice (after dropdown is populated)
            $('#ttp_override_voice').val(voiceId);
            $('#ttp_override_voice_speed').val(voiceSpeed);
            
            $('#ttp_override_temperature').val(config.temperature || '0.7');
            $('#ttp_override_max_tokens').val(config.maxTokens || '1000');
            $('#ttp_override_max_call_duration').val(config.maxCallDuration || '300');
        }
        
        function autoSaveSettings(agentId, agentName) {
            if (!agentId) return;
            var $notice = $('<div class="notice notice-info" id="ttp-autosave-notice" style="margin: 10px 0; padding: 10px;"><p>⏳ Auto-saving...</p></div>');
            $('.ttp-admin-wrap .wp-header').after($notice);
            
            $.post(ajaxurl, { action: 'ttp_save_agent_selection', nonce: ajaxNonce, agent_id: agentId, agent_name: agentName }, function(r) {
                if (r.success) {
                    $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-success').html('<p>✅ Saved! Reloading...</p>');
                    setTimeout(function() { window.location.reload(); }, 300);
                } else {
                    $('#ttp-autosave-notice').removeClass('notice-info').addClass('notice-error').html('<p>❌ Failed. Save manually.</p>');
                }
            });
        }
        
        // === VOICES ===
        function fetchVoices(callback) {
            $.post(ajaxurl, { action: 'ttp_fetch_voices', nonce: ajaxNonce }, function(r) {
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
                
                var $lang = $('#ttp_override_language');
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
            var $v = $('#ttp_override_voice');
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
        
        $('#ttp_override_voice').on('change', function() {
            var speed = $(this).find('option:selected').data('default-speed');
            if (speed) $('#ttp_override_voice_speed').val(speed);
        });
        
        // === GENERATE PROMPT ===
        $('#ttpGeneratePrompt').on('click', function() {
            var $btn = $(this), $ta = $('#ttp_override_prompt');
            if ($ta.val().trim() !== '' && !confirm('Replace current prompt?')) return;
            
            $btn.prop('disabled', true).text('Generating...');
            
            $.post(ajaxurl, { action: 'ttp_generate_prompt', nonce: ajaxNonce }, function(r) {
                if (r.success && r.data.prompt) {
                    $ta.val(r.data.prompt).css('background-color', '#e8f5e9');
                    setTimeout(function() { $ta.css('background-color', ''); }, 2000);
                } else {
                    alert('Error: ' + (r.data?.message || 'Failed to generate prompt'));
                }
                $btn.prop('disabled', false).text('🔄 Generate from Site Content');
            });
        });
        
        // === CREATE AGENT ===
        $('#createAgentBtn').on('click', function() {
            var name = $('#newAgentName').val().trim();
            if (!name) { alert('Enter agent name'); return; }
            var $btn = $(this).prop('disabled', true).text('Creating...');
            
            $.post(ajaxurl, {
                action: 'ttp_create_agent',
                nonce: ajaxNonce,
                agent_name: name,
                auto_generate_prompt: 'true'
            }, function(r) {
                if (r.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (r.data?.message || 'Failed to create agent'));
                }
                $btn.prop('disabled', false).text('Create Agent');
            });
        });
        
        // === SAVE AGENT SETTINGS ===
        // Saves to both WordPress (for fast UI cache) and TalkToPC backend (actual DB)
        $('#saveAgentSettingsBtn').on('click', function() {
            var $btn = $(this);
            var $status = $('#agentSaveStatus');
            var selectedAgentId = $('#defaultAgentSelect').val();
            
            if (!selectedAgentId || selectedAgentId === 'none') {
                alert('Please select an agent first');
                return;
            }
            
            // Collect form data
            var formData = {
                system_prompt: $('#ttp_override_prompt').val(),
                first_message: $('#ttp_override_first_message').val(),
                voice_id: $('#ttp_override_voice').val(),
                voice_speed: $('#ttp_override_voice_speed').val(),
                language: $('#ttp_override_language').val(),
                temperature: $('#ttp_override_temperature').val(),
                max_tokens: $('#ttp_override_max_tokens').val(),
                max_call_duration: $('#ttp_override_max_call_duration').val()
            };
            
            $btn.prop('disabled', true).text('Saving...');
            $status.text('').removeClass('saved error');
            
            // Step 1: Save to WordPress options (for fast UI cache)
            $.post(ajaxurl, {
                action: 'ttp_save_agent_settings_local',
                nonce: ajaxNonce,
                ...formData
            }, function(localResult) {
                // Step 2: Save to TalkToPC backend (actual DB)
                $.post(ajaxurl, {
                    action: 'ttp_update_agent',
                    nonce: ajaxNonce,
                    agent_id: selectedAgentId,
                    ...formData
                }, function(r) {
                    if (r.success) {
                        $status.text('✓ Saved to TalkToPC').addClass('saved');
                        setTimeout(function() {
                            $status.text('');
                        }, 3000);
                    } else {
                        $status.text('⚠ Local saved, backend failed: ' + (r.data?.message || 'Unknown error')).addClass('error');
                    }
                    $btn.prop('disabled', false).text('Save Agent Settings');
                }).fail(function() {
                    $status.text('⚠ Local saved, backend unreachable').addClass('error');
                    $btn.prop('disabled', false).text('Save Agent Settings');
                });
            }).fail(function() {
                $status.text('✗ Failed to save').addClass('error');
                $btn.prop('disabled', false).text('Save Agent Settings');
            });
        });
    });
    
    function toggleAgentSettings() {
        var el = document.getElementById('agentSettings');
        el.classList.toggle('collapsed');
        if (el.classList.contains('collapsed')) {
            el.style.display = 'none';
        } else {
            el.style.display = 'block';
        }
    }
    </script>
    <?php
}