/**
 * Widget Customization Live Preview
 * 
 * Handles live preview and customization controls
 */

(function($) {
    'use strict';
    
    // Get settings from PHP
    const widgetConfig = talktopcWidgetSettings.settings || {};
    let selectedElement = null;
    let currentView = 'landing'; // 'landing', 'text', 'voice'
    let panelOpen = true;
    let historyExpanded = false; // Track conversation history state
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // Show deployment version info in console
        console.log('%c🚀 TalkToPC Widget Customization', 'color: #7C3AED; font-size: 16px; font-weight: bold;');
        console.log('%cJavaScript File Version: 1.9.96-fixed-docker-v2', 'color: #10B981; font-size: 12px;');
        console.log('%cIf you see this, the JavaScript file is loaded!', 'color: #6B7280; font-size: 11px;');
        console.log('Testing PHP handler deployment...');
        
        initMockWidget();
        setupEventListeners();
        
        // Test PHP handler on page load
        testPHPHandler();
    });
    
    function testPHPHandler() {
        if (!talktopcWidgetSettings || !talktopcWidgetSettings.ajaxUrl) {
            console.warn('⚠️ TalkToPC: Cannot test PHP handler - settings not loaded');
            return;
        }
        
        $.ajax({
            url: talktopcWidgetSettings.ajaxUrl,
            type: 'POST',
            data: {
                action: 'talktopc_test_handler'
            },
            success: function(response) {
                if (response.success && response.data) {
                    const version = response.data.version || 'unknown';
                    const fileMtime = response.data.file_mtime_formatted || 
                        (response.data.file_mtime > 0 ? new Date(response.data.file_mtime * 1000).toLocaleString() : 'Unknown');
                    
                    console.log('%c✅ PHP Handler Deployed!', 'color: #10B981; font-size: 14px; font-weight: bold;');
                    console.log('Version:', version);
                    console.log('File modified:', fileMtime);
                    console.log('File path:', response.data.file);
                    console.log('Full response:', response.data);
                    
                    // Check if version matches expected
                    if (version.includes('fixed-docker')) {
                        console.log('%c✅ Version matches! File is up to date.', 'color: #10B981; font-weight: bold;');
                    } else {
                        console.warn('%c⚠️ Version mismatch! Expected "fixed-docker" but got:', 'color: #F59E0B; font-weight: bold;', version);
                    }
                    
                    // Show visual indicator
                    showDeploymentStatus(true, version, fileMtime);
                } else {
                    console.warn('⚠️ PHP Handler test returned unexpected response:', response);
                    showDeploymentStatus(false, 'Unknown', 'Unknown');
                }
            },
            error: function(xhr, status, error) {
                console.error('%c❌ PHP Handler Test Failed', 'color: #EF4444; font-size: 14px; font-weight: bold;');
                console.error('Status:', status, 'Error:', error);
                console.error('Response:', xhr.responseText);
                showDeploymentStatus(false, 'Error: ' + error);
            }
        });
    }
    
    function showDeploymentStatus(success, version, fileMtime) {
        const statusHTML = success 
            ? `<div style="background: #D1FAE5; border-left: 4px solid #10B981; padding: 12px; margin: 10px 0; border-radius: 4px;">
                <strong style="color: #065F46;">✅ PHP File Deployed</strong>
                <div style="color: #047857; font-size: 12px; margin-top: 4px;">
                    <div>Version: <strong>${version}</strong></div>
                    <div style="margin-top: 4px;">Modified: ${fileMtime}</div>
                </div>
               </div>`
            : `<div style="background: #FEE2E2; border-left: 4px solid #EF4444; padding: 12px; margin: 10px 0; border-radius: 4px;">
                <strong style="color: #991B1B;">❌ Deployment Check Failed</strong>
                <div style="color: #B91C1C; font-size: 12px; margin-top: 4px;">${version}</div>
               </div>`;
        
        // Add to the top of customization panel
        const $panel = $('.talktopc-customization-panel');
        if ($panel.length) {
            const $existingStatus = $panel.find('.deployment-status');
            if ($existingStatus.length) {
                $existingStatus.replaceWith(statusHTML);
            } else {
                $panel.prepend('<div class="deployment-status">' + statusHTML + '</div>');
            }
        }
    }
    
    function initMockWidget() {
        const mockButton = $('#mockButton');
        const mockPanel = $('#mockPanel');
        
        // Apply button styles
        applyButtonStyles(mockButton[0]);
        
        // Open panel by default
        mockPanel.addClass('open');
        
        // Setup button click handler
        mockButton.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleElementClick('button', this, e);
        });
        
        // Render initial panel content
        renderPanelContent();
        
        // Show default customization controls
        showCustomizationControls('default');
    }
    
    function applyButtonStyles(button) {
        const config = widgetConfig.button || {};
        const $button = $(button);
        
        const sizeMap = { small: 48, medium: 60, large: 72, 'extra-large': 84 };
        const size = sizeMap[config.size] || 60;
        
        $button.css({
            width: size + 'px',
            height: size + 'px',
            backgroundColor: config.backgroundColor || '#FFFFFF',
            borderRadius: config.shape === 'circle' ? '50%' : config.shape === 'rounded' ? '12px' : '0',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)'
        });
        
        // Apply icon
        const iconConfig = widgetConfig.icon || {};
        $button.empty();
        
        if (iconConfig.type === 'custom') {
            const imageUrl = iconConfig.customImage || 'https://talktopc.com/logo192.png';
            const img = $('<img>').attr('src', imageUrl).attr('alt', 'Chat Assistant');
            const iconSize = Math.floor(size * 0.6);
            img.css({
                width: iconSize + 'px',
                height: iconSize + 'px',
                objectFit: 'contain'
            });
            $button.append(img);
        } else if (iconConfig.type === 'emoji' && iconConfig.emoji) {
            $button.text(iconConfig.emoji);
        } else if (iconConfig.type === 'text' && iconConfig.text) {
            $button.text(iconConfig.text);
        } else {
            // Default to custom image if type is not set
            const img = $('<img>').attr('src', 'https://talktopc.com/logo192.png').attr('alt', 'Chat Assistant');
            const iconSize = Math.floor(size * 0.6);
            img.css({
                width: iconSize + 'px',
                height: iconSize + 'px',
                objectFit: 'contain'
            });
            $button.append(img);
        }
        
        button.dataset.elementType = 'button';
    }
    
    function renderPanelContent() {
        const mockPanel = $('#mockPanel');
        
        // Apply panel styles
        const panelConfig = widgetConfig.panel || {};
        mockPanel.css({
            width: (panelConfig.width || 360) + 'px',
            height: (panelConfig.height || 550) + 'px',
            borderRadius: (panelConfig.borderRadius || 12) + 'px',
            backgroundColor: panelConfig.backgroundColor || '#FFFFFF',
            border: panelConfig.border || '1px solid #E5E7EB'
        });
        
        // Render based on current view
        if (currentView === 'landing') {
            renderLandingScreen(mockPanel);
        } else if (currentView === 'text') {
            renderTextInterface(mockPanel);
        } else if (currentView === 'voice') {
            renderVoiceInterface(mockPanel);
        }
        
        // Add panel selector
        addPanelSelector();
    }
    
    function renderLandingScreen($panel) {
        const config = widgetConfig.landing || {};
        const headerConfig = widgetConfig.header || {};
        
        const html = `
            <div class="mock-panel-header" style="background: ${headerConfig.backgroundColor || '#7C3AED'}; color: ${headerConfig.textColor || '#FFFFFF'};" data-element-type="header">
                <span>${headerConfig.title || 'Chat Assistant'}</span>
                ${headerConfig.showCloseButton ? '<button class="mock-panel-close" data-element-type="closeButton">×</button>' : ''}
            </div>
            <div class="mock-panel-content">
                <div class="mock-landing-screen">
                    <div class="mock-landing-logo" data-element-type="landingLogo">
                        ${config.logoType === 'image' && config.logoImageUrl ? `
                            <img src="${config.logoImageUrl}" alt="Logo">
                        ` : `
                            <span>${config.logoIcon || config.logo || '🤖'}</span>
                        `}
                    </div>
                    <div class="mock-landing-title" style="color: ${config.titleColor || '#1e293b'};" data-element-type="landingTitle">${config.title || 'Welcome to AI Assistant'}</div>
                    <div class="mock-landing-subtitle" style="color: ${config.subtitleColor || '#64748b'};" data-element-type="landingSubtitle">${config.subtitle || 'Choose how you\'d like to interact'}</div>
                    <div class="mock-mode-cards">
                        <div class="mock-mode-card" style="background: ${config.modeCardBackgroundColor || '#FFFFFF'};" data-element-type="modeCard" data-mode="voice">
                            <div class="mock-mode-icon">🎤</div>
                            <div class="mock-mode-title">${config.voiceCardTitle || 'Voice Call'}</div>
                            <div class="mock-mode-desc">${config.voiceCardDesc || 'Start a voice conversation'}</div>
                        </div>
                        <div class="mock-mode-card" style="background: ${config.modeCardBackgroundColor || '#FFFFFF'};" data-element-type="modeCard" data-mode="text">
                            <div class="mock-mode-icon">💬</div>
                            <div class="mock-mode-title">${config.textCardTitle || 'Text Chat'}</div>
                            <div class="mock-mode-desc">${config.textCardDesc || 'Chat via text messages'}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $panel.html(html);
        setupElementListeners($panel);
    }
    
    function renderTextInterface($panel) {
        const config = widgetConfig.text || {};
        const headerConfig = widgetConfig.header || {};
        const msgConfig = widgetConfig.messages || {};
        
        const html = `
            <div class="mock-panel-header" style="background: ${headerConfig.backgroundColor || '#7C3AED'}; color: ${headerConfig.textColor || '#FFFFFF'};" data-element-type="header">
                <span>${headerConfig.title || 'Chat Assistant'}</span>
                ${headerConfig.showCloseButton ? '<button class="mock-panel-close" data-element-type="closeButton">×</button>' : ''}
            </div>
            <div class="mock-text-interface">
                <div class="mock-messages">
                    <div class="mock-message user" style="background: ${msgConfig.userBackgroundColor || '#E5E7EB'}; color: ${msgConfig.textColor || '#1F2937'}; border-radius: ${msgConfig.borderRadius || 16}px; font-size: ${msgConfig.fontSize || '14px'};" data-element-type="userMessage">
                        Hello! How can I help you?
                    </div>
                    <div class="mock-message agent" style="background: ${msgConfig.agentBackgroundColor || '#F3F4F6'}; color: ${msgConfig.textColor || '#1F2937'}; border-radius: ${msgConfig.borderRadius || 16}px; font-size: ${msgConfig.fontSize || '14px'};" data-element-type="agentMessage">
                        Hi! I'm here to assist you. What would you like to know?
                    </div>
                </div>
                <div class="mock-input-area">
                    <input type="text" class="mock-input" placeholder="${config.inputPlaceholder || 'Type your message...'}" style="border-color: ${config.inputFocusColor || '#7C3AED'};" data-element-type="input">
                    <button class="mock-send-button" style="background: ${config.sendButtonColor || '#7C3AED'};" data-element-type="sendButton">${config.sendButtonText || '→'}</button>
                </div>
            </div>
        `;
        
        $panel.html(html);
        setupElementListeners($panel);
    }
    
    function renderVoiceInterface($panel) {
        // Update config first to get latest values
        updateWidgetConfig();
        const config = widgetConfig.voice || {};
        const headerConfig = widgetConfig.header || {};
        const textConfig = widgetConfig.text || {};
        
        const html = `
            <div class="mock-panel-header" style="background: ${headerConfig.backgroundColor || '#7C3AED'}; color: ${headerConfig.textColor || '#FFFFFF'};" data-element-type="header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>${headerConfig.title || 'Chat Assistant'}</span>
                    <div style="display: flex; align-items: center; gap: 6px; margin-left: 8px;">
                        <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></div>
                        <span style="font-size: 12px; opacity: 0.9;">Online</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button style="background: none; border: none; color: ${headerConfig.textColor || '#FFFFFF'}; cursor: pointer; padding: 4px; display: flex; align-items: center;" data-element-type="backButton">←</button>
                    ${headerConfig.showCloseButton ? '<button class="mock-panel-close" data-element-type="closeButton">×</button>' : ''}
                </div>
            </div>
            <div class="mock-voice-interface">
                <!-- Voice Section - Multi-row layout when history is collapsed -->
                <div class="mock-voice-section" id="mockVoiceSectionExpanded">
                    <div class="mock-voice-timer" data-element-type="timer" style="color: ${config.timerTextColor || '#64748b'};">
                        <div class="mock-timer-dot" style="background: ${config.timerDotColor || '#ef4444'};"></div>
                        <span>00:11</span>
                    </div>
                    <div class="mock-waveform" data-element-type="waveform">
                        ${config.waveformType === 'waveform' ? Array(15).fill(0).map(() => '<div class="mock-waveform-bar"></div>').join('') : config.waveformType === 'icon' ? `
                            <div class="mock-waveform-icon" style="font-size: 48px; line-height: 1;">${config.waveformIcon || '🎤'}</div>
                        ` : config.waveformType === 'image' && config.waveformImageUrl ? `
                            <img src="${config.waveformImageUrl}" alt="Waveform" class="mock-waveform-image" style="max-width: 60px; max-height: 60px; object-fit: contain;">
                        ` : Array(15).fill(0).map(() => '<div class="mock-waveform-bar"></div>').join('')}
                    </div>
                    <div class="mock-voice-status" data-element-type="statusTitle" style="color: ${config.statusTitleColor || '#64748b'};">
                        <div class="mock-status-dot" style="background: ${config.statusDotColor || '#10b981'};"></div>
                        <span>${config.statusText || 'Listening...'}</span>
                    </div>
                    <div class="mock-voice-controls">
                        <button class="mock-control-btn secondary" data-element-type="micButton" title="Mute">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                <line x1="12" y1="19" x2="12" y2="23"/>
                            </svg>
                        </button>
                        <button class="mock-control-btn danger" data-element-type="endCallButton" title="End Call">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" transform="rotate(135 12 12)"/>
                            </svg>
                        </button>
                        <button class="mock-control-btn secondary" data-element-type="speakerButton" title="Speaker">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                <path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Single-row compact layout when history is expanded -->
                <div class="mock-voice-section-compact" id="mockVoiceSectionCompact" style="display: none;">
                    <div class="mock-compact-row">
                        <div class="mock-compact-waveform" data-element-type="waveform">
                            ${config.waveformType === 'waveform' ? Array(15).fill(0).map(() => '<div class="mock-waveform-bar"></div>').join('') : config.waveformType === 'icon' ? `
                                <div class="mock-waveform-icon" style="font-size: 24px; line-height: 1;">${config.waveformIcon || '🎤'}</div>
                            ` : config.waveformType === 'image' && config.waveformImageUrl ? `
                                <img src="${config.waveformImageUrl}" alt="Waveform" class="mock-waveform-image" style="max-width: 32px; max-height: 32px; object-fit: contain;">
                            ` : Array(15).fill(0).map(() => '<div class="mock-waveform-bar"></div>').join('')}
                        </div>
                        <div class="mock-compact-timer" data-element-type="timer" style="color: ${config.timerTextColor || '#64748b'};">
                            <div class="mock-timer-dot" style="background: ${config.timerDotColor || '#ef4444'};"></div>
                            <span>00:11</span>
                        </div>
                        <div class="mock-compact-status" data-element-type="statusTitle" style="color: ${config.statusTitleColor || '#10b981'};">
                            <div class="mock-status-dot" style="background: ${config.statusDotColor || '#10b981'};"></div>
                            <span>${config.statusText || 'Listening...'}</span>
                        </div>
                        <div class="mock-compact-controls">
                            <button class="mock-control-btn secondary" data-element-type="micButton" title="Mute">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                    <line x1="12" y1="19" x2="12" y2="23"/>
                                </svg>
                            </button>
                            <button class="mock-control-btn danger" data-element-type="endCallButton" title="End Call">
                                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" transform="rotate(135 12 12)"/>
                                </svg>
                            </button>
                            <button class="mock-control-btn secondary" data-element-type="speakerButton" title="Speaker">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                    <path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Conversation Section -->
                <div class="mock-conversation-section">
                    <div class="mock-conversation-header">
                        <span>CONVERSATION</span>
                        <div class="mock-conversation-toggle" data-element-type="conversationToggle">
                            <span id="historyToggleText">Show history</span>
                            <svg id="historyToggleIcon" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Collapsed: Live transcript only -->
                    <div class="mock-live-transcript-collapsed" id="collapsedTranscript" data-element-type="liveTranscript">
                        <div class="mock-live-indicator" data-element-type="liveIndicator" style="color: ${config.liveTextColor || '#10b981'};">
                            <div class="mock-live-dot" style="background: ${config.liveDotColor || '#10b981'};"></div>
                            <span>LIVE</span>
                        </div>
                        <div class="mock-live-text-collapsed" data-element-type="liveTranscriptText" style="color: ${config.liveTranscriptColor || '#64748b'}; font-size: ${config.liveTranscriptFontSize || '14px'}; line-height: 1.6; margin-top: 8px;">
                            Hello, I'm Sasha from Bridgewise, How can I help you today?
                        </div>
                    </div>
                    
                    <!-- Expanded: Full conversation history -->
                    <div class="mock-conversation-history" id="expandedHistory" style="display: none;">
                        <div class="mock-history-message">
                            <div class="mock-history-avatar" data-element-type="agentAvatar" style="background: ${config.avatarBackgroundColor || '#667eea'};">
                                ${config.avatarType === 'image' && config.avatarImageUrl ? `
                                    <img src="${config.avatarImageUrl}" alt="Agent" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                ` : `
                                    <span>${config.avatarIcon || '🤖'}</span>
                                `}
                            </div>
                            <div class="mock-history-bubble">I help you today?</div>
                        </div>
                        <div class="mock-history-message">
                            <div class="mock-history-avatar" data-element-type="agentAvatar" style="background: ${config.avatarBackgroundColor || '#667eea'};">
                                ${config.avatarType === 'image' && config.avatarImageUrl ? `
                                    <img src="${config.avatarImageUrl}" alt="Agent" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                ` : `
                                    <span>${config.avatarIcon || '🤖'}</span>
                                `}
                            </div>
                            <div class="mock-history-bubble">I am doing well, thank you for asking.</div>
                        </div>
                        <div class="mock-live-message-row">
                            <div class="mock-history-avatar" data-element-type="agentAvatar" style="background: ${config.avatarBackgroundColor || '#667eea'};">
                                ${config.avatarType === 'image' && config.avatarImageUrl ? `
                                    <img src="${config.avatarImageUrl}" alt="Agent" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                ` : `
                                    <span>${config.avatarIcon || '🤖'}</span>
                                `}
                            </div>
                            <div class="mock-history-bubble">
                                <span class="mock-live-badge">LIVE</span>
                                How may I assist you with the website today?
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Text Input Area -->
                <div class="mock-voice-input-area">
                    <div class="mock-voice-input-wrapper">
                        <input type="text" class="mock-voice-text-input" placeholder="${textConfig.inputPlaceholder || 'Type your message...'}" data-element-type="voiceInput">
                        <button class="mock-voice-send-btn" style="background: ${textConfig.sendButtonColor || '#7C3AED'};" data-element-type="voiceSendButton">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Powered By -->
                <div class="mock-powered-by">
                    <span>⚡</span>
                    <span>Powered by <strong>TalkToPC</strong></span>
                </div>
            </div>
        `;
        
        $panel.html(html);
        setupElementListeners($panel);
        
        // Initialize history view state
        setTimeout(function() {
            updateHistoryView();
        }, 0);
    }
    
    function updateHistoryView() {
        const $collapsed = $('#collapsedTranscript');
        const $expanded = $('#expandedHistory');
        const $toggleText = $('#historyToggleText');
        const $toggleIcon = $('#historyToggleIcon');
        const $expandedSection = $('#mockVoiceSectionExpanded');
        const $compactSection = $('#mockVoiceSectionCompact');
        
        if (!$collapsed.length || !$expanded.length || !$toggleText.length || !$toggleIcon.length) {
            // Elements not found yet, try again after a short delay
            setTimeout(updateHistoryView, 100);
            return;
        }
        
        if (historyExpanded) {
            // Hide collapsed transcript
            $collapsed.hide();
            // Show expanded history
            $expanded.show().addClass('expanded');
            // Switch to compact single-row layout
            if ($expandedSection.length) $expandedSection.hide();
            if ($compactSection.length) $compactSection.show();
            // Update toggle text and icon (upward caret)
            $toggleText.text('Hide history');
            $toggleIcon.find('path').attr('d', 'M18 15l-6-6-6 6');
        } else {
            // Show collapsed transcript
            $collapsed.show();
            // Hide expanded history
            $expanded.hide().removeClass('expanded');
            // Switch to multi-row expanded layout
            if ($expandedSection.length) $expandedSection.show();
            if ($compactSection.length) $compactSection.hide();
            // Update toggle text and icon (downward caret)
            $toggleText.text('Show history');
            $toggleIcon.find('path').attr('d', 'M6 9l6 6 6-6');
        }
    }
    
    function toggleConversationHistory() {
        historyExpanded = !historyExpanded;
        updateHistoryView();
    }
    
    function setupElementListeners($panel) {
        $panel.find('[data-element-type]').each(function() {
            const $el = $(this);
            const elementType = $el.data('element-type');
            
            // Remove existing handlers
            $el.off('click.customization');
            
            // Add click handler
            $el.on('click.customization', function(e) {
                e.stopPropagation();
                handleElementClick(elementType, this, e);
            });
        });
    }
    
    function handleElementClick(elementType, element, event) {
        const currentTime = Date.now();
        const lastClick = $(element).data('lastClick') || 0;
        const timeSinceLastClick = currentTime - lastClick;
        
        $(element).data('lastClick', currentTime);
        
        if (timeSinceLastClick < 300) {
            // Double click - perform normal interaction
            performNormalInteraction(elementType, element, event);
        } else {
            // Single click - select for customization
            setTimeout(function() {
                const newLastClick = $(element).data('lastClick') || 0;
                if (currentTime === newLastClick) {
                    selectElement(elementType, element);
                }
            }, 300);
        }
    }
    
    function performNormalInteraction(elementType, element, event) {
        if (elementType === 'modeCard') {
            const mode = $(element).data('mode');
            if (mode === 'text') {
                currentView = 'text';
            } else if (mode === 'voice') {
                currentView = 'voice';
            }
            renderPanelContent();
        } else if (elementType === 'closeButton' || elementType === 'backButton') {
            currentView = 'landing';
            renderPanelContent();
        } else if (elementType === 'conversationToggle') {
            // Toggle conversation history
            toggleConversationHistory();
        }
    }
    
    function selectElement(elementType, element) {
        // Remove previous highlights
        $('.element-highlight').removeClass('element-highlight');
        
        // Add highlight to selected element
        $(element).addClass('element-highlight');
        selectedElement = element;
        
        // Show customization controls
        showCustomizationControls(elementType);
    }
    
    function showCustomizationControls(elementType) {
        const controlsDiv = $('#customizationControls');
        let controlsHTML = '';
        
        switch(elementType) {
            case 'button':
                controlsHTML = getButtonControls();
                break;
            case 'header':
                controlsHTML = getHeaderControls();
                break;
            case 'panel':
            case 'position':
                controlsHTML = getPanelControls();
                break;
            case 'direction':
                controlsHTML = getDirectionControls();
                break;
            case 'userMessage':
            case 'agentMessage':
                controlsHTML = getMessageControls();
                break;
            case 'sendButton':
                controlsHTML = getSendButtonControls();
                break;
            case 'startCallButton':
                controlsHTML = getStartCallButtonControls();
                break;
            case 'micButton':
                controlsHTML = getMicButtonControls();
                break;
            case 'endCallButton':
                controlsHTML = getEndCallButtonControls();
                break;
            case 'speakerButton':
                controlsHTML = getSpeakerButtonControls();
                break;
            case 'timer':
                controlsHTML = getTimerControls();
                break;
            case 'waveform':
                controlsHTML = getWaveformControls();
                break;
            case 'statusTitle':
                controlsHTML = getStatusTitleControls();
                break;
            case 'liveTranscript':
            case 'liveTranscriptText':
                controlsHTML = getLiveTranscriptControls();
                break;
            case 'voiceInput':
            case 'voiceSendButton':
                controlsHTML = getVoiceInputControls();
                break;
            case 'voiceAvatar':
                controlsHTML = getVoiceAvatarControls();
                break;
            case 'conversationHistory':
                controlsHTML = getConversationHistoryControls();
                break;
            case 'agentAvatar':
                controlsHTML = getAgentAvatarControls();
                break;
            case 'liveIndicator':
                controlsHTML = getLiveIndicatorControls();
                break;
            case 'landingLogo':
                controlsHTML = getLandingLogoControls();
                break;
            case 'landingTitle':
                controlsHTML = getLandingTitleControls();
                break;
            case 'landingSubtitle':
                controlsHTML = getLandingSubtitleControls();
                break;
            case 'modeCard':
                controlsHTML = getModeCardControls();
                break;
            default:
                controlsHTML = getDefaultControls();
        }
        
        controlsDiv.html(controlsHTML);
        attachControlListeners(elementType);
    }
    
    function getButtonControls() {
        const config = widgetConfig.button || {};
        const iconConfig = widgetConfig.icon || {};
        
        return `
            <div class="customization-group">
                <h3>Floating Button</h3>
                <div class="control-item">
                    <label>Size</label>
                    <select id="btnSize" name="talktopc_button_size">
                        <option value="small" ${config.size === 'small' ? 'selected' : ''}>Small</option>
                        <option value="medium" ${(!config.size || config.size === 'medium') ? 'selected' : ''}>Medium</option>
                        <option value="large" ${config.size === 'large' ? 'selected' : ''}>Large</option>
                        <option value="extra-large" ${config.size === 'extra-large' ? 'selected' : ''}>Extra Large</option>
                    </select>
                </div>
                <div class="control-item">
                    <label>Shape</label>
                    <select id="btnShape" name="talktopc_button_shape">
                        <option value="circle" ${(!config.shape || config.shape === 'circle') ? 'selected' : ''}>Circle</option>
                        <option value="rounded" ${config.shape === 'rounded' ? 'selected' : ''}>Rounded</option>
                        <option value="square" ${config.shape === 'square' ? 'selected' : ''}>Square</option>
                    </select>
                </div>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="btnBgColor" name="talktopc_button_bg_color" class="talktopc-color-picker" value="${config.backgroundColor || '#FFFFFF'}">
                </div>
                <div class="control-item">
                    <label>Hover Color</label>
                    <input type="text" id="btnHoverColor" name="talktopc_button_hover_color" class="talktopc-color-picker" value="${config.hoverColor || '#D3D3D3'}">
                </div>
            </div>
            <div class="customization-group">
                <h3>Icon</h3>
                <div class="control-item">
                    <label>Type</label>
                    <select id="iconType" name="talktopc_icon_type">
                        <option value="custom" ${(!iconConfig.type || iconConfig.type === 'custom') ? 'selected' : ''}>Custom Image</option>
                        <option value="emoji" ${iconConfig.type === 'emoji' ? 'selected' : ''}>Emoji</option>
                        <option value="text" ${iconConfig.type === 'text' ? 'selected' : ''}>Text</option>
                    </select>
                </div>
                <div class="control-item" id="iconCustomImageControl" style="display: ${(!iconConfig.type || iconConfig.type === 'custom') ? 'block' : 'none'};">
                    <label>Image URL</label>
                    <input type="text" id="iconCustomImage" name="talktopc_icon_custom_image" value="${iconConfig.customImage || 'https://talktopc.com/logo192.png'}" placeholder="https://talktopc.com/logo192.png">
                </div>
                <div class="control-item" id="iconEmojiControl" style="display: ${iconConfig.type === 'emoji' ? 'block' : 'none'};">
                    <label>Emoji</label>
                    <input type="text" id="iconEmoji" name="talktopc_icon_emoji" value="${iconConfig.emoji || '🎤'}" placeholder="🎤">
                </div>
                <div class="control-item" id="iconTextControl" style="display: ${iconConfig.type === 'text' ? 'block' : 'none'};">
                    <label>Text</label>
                    <input type="text" id="iconText" name="talktopc_icon_text" value="${iconConfig.text || 'AI'}" placeholder="AI">
                </div>
            </div>
        `;
    }
    
    function getHeaderControls() {
        const config = widgetConfig.header || {};
        
        return `
            <div class="customization-group">
                <h3>Header</h3>
                <div class="control-item">
                    <label>Title Text</label>
                    <input type="text" id="headerTitle" name="talktopc_header_title" value="${config.title || 'Chat Assistant'}" placeholder="Chat Assistant">
                </div>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="headerBgColor" name="talktopc_header_bg_color" class="talktopc-color-picker" value="${config.backgroundColor || '#7C3AED'}">
                </div>
                <div class="control-item">
                    <label>Text Color</label>
                    <input type="text" id="headerTextColor" name="talktopc_header_text_color" class="talktopc-color-picker" value="${config.textColor || '#FFFFFF'}">
                </div>
                <div class="control-item">
                    <label>
                        <input type="checkbox" id="headerShowClose" name="talktopc_header_show_close" value="1" ${config.showCloseButton ? 'checked' : ''}>
                        Show Close Button
                    </label>
                </div>
            </div>
        `;
    }
    
    function getPanelControls() {
        const config = widgetConfig.panel || {};
        const position = widgetConfig.position || {};
        
        return `
            <div class="customization-group">
                <h3>Panel</h3>
                <div class="control-item">
                    <label>Width (px)</label>
                    <input type="number" id="panelWidth" name="talktopc_panel_width" value="${config.width || 350}">
                </div>
                <div class="control-item">
                    <label>Height (px)</label>
                    <input type="number" id="panelHeight" name="talktopc_panel_height" value="${config.height || 550}">
                </div>
                <div class="control-item">
                    <label>Border Radius (px)</label>
                    <input type="number" id="panelRadius" name="talktopc_panel_border_radius" value="${config.borderRadius || 12}">
                </div>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="panelBgColor" name="talktopc_panel_bg_color" class="talktopc-color-picker" value="${config.backgroundColor || '#FFFFFF'}">
                </div>
            </div>
            <div class="customization-group">
                <h3>Widget Position</h3>
                <div class="control-item">
                    <label>Position</label>
                    <select id="position" name="talktopc_position">
                        <option value="bottom-right" ${(!widgetConfig.position || (position.vertical === 'bottom' && position.horizontal === 'right')) ? 'selected' : ''}>Bottom Right</option>
                        <option value="bottom-left" ${(position.vertical === 'bottom' && position.horizontal === 'left') ? 'selected' : ''}>Bottom Left</option>
                        <option value="top-right" ${(position.vertical === 'top' && position.horizontal === 'right') ? 'selected' : ''}>Top Right</option>
                        <option value="top-left" ${(position.vertical === 'top' && position.horizontal === 'left') ? 'selected' : ''}>Top Left</option>
                    </select>
                </div>
            </div>
        `;
    }
    
    function getMessageControls() {
        const config = widgetConfig.messages || {};
        
        return `
            <div class="customization-group">
                <h3>Messages</h3>
                <div class="control-item">
                    <label>User Message Background</label>
                    <input type="text" id="msgUserBg" name="talktopc_msg_user_bg" class="talktopc-color-picker" value="${config.userBackgroundColor || '#E5E7EB'}">
                </div>
                <div class="control-item">
                    <label>Agent Message Background</label>
                    <input type="text" id="msgAgentBg" name="talktopc_msg_agent_bg" class="talktopc-color-picker" value="${config.agentBackgroundColor || '#F3F4F6'}">
                </div>
                <div class="control-item">
                    <label>Text Color</label>
                    <input type="text" id="msgTextColor" name="talktopc_msg_text_color" class="talktopc-color-picker" value="${config.textColor || '#1F2937'}">
                </div>
                <div class="control-item">
                    <label>Font Size</label>
                    <input type="text" id="msgFontSize" name="talktopc_msg_font_size" value="${config.fontSize || '14px'}" placeholder="14px">
                </div>
                <div class="control-item">
                    <label>Border Radius (px)</label>
                    <input type="number" id="msgRadius" name="talktopc_msg_border_radius" value="${config.borderRadius || 16}">
                </div>
            </div>
        `;
    }
    
    function getSendButtonControls() {
        const config = widgetConfig.text || {};
        
        return `
            <div class="customization-group">
                <h3>Send Button</h3>
                <div class="control-item">
                    <label>Button Text/Icon</label>
                    <input type="text" id="sendButtonText" name="talktopc_text_send_btn_text" value="${config.sendButtonText || '→'}" placeholder="→ or Send">
                </div>
                <div class="control-item">
                    <label>Color</label>
                    <input type="text" id="sendBtnColor" name="talktopc_text_send_btn_color" class="talktopc-color-picker" value="${config.sendButtonColor || '#7C3AED'}">
                </div>
                <div class="control-item">
                    <label>Hover Color</label>
                    <input type="text" id="sendBtnHover" name="talktopc_text_send_btn_hover_color" class="talktopc-color-picker" value="${config.sendButtonHoverColor || '#6D28D9'}">
                </div>
            </div>
            <div class="customization-group">
                <h3>Input Field</h3>
                <div class="control-item">
                    <label>Placeholder Text</label>
                    <input type="text" id="inputPlaceholder" name="talktopc_text_input_placeholder" value="${config.inputPlaceholder || 'Type your message...'}" placeholder="Type your message...">
                </div>
                <div class="control-item">
                    <label>Focus Color</label>
                    <input type="text" id="inputFocusColor" name="talktopc_text_input_focus_color" class="talktopc-color-picker" value="${config.inputFocusColor || '#7C3AED'}">
                </div>
            </div>
        `;
    }
    
    function getLandingTitleControls() {
        const config = widgetConfig.landing || {};
        
        return `
            <div class="customization-group">
                <h3>Landing Screen - Title</h3>
                <div class="control-item">
                    <label>Title Text</label>
                    <input type="text" id="landingTitleText" name="talktopc_landing_title" value="${config.title || 'Welcome to AI Assistant'}" placeholder="Welcome to AI Assistant">
                </div>
                <div class="control-item">
                    <label>Title Color</label>
                    <input type="text" id="landingTitleColor" name="talktopc_landing_title_color" class="talktopc-color-picker" value="${config.titleColor || '#1e293b'}">
                </div>
            </div>
        `;
    }
    
    function getModeCardControls() {
        const config = widgetConfig.landing || {};
        
        return `
            <div class="customization-group">
                <h3>Mode Cards (Voice/Text Buttons)</h3>
                <div class="control-item">
                    <label>Voice Card Title</label>
                    <input type="text" id="voiceCardTitle" name="talktopc_landing_voice_title" value="${config.voiceCardTitle || 'Voice Call'}" placeholder="Voice Call">
                </div>
                <div class="control-item">
                    <label>Voice Card Description</label>
                    <input type="text" id="voiceCardDesc" name="talktopc_landing_voice_desc" value="${config.voiceCardDesc || 'Start a voice conversation'}" placeholder="Start a voice conversation">
                </div>
                <div class="control-item">
                    <label>Text Card Title</label>
                    <input type="text" id="textCardTitle" name="talktopc_landing_text_title" value="${config.textCardTitle || 'Text Chat'}" placeholder="Text Chat">
                </div>
                <div class="control-item">
                    <label>Text Card Description</label>
                    <input type="text" id="textCardDesc" name="talktopc_landing_text_desc" value="${config.textCardDesc || 'Chat via text messages'}" placeholder="Chat via text messages">
                </div>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="modeCardBg" name="talktopc_landing_card_bg_color" class="talktopc-color-picker" value="${config.modeCardBackgroundColor || '#FFFFFF'}">
                </div>
                <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                    These are the buttons on the landing screen that let users choose between voice and text chat.
                </p>
            </div>
        `;
    }
    
    function getDirectionControls() {
        return `
            <div class="customization-group">
                <h3>Text Direction</h3>
                <div class="control-item">
                    <label>Direction</label>
                    <select id="direction" name="talktopc_direction">
                        <option value="ltr" ${(!widgetConfig.direction || widgetConfig.direction === 'ltr') ? 'selected' : ''}>Left to Right (LTR)</option>
                        <option value="rtl" ${widgetConfig.direction === 'rtl' ? 'selected' : ''}>Right to Left (RTL)</option>
                    </select>
                </div>
                <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                    Text direction for the widget. Use RTL for languages like Arabic or Hebrew.
                </p>
            </div>
        `;
    }
    
    function getStartCallButtonControls() {
        const config = widgetConfig.voice || {};
        
        return `
            <div class="customization-group">
                <h3>Start Call Button</h3>
                <div class="control-item">
                    <label>Button Text</label>
                    <input type="text" id="startCallBtnText" name="talktopc_voice_start_btn_text" value="${config.startCallButtonText || 'Start Call'}" placeholder="Start Call">
                </div>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="startCallBtnColor" name="talktopc_voice_start_btn_color" class="talktopc-color-picker" value="${config.startCallButtonColor || '#667eea'}">
                </div>
                <div class="control-item">
                    <label>Text Color</label>
                    <input type="text" id="startCallBtnTextColor" name="talktopc_voice_start_btn_text_color" class="talktopc-color-picker" value="${config.startCallButtonTextColor || '#FFFFFF'}">
                </div>
            </div>
        `;
    }
    
    function getMicButtonControls() {
        const config = widgetConfig.voice || {};
        
        return `
            <div class="customization-group">
                <h3>Microphone Button</h3>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="micBtnColor" name="talktopc_voice_mic_color" class="talktopc-color-picker" value="${config.micButtonColor || '#7C3AED'}">
                </div>
                <div class="control-item">
                    <label>Active/Muted Color</label>
                    <input type="text" id="micBtnActive" name="talktopc_voice_mic_active_color" class="talktopc-color-picker" value="${config.micButtonActiveColor || '#EF4444'}">
                </div>
            </div>
        `;
    }
    
    function getEndCallButtonControls() {
        return `
            <div class="customization-group">
                <h3>End Call Button</h3>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="endCallBtnColor" name="talktopc_voice_end_btn_color" class="talktopc-color-picker" value="#ef4444">
                </div>
            </div>
        `;
    }
    
    function getSpeakerButtonControls() {
        return `
            <div class="customization-group">
                <h3>Speaker Button</h3>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="speakerBtnColor" name="talktopc_voice_control_btn_secondary_color" class="talktopc-color-picker" value="#FFFFFF">
                </div>
            </div>
        `;
    }
    
    function getTimerControls() {
        return `
            <div class="customization-group">
                <h3>Call Timer</h3>
                <div class="control-item">
                    <label>Timer Dot Color</label>
                    <input type="text" id="timerDotColor" class="talktopc-color-picker" value="#ef4444">
                </div>
                <div class="control-item">
                    <label>Timer Text Color</label>
                    <input type="text" id="timerTextColor" class="talktopc-color-picker" value="#64748b">
                </div>
            </div>
        `;
    }
    
    function getWaveformControls() {
        const config = widgetConfig.voice || {};
        
        return `
            <div class="customization-group">
                <h3>Waveform Visualizer</h3>
                <div class="control-item">
                    <label>Display Type</label>
                    <select id="waveformType">
                        <option value="waveform" ${(!config.waveformType || config.waveformType === 'waveform') ? 'selected' : ''}>Waveform</option>
                        <option value="icon" ${config.waveformType === 'icon' ? 'selected' : ''}>Icon</option>
                        <option value="image" ${config.waveformType === 'image' ? 'selected' : ''}>Image URL</option>
                    </select>
                </div>
                <div class="control-item" id="waveformColorControl">
                    <label>Waveform Color</label>
                    <input type="text" id="waveformColor" class="talktopc-color-picker" value="${config.micButtonColor || '#7C3AED'}">
                </div>
                <div class="control-item" id="waveformIconControl" style="display: ${config.waveformType === 'icon' ? 'block' : 'none'};">
                    <label>Icon (Emoji or Text)</label>
                    <input type="text" id="waveformIcon" value="${config.waveformIcon || '🎤'}" placeholder="🎤">
                </div>
                <div class="control-item" id="waveformImageControl" style="display: ${config.waveformType === 'image' ? 'block' : 'none'};">
                    <label>Image URL</label>
                    <input type="text" id="waveformImageUrl" value="${config.waveformImageUrl || ''}" placeholder="https://example.com/image.png">
                </div>
            </div>
        `;
    }
    
    function getStatusTitleControls() {
        const config = widgetConfig.voice || {};
        
        return `
            <div class="customization-group">
                <h3>Status Text</h3>
                <div class="control-item">
                    <label>Status Text</label>
                    <input type="text" id="statusText" value="Listening..." placeholder="Listening...">
                </div>
                <div class="control-item">
                    <label>Text Color</label>
                    <input type="text" id="statusTitleColor" name="talktopc_voice_status_title_color" class="talktopc-color-picker" value="${config.statusTitleColor || '#1e293b'}">
                </div>
                <div class="control-item">
                    <label>Status Dot Color</label>
                    <input type="text" id="statusDotColor" class="talktopc-color-picker" value="#10b981">
                </div>
            </div>
        `;
    }
    
    function getLiveTranscriptControls() {
        return `
            <div class="customization-group">
                <h3>Live Transcript (Collapsed View)</h3>
                <div class="control-item">
                    <label>Transcript Text Color</label>
                    <input type="text" id="liveTranscriptColor" class="talktopc-color-picker" value="#64748b">
                </div>
                <div class="control-item">
                    <label>Font Size</label>
                    <input type="text" id="liveTranscriptFontSize" value="14px" placeholder="14px">
                </div>
                <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                    This is the live transcript shown when history is collapsed. It displays only the current spoken text (max 2 lines).
                </p>
            </div>
        `;
    }
    
    function getVoiceInputControls() {
        const config = widgetConfig.text || {};
        
        return `
            <div class="customization-group">
                <h3>Voice Text Input</h3>
                <div class="control-item">
                    <label>Placeholder Text</label>
                    <input type="text" id="voiceInputPlaceholder" name="talktopc_text_input_placeholder" value="${config.inputPlaceholder || 'Type your message...'}" placeholder="Type your message...">
                </div>
                <div class="control-item">
                    <label>Send Button Color</label>
                    <input type="text" id="voiceSendBtnColor" name="talktopc_text_send_btn_color" class="talktopc-color-picker" value="${config.sendButtonColor || '#7C3AED'}">
                </div>
            </div>
        `;
    }
    
    function getVoiceAvatarControls() {
        const config = widgetConfig.voice || {};
        
        return `
            <div class="customization-group">
                <h3>Voice Avatar</h3>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="avatarBg" name="talktopc_voice_avatar_color" class="talktopc-color-picker" value="${config.avatarBackgroundColor || '#667eea'}">
                </div>
            </div>
        `;
    }
    
    function getConversationHistoryControls() {
        return `
            <div class="customization-group">
                <h3>Conversation History</h3>
                <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                    This is the expanded conversation history view showing all messages.
                </p>
            </div>
        `;
    }
    
    function getAgentAvatarControls() {
        const config = widgetConfig.voice || {};
        
        return `
            <div class="customization-group">
                <h3>Agent Avatar (Next to Messages)</h3>
                <div class="control-item">
                    <label>Display Type</label>
                    <select id="agentAvatarType">
                        <option value="icon" ${(!config.avatarType || config.avatarType === 'icon') ? 'selected' : ''}>Icon</option>
                        <option value="image" ${config.avatarType === 'image' ? 'selected' : ''}>Image URL</option>
                    </select>
                </div>
                <div class="control-item" id="agentAvatarIconControl" style="display: ${config.avatarType === 'image' ? 'none' : 'block'};">
                    <label>Icon (Emoji or Text)</label>
                    <input type="text" id="agentAvatarIcon" value="${config.avatarIcon || '🤖'}" placeholder="🤖">
                </div>
                <div class="control-item" id="agentAvatarImageControl" style="display: ${config.avatarType === 'image' ? 'block' : 'none'};">
                    <label>Image URL</label>
                    <input type="text" id="agentAvatarImageUrl" value="${config.avatarImageUrl || ''}" placeholder="https://example.com/avatar.png">
                </div>
                <div class="control-item">
                    <label>Background Color</label>
                    <input type="text" id="agentAvatarBg" name="talktopc_voice_avatar_color" class="talktopc-color-picker" value="${config.avatarBackgroundColor || '#667eea'}">
                </div>
                <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                    This icon appears next to agent messages in the conversation history.
                </p>
            </div>
        `;
    }
    
    function getLiveIndicatorControls() {
        const config = widgetConfig.voice || {};
        return `
            <div class="customization-group">
                <h3>Live Indicator</h3>
                <div class="control-item">
                    <label>Live Dot Color</label>
                    <input type="text" id="liveDotColor" name="talktopc_voice_live_dot_color" class="talktopc-color-picker" value="${config.liveDotColor || '#10b981'}">
                </div>
                <div class="control-item">
                    <label>Live Text Color</label>
                    <input type="text" id="liveTextColor" name="talktopc_voice_live_text_color" class="talktopc-color-picker" value="${config.liveTextColor || '#10b981'}">
                </div>
            </div>
        `;
    }
    
    function getLandingLogoControls() {
        const config = widgetConfig.landing || {};
        
        return `
            <div class="customization-group">
                <h3>Landing Screen - Logo</h3>
                <div class="control-item">
                    <label>Display Type</label>
                    <select id="logoType">
                        <option value="icon" ${(!config.logoType || config.logoType === 'icon') ? 'selected' : ''}>Icon</option>
                        <option value="image" ${config.logoType === 'image' ? 'selected' : ''}>Image URL</option>
                    </select>
                </div>
                <div class="control-item" id="logoIconControl" style="display: ${config.logoType === 'image' ? 'none' : 'block'};">
                    <label>Icon (Emoji or Text)</label>
                    <input type="text" id="landingLogoIcon" name="talktopc_landing_logo" value="${config.logoIcon || config.logo || '🤖'}" placeholder="🤖">
                </div>
                <div class="control-item" id="logoImageControl" style="display: ${config.logoType === 'image' ? 'block' : 'none'};">
                    <label>Image URL</label>
                    <input type="text" id="landingLogoImageUrl" value="${config.logoImageUrl || ''}" placeholder="https://example.com/logo.png">
                </div>
            </div>
        `;
    }
    
    function getLandingSubtitleControls() {
        const config = widgetConfig.landing || {};
        
        return `
            <div class="customization-group">
                <h3>Landing Screen - Subtitle</h3>
                <div class="control-item">
                    <label>Subtitle Text</label>
                    <input type="text" id="landingSubtitleText" value="${config.subtitle || 'Choose how you\'d like to interact'}" placeholder="Choose how you'd like to interact">
                </div>
                <div class="control-item">
                    <label>Subtitle Color</label>
                    <input type="text" id="landingSubtitleColor" name="talktopc_landing_subtitle_color" class="talktopc-color-picker" value="${config.subtitleColor || '#64748b'}">
                </div>
            </div>
        `;
    }
    
    function getDefaultControls() {
        return `
            <div class="customization-group">
                <h3>Select an Element</h3>
                <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                    Click on any widget element in the preview to start customizing.
                </p>
                <p style="color: #6b7280; font-size: 13px; margin-top: 12px;">
                    💡 <strong>Tip:</strong> Click the ⚙️ settings icon on the widget panel to configure panel size, position, and text direction.
                </p>
            </div>
        `;
    }
    
    function attachControlListeners(elementType) {
        // Initialize color pickers
        $('.talktopc-color-picker').each(function() {
            const $picker = $(this);
            // Only initialize if not already initialized
            if (!$picker.data('wp-color-picker')) {
                $picker.wpColorPicker({
                    change: function(event, ui) {
                        updateWidgetConfig();
                        renderPreview();
                    }
                });
            }
        });
        
        // Button controls
        if (elementType === 'button') {
            $('#btnSize, #btnShape').on('change', function() {
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#iconType').on('change', function() {
                const type = $(this).val();
                $('#iconCustomImageControl').toggle(type === 'custom');
                $('#iconEmojiControl').toggle(type === 'emoji');
                $('#iconTextControl').toggle(type === 'text');
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#iconCustomImage, #iconEmoji, #iconText').on('input', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Header controls
        if (elementType === 'header') {
            $('#headerTitle').on('input', function() {
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#headerShowClose').on('change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Panel controls
        if (elementType === 'panel' || elementType === 'position') {
            $('#panelWidth, #panelHeight, #panelRadius').on('input', function() {
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#position').on('change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Message controls
        if (elementType === 'userMessage' || elementType === 'agentMessage') {
            $('#msgUserBg, #msgAgentBg, #msgTextColor, #msgFontSize, #msgRadius').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Send button controls
        if (elementType === 'sendButton') {
            $('#sendButtonText, #sendBtnColor, #sendBtnHover, #inputPlaceholder, #inputFocusColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Landing title controls
        if (elementType === 'landingTitle') {
            $('#landingTitleText, #landingTitleColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Mode card controls
        if (elementType === 'modeCard') {
            $('#voiceCardTitle, #textCardTitle, #voiceCardDesc, #textCardDesc, #modeCardBg').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Direction controls
        if (elementType === 'direction') {
            $('#direction').on('change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Start call button controls
        if (elementType === 'startCallButton') {
            $('#startCallBtnText, #startCallBtnColor, #startCallBtnTextColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Mic button controls
        if (elementType === 'micButton') {
            $('#micBtnColor, #micBtnActive').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // End call button controls
        if (elementType === 'endCallButton') {
            $('#endCallBtnColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Speaker button controls
        if (elementType === 'speakerButton') {
            $('#speakerBtnColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Timer controls
        if (elementType === 'timer') {
            $('#timerDotColor, #timerTextColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Waveform controls
        if (elementType === 'waveform') {
            $('#waveformType').on('change', function() {
                const type = $(this).val();
                $('#waveformColorControl').toggle(type === 'waveform');
                $('#waveformIconControl').toggle(type === 'icon');
                $('#waveformImageControl').toggle(type === 'image');
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#waveformColor, #waveformIcon, #waveformImageUrl').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Status title controls
        if (elementType === 'statusTitle') {
            $('#statusText, #statusTitleColor, #statusDotColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Live transcript controls
        if (elementType === 'liveTranscript' || elementType === 'liveTranscriptText') {
            $('#liveTranscriptColor, #liveTranscriptFontSize').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Voice input controls
        if (elementType === 'voiceInput' || elementType === 'voiceSendButton') {
            $('#voiceInputPlaceholder, #voiceSendBtnColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Voice avatar controls
        if (elementType === 'voiceAvatar') {
            $('#avatarBg').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Agent avatar controls
        if (elementType === 'agentAvatar') {
            $('#agentAvatarType').on('change', function() {
                const type = $(this).val();
                $('#agentAvatarIconControl').toggle(type === 'icon');
                $('#agentAvatarImageControl').toggle(type === 'image');
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#agentAvatarIcon, #agentAvatarImageUrl, #agentAvatarBg').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Live indicator controls
        if (elementType === 'liveIndicator') {
            $('#liveDotColor, #liveTextColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Landing logo controls
        if (elementType === 'landingLogo') {
            $('#logoType').on('change', function() {
                const type = $(this).val();
                $('#logoIconControl').toggle(type === 'icon');
                $('#logoImageControl').toggle(type === 'image');
                updateWidgetConfig();
                renderPreview();
            });
            
            $('#landingLogoIcon, #landingLogoImageUrl').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
        
        // Landing subtitle controls
        if (elementType === 'landingSubtitle') {
            $('#landingSubtitleText, #landingSubtitleColor').on('input change', function() {
                updateWidgetConfig();
                renderPreview();
            });
        }
    }
    
    function updateWidgetConfig() {
        // Update button config
        widgetConfig.button = {
            size: $('#btnSize').val() || widgetConfig.button?.size || 'medium',
            shape: $('#btnShape').val() || widgetConfig.button?.shape || 'circle',
            backgroundColor: $('#btnBgColor').val() || widgetConfig.button?.backgroundColor || '#FFFFFF',
            hoverColor: $('#btnHoverColor').val() || widgetConfig.button?.hoverColor || '#D3D3D3'
        };
        
        // Update icon config
        const iconType = $('#iconType').val() || widgetConfig.icon?.type || 'custom';
        widgetConfig.icon = {
            type: iconType,
            customImage: $('#iconCustomImage').val() || widgetConfig.icon?.customImage || 'https://talktopc.com/logo192.png',
            emoji: $('#iconEmoji').val() || '',
            text: $('#iconText').val() || '',
            size: widgetConfig.icon?.size || 'medium',
            backgroundColor: widgetConfig.icon?.backgroundColor || '#FFFFFF'
        };
        
        // Update header config
        widgetConfig.header = {
            title: $('#headerTitle').val() || widgetConfig.header?.title || 'Chat Assistant',
            backgroundColor: $('#headerBgColor').val() || widgetConfig.header?.backgroundColor || '#7C3AED',
            textColor: $('#headerTextColor').val() || widgetConfig.header?.textColor || '#FFFFFF',
            showCloseButton: $('#headerShowClose').is(':checked')
        };
        
        // Update panel config
        widgetConfig.panel = {
            width: parseInt($('#panelWidth').val()) || widgetConfig.panel?.width || 350,
            height: parseInt($('#panelHeight').val()) || widgetConfig.panel?.height || 550,
            borderRadius: parseInt($('#panelRadius').val()) || widgetConfig.panel?.borderRadius || 12,
            backgroundColor: $('#panelBgColor').val() || widgetConfig.panel?.backgroundColor || '#FFFFFF',
            border: widgetConfig.panel?.border || '1px solid #E5E7EB'
        };
        
        // Update position
        const position = $('#position').val() || 'bottom-right';
        const parts = position.split('-');
        widgetConfig.position = {
            vertical: parts[0] || 'bottom',
            horizontal: parts[1] || 'right'
        };
        
        // Update messages config
        widgetConfig.messages = {
            userBackgroundColor: $('#msgUserBg').val() || widgetConfig.messages?.userBackgroundColor || '#E5E7EB',
            agentBackgroundColor: $('#msgAgentBg').val() || widgetConfig.messages?.agentBackgroundColor || '#F3F4F6',
            textColor: $('#msgTextColor').val() || widgetConfig.messages?.textColor || '#1F2937',
            fontSize: $('#msgFontSize').val() || widgetConfig.messages?.fontSize || '14px',
            borderRadius: parseInt($('#msgRadius').val()) || widgetConfig.messages?.borderRadius || 16
        };
        
        // Update text config
        widgetConfig.text = {
            sendButtonText: $('#sendButtonText').val() || widgetConfig.text?.sendButtonText || '→',
            sendButtonColor: $('#sendBtnColor').val() || widgetConfig.text?.sendButtonColor || '#7C3AED',
            sendButtonHoverColor: $('#sendBtnHover').val() || widgetConfig.text?.sendButtonHoverColor || '#6D28D9',
            inputPlaceholder: $('#inputPlaceholder').val() || widgetConfig.text?.inputPlaceholder || 'Type your message...',
            inputFocusColor: $('#inputFocusColor').val() || widgetConfig.text?.inputFocusColor || '#7C3AED'
        };
        
        // Update direction
        widgetConfig.direction = $('#direction').val() || widgetConfig.direction || 'ltr';
        
        // Update voice config
        widgetConfig.voice = {
            ...widgetConfig.voice,
            micButtonColor: $('#micBtnColor').val() || widgetConfig.voice?.micButtonColor || '#7C3AED',
            micButtonActiveColor: $('#micBtnActive').val() || widgetConfig.voice?.micButtonActiveColor || '#EF4444',
            avatarBackgroundColor: $('#avatarBg').val() || $('#agentAvatarBg').val() || widgetConfig.voice?.avatarBackgroundColor || '#667eea',
            startCallButtonText: $('#startCallBtnText').val() || widgetConfig.voice?.startCallButtonText || 'Start Call',
            startCallButtonColor: $('#startCallBtnColor').val() || widgetConfig.voice?.startCallButtonColor || '#667eea',
            startCallButtonTextColor: $('#startCallBtnTextColor').val() || widgetConfig.voice?.startCallButtonTextColor || '#FFFFFF',
            statusTitleColor: $('#statusTitleColor').val() || widgetConfig.voice?.statusTitleColor || '#1e293b',
            statusText: $('#statusText').val() || widgetConfig.voice?.statusText || 'Listening...',
            statusDotColor: $('#statusDotColor').val() || widgetConfig.voice?.statusDotColor || '#10b981',
            timerDotColor: $('#timerDotColor').val() || widgetConfig.voice?.timerDotColor || '#ef4444',
            timerTextColor: $('#timerTextColor').val() || widgetConfig.voice?.timerTextColor || '#64748b',
            waveformType: $('#waveformType').val() || widgetConfig.voice?.waveformType || 'waveform',
            waveformIcon: $('#waveformIcon').val() || widgetConfig.voice?.waveformIcon || '🎤',
            waveformImageUrl: $('#waveformImageUrl').val() || widgetConfig.voice?.waveformImageUrl || '',
            avatarType: $('#agentAvatarType').val() || widgetConfig.voice?.avatarType || 'icon',
            avatarIcon: $('#agentAvatarIcon').val() || widgetConfig.voice?.avatarIcon || '🤖',
            avatarImageUrl: $('#agentAvatarImageUrl').val() || widgetConfig.voice?.avatarImageUrl || '',
            liveTranscriptColor: $('#liveTranscriptColor').val() || widgetConfig.voice?.liveTranscriptColor || '#64748b',
            liveTranscriptFontSize: $('#liveTranscriptFontSize').val() || widgetConfig.voice?.liveTranscriptFontSize || '14px',
            liveDotColor: $('#liveDotColor').val() || widgetConfig.voice?.liveDotColor || '#10b981',
            liveTextColor: $('#liveTextColor').val() || widgetConfig.voice?.liveTextColor || '#10b981'
        };
        
        // Update text config (for voice input)
        widgetConfig.text = {
            ...widgetConfig.text,
            inputPlaceholder: $('#voiceInputPlaceholder').val() || $('#inputPlaceholder').val() || widgetConfig.text?.inputPlaceholder || 'Type your message...',
            sendButtonColor: $('#voiceSendBtnColor').val() || $('#sendBtnColor').val() || widgetConfig.text?.sendButtonColor || '#7C3AED'
        };
        
        // Update landing config
        widgetConfig.landing = {
            ...widgetConfig.landing,
            title: $('#landingTitleText').val() || widgetConfig.landing?.title || 'Welcome to AI Assistant',
            titleColor: $('#landingTitleColor').val() || widgetConfig.landing?.titleColor || '#1e293b',
            subtitle: $('#landingSubtitleText').val() || widgetConfig.landing?.subtitle || 'Choose how you\'d like to interact',
            subtitleColor: $('#landingSubtitleColor').val() || widgetConfig.landing?.subtitleColor || '#64748b',
            voiceCardTitle: $('#voiceCardTitle').val() || widgetConfig.landing?.voiceCardTitle || 'Voice Call',
            voiceCardDesc: $('#voiceCardDesc').val() || widgetConfig.landing?.voiceCardDesc || 'Start a voice conversation',
            textCardTitle: $('#textCardTitle').val() || widgetConfig.landing?.textCardTitle || 'Text Chat',
            textCardDesc: $('#textCardDesc').val() || widgetConfig.landing?.textCardDesc || 'Chat via text messages',
            modeCardBackgroundColor: $('#modeCardBg').val() || widgetConfig.landing?.modeCardBackgroundColor || '#FFFFFF',
            logo: $('#landingLogoIcon').val() || widgetConfig.landing?.logo || '🤖',
            logoType: $('#logoType').val() || widgetConfig.landing?.logoType || 'icon',
            logoIcon: $('#landingLogoIcon').val() || widgetConfig.landing?.logoIcon || '🤖',
            logoImageUrl: $('#landingLogoImageUrl').val() || widgetConfig.landing?.logoImageUrl || ''
        };
    }
    
    function renderPreview() {
        applyButtonStyles($('#mockButton')[0]);
        renderPanelContent();
    }
    
    function addPanelSelector() {
        const $panel = $('#mockPanel');
        if ($panel.find('.panel-selector').length === 0) {
            const selector = $('<div>').addClass('panel-selector')
                .css({
                    position: 'absolute',
                    top: '8px',
                    right: '8px',
                    width: '24px',
                    height: '24px',
                    background: 'rgba(102, 126, 234, 0.2)',
                    border: '2px dashed #667eea',
                    borderRadius: '4px',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: '12px',
                    zIndex: 1001
                })
                .text('⚙️')
                .attr('title', 'Click to customize panel')
                .on('click', function(e) {
                    e.stopPropagation();
                    selectElement('panel', $panel[0]);
                });
            $panel.append(selector);
        }
    }
    
    function setupEventListeners() {
        // Save button
        $('#saveCustomizationBtn').on('click', function() {
            saveSettings();
        });
        
        // Reset button
        $('#resetBtn').on('click', function() {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                resetToDefaults();
            }
        });
        
        // Toggle panel button
        $('#togglePanelBtn').on('click', function() {
            panelOpen = !panelOpen;
            $('#mockPanel').toggleClass('open', panelOpen);
            if (panelOpen) {
                renderPanelContent();
            }
        });
        
        // Make panel selectable
        $('#mockPanel').on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('mock-panel-content')) {
                selectElement('panel', this);
            }
        });
    }
    
    function saveSettings() {
        updateWidgetConfig();
        
        const $status = $('#saveStatus');
        $status.removeClass('success error').show().text('Saving...');
        
        // Collect ONLY customization fields (talktopc_*)
        // Don't include any WordPress form fields
        const allData = {};
        $('#customizationControls').find('input, select, textarea').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            const type = $field.attr('type');
            
            // STRICT: Only collect fields that start with talktopc_
            // Ignore everything else (action, option_page, _wpnonce, etc.)
            if (name && name.startsWith('talktopc_')) {
                if (type === 'checkbox') {
                    allData[name] = $field.is(':checked') ? '1' : '0';
                } else if ($field.hasClass('talktopc-color-picker')) {
                    // WordPress color picker creates a hidden input - get the actual color value
                    const colorValue = $field.val();
                    if (colorValue) {
                        allData[name] = colorValue;
                    }
                } else {
                    const value = $field.val();
                    if (value !== null && value !== undefined && value !== '') {
                        allData[name] = value;
                    }
                }
            }
        });
        
        // Verify we only have talktopc_ fields
        const nonTalktopcFields = Object.keys(allData).filter(k => !k.startsWith('talktopc_'));
        if (nonTalktopcFields.length > 0) {
            console.warn('⚠️ Found non-talktopc fields in allData:', nonTalktopcFields);
            // Remove them
            nonTalktopcFields.forEach(key => delete allData[key]);
        }
        
        // Debug: log what we're sending
        console.log('Saving settings:', Object.keys(allData).length, 'fields');
        console.log('Fields being sent:', Object.keys(allData));
        console.log('Talktopc fields only:', Object.keys(allData).filter(k => k.startsWith('talktopc_')));
        console.log('Non-talktopc fields (should be empty):', Object.keys(allData).filter(k => !k.startsWith('talktopc_')));
        console.log('Action:', talktopcWidgetSettings.saveAction);
        console.log('Nonce present:', !!talktopcWidgetSettings.nonce);
        
        // Sanity check: allData should ONLY contain talktopc_ fields
        const invalidFields = Object.keys(allData).filter(k => !k.startsWith('talktopc_'));
        if (invalidFields.length > 0) {
            console.error('❌ ERROR: Found invalid fields in allData:', invalidFields);
            console.error('These will be removed before sending');
            invalidFields.forEach(key => delete allData[key]);
        }
        
        // Verify we have the required settings
        if (!talktopcWidgetSettings || !talktopcWidgetSettings.ajaxUrl || !talktopcWidgetSettings.nonce) {
            $status.addClass('error').text('Error: Missing configuration. Please refresh the page.');
            console.error('Missing talktopcWidgetSettings:', talktopcWidgetSettings);
            return;
        }
        
        // Build data object explicitly - CRITICAL: action MUST be our AJAX action
        // Don't let form fields override it!
        const ajaxData = {
            action: talktopcWidgetSettings.saveAction, // MUST be 'talktopc_save_widget_customization'
            nonce: talktopcWidgetSettings.nonce
        };
        
        // Add only customization fields (exclude WordPress form fields that override action)
        for (const key in allData) {
            if (allData.hasOwnProperty(key)) {
                // Skip WordPress form fields that interfere with AJAX
                if (key !== 'action' && key !== 'option_page' && key !== '_wpnonce' && key !== '_wp_http_referer') {
                    ajaxData[key] = allData[key];
                }
            }
        }
        
        // Force action to be correct (in case something overwrote it)
        ajaxData.action = talktopcWidgetSettings.saveAction;
        
        console.log('Final AJAX data keys:', Object.keys(ajaxData));
        console.log('Action:', ajaxData.action, '(MUST be talktopc_save_widget_customization)');
        console.log('Nonce present:', !!ajaxData.nonce);
        console.log('Customization fields:', Object.keys(ajaxData).filter(k => k.startsWith('talktopc_')));
        
        // CRITICAL: Final check - ensure action is correct
        if (ajaxData.action !== 'talktopc_save_widget_customization') {
            console.error('%c❌ CRITICAL ERROR: Action is wrong!', 'color: #EF4444; font-size: 16px; font-weight: bold;');
            console.error('Expected: talktopc_save_widget_customization');
            console.error('Got:', ajaxData.action);
            console.error('talktopcWidgetSettings.saveAction:', talktopcWidgetSettings.saveAction);
            console.error('Full ajaxData:', ajaxData);
            $status.addClass('error').text('Error: Invalid action. Please refresh the page.');
            return;
        }
        
        // Final safety: remove any non-talktopc fields that might have snuck in
        Object.keys(ajaxData).forEach(key => {
            if (key !== 'action' && key !== 'nonce' && !key.startsWith('talktopc_')) {
                console.warn('Removing invalid field from ajaxData:', key);
                delete ajaxData[key];
            }
        });
        
        // CRITICAL: Force action to be correct (in case something overwrote it)
        ajaxData.action = 'talktopc_save_widget_customization';
        
        console.log('%c✅ Ready to send AJAX', 'color: #10B981; font-weight: bold;');
        console.log('Action:', ajaxData.action, '(forced to talktopc_save_widget_customization)');
        console.log('Fields count:', Object.keys(ajaxData).length);
        console.log('All fields:', Object.keys(ajaxData));
        
        $.ajax({
            url: talktopcWidgetSettings.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            traditional: true, // Use traditional serialization for arrays
            data: ajaxData,
            success: function(response) {
                if (response.success) {
                    // Check if we got version info (confirms updated file is deployed)
                    const versionInfo = response.data?.version ? ' (v' + response.data.version + ')' : '';
                    const savedCount = response.data?.saved_count ? ' - ' + response.data.saved_count + ' settings saved' : '';
                    $status.addClass('success').html('Settings saved successfully!' + versionInfo + savedCount);
                    
                    // Log to console with styling
                    console.log('%c✅ Settings Saved Successfully!', 'color: #10B981; font-size: 14px; font-weight: bold;');
                    console.log('Version:', response.data?.version || 'unknown');
                    console.log('Saved count:', response.data?.saved_count || 0);
                    console.log('Full response:', response);
                    
                    setTimeout(function() {
                        $status.fadeOut();
                    }, 5000); // Show longer to see version info
                } else {
                    const versionInfo = response.data?.version ? ' (v' + response.data.version + ')' : '';
                    $status.addClass('error').html((response.data?.message || 'Error saving settings') + versionInfo);
                    console.error('%c❌ Save Failed', 'color: #EF4444; font-size: 14px; font-weight: bold;');
                    console.error('Version:', response.data?.version || 'unknown');
                    console.error('Error:', response.data?.message);
                    console.error('Full response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('%c❌ AJAX Error', 'color: #EF4444; font-size: 16px; font-weight: bold;');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Status Code:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                console.error('Request Data:', {
                    action: talktopcWidgetSettings.saveAction,
                    nonce: talktopcWidgetSettings.nonce ? 'Present' : 'Missing',
                    fieldCount: Object.keys(allData).length,
                    fields: Object.keys(allData)
                });
                
                // Try to parse response if it's JSON
                let parsedResponse = null;
                if (xhr.responseText && xhr.responseText !== '0') {
                    try {
                        parsedResponse = JSON.parse(xhr.responseText);
                        console.error('Parsed Error Response:', parsedResponse);
                        if (parsedResponse.data && parsedResponse.data.version) {
                            console.log('Server version:', parsedResponse.data.version);
                        }
                    } catch (e) {
                        console.error('Response is not JSON:', xhr.responseText);
                    }
                } else if (xhr.responseText === '0') {
                    console.error('%c⚠️ WordPress returned "0" - This usually means:', 'color: #F59E0B; font-weight: bold;');
                    console.error('1. PHP fatal error before wp_send_json can run');
                    console.error('2. Action hook not registered properly');
                    console.error('3. Syntax error in PHP file');
                    console.error('Check WordPress debug log for PHP errors!');
                }
                
                let errorMsg = 'Error saving settings. ';
                
                // Handle different error codes
                if (xhr.status === 400) {
                    errorMsg += 'Bad Request (400). ';
                    if (xhr.responseText) {
                        errorMsg += 'Server says: ' + xhr.responseText.substring(0, 200);
                    } else {
                        errorMsg += 'This usually means the request format is incorrect.';
                    }
                } else if (xhr.status === 403) {
                    errorMsg += 'Forbidden (403). Security check failed. Please refresh the page.';
                } else if (xhr.status === 500) {
                    errorMsg += 'Server Error (500). Check WordPress debug logs.';
                } else {
                    errorMsg += 'Please try again.';
                }
                
                // Try to parse JSON response
                if (xhr.responseText) {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.data && errorResponse.data.message) {
                            errorMsg = errorResponse.data.message;
                        }
                    } catch (e) {
                        // Not JSON - might be HTML error page
                        if (xhr.responseText.includes('Fatal error') || xhr.responseText.includes('Parse error')) {
                            errorMsg += ' PHP Error detected. Check server logs.';
                        }
                    }
                }
                
                $status.addClass('error').html(errorMsg + '<br><small style="color: #666;">Status: ' + xhr.status + ' | Check browser console (F12) for details.</small>');
            }
        });
    }
    
    function resetToDefaults() {
        // Reset to default values
        widgetConfig.button = {
            size: 'medium',
            shape: 'circle',
            backgroundColor: '#FFFFFF',
            hoverColor: '#D3D3D3'
        };
        
        widgetConfig.icon = {
            type: 'custom',
            customImage: 'https://talktopc.com/logo192.png',
            emoji: '',
            text: '',
            size: 'medium',
            backgroundColor: '#FFFFFF'
        };
        
        widgetConfig.header = {
            title: 'Chat Assistant',
            backgroundColor: '#7C3AED',
            textColor: '#FFFFFF',
            showCloseButton: true
        };
        
        widgetConfig.panel = {
            width: 350,
            height: 550,
            borderRadius: 12,
            backgroundColor: '#FFFFFF',
            border: '1px solid #E5E7EB'
        };
        
        widgetConfig.position = {
            vertical: 'bottom',
            horizontal: 'right'
        };
        
        widgetConfig.messages = {
            userBackgroundColor: '#E5E7EB',
            agentBackgroundColor: '#F3F4F6',
            textColor: '#1F2937',
            fontSize: '14px',
            borderRadius: 16
        };
        
        widgetConfig.text = {
            sendButtonText: '→',
            sendButtonColor: '#7C3AED',
            sendButtonHoverColor: '#6D28D9',
            inputPlaceholder: 'Type your message...',
            inputFocusColor: '#7C3AED'
        };
        
        widgetConfig.landing = {
            title: 'Welcome to AI Assistant',
            titleColor: '#1e293b',
            subtitle: 'Choose how you\'d like to interact',
            subtitleColor: '#64748b',
            voiceCardTitle: 'Voice Call',
            voiceCardDesc: 'Start a voice conversation',
            textCardTitle: 'Text Chat',
            textCardDesc: 'Chat via text messages',
            modeCardBackgroundColor: '#FFFFFF',
            logo: '🤖',
            logoType: 'icon',
            logoIcon: '🤖',
            logoImageUrl: ''
        };
        
        widgetConfig.voice = {
            micButtonColor: '#7C3AED',
            micButtonActiveColor: '#EF4444',
            avatarBackgroundColor: '#667eea',
            startCallButtonText: 'Start Call',
            startCallButtonColor: '#667eea',
            startCallButtonTextColor: '#FFFFFF',
            statusTitleColor: '#1e293b',
            statusText: 'Listening...',
            statusDotColor: '#10b981',
            timerDotColor: '#ef4444',
            timerTextColor: '#64748b',
            waveformType: 'waveform',
            waveformIcon: '🎤',
            waveformImageUrl: '',
            avatarType: 'icon',
            avatarIcon: '🤖',
            avatarImageUrl: '',
            liveTranscriptColor: '#64748b',
            liveTranscriptFontSize: '14px',
            liveDotColor: '#10b981',
            liveTextColor: '#10b981'
        };
        
        widgetConfig.direction = 'ltr';
        
        initMockWidget();
        showCustomizationControls('default');
    }
    
    // Add CSS for mock widget
    if (!$('#talktopc-mock-widget-styles').length) {
        $('<style id="talktopc-mock-widget-styles">')
            .text(`
                .mock-widget {
                    position: relative;
                }
                .mock-widget-button {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    background: #FFFFFF;
                    border: none;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    transition: all 0.3s;
                    position: absolute;
                    bottom: 0;
                    right: 0;
                    z-index: 1001;
                }
                .mock-widget-panel {
                    position: relative;
                    margin-bottom: 100px;
                    width: 360px;
                    height: 550px;
                    background: white;
                    border-radius: 24px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    display: none;
                    overflow: hidden;
                    flex-direction: column;
                    z-index: 1000;
                }
                .mock-widget-panel.open {
                    display: flex;
                }
                .mock-panel-header {
                    background: #7C3AED;
                    color: white;
                    padding: 16px 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    font-weight: 600;
                }
                .mock-panel-close {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    transition: background 0.2s;
                }
                .mock-panel-close:hover {
                    background: rgba(255, 255, 255, 0.2);
                }
                .mock-panel-content {
                    flex: 1;
                    overflow-y: auto;
                    padding: 20px;
                }
                .mock-landing-screen {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100%;
                    padding: 40px 20px;
                    text-align: center;
                }
                .mock-landing-logo {
                    font-size: 64px;
                    margin-bottom: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    line-height: 1;
                }
                .mock-landing-logo img {
                    display: block;
                    max-width: 64px;
                    max-height: 64px;
                    object-fit: contain;
                }
                .mock-landing-title {
                    font-size: 24px;
                    font-weight: 700;
                    color: #1e293b;
                    margin-bottom: 8px;
                    text-align: center;
                    line-height: 1.2;
                }
                .mock-landing-subtitle {
                    font-size: 14px;
                    color: #64748b;
                    margin-bottom: 32px;
                    text-align: center;
                    line-height: 1.4;
                }
                .mock-mode-cards {
                    display: flex;
                    gap: 16px;
                    width: 100%;
                }
                .mock-mode-card {
                    flex: 1;
                    background: white;
                    border: 2px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 24px;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                .mock-mode-card:hover {
                    border-color: #667eea;
                    transform: translateY(-2px);
                }
                .mock-mode-icon {
                    font-size: 32px;
                    margin-bottom: 12px;
                }
                .mock-mode-title {
                    font-size: 16px;
                    font-weight: 600;
                    color: #1e293b;
                    margin-bottom: 4px;
                }
                .mock-mode-desc {
                    font-size: 12px;
                    color: #64748b;
                }
                .mock-text-interface {
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                }
                .mock-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 20px;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }
                .mock-message {
                    max-width: 75%;
                    padding: 12px 16px;
                    border-radius: 16px;
                    font-size: 14px;
                    line-height: 1.5;
                }
                .mock-message.user {
                    align-self: flex-end;
                    background: #E5E7EB;
                    color: #1F2937;
                }
                .mock-message.agent {
                    align-self: flex-start;
                    background: #F3F4F6;
                    color: #1F2937;
                }
                .mock-input-area {
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    gap: 8px;
                }
                .mock-input {
                    flex: 1;
                    padding: 12px 16px;
                    border: 1px solid #d1d5db;
                    border-radius: 24px;
                    font-size: 14px;
                    outline: none;
                }
                .mock-input:focus {
                    border-color: #667eea;
                }
                .mock-send-button {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: #7C3AED;
                    border: none;
                    color: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    transition: background 0.2s;
                }
                .mock-send-button:hover {
                    background: #6D28D9;
                }
                .mock-voice-interface {
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                    overflow-y: auto;
                }
                .mock-voice-section {
                    padding: 24px;
                    border-bottom: 1px solid #e5e7eb;
                }
                .mock-voice-timer {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 16px;
                    font-size: 14px;
                    color: #64748b;
                }
                .mock-timer-dot {
                    width: 8px;
                    height: 8px;
                    background: #ef4444;
                    border-radius: 50%;
                }
                .mock-waveform {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 3px;
                    height: 60px;
                    margin-bottom: 16px;
                }
                .mock-waveform-bar {
                    width: 3px;
                    background: #7C3AED;
                    border-radius: 2px;
                    animation: waveformAnimation 0.8s ease-in-out infinite;
                }
                .mock-waveform-bar:nth-child(1) { height: 12px; animation-delay: 0s; }
                .mock-waveform-bar:nth-child(2) { height: 20px; animation-delay: 0.05s; }
                .mock-waveform-bar:nth-child(3) { height: 28px; animation-delay: 0.1s; }
                .mock-waveform-bar:nth-child(4) { height: 36px; animation-delay: 0.15s; }
                .mock-waveform-bar:nth-child(5) { height: 44px; animation-delay: 0.2s; }
                .mock-waveform-bar:nth-child(6) { height: 50px; animation-delay: 0.25s; }
                .mock-waveform-bar:nth-child(7) { height: 54px; animation-delay: 0.3s; }
                .mock-waveform-bar:nth-child(8) { height: 56px; animation-delay: 0.35s; }
                .mock-waveform-bar:nth-child(9) { height: 54px; animation-delay: 0.4s; }
                .mock-waveform-bar:nth-child(10) { height: 50px; animation-delay: 0.45s; }
                .mock-waveform-bar:nth-child(11) { height: 44px; animation-delay: 0.5s; }
                .mock-waveform-bar:nth-child(12) { height: 36px; animation-delay: 0.55s; }
                .mock-waveform-bar:nth-child(13) { height: 28px; animation-delay: 0.6s; }
                .mock-waveform-bar:nth-child(14) { height: 20px; animation-delay: 0.65s; }
                .mock-waveform-bar:nth-child(15) { height: 12px; animation-delay: 0.7s; }
                @keyframes waveformAnimation {
                    0%, 100% { 
                        transform: scaleY(0.3);
                        opacity: 0.7;
                    }
                    50% { 
                        transform: scaleY(1);
                        opacity: 1;
                    }
                }
                .mock-voice-status {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 24px;
                    font-size: 14px;
                    color: #64748b;
                }
                .mock-status-dot {
                    width: 8px;
                    height: 8px;
                    background: #10b981;
                    border-radius: 50%;
                    animation: pulse-dot 2s infinite;
                }
                @keyframes pulse-dot {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .mock-voice-controls {
                    display: flex;
                    justify-content: center;
                    gap: 12px;
                }
                .mock-voice-section-compact {
                    padding: 16px 24px;
                    border-bottom: 1px solid #e5e7eb;
                }
                .mock-compact-row {
                    display: flex;
                    align-items: center;
                    gap: 16px;
                    flex-wrap: nowrap;
                }
                .mock-compact-waveform {
                    display: flex;
                    align-items: center;
                    gap: 2px;
                    height: 32px;
                    flex-shrink: 0;
                }
                .mock-compact-waveform .mock-waveform-bar {
                    width: 2px;
                    background: #7C3AED;
                    border-radius: 1px;
                    animation: waveformAnimation 0.8s ease-in-out infinite;
                }
                .mock-compact-waveform .mock-waveform-bar:nth-child(1) { height: 8px; animation-delay: 0s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(2) { height: 12px; animation-delay: 0.05s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(3) { height: 16px; animation-delay: 0.1s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(4) { height: 20px; animation-delay: 0.15s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(5) { height: 24px; animation-delay: 0.2s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(6) { height: 28px; animation-delay: 0.25s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(7) { height: 30px; animation-delay: 0.3s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(8) { height: 32px; animation-delay: 0.35s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(9) { height: 30px; animation-delay: 0.4s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(10) { height: 28px; animation-delay: 0.45s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(11) { height: 24px; animation-delay: 0.5s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(12) { height: 20px; animation-delay: 0.55s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(13) { height: 16px; animation-delay: 0.6s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(14) { height: 12px; animation-delay: 0.65s; }
                .mock-compact-waveform .mock-waveform-bar:nth-child(15) { height: 8px; animation-delay: 0.7s; }
                .mock-compact-timer {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-size: 14px;
                    color: #64748b;
                    flex-shrink: 0;
                }
                .mock-compact-timer .mock-timer-dot {
                    width: 6px;
                    height: 6px;
                }
                .mock-compact-status {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-size: 14px;
                    color: #10b981;
                    flex-shrink: 0;
                }
                .mock-compact-status .mock-status-dot {
                    width: 6px;
                    height: 6px;
                }
                .mock-compact-controls {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-left: auto;
                    flex-shrink: 0;
                }
                .mock-compact-controls .mock-control-btn {
                    width: 40px;
                    height: 40px;
                }
                .mock-control-btn {
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                }
                .mock-control-btn.secondary {
                    background: white;
                    color: #374151;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .mock-control-btn.danger {
                    background: #ef4444;
                    color: white;
                    width: 56px;
                    height: 56px;
                }
                .mock-control-btn svg {
                    width: 20px;
                    height: 20px;
                }
                .mock-conversation-section {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    overflow-y: auto;
                    padding: 16px;
                }
                .mock-conversation-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 12px;
                    font-size: 12px;
                    font-weight: 600;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .mock-conversation-toggle {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    cursor: pointer;
                    color: #6b7280;
                    font-size: 12px;
                }
                .mock-live-indicator {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    margin-bottom: 12px;
                    font-size: 12px;
                    font-weight: 600;
                    color: #10b981;
                }
                .mock-live-dot {
                    width: 6px;
                    height: 6px;
                    background: #10b981;
                    border-radius: 50%;
                    animation: pulse-dot 2s infinite;
                }
                .mock-live-transcript-collapsed {
                    padding: 0;
                }
                .mock-live-text-collapsed {
                    color: #64748b;
                    font-size: 14px;
                    line-height: 1.6;
                    min-height: 44px;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
                .mock-conversation-history {
                    display: none;
                    flex-direction: column;
                    gap: 16px;
                    max-height: 300px;
                    overflow-y: auto;
                    padding-right: 8px;
                }
                .mock-conversation-history.expanded {
                    display: flex !important;
                }
                .mock-conversation-toggle {
                    cursor: pointer;
                    transition: opacity 0.2s;
                }
                .mock-conversation-toggle:hover {
                    opacity: 0.7;
                }
                .mock-history-message {
                    display: flex;
                    gap: 12px;
                    align-items: flex-start;
                }
                .mock-history-avatar {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    font-size: 14px;
                    color: white;
                    overflow: hidden;
                }
                .mock-history-bubble {
                    background: #f3f4f6;
                    padding: 12px 16px;
                    border-radius: 16px;
                    font-size: 14px;
                    color: #1f2937;
                    line-height: 1.5;
                    max-width: calc(100% - 44px);
                }
                .mock-live-message-row {
                    display: flex;
                    gap: 12px;
                    align-items: flex-start;
                    margin-top: 8px;
                }
                .mock-live-badge {
                    display: inline-block;
                    background: #10b981;
                    color: white;
                    font-size: 10px;
                    font-weight: 600;
                    padding: 2px 6px;
                    border-radius: 4px;
                    margin-right: 8px;
                    text-transform: uppercase;
                }
                .mock-voice-input-area {
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                }
                .mock-voice-input-wrapper {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .mock-voice-text-input {
                    flex: 1;
                    padding: 12px 16px;
                    border: 1px solid #d1d5db;
                    border-radius: 24px;
                    font-size: 14px;
                    outline: none;
                }
                .mock-voice-text-input:focus {
                    border-color: #7C3AED;
                }
                .mock-voice-send-btn {
                    width: 40px;
                    height: 40px;
                    border-radius: 8px;
                    background: #7C3AED;
                    border: none;
                    color: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                }
                .mock-voice-send-btn:hover {
                    background: #6D28D9;
                }
                .mock-powered-by {
                    padding: 12px 16px;
                    border-top: 1px solid #e5e7eb;
                    font-size: 11px;
                    color: #9ca3af;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }
                .mock-powered-by strong {
                    color: #7C3AED;
                }
                .element-highlight {
                    outline: 2px solid #667eea;
                    outline-offset: 2px;
                    cursor: pointer;
                    transition: outline-color 0.2s;
                }
                .element-highlight:hover {
                    outline-color: #ef4444;
                }
            `)
            .appendTo('head');
    }
    
})(jQuery);
