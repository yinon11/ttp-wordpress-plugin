/**
 * Widget Customization2 - Based on SDK widget-customization.html
 * Matches widget-customization.html from SDK
 */

(function($) {
    'use strict';
    
    // Initialize widgetConfig from WordPress settings or use defaults
    // Deep merge WordPress settings with defaults to ensure all properties exist
    const defaultConfig = {
      button: {
        size: 'medium',
        shape: 'circle',
        backgroundColor: '#FFFFFF',
        hoverColor: '#D3D3D3', // SDK default: light gray
        shadow: true,
        shadowColor: 'rgba(0,0,0,0.15)'
      },
      icon: {
        type: 'custom',
        customImage: 'https://talktopc.com/logo192.png',
        size: 'medium',
        backgroundColor: '#FFFFFF'
      },
      panel: {
        width: 360,
        height: 550, // Updated from SDK default 500
        borderRadius: 24,
        backgroundColor: '#FFFFFF',
        border: '1px solid #E5E7EB'
      },
      direction: 'ltr', // 'ltr' or 'rtl'
      position: {
        vertical: 'bottom', // 'top' or 'bottom'
        horizontal: 'right', // 'left' or 'right'
        offset: { x: 20, y: 20 }
      },
      header: {
        title: 'Chat Assistant',
        backgroundColor: '#7C3AED',
        textColor: '#FFFFFF',
        showCloseButton: true
      },
      messages: {
        userBackgroundColor: '#E5E7EB',
        agentBackgroundColor: '#F3F4F6',
        textColor: '#1F2937',
        fontSize: '14px',
        borderRadius: 16
      },
      text: {
        sendButtonText: '→',
        sendButtonColor: '#7C3AED',
        sendButtonHoverColor: '#6D28D9',
        inputPlaceholder: 'Type your message...',
        inputFocusColor: '#7C3AED'
      },
      voice: {
        micButtonColor: '#7C3AED',
        micButtonActiveColor: '#EF4444',
        avatarBackgroundColor: '#667eea',
        avatarType: 'icon', // 'icon' or 'image'
        avatarIcon: '🤖',
        avatarImageUrl: '',
        startCallButtonText: 'Start Call',
        startCallButtonColor: '#667eea',
        startCallButtonTextColor: '#FFFFFF',
        statusTitleColor: '#1e293b',
        statusSubtitleColor: '#64748b',
        waveformType: 'waveform', // 'waveform', 'icon', or 'image'
        waveformIcon: '🎤',
        waveformImageUrl: ''
      },
      landing: {
        logo: '🤖',
        logoType: 'icon', // 'icon' or 'image'
        logoIcon: '🤖',
        logoImageUrl: '',
        title: 'Welcome to AI Assistant',
        subtitle: 'Choose how you\'d like to interact',
        voiceCardTitle: 'Voice Call',
        textCardTitle: 'Text Chat',
        titleColor: '#1e293b',
        subtitleColor: '#64748b',
        modeCardBackgroundColor: '#FFFFFF'
      },
      position: {
        vertical: 'bottom',
        horizontal: 'right',
        offset: { x: 20, y: 20 }
      }
    };
    
    // Merge WordPress settings with defaults
    const wpSettings = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.settings) ? talktopcWidgetSettings2.settings : {};
    let widgetConfig = JSON.parse(JSON.stringify(defaultConfig)); // Deep copy defaults
    
    // Deep merge function
    function deepMerge(target, source) {
        for (const key in source) {
            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                if (!target[key]) target[key] = {};
                deepMerge(target[key], source[key]);
            } else if (source[key] !== undefined && source[key] !== null && source[key] !== '') {
                target[key] = source[key];
            }
        }
        return target;
    }
    
    // Merge WordPress settings into defaults
    if (wpSettings && Object.keys(wpSettings).length > 0) {
        widgetConfig = deepMerge(widgetConfig, wpSettings);
    }

    let selectedElement = null;
    let currentView = 'landing'; // 'landing', 'text', 'voice'
    let panelOpen = true; // Start with panel open to show defaults
    let clickTimeout = null; // Track single vs double click
    let historyExpanded = false; // Track conversation history state
    
    // Track which properties have been modified by the user
    // Only modified properties will be included in the config code output
    const modifiedProperties = {};
    
    // Helper function to mark a property path as modified
    // Example: markPropertyModified('header.backgroundColor') or markPropertyModified('button', 'size')
    function markPropertyModified(...path) {
        let current = modifiedProperties;
        for (let i = 0; i < path.length - 1; i++) {
            if (!current[path[i]]) {
                current[path[i]] = {};
            }
            current = current[path[i]];
        }
        current[path[path.length - 1]] = true;
    }
    
    // Helper function to check if a property path has been modified
    function isPropertyModified(...path) {
        let current = modifiedProperties;
        for (const key of path) {
            if (!current || !current[key]) {
                return false;
            }
            current = current[key];
        }
        return true;
    }
    
    // Helper function to get a nested property value from an object
    function getNestedProperty(obj, ...path) {
        let current = obj;
        for (const key of path) {
            if (current === undefined || current === null) {
                return undefined;
            }
            current = current[key];
        }
        return current;
    }
    
    // Helper function to set a nested property value in an object
    function setNestedProperty(obj, value, ...path) {
        let current = obj;
        for (let i = 0; i < path.length - 1; i++) {
            if (!current[path[i]]) {
                current[path[i]] = {};
            }
            current = current[path[i]];
        }
        current[path[path.length - 1]] = value;
    }
    
    // Helper function to build config object with only modified properties
    function buildMinimalConfig() {
        const agentId = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.agentId) 
            ? talktopcWidgetSettings2.agentId 
            : 'your_agent_id';
        const appId = (typeof talktopcWidgetSettings2 !== 'undefined' && talktopcWidgetSettings2.appId) 
            ? talktopcWidgetSettings2.appId 
            : 'your_app_id';
        
        const config = {
            agentId: agentId,
            appId: appId
        };
        
        // Helper to check if an object has any modified nested properties
        function hasModifiedChildren(obj, path = []) {
            if (!obj || typeof obj !== 'object') return false;
            for (const key in obj) {
                const currentPath = [...path, key];
                if (isPropertyModified(...currentPath)) {
                    return true;
                }
                // Recursively check nested objects
                if (obj[key] && typeof obj[key] === 'object' && !Array.isArray(obj[key])) {
                    if (hasModifiedChildren(obj[key], currentPath)) {
                        return true;
                    }
                }
            }
            return false;
        }
        
        // Helper to recursively add modified properties
        function addModifiedProperties(source, target, path = []) {
            for (const key in source) {
                const currentPath = [...path, key];
                const isModified = isPropertyModified(...currentPath);
                
                // Also check if any nested property is modified (for parent objects)
                const value = source[key];
                const hasNestedModifications = value && typeof value === 'object' && !Array.isArray(value) && value.constructor === Object
                    ? hasModifiedChildren(value, currentPath)
                    : false;
                
                if (isModified || hasNestedModifications) {
                    if (value && typeof value === 'object' && !Array.isArray(value) && value.constructor === Object) {
                        // Create nested object and add only modified nested properties
                        if (!target[key]) {
                            target[key] = {};
                        }
                        addModifiedProperties(value, target[key], currentPath);
                    } else {
                        // Primitive value or array, add it
                        target[key] = value;
                    }
                }
            }
        }
        
        // Add modified properties from widgetConfig
        addModifiedProperties(widgetConfig, config);
        
        return config;
    }

    // Initialize mock widget
    function initMockWidget() {
      const mockButton = document.getElementById('mockButton');
      const mockPanel = document.getElementById('mockPanel');
      
      if (!mockButton || !mockPanel) {
        console.error('TalkToPC Widget Customization: Required elements not found. mockButton:', !!mockButton, 'mockPanel:', !!mockPanel);
        return;
      }
      
      // Apply button styles
      applyButtonStyles(mockButton);
      
      // Open panel by default to show defaults
      mockPanel.classList.add('open');
      
      // Setup button click handler
      mockButton.addEventListener('click', () => {
        panelOpen = !panelOpen;
        mockPanel.classList.toggle('open');
        if (panelOpen) {
          renderPanelContent();
        }
      });

      // Render initial panel content with defaults
      renderPanelContent();
      
      // Show default customization controls
      showCustomizationControls('default');
    }

    function applyButtonStyles(button) {
      const config = widgetConfig.button;
      button.style.width = getSizeValue(config.size) + 'px';
      button.style.height = getSizeValue(config.size) + 'px';
      button.style.backgroundColor = config.backgroundColor;
      button.style.borderRadius = config.shape === 'circle' ? '50%' : config.shape === 'rounded' ? '12px' : '0';
      
      if (config.shadow) {
        button.style.boxShadow = `0 4px 12px ${config.shadowColor}`;
      } else {
        button.style.boxShadow = 'none';
      }

      // Apply icon
      const iconConfig = widgetConfig.icon;
      button.innerHTML = ''; // Clear previous content
      
      if (iconConfig.type === 'custom' && iconConfig.customImage) {
        const img = document.createElement('img');
        img.src = iconConfig.customImage;
        img.alt = 'Chat Assistant';
        const iconSize = Math.floor(getSizeValue(config.size) * 0.6);
        img.style.width = iconSize + 'px';
        img.style.height = iconSize + 'px';
        img.style.objectFit = 'contain';
        button.appendChild(img);
      } else if (iconConfig.type === 'emoji') {
        button.textContent = iconConfig.emoji;
      } else if (iconConfig.type === 'text') {
        button.textContent = iconConfig.text;
      } else if (iconConfig.type === 'microphone') {
        // Default microphone SVG
        const iconSize = Math.floor(getSizeValue(config.size) * 0.5);
        button.innerHTML = `<svg viewBox="0 0 24 24" style="width: ${iconSize}px; height: ${iconSize}px; fill: #7C3AED;">
          <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
          <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
        </svg>`;
      } else {
        // Fallback to custom image if type is custom but no image specified
        const img = document.createElement('img');
        img.src = 'https://talktopc.com/logo192.png';
        img.alt = 'Chat Assistant';
        const iconSize = Math.floor(getSizeValue(config.size) * 0.6);
        img.style.width = iconSize + 'px';
        img.style.height = iconSize + 'px';
        img.style.objectFit = 'contain';
        button.appendChild(img);
      }

      // Make button highlightable
      button.dataset.elementType = 'button';
      
      // Remove old event listeners by replacing the button
      const oldButton = button;
      const newButton = oldButton.cloneNode(true);
      oldButton.parentNode.replaceChild(newButton, oldButton);
      newButton.id = 'mockButton';
      
      newButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        handleElementClick('button', newButton, e);
      });
    }

    function getSizeValue(size) {
      const sizes = { small: 48, medium: 60, large: 72, xl: 84 };
      return sizes[size] || 60;
    }

    function renderPanelContent() {
      const mockPanel = document.getElementById('mockPanel');
      
      // Apply panel styles
      mockPanel.style.width = widgetConfig.panel.width + 'px';
      mockPanel.style.height = widgetConfig.panel.height + 'px';
      mockPanel.style.borderRadius = widgetConfig.panel.borderRadius + 'px';
      mockPanel.style.backgroundColor = widgetConfig.panel.backgroundColor;
      mockPanel.style.border = widgetConfig.panel.border;

      // Render based on current view
      if (currentView === 'landing') {
        renderLandingScreen(mockPanel);
      } else if (currentView === 'text') {
        renderTextInterface(mockPanel);
      } else if (currentView === 'voice') {
        renderVoiceInterface(mockPanel);
      }
      
      // Add panel selector after rendering
      addPanelSelector();
    }

    function renderLandingScreen(panel) {
      const config = widgetConfig.landing;
      panel.innerHTML = `
        <div class="mock-panel-header" style="background: ${widgetConfig.header.backgroundColor}; color: ${widgetConfig.header.textColor};" data-element-type="header">
          <span>${widgetConfig.header.title}</span>
          ${widgetConfig.header.showCloseButton ? '<button class="mock-panel-close" data-element-type="closeButton">×</button>' : ''}
        </div>
        <div class="mock-panel-content">
          <div class="mock-landing-screen">
            <div class="mock-landing-logo" data-element-type="landingLogo">
              ${config.logoType === 'image' && config.logoImageUrl ? `
                <img src="${config.logoImageUrl}" alt="Logo" style="max-width: 64px; max-height: 64px; object-fit: contain;">
              ` : `
                <span>${config.logoIcon || config.logo || '🤖'}</span>
              `}
            </div>
            <div class="mock-landing-title" style="color: ${config.titleColor};" data-element-type="landingTitle">${config.title || 'Welcome to AI Assistant'}</div>
            <div class="mock-landing-subtitle" style="color: ${config.subtitleColor};" data-element-type="landingSubtitle">${config.subtitle || 'Choose how you\'d like to interact'}</div>
            <div class="mock-mode-cards">
              <div class="mock-mode-card" style="background: ${config.modeCardBackgroundColor};" data-element-type="modeCard" data-mode="voice">
                <div class="mock-mode-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #7C3AED;">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" fill="currentColor"/>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
                    <line x1="12" y1="19" x2="12" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="8" y1="23" x2="16" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </div>
                <div class="mock-mode-title">${config.voiceCardTitle || 'Voice Call'}</div>
              </div>
              <div class="mock-mode-card" style="background: ${config.modeCardBackgroundColor};" data-element-type="modeCard" data-mode="text">
                <div class="mock-mode-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #7C3AED;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <path d="M8 10h.01M12 10h.01M16 10h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </div>
                <div class="mock-mode-title">${config.textCardTitle || 'Text Chat'}</div>
              </div>
            </div>
          </div>
        </div>
      `;
      
      // Make elements selectable
      setupElementListeners(panel);
    }

    function renderTextInterface(panel) {
      const config = widgetConfig.text;
      panel.innerHTML = `
        <div class="mock-panel-header" style="background: ${widgetConfig.header.backgroundColor}; color: ${widgetConfig.header.textColor};" data-element-type="header">
          <span>${widgetConfig.header.title}</span>
          ${widgetConfig.header.showCloseButton ? '<button class="mock-panel-close" data-element-type="closeButton" onclick="switchView(\'landing\')">×</button>' : ''}
        </div>
        <div class="mock-text-interface">
          <div class="mock-messages">
            <div class="mock-message user" style="background: ${widgetConfig.messages.userBackgroundColor}; color: ${widgetConfig.messages.textColor}; border-radius: ${widgetConfig.messages.borderRadius}px; font-size: ${widgetConfig.messages.fontSize};" data-element-type="userMessage">
              Hello! How can I help you?
            </div>
            <div class="mock-message agent" style="background: ${widgetConfig.messages.agentBackgroundColor}; color: ${widgetConfig.messages.textColor}; border-radius: ${widgetConfig.messages.borderRadius}px; font-size: ${widgetConfig.messages.fontSize};" data-element-type="agentMessage">
              Hi! I'm here to assist you. What would you like to know?
            </div>
            <div class="mock-message user" style="background: ${widgetConfig.messages.userBackgroundColor}; color: ${widgetConfig.messages.textColor}; border-radius: ${widgetConfig.messages.borderRadius}px; font-size: ${widgetConfig.messages.fontSize};" data-element-type="userMessage">
              Can you tell me about your features?
            </div>
          </div>
          <div class="mock-input-area">
            <input type="text" class="mock-input" placeholder="${config.inputPlaceholder}" style="border-color: ${config.inputFocusColor};" data-element-type="input">
            <button class="mock-send-button" style="background: ${config.sendButtonColor};" data-element-type="sendButton">${config.sendButtonText || '→'}</button>
          </div>
        </div>
      `;
      
      // Make elements selectable
      setupElementListeners(panel);
    }

    function renderVoiceInterface(panel) {
      const config = widgetConfig.voice;
      // Show active call state matching real widget structure
      panel.innerHTML = `
        <div class="mock-panel-header" style="background: ${widgetConfig.header.backgroundColor}; color: ${widgetConfig.header.textColor};" data-element-type="header">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span>${widgetConfig.header.title}</span>
            <div style="display: flex; align-items: center; gap: 6px; margin-left: 8px;">
              <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></div>
              <span style="font-size: 12px; opacity: 0.9;">Online</span>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <button style="background: none; border: none; color: ${widgetConfig.header.textColor}; cursor: pointer; padding: 4px; display: flex; align-items: center;" data-element-type="backButton">←</button>
            ${widgetConfig.header.showCloseButton ? '<button class="mock-panel-close" data-element-type="closeButton">×</button>' : ''}
          </div>
        </div>
        <div class="mock-voice-interface">
          <!-- Voice Section -->
          <!-- Multi-row layout when history is collapsed -->
          <div class="mock-voice-section" id="mockVoiceSectionExpanded">
            <div class="mock-voice-timer" data-element-type="timer">
              <div class="mock-timer-dot"></div>
              <span>00:11</span>
            </div>
            <div class="mock-waveform" data-element-type="waveform">
              ${widgetConfig.voice.waveformType === 'waveform' ? `
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
              ` : widgetConfig.voice.waveformType === 'icon' ? `
                <div class="mock-waveform-icon" style="font-size: 48px; line-height: 1;">${widgetConfig.voice.waveformIcon || '🎤'}</div>
              ` : widgetConfig.voice.waveformType === 'image' && widgetConfig.voice.waveformImageUrl ? `
                <img src="${widgetConfig.voice.waveformImageUrl}" alt="Waveform" class="mock-waveform-image" style="max-width: 60px; max-height: 60px; object-fit: contain;">
              ` : `
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
                <div class="mock-waveform-bar"></div>
              `}
            </div>
            <div class="mock-voice-status" data-element-type="statusTitle">
              <div class="mock-status-dot"></div>
              <span>Listening...</span>
            </div>
            <div class="mock-voice-controls">
              <button class="mock-control-btn secondary" data-element-type="micButton" title="Mute">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                  <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                  <line x1="12" y1="19" x2="12" y2="23"/>
                </svg>
              </button>
              <button class="mock-control-btn danger" data-element-type="endCallButton" title="End Call">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" transform="rotate(135 12 12)"/>
                </svg>
              </button>
              <button class="mock-control-btn secondary" data-element-type="speakerButton" title="Speaker">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                ${widgetConfig.voice.waveformType === 'waveform' ? `
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                ` : widgetConfig.voice.waveformType === 'icon' ? `
                  <div class="mock-waveform-icon" style="font-size: 24px; line-height: 1;">${widgetConfig.voice.waveformIcon || '🎤'}</div>
                ` : widgetConfig.voice.waveformType === 'image' && widgetConfig.voice.waveformImageUrl ? `
                  <img src="${widgetConfig.voice.waveformImageUrl}" alt="Waveform" class="mock-waveform-image" style="max-width: 32px; max-height: 32px; object-fit: contain;">
                ` : `
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                  <div class="mock-waveform-bar"></div>
                `}
              </div>
              <div class="mock-compact-timer" data-element-type="timer">
                <div class="mock-timer-dot"></div>
                <span>00:11</span>
              </div>
              <div class="mock-compact-status" data-element-type="statusTitle">
                <div class="mock-status-dot"></div>
                <span>Listening...</span>
              </div>
              <div class="mock-compact-controls">
                <button class="mock-control-btn secondary" data-element-type="micButton" title="Mute">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                    <line x1="12" y1="19" x2="12" y2="23"/>
                  </svg>
                </button>
                <button class="mock-control-btn danger" data-element-type="endCallButton" title="End Call">
                  <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" transform="rotate(135 12 12)"/>
                  </svg>
                </button>
                <button class="mock-control-btn secondary" data-element-type="speakerButton" title="Speaker">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
            
            <!-- Collapsed: Live transcript only (no bubbles) -->
            <div class="mock-live-transcript-collapsed" id="collapsedTranscript" data-element-type="liveTranscript">
              <div class="mock-live-indicator" data-element-type="liveIndicator">
                <div class="mock-live-dot"></div>
                <span>LIVE</span>
              </div>
              <div class="mock-live-text-collapsed" data-element-type="liveTranscriptText" style="color: #64748b; font-size: 14px; line-height: 1.6; margin-top: 8px;">
                Hello, I'm Sasha from Bridgewise, How can I help you today?
              </div>
            </div>
            
            <!-- Expanded: Full conversation history -->
            <div class="mock-conversation-history" id="expandedHistory">
              <div class="mock-history-message">
                <div class="mock-history-avatar" data-element-type="agentAvatar" style="cursor: pointer;">
                  ${widgetConfig.voice.avatarType === 'image' && widgetConfig.voice.avatarImageUrl ? `
                    <img src="${widgetConfig.voice.avatarImageUrl}" alt="Agent" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; pointer-events: none;">
                  ` : `
                    <span style="pointer-events: none;">${widgetConfig.voice.avatarIcon || '🤖'}</span>
                  `}
                </div>
                <div class="mock-history-bubble">I help you today?</div>
              </div>
              <div class="mock-history-message">
                <div class="mock-history-avatar" data-element-type="agentAvatar" style="cursor: pointer;">
                  ${widgetConfig.voice.avatarType === 'image' && widgetConfig.voice.avatarImageUrl ? `
                    <img src="${widgetConfig.voice.avatarImageUrl}" alt="Agent" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; pointer-events: none;">
                  ` : `
                    <span style="pointer-events: none;">${widgetConfig.voice.avatarIcon || '🤖'}</span>
                  `}
                </div>
                <div class="mock-history-bubble">I am doing well, thank you for asking.</div>
              </div>
              <div class="mock-live-message-row">
                <div class="mock-history-avatar" data-element-type="agentAvatar" style="cursor: pointer;">
                  ${widgetConfig.voice.avatarType === 'image' && widgetConfig.voice.avatarImageUrl ? `
                    <img src="${widgetConfig.voice.avatarImageUrl}" alt="Agent" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; pointer-events: none;">
                  ` : `
                    <span style="pointer-events: none;">${widgetConfig.voice.avatarIcon || '🤖'}</span>
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
              <input type="text" class="mock-voice-text-input" placeholder="${widgetConfig.text.inputPlaceholder || 'Type your message...'}" data-element-type="voiceInput">
              <button class="mock-voice-send-btn" style="background: ${widgetConfig.text.sendButtonColor};" data-element-type="voiceSendButton">
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
      
      // Make elements selectable
      setupElementListeners(panel);
      
      // Initialize history view state
      setTimeout(() => {
        updateHistoryView();
      }, 0);
    }
    
    function setupElementListeners(panel) {
      // Get all elements with data-element-type, sorted by depth (deepest first)
      const elements = Array.from(panel.querySelectorAll('[data-element-type]'));
      // Sort by depth - deeper elements first so we handle children before parents
      elements.sort((a, b) => {
        const depthA = (a.parentElement.closest('[data-element-type]') ? 1 : 0) + (a.querySelectorAll('[data-element-type]').length);
        const depthB = (b.parentElement.closest('[data-element-type]') ? 1 : 0) + (b.querySelectorAll('[data-element-type]').length);
        return depthB - depthA;
      });
      
      elements.forEach(el => {
        // Skip if this element is inside another element with data-element-type (child elements)
        const parentWithType = el.parentElement.closest('[data-element-type]');
        if (parentWithType && parentWithType !== panel) {
          // This is a child element, make sure it stops propagation
          const newEl = el.cloneNode(true);
          if (el.id) newEl.id = el.id;
          el.parentNode.replaceChild(newEl, el);
          
          newEl.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            handleElementClick(newEl.dataset.elementType, newEl, e);
          });
        } else {
          // This is a top-level element
          const newEl = el.cloneNode(true);
          if (el.id) newEl.id = el.id;
          el.parentNode.replaceChild(newEl, el);
          
          newEl.addEventListener('click', (e) => {
            // Check if click was on a child element with data-element-type
            const clickedElement = e.target.closest('[data-element-type]');
            if (clickedElement && clickedElement !== newEl) {
              // Click was on a child element, don't handle it here
              return;
            }
            e.preventDefault();
            e.stopPropagation();
            handleElementClick(newEl.dataset.elementType, newEl, e);
          });
        }
      });
    }

    function switchView(view) {
      currentView = view;
      renderPanelContent();
    }

    // Make switchView and selectElement available globally
    window.switchView = switchView;
    window.selectElement = selectElement;

    function selectElement(elementType, element, event) {
      // Prevent default action
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }
      
      // Remove previous selection
      document.querySelectorAll('.element-highlight').forEach(el => {
        el.classList.remove('element-highlight');
      });
      
      // Highlight selected element
      element.classList.add('element-highlight');
      selectedElement = { type: elementType, element };
      
      // Show customization controls
      showCustomizationControls(elementType);
    }
    
    let lastClickTime = 0;
    let lastClickElement = null;
    let lastClickType = null;
    
    function handleElementClick(elementType, element, event) {
      const currentTime = Date.now();
      const timeSinceLastClick = currentTime - lastClickTime;
      
      // Check if this is a double click (within 400ms and same element type)
      if (timeSinceLastClick < 400 && lastClickElement && elementType === lastClickType && 
          lastClickElement.dataset.elementType === element.dataset.elementType) {
        // Double click detected - perform normal interaction
        lastClickTime = 0;
        lastClickElement = null;
        lastClickType = null;
        performNormalInteraction(elementType, element, event);
        return;
      }
      
      // Single click - wait to see if there's a second click
      lastClickTime = currentTime;
      lastClickElement = element;
      lastClickType = elementType;
      
      setTimeout(() => {
        // If no second click happened, treat as single click
        if (lastClickTime === currentTime && lastClickElement === element) {
          lastClickTime = 0;
          lastClickElement = null;
          lastClickType = null;
          selectElement(elementType, element, event);
        }
      }, 400);
    }
    
    function performNormalInteraction(elementType, element, event) {
      if (elementType === 'closeButton' || elementType === 'endCallButton' || elementType === 'backButton') {
        // End call or close should go back to landing screen
        switchView('landing');
      } else if (elementType === 'modeCard') {
        const mode = element.getAttribute('data-mode');
        if (mode === 'voice') {
          switchView('voice');
        } else if (mode === 'text') {
          switchView('text');
        }
      } else if (elementType === 'button') {
        // Toggle panel
        panelOpen = !panelOpen;
        const panel = document.getElementById('mockPanel');
        panel.classList.toggle('open');
        if (panelOpen) {
          renderPanelContent();
        }
      } else if (elementType === 'conversationToggle') {
        // Toggle conversation history
        toggleConversationHistory();
      }
      // For other elements, allow normal behavior
    }
    
    function updateHistoryView() {
      const collapsed = document.getElementById('collapsedTranscript');
      const expanded = document.getElementById('expandedHistory');
      const toggleText = document.getElementById('historyToggleText');
      const toggleIcon = document.getElementById('historyToggleIcon');
      const expandedSection = document.getElementById('mockVoiceSectionExpanded');
      const compactSection = document.getElementById('mockVoiceSectionCompact');
      
      console.log('updateHistoryView called, historyExpanded:', historyExpanded);
      console.log('Elements found:', { collapsed: !!collapsed, expanded: !!expanded, toggleText: !!toggleText, toggleIcon: !!toggleIcon, expandedSection: !!expandedSection, compactSection: !!compactSection });
      
      if (!collapsed || !expanded || !toggleText || !toggleIcon) {
        // Elements not found yet, try again after a short delay
        console.log('Elements not found, retrying...');
        setTimeout(updateHistoryView, 100);
        return;
      }
      
      if (historyExpanded) {
        console.log('Expanding history...');
        // Hide collapsed transcript
        collapsed.style.display = 'none';
        // Show expanded history
        expanded.style.display = 'flex';
        expanded.classList.add('expanded');
        // Switch to compact single-row layout
        if (expandedSection) expandedSection.style.display = 'none';
        if (compactSection) compactSection.style.display = 'block';
        // Update toggle text and icon
        toggleText.textContent = 'Hide history';
        const path = toggleIcon.querySelector('path');
        if (path) {
          path.setAttribute('d', 'M18 15l-6-6-6 6');
        } else {
          toggleIcon.innerHTML = '<path d="M18 15l-6-6-6 6" stroke="currentColor" stroke-width="2" fill="none"/>';
        }
      } else {
        console.log('Collapsing history...');
        // Show collapsed transcript
        collapsed.style.display = 'block';
        // Hide expanded history
        expanded.style.display = 'none';
        expanded.classList.remove('expanded');
        // Switch to multi-row expanded layout
        if (expandedSection) expandedSection.style.display = 'block';
        if (compactSection) compactSection.style.display = 'none';
        // Update toggle text and icon
        toggleText.textContent = 'Show history';
        const path = toggleIcon.querySelector('path');
        if (path) {
          path.setAttribute('d', 'M6 9l6 6 6-6');
        } else {
          toggleIcon.innerHTML = '<path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" fill="none"/>';
        }
      }
    }
    
    function toggleConversationHistory() {
      historyExpanded = !historyExpanded;
      console.log('toggleConversationHistory called, new state:', historyExpanded);
      updateHistoryView();
    }

    function showCustomizationControls(elementType) {
      const controlsDiv = document.getElementById('customizationControls');
      let controlsHTML = '';

      switch(elementType) {
        case 'button':
          controlsHTML = `
            <div class="customization-group">
              <h3>Floating Button</h3>
              <div class="control-item">
                <label>Size</label>
                <select id="btnSize">
                  <option value="small">Small</option>
                  <option value="medium" selected>Medium</option>
                  <option value="large">Large</option>
                  <option value="xl">Extra Large</option>
                </select>
              </div>
              <div class="control-item">
                <label>Shape</label>
                <select id="btnShape">
                  <option value="circle" selected>Circle</option>
                  <option value="rounded">Rounded</option>
                  <option value="square">Square</option>
                </select>
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="btnBgColor" value="${widgetConfig.button.backgroundColor}">
              </div>
              <div class="control-item">
                <label>Hover Color</label>
                <input type="color" id="btnHoverColor" value="${widgetConfig.button.hoverColor}">
              </div>
            </div>
            <div class="customization-group">
              <h3>Icon</h3>
              <div class="control-item">
                <label>Type</label>
                <select id="iconType">
                  <option value="custom" ${widgetConfig.icon.type === 'custom' ? 'selected' : ''}>Custom Image</option>
                  <option value="microphone" ${widgetConfig.icon.type === 'microphone' ? 'selected' : ''}>Microphone</option>
                  <option value="emoji" ${widgetConfig.icon.type === 'emoji' ? 'selected' : ''}>Emoji</option>
                  <option value="text" ${widgetConfig.icon.type === 'text' ? 'selected' : ''}>Text</option>
                </select>
              </div>
              <div class="control-item" id="iconCustomImageControl" style="display: ${widgetConfig.icon.type === 'custom' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="iconCustomImage" value="${widgetConfig.icon.customImage || 'https://talktopc.com/logo192.png'}" placeholder="https://talktopc.com/logo192.png">
              </div>
              <div class="control-item" id="iconEmojiControl" style="display: ${widgetConfig.icon.type === 'emoji' ? 'block' : 'none'};">
                <label>Emoji</label>
                <input type="text" id="iconEmoji" value="${widgetConfig.icon.emoji || '🎤'}" placeholder="🎤">
              </div>
              <div class="control-item" id="iconTextControl" style="display: ${widgetConfig.icon.type === 'text' ? 'block' : 'none'};">
                <label>Text</label>
                <input type="text" id="iconText" value="${widgetConfig.icon.text || 'AI'}" placeholder="AI">
              </div>
            </div>
          `;
          break;
        case 'header':
          controlsHTML = `
            <div class="customization-group">
              <h3>Header</h3>
              <div class="control-item">
                <label>Title Text</label>
                <input type="text" id="headerTitle" value="${widgetConfig.header.title}" placeholder="Chat Assistant">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="headerBgColor" value="${widgetConfig.header.backgroundColor}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="headerTextColor" value="${widgetConfig.header.textColor}">
              </div>
              <div class="control-item">
                <label>
                  <input type="checkbox" id="headerShowClose" ${widgetConfig.header.showCloseButton ? 'checked' : ''}>
                  Show Close Button
                </label>
              </div>
            </div>
          `;
          break;
        case 'panel':
          controlsHTML = `
            <div class="customization-group">
              <h3>Panel</h3>
              <div class="control-item">
                <label>Width (px)</label>
                <input type="number" id="panelWidth" value="${widgetConfig.panel.width}">
              </div>
              <div class="control-item">
                <label>Height (px)</label>
                <input type="number" id="panelHeight" value="${widgetConfig.panel.height}">
              </div>
              <div class="control-item">
                <label>Border Radius (px)</label>
                <input type="number" id="panelRadius" value="${widgetConfig.panel.borderRadius}">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="panelBgColor" value="${widgetConfig.panel.backgroundColor}">
              </div>
            </div>
            <div class="customization-group">
              <h3>Widget Position</h3>
              <div class="control-item">
                <label>Vertical Position</label>
                <select id="positionVertical">
                  <option value="bottom" ${widgetConfig.position.vertical === 'bottom' ? 'selected' : ''}>Bottom</option>
                  <option value="top" ${widgetConfig.position.vertical === 'top' ? 'selected' : ''}>Top</option>
                </select>
              </div>
              <div class="control-item">
                <label>Horizontal Position</label>
                <select id="positionHorizontal">
                  <option value="right" ${widgetConfig.position.horizontal === 'right' ? 'selected' : ''}>Right</option>
                  <option value="left" ${widgetConfig.position.horizontal === 'left' ? 'selected' : ''}>Left</option>
                </select>
              </div>
              <div class="control-item">
                <label>Offset X (px)</label>
                <input type="number" id="positionOffsetX" value="${widgetConfig.position.offset.x}">
              </div>
              <div class="control-item">
                <label>Offset Y (px)</label>
                <input type="number" id="positionOffsetY" value="${widgetConfig.position.offset.y}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Position of the floating button and widget on the page.
              </p>
            </div>
            <div class="customization-group">
              <h3>Text Direction</h3>
              <div class="control-item">
                <label>Direction</label>
                <select id="direction">
                  <option value="ltr" ${widgetConfig.direction === 'ltr' ? 'selected' : ''}>Left to Right (LTR)</option>
                  <option value="rtl" ${widgetConfig.direction === 'rtl' ? 'selected' : ''}>Right to Left (RTL)</option>
                </select>
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Text direction for the widget. Use RTL for languages like Arabic or Hebrew.
              </p>
            </div>
          `;
          break;
        case 'position':
          controlsHTML = `
            <div class="customization-group">
              <h3>Widget Position</h3>
              <div class="control-item">
                <label>Vertical Position</label>
                <select id="positionVertical">
                  <option value="bottom" ${widgetConfig.position.vertical === 'bottom' ? 'selected' : ''}>Bottom</option>
                  <option value="top" ${widgetConfig.position.vertical === 'top' ? 'selected' : ''}>Top</option>
                </select>
              </div>
              <div class="control-item">
                <label>Horizontal Position</label>
                <select id="positionHorizontal">
                  <option value="right" ${widgetConfig.position.horizontal === 'right' ? 'selected' : ''}>Right</option>
                  <option value="left" ${widgetConfig.position.horizontal === 'left' ? 'selected' : ''}>Left</option>
                </select>
              </div>
              <div class="control-item">
                <label>Offset X (px)</label>
                <input type="number" id="positionOffsetX" value="${widgetConfig.position.offset.x}">
              </div>
              <div class="control-item">
                <label>Offset Y (px)</label>
                <input type="number" id="positionOffsetY" value="${widgetConfig.position.offset.y}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Position of the floating button and widget on the page.
              </p>
            </div>
          `;
          break;
        case 'direction':
          controlsHTML = `
            <div class="customization-group">
              <h3>Text Direction</h3>
              <div class="control-item">
                <label>Direction</label>
                <select id="direction">
                  <option value="ltr" ${widgetConfig.direction === 'ltr' ? 'selected' : ''}>Left to Right (LTR)</option>
                  <option value="rtl" ${widgetConfig.direction === 'rtl' ? 'selected' : ''}>Right to Left (RTL)</option>
                </select>
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Text direction for the widget. Use RTL for languages like Arabic or Hebrew.
              </p>
            </div>
          `;
          break;
        case 'userMessage':
        case 'agentMessage':
          controlsHTML = `
            <div class="customization-group">
              <h3>Messages</h3>
              <div class="control-item">
                <label>User Message Background</label>
                <input type="color" id="msgUserBg" value="${widgetConfig.messages.userBackgroundColor}">
              </div>
              <div class="control-item">
                <label>Agent Message Background</label>
                <input type="color" id="msgAgentBg" value="${widgetConfig.messages.agentBackgroundColor}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="msgTextColor" value="${widgetConfig.messages.textColor}">
              </div>
              <div class="control-item">
                <label>Font Size</label>
                <input type="text" id="msgFontSize" value="${widgetConfig.messages.fontSize}" placeholder="14px">
              </div>
              <div class="control-item">
                <label>Border Radius (px)</label>
                <input type="number" id="msgRadius" value="${widgetConfig.messages.borderRadius}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                Note: Message text content is controlled by your agent, not the widget configuration.
              </p>
            </div>
          `;
          break;
        case 'sendButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Send Button</h3>
              <div class="control-item">
                <label>Button Text/Icon</label>
                <input type="text" id="sendButtonText" value="→" placeholder="→ or Send">
              </div>
              <div class="control-item">
                <label>Color</label>
                <input type="color" id="sendBtnColor" value="${widgetConfig.text.sendButtonColor}">
              </div>
              <div class="control-item">
                <label>Hover Color</label>
                <input type="color" id="sendBtnHover" value="${widgetConfig.text.sendButtonHoverColor}">
              </div>
            </div>
            <div class="customization-group">
              <h3>Input Field</h3>
              <div class="control-item">
                <label>Placeholder Text</label>
                <input type="text" id="inputPlaceholder" value="${widgetConfig.text.inputPlaceholder}" placeholder="Type your message...">
              </div>
              <div class="control-item">
                <label>Focus Color</label>
                <input type="color" id="inputFocusColor" value="${widgetConfig.text.inputFocusColor}">
              </div>
            </div>
          `;
          break;
        case 'startCallButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Start Call Button</h3>
              <div class="control-item">
                <label>Button Text</label>
                <input type="text" id="startCallBtnText" value="Start Call" placeholder="Start Call">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="startCallBtnColor" value="${widgetConfig.voice.startCallButtonColor}">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="startCallBtnTextColor" value="${widgetConfig.voice.startCallButtonTextColor}">
              </div>
            </div>
          `;
          break;
        case 'micButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Microphone Button</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="micBtnColor" value="${widgetConfig.voice.micButtonColor}">
              </div>
              <div class="control-item">
                <label>Active/Muted Color</label>
                <input type="color" id="micBtnActive" value="${widgetConfig.voice.micButtonActiveColor}">
              </div>
            </div>
          `;
          break;
        case 'endCallButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>End Call Button</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="endCallBtnColor" value="#ef4444">
              </div>
            </div>
          `;
          break;
        case 'speakerButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Speaker Button</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="speakerBtnColor" value="#FFFFFF">
              </div>
            </div>
          `;
          break;
        case 'timer':
          controlsHTML = `
            <div class="customization-group">
              <h3>Call Timer</h3>
              <div class="control-item">
                <label>Timer Dot Color</label>
                <input type="color" id="timerDotColor" value="#ef4444">
              </div>
              <div class="control-item">
                <label>Timer Text Color</label>
                <input type="color" id="timerTextColor" value="#64748b">
              </div>
            </div>
          `;
          break;
        case 'waveform':
          controlsHTML = `
            <div class="customization-group">
              <h3>Waveform Visualizer</h3>
              <div class="control-item">
                <label>Display Type</label>
                <select id="waveformType">
                  <option value="waveform" ${widgetConfig.voice.waveformType === 'waveform' ? 'selected' : ''}>Waveform</option>
                  <option value="icon" ${widgetConfig.voice.waveformType === 'icon' ? 'selected' : ''}>Icon</option>
                  <option value="image" ${widgetConfig.voice.waveformType === 'image' ? 'selected' : ''}>Image URL</option>
                </select>
              </div>
              <div class="control-item" id="waveformColorControl">
                <label>Waveform Color</label>
                <input type="color" id="waveformColor" value="${widgetConfig.voice.micButtonColor}">
              </div>
              <div class="control-item" id="waveformIconControl" style="display: ${widgetConfig.voice.waveformType === 'icon' ? 'block' : 'none'};">
                <label>Icon (Emoji or Text)</label>
                <input type="text" id="waveformIcon" value="${widgetConfig.voice.waveformIcon || '🎤'}" placeholder="🎤">
              </div>
              <div class="control-item" id="waveformImageControl" style="display: ${widgetConfig.voice.waveformType === 'image' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="waveformImageUrl" value="${widgetConfig.voice.waveformImageUrl || ''}" placeholder="https://example.com/image.png">
              </div>
            </div>
          `;
          break;
        case 'statusTitle':
          controlsHTML = `
            <div class="customization-group">
              <h3>Status Text</h3>
              <div class="control-item">
                <label>Status Text</label>
                <input type="text" id="statusText" value="Listening..." placeholder="Listening...">
              </div>
              <div class="control-item">
                <label>Text Color</label>
                <input type="color" id="statusTitleColor" value="${widgetConfig.voice.statusTitleColor}">
              </div>
              <div class="control-item">
                <label>Status Dot Color</label>
                <input type="color" id="statusDotColor" value="#10b981">
              </div>
            </div>
          `;
          break;
        case 'liveTranscript':
        case 'liveTranscriptText':
          controlsHTML = `
            <div class="customization-group">
              <h3>Live Transcript (Collapsed View)</h3>
              <div class="control-item">
                <label>Transcript Text Color</label>
                <input type="color" id="liveTranscriptColor" value="#64748b">
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
          break;
        case 'voiceInput':
        case 'voiceSendButton':
          controlsHTML = `
            <div class="customization-group">
              <h3>Voice Text Input</h3>
              <div class="control-item">
                <label>Placeholder Text</label>
                <input type="text" id="voiceInputPlaceholder" value="${widgetConfig.text.inputPlaceholder}" placeholder="Type your message...">
              </div>
              <div class="control-item">
                <label>Send Button Color</label>
                <input type="color" id="voiceSendBtnColor" value="${widgetConfig.text.sendButtonColor}">
              </div>
            </div>
          `;
          break;
        case 'voiceAvatar':
          controlsHTML = `
            <div class="customization-group">
              <h3>Voice Avatar</h3>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="avatarBg" value="${widgetConfig.voice.avatarBackgroundColor}">
              </div>
            </div>
          `;
          break;
        case 'conversationHistory':
          controlsHTML = `
            <div class="customization-group">
              <h3>Conversation History</h3>
              <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                This is the expanded conversation history view showing all messages.
              </p>
            </div>
          `;
          break;
        case 'agentAvatar':
          controlsHTML = `
            <div class="customization-group">
              <h3>Agent Avatar (Next to Messages)</h3>
              <div class="control-item">
                <label>Display Type</label>
                <select id="agentAvatarType">
                  <option value="icon" ${widgetConfig.voice.avatarType === 'icon' || !widgetConfig.voice.avatarType ? 'selected' : ''}>Icon</option>
                  <option value="image" ${widgetConfig.voice.avatarType === 'image' ? 'selected' : ''}>Image URL</option>
                </select>
              </div>
              <div class="control-item" id="agentAvatarIconControl" style="display: ${widgetConfig.voice.avatarType === 'image' ? 'none' : 'block'};">
                <label>Icon (Emoji or Text)</label>
                <input type="text" id="agentAvatarIcon" value="${widgetConfig.voice.avatarIcon || '🤖'}" placeholder="🤖">
              </div>
              <div class="control-item" id="agentAvatarImageControl" style="display: ${widgetConfig.voice.avatarType === 'image' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="agentAvatarImageUrl" value="${widgetConfig.voice.avatarImageUrl || ''}" placeholder="https://example.com/avatar.png">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="agentAvatarBg" value="${widgetConfig.voice.avatarBackgroundColor}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                This icon appears next to agent messages in the conversation history.
              </p>
            </div>
          `;
          break;
        case 'liveIndicator':
          controlsHTML = `
            <div class="customization-group">
              <h3>Live Indicator</h3>
              <div class="control-item">
                <label>Live Dot Color</label>
                <input type="color" id="liveDotColor" value="#10b981">
              </div>
              <div class="control-item">
                <label>Live Text Color</label>
                <input type="color" id="liveTextColor" value="#10b981">
              </div>
            </div>
          `;
          break;
        case 'landingLogo':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Logo</h3>
              <div class="control-item">
                <label>Display Type</label>
                <select id="logoType">
                  <option value="icon" ${widgetConfig.landing.logoType === 'icon' || !widgetConfig.landing.logoType ? 'selected' : ''}>Icon</option>
                  <option value="image" ${widgetConfig.landing.logoType === 'image' ? 'selected' : ''}>Image URL</option>
                </select>
              </div>
              <div class="control-item" id="logoIconControl" style="display: ${widgetConfig.landing.logoType === 'image' ? 'none' : 'block'};">
                <label>Icon (Emoji or Text)</label>
                <input type="text" id="landingLogoIcon" value="${widgetConfig.landing.logoIcon || widgetConfig.landing.logo || '🤖'}" placeholder="🤖">
              </div>
              <div class="control-item" id="logoImageControl" style="display: ${widgetConfig.landing.logoType === 'image' ? 'block' : 'none'};">
                <label>Image URL</label>
                <input type="text" id="landingLogoImageUrl" value="${widgetConfig.landing.logoImageUrl || ''}" placeholder="https://example.com/logo.png">
              </div>
            </div>
          `;
          break;
        case 'landingTitle':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Title</h3>
              <div class="control-item">
                <label>Title Text</label>
                <input type="text" id="landingTitleText" value="${widgetConfig.landing.title || 'Welcome to AI Assistant'}" placeholder="Welcome to AI Assistant">
              </div>
              <div class="control-item">
                <label>Title Color</label>
                <input type="color" id="landingTitleColor" value="${widgetConfig.landing.titleColor}">
              </div>
            </div>
          `;
          break;
        case 'landingSubtitle':
          controlsHTML = `
            <div class="customization-group">
              <h3>Landing Screen - Subtitle</h3>
              <div class="control-item">
                <label>Subtitle Text</label>
                <input type="text" id="landingSubtitleText" value="${widgetConfig.landing.subtitle || 'Choose how you\'d like to interact'}" placeholder="Choose how you'd like to interact">
              </div>
              <div class="control-item">
                <label>Subtitle Color</label>
                <input type="color" id="landingSubtitleColor" value="${widgetConfig.landing.subtitleColor}">
              </div>
            </div>
          `;
          break;
        case 'modeCard':
          controlsHTML = `
            <div class="customization-group">
              <h3>Mode Cards (Voice/Text Buttons)</h3>
              <div class="control-item">
                <label>Voice Card Title</label>
                <input type="text" id="voiceCardTitle" value="Voice Call" placeholder="Voice Call">
              </div>
              <div class="control-item">
                <label>Text Card Title</label>
                <input type="text" id="textCardTitle" value="Text Chat" placeholder="Text Chat">
              </div>
              <div class="control-item">
                <label>Background Color</label>
                <input type="color" id="modeCardBg" value="${widgetConfig.landing.modeCardBackgroundColor}">
              </div>
              <p style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                These are the buttons on the landing screen that let users choose between voice and text chat.
              </p>
            </div>
          `;
          break;
        default:
          controlsHTML = `
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

      controlsDiv.innerHTML = controlsHTML;
      
      // Scroll controls to top so user can see them
      controlsDiv.scrollTop = 0;
      
      // Attach event listeners based on element type
      attachControlListeners(elementType);
      
      // Update code output
      updateConfigCode();
      
      // Ensure controls are visible by scrolling the panel if needed
      setTimeout(() => {
        const firstControl = controlsDiv.querySelector('.control-item');
        if (firstControl) {
          firstControl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }, 100);
    }

    function attachControlListeners(elementType) {
      // Get controls div reference (it was already updated in showCustomizationControls)
      const controlsDiv = document.getElementById('customizationControls');
      if (!controlsDiv) return;
      
      // Button controls
      if (elementType === 'button') {
        document.getElementById('btnSize')?.addEventListener('change', (e) => {
          widgetConfig.button.size = e.target.value;
          markPropertyModified('button', 'size');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('btnShape')?.addEventListener('change', (e) => {
          widgetConfig.button.shape = e.target.value;
          markPropertyModified('button', 'shape');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('btnBgColor')?.addEventListener('input', (e) => {
          widgetConfig.button.backgroundColor = e.target.value;
          markPropertyModified('button', 'backgroundColor');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconType')?.addEventListener('change', (e) => {
          widgetConfig.icon.type = e.target.value;
          markPropertyModified('icon', 'type');
          // Show/hide relevant controls
          const customImageControl = document.getElementById('iconCustomImageControl');
          const emojiControl = document.getElementById('iconEmojiControl');
          const textControl = document.getElementById('iconTextControl');
          if (customImageControl) customImageControl.style.display = e.target.value === 'custom' ? 'block' : 'none';
          if (emojiControl) emojiControl.style.display = e.target.value === 'emoji' ? 'block' : 'none';
          if (textControl) textControl.style.display = e.target.value === 'text' ? 'block' : 'none';
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconCustomImage')?.addEventListener('input', (e) => {
          widgetConfig.icon.customImage = e.target.value;
          markPropertyModified('icon', 'customImage');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconEmoji')?.addEventListener('input', (e) => {
          widgetConfig.icon.emoji = e.target.value;
          markPropertyModified('icon', 'emoji');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
        document.getElementById('iconText')?.addEventListener('input', (e) => {
          widgetConfig.icon.text = e.target.value;
          markPropertyModified('icon', 'text');
          applyButtonStyles(document.getElementById('mockButton'));
          updateConfigCode();
        });
      }

      // Header controls
      if (elementType === 'header') {
        document.getElementById('headerTitle')?.addEventListener('input', (e) => {
          widgetConfig.header.title = e.target.value;
          markPropertyModified('header', 'title');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('headerBgColor')?.addEventListener('input', (e) => {
          // Update header backgroundColor in widgetConfig
          widgetConfig.header.backgroundColor = e.target.value;
          markPropertyModified('header', 'backgroundColor');
          // Update visual preview
          renderPanelContent();
          // Update config code display to show the change
          updateConfigCode();
          // Debug: Log to verify the change
          console.log('Header backgroundColor updated to:', widgetConfig.header.backgroundColor);
        });
        document.getElementById('headerTextColor')?.addEventListener('input', (e) => {
          widgetConfig.header.textColor = e.target.value;
          markPropertyModified('header', 'textColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('headerShowClose')?.addEventListener('change', (e) => {
          widgetConfig.header.showCloseButton = e.target.checked;
          markPropertyModified('header', 'showCloseButton');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Panel controls
      if (elementType === 'panel') {
        ['Width', 'Height', 'Radius'].forEach(prop => {
          const id = `panel${prop}`;
          document.getElementById(id)?.addEventListener('input', (e) => {
            const key = prop.toLowerCase();
            widgetConfig.panel[key] = parseInt(e.target.value);
            markPropertyModified('panel', key);
            renderPanelContent();
            updateConfigCode();
          });
        });
        document.getElementById('panelBgColor')?.addEventListener('input', (e) => {
          widgetConfig.panel.backgroundColor = e.target.value;
          markPropertyModified('panel', 'backgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
        // Position controls (included in panel settings)
        document.getElementById('positionVertical')?.addEventListener('change', (e) => {
          widgetConfig.position.vertical = e.target.value;
          markPropertyModified('position', 'vertical');
          updateConfigCode();
        });
        document.getElementById('positionHorizontal')?.addEventListener('change', (e) => {
          widgetConfig.position.horizontal = e.target.value;
          markPropertyModified('position', 'horizontal');
          updateConfigCode();
        });
        document.getElementById('positionOffsetX')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.x = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'x');
          updateConfigCode();
        });
        document.getElementById('positionOffsetY')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.y = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'y');
          updateConfigCode();
        });
        // Direction controls (included in panel settings)
        document.getElementById('direction')?.addEventListener('change', (e) => {
          widgetConfig.direction = e.target.value;
          markPropertyModified('direction');
          updateConfigCode();
        });
      }

      // Position controls
      if (elementType === 'position') {
        document.getElementById('positionVertical')?.addEventListener('change', (e) => {
          widgetConfig.position.vertical = e.target.value;
          markPropertyModified('position', 'vertical');
          updateConfigCode();
        });
        document.getElementById('positionHorizontal')?.addEventListener('change', (e) => {
          widgetConfig.position.horizontal = e.target.value;
          markPropertyModified('position', 'horizontal');
          updateConfigCode();
        });
        document.getElementById('positionOffsetX')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.x = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'x');
          updateConfigCode();
        });
        document.getElementById('positionOffsetY')?.addEventListener('input', (e) => {
          widgetConfig.position.offset.y = parseInt(e.target.value) || 0;
          markPropertyModified('position', 'offset', 'y');
          updateConfigCode();
        });
      }

      // Direction controls
      if (elementType === 'direction') {
        document.getElementById('direction')?.addEventListener('change', (e) => {
          widgetConfig.direction = e.target.value;
          markPropertyModified('direction');
          updateConfigCode();
        });
      }

      // Message controls
      if (elementType === 'userMessage' || elementType === 'agentMessage') {
        document.getElementById('msgUserBg')?.addEventListener('input', (e) => {
          widgetConfig.messages.userBackgroundColor = e.target.value;
          markPropertyModified('messages', 'userBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgAgentBg')?.addEventListener('input', (e) => {
          widgetConfig.messages.agentBackgroundColor = e.target.value;
          markPropertyModified('messages', 'agentBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgTextColor')?.addEventListener('input', (e) => {
          widgetConfig.messages.textColor = e.target.value;
          markPropertyModified('messages', 'textColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgRadius')?.addEventListener('input', (e) => {
          widgetConfig.messages.borderRadius = parseInt(e.target.value);
          markPropertyModified('messages', 'borderRadius');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('msgFontSize')?.addEventListener('input', (e) => {
          widgetConfig.messages.fontSize = e.target.value;
          markPropertyModified('messages', 'fontSize');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Send button and input controls
      if (elementType === 'sendButton') {
        document.getElementById('sendButtonText')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonText = e.target.value;
          markPropertyModified('text', 'sendButtonText');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('sendBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonColor = e.target.value;
          markPropertyModified('text', 'sendButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('sendBtnHover')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonHoverColor = e.target.value;
          markPropertyModified('text', 'sendButtonHoverColor');
          updateConfigCode();
        });
        document.getElementById('inputPlaceholder')?.addEventListener('input', (e) => {
          widgetConfig.text.inputPlaceholder = e.target.value;
          markPropertyModified('text', 'inputPlaceholder');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('inputFocusColor')?.addEventListener('input', (e) => {
          widgetConfig.text.inputFocusColor = e.target.value;
          markPropertyModified('text', 'inputFocusColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Start call button controls
      if (elementType === 'startCallButton') {
        document.getElementById('startCallBtnText')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonText = e.target.value;
          markPropertyModified('voice', 'startCallButtonText');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('startCallBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonColor = e.target.value;
          markPropertyModified('voice', 'startCallButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('startCallBtnTextColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.startCallButtonTextColor = e.target.value;
          markPropertyModified('voice', 'startCallButtonTextColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Mic button controls
      if (elementType === 'micButton') {
        document.getElementById('micBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.micButtonColor = e.target.value;
          markPropertyModified('voice', 'micButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('micBtnActive')?.addEventListener('input', (e) => {
          widgetConfig.voice.micButtonActiveColor = e.target.value;
          markPropertyModified('voice', 'micButtonActiveColor');
          updateConfigCode();
        });
      }

      // Voice avatar controls (for voice interface)
      if (elementType === 'voiceAvatar') {
        document.getElementById('avatarBg')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarBackgroundColor = e.target.value;
          markPropertyModified('voice', 'avatarBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Agent avatar controls (for conversation history)
      if (elementType === 'agentAvatar') {
        document.getElementById('agentAvatarType')?.addEventListener('change', (e) => {
          widgetConfig.voice.avatarType = e.target.value;
          markPropertyModified('voice', 'avatarType');
          // Show/hide relevant controls
          const iconControl = document.getElementById('agentAvatarIconControl');
          const imageControl = document.getElementById('agentAvatarImageControl');
          if (iconControl) iconControl.style.display = e.target.value === 'icon' ? 'block' : 'none';
          if (imageControl) imageControl.style.display = e.target.value === 'image' ? 'block' : 'none';
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('agentAvatarIcon')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarIcon = e.target.value;
          markPropertyModified('voice', 'avatarIcon');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('agentAvatarImageUrl')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarImageUrl = e.target.value;
          markPropertyModified('voice', 'avatarImageUrl');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('agentAvatarBg')?.addEventListener('input', (e) => {
          widgetConfig.voice.avatarBackgroundColor = e.target.value;
          markPropertyModified('voice', 'avatarBackgroundColor');
          const avatars = document.querySelectorAll('.mock-history-avatar');
          avatars.forEach(avatar => {
            avatar.style.background = e.target.value;
          });
          updateConfigCode();
        });
      }

      // Status text controls
      if (elementType === 'statusTitle' || elementType === 'statusSubtitle') {
        document.getElementById('statusText')?.addEventListener('input', (e) => {
          // Update status text in both regular and compact views
          const statusEls = document.querySelectorAll('.mock-voice-status span, .mock-compact-status span');
          statusEls.forEach(el => el.textContent = e.target.value);
          updateConfigCode();
        });
        document.getElementById('statusTitleColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.statusTitleColor = e.target.value;
          markPropertyModified('voice', 'statusTitleColor');
          const statusEls = document.querySelectorAll('.mock-voice-status, .mock-compact-status');
          statusEls.forEach(el => el.style.color = e.target.value);
          updateConfigCode();
        });
        document.getElementById('statusDotColor')?.addEventListener('input', (e) => {
          const dots = document.querySelectorAll('.mock-status-dot');
          dots.forEach(dot => dot.style.background = e.target.value);
          updateConfigCode();
        });
        document.getElementById('statusSubtitleColor')?.addEventListener('input', (e) => {
          widgetConfig.voice.statusSubtitleColor = e.target.value;
          markPropertyModified('voice', 'statusSubtitleColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Live indicator controls
      if (elementType === 'liveIndicator') {
        document.getElementById('liveDotColor')?.addEventListener('input', (e) => {
          const dots = document.querySelectorAll('.mock-live-dot');
          dots.forEach(dot => dot.style.background = e.target.value);
          updateConfigCode();
        });
        document.getElementById('liveTextColor')?.addEventListener('input', (e) => {
          const indicators = document.querySelectorAll('.mock-live-indicator');
          indicators.forEach(ind => ind.style.color = e.target.value);
          updateConfigCode();
        });
      }

      // Timer controls
      if (elementType === 'timer') {
        document.getElementById('timerDotColor')?.addEventListener('input', (e) => {
          const dots = document.querySelectorAll('.mock-timer-dot');
          dots.forEach(dot => dot.style.background = e.target.value);
          updateConfigCode();
        });
        document.getElementById('timerTextColor')?.addEventListener('input', (e) => {
          const timers = document.querySelectorAll('.mock-voice-timer, .mock-compact-timer');
          timers.forEach(timer => timer.style.color = e.target.value);
          updateConfigCode();
        });
      }

      // End call button controls
      if (elementType === 'endCallButton') {
        document.getElementById('endCallBtnColor')?.addEventListener('input', (e) => {
          const buttons = document.querySelectorAll('[data-element-type="endCallButton"]');
          buttons.forEach(btn => btn.style.background = e.target.value);
          updateConfigCode();
        });
      }

      // Speaker button controls
      if (elementType === 'speakerButton') {
        document.getElementById('speakerBtnColor')?.addEventListener('input', (e) => {
          const buttons = document.querySelectorAll('[data-element-type="speakerButton"]');
          buttons.forEach(btn => btn.style.background = e.target.value);
          updateConfigCode();
        });
      }

      // Waveform controls
      if (elementType === 'waveform') {
        document.getElementById('waveformType')?.addEventListener('change', (e) => {
          widgetConfig.voice.waveformType = e.target.value;
          markPropertyModified('voice', 'waveformType');
          // Show/hide relevant controls
          const colorControl = document.getElementById('waveformColorControl');
          const iconControl = document.getElementById('waveformIconControl');
          const imageControl = document.getElementById('waveformImageControl');
          if (colorControl) colorControl.style.display = e.target.value === 'waveform' ? 'block' : 'none';
          if (iconControl) iconControl.style.display = e.target.value === 'icon' ? 'block' : 'none';
          if (imageControl) imageControl.style.display = e.target.value === 'image' ? 'block' : 'none';
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('waveformColor')?.addEventListener('input', (e) => {
          const bars = document.querySelectorAll('.mock-waveform-bar');
          bars.forEach(bar => bar.style.background = e.target.value);
          widgetConfig.voice.micButtonColor = e.target.value; // Store for config
          markPropertyModified('voice', 'micButtonColor');
          updateConfigCode();
        });
        document.getElementById('waveformIcon')?.addEventListener('input', (e) => {
          widgetConfig.voice.waveformIcon = e.target.value;
          markPropertyModified('voice', 'waveformIcon');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('waveformImageUrl')?.addEventListener('input', (e) => {
          widgetConfig.voice.waveformImageUrl = e.target.value;
          markPropertyModified('voice', 'waveformImageUrl');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Live transcript controls
      if (elementType === 'liveTranscript' || elementType === 'liveTranscriptText') {
        document.getElementById('liveTranscriptColor')?.addEventListener('input', (e) => {
          const text = document.querySelector('.mock-live-text-collapsed');
          if (text) text.style.color = e.target.value;
          updateConfigCode();
        });
        document.getElementById('liveTranscriptFontSize')?.addEventListener('input', (e) => {
          const text = document.querySelector('.mock-live-text-collapsed');
          if (text) text.style.fontSize = e.target.value;
          updateConfigCode();
        });
      }

      // Voice input controls
      if (elementType === 'voiceInput' || elementType === 'voiceSendButton') {
        document.getElementById('voiceInputPlaceholder')?.addEventListener('input', (e) => {
          widgetConfig.text.inputPlaceholder = e.target.value;
          markPropertyModified('text', 'inputPlaceholder');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('voiceSendBtnColor')?.addEventListener('input', (e) => {
          widgetConfig.text.sendButtonColor = e.target.value;
          markPropertyModified('text', 'sendButtonColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      // Landing screen controls
      if (elementType === 'landingLogo') {
        document.getElementById('logoType')?.addEventListener('change', (e) => {
          widgetConfig.landing.logoType = e.target.value;
          markPropertyModified('landing', 'logoType');
          // Show/hide relevant controls
          const iconControl = document.getElementById('logoIconControl');
          const imageControl = document.getElementById('logoImageControl');
          if (iconControl) iconControl.style.display = e.target.value === 'icon' ? 'block' : 'none';
          if (imageControl) imageControl.style.display = e.target.value === 'image' ? 'block' : 'none';
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingLogoIcon')?.addEventListener('input', (e) => {
          widgetConfig.landing.logoIcon = e.target.value;
          widgetConfig.landing.logo = e.target.value; // Keep backward compatibility
          markPropertyModified('landing', 'logoIcon');
          markPropertyModified('landing', 'logo');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingLogoImageUrl')?.addEventListener('input', (e) => {
          widgetConfig.landing.logoImageUrl = e.target.value;
          markPropertyModified('landing', 'logoImageUrl');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'landingTitle') {
        document.getElementById('landingTitleText')?.addEventListener('input', (e) => {
          widgetConfig.landing.title = e.target.value;
          markPropertyModified('landing', 'title');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingTitleColor')?.addEventListener('input', (e) => {
          widgetConfig.landing.titleColor = e.target.value;
          markPropertyModified('landing', 'titleColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'landingSubtitle') {
        document.getElementById('landingSubtitleText')?.addEventListener('input', (e) => {
          widgetConfig.landing.subtitle = e.target.value;
          markPropertyModified('landing', 'subtitle');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('landingSubtitleColor')?.addEventListener('input', (e) => {
          widgetConfig.landing.subtitleColor = e.target.value;
          markPropertyModified('landing', 'subtitleColor');
          renderPanelContent();
          updateConfigCode();
        });
      }

      if (elementType === 'modeCard') {
        document.getElementById('voiceCardTitle')?.addEventListener('input', (e) => {
          widgetConfig.landing.voiceCardTitle = e.target.value;
          markPropertyModified('landing', 'voiceCardTitle');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('textCardTitle')?.addEventListener('input', (e) => {
          widgetConfig.landing.textCardTitle = e.target.value;
          markPropertyModified('landing', 'textCardTitle');
          renderPanelContent();
          updateConfigCode();
        });
        document.getElementById('modeCardBg')?.addEventListener('input', (e) => {
          widgetConfig.landing.modeCardBackgroundColor = e.target.value;
          markPropertyModified('landing', 'modeCardBackgroundColor');
          renderPanelContent();
          updateConfigCode();
        });
      }
    }

    function updateConfigCode() {
      const codeOutput = document.getElementById('configCode');
      
      // Build minimal config with only agentId, appId, and modified properties
      const displayConfig = buildMinimalConfig();
      
      // Format as proper JavaScript object with comments
      let configStr = JSON.stringify(displayConfig, null, 2);
      
      // Add helpful comments for agentId and appId
      const agentId = displayConfig.agentId || 'your_agent_id';
      const appId = displayConfig.appId || 'your_app_id';
      configStr = configStr
        .replace(new RegExp(`"agentId": "${agentId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"`, 'g'), 
                 `"agentId": "${agentId}"  // Required: Your agent ID`)
        .replace(new RegExp(`"appId": "${appId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"`, 'g'), 
                 `"appId": "${appId}"  // Required: Your app ID`);
      
      codeOutput.textContent = `const widget = new TTPChatWidget(${configStr});`;
    }

    function resetToDefaults() {
      // Clear all modified properties tracking
      Object.keys(modifiedProperties).forEach(key => delete modifiedProperties[key]);
      
      widgetConfig = {
        button: {
          size: 'medium',
          shape: 'circle',
          backgroundColor: '#FFFFFF',
          hoverColor: '#D3D3D3', // SDK default: light gray
          shadow: true,
          shadowColor: 'rgba(0,0,0,0.15)'
        },
        icon: {
          type: 'custom',
          customImage: 'https://talktopc.com/logo192.png',
          size: 'medium',
          backgroundColor: '#FFFFFF'
        },
        panel: {
          width: 360,
          height: 550, // Updated from SDK default 500
          borderRadius: 24,
          backgroundColor: '#FFFFFF',
          border: '1px solid #E5E7EB'
        },
        direction: 'ltr',
        position: {
          vertical: 'bottom',
          horizontal: 'right',
          offset: { x: 20, y: 20 }
        },
        header: {
          title: 'Chat Assistant',
          backgroundColor: '#7C3AED',
          textColor: '#FFFFFF',
          showCloseButton: true
        },
        messages: {
          userBackgroundColor: '#E5E7EB',
          agentBackgroundColor: '#F3F4F6',
          textColor: '#1F2937',
          fontSize: '14px',
          borderRadius: 16
        },
        text: {
          sendButtonText: '→',
          sendButtonColor: '#7C3AED',
          sendButtonHoverColor: '#6D28D9',
          inputPlaceholder: 'Type your message...',
          inputFocusColor: '#7C3AED'
        },
        voice: {
          micButtonColor: '#7C3AED',
          micButtonActiveColor: '#EF4444',
          avatarBackgroundColor: '#667eea',
          avatarType: 'icon',
          avatarIcon: '🤖',
          avatarImageUrl: '',
          startCallButtonText: 'Start Call',
          startCallButtonColor: '#667eea',
          startCallButtonTextColor: '#FFFFFF',
          statusTitleColor: '#1e293b',
          statusSubtitleColor: '#64748b',
          waveformType: 'waveform',
          waveformIcon: '🎤',
          waveformImageUrl: ''
        },
        landing: {
          logo: '🤖',
          logoType: 'icon',
          logoIcon: '🤖',
          logoImageUrl: '',
          title: 'Welcome to AI Assistant',
          subtitle: 'Choose how you\'d like to interact',
        voiceCardTitle: 'Voice Call',
        textCardTitle: 'Text Chat',
          titleColor: '#1e293b',
          subtitleColor: '#64748b',
          modeCardBackgroundColor: '#FFFFFF'
        },
        position: {
          vertical: 'bottom',
          horizontal: 'right',
          offset: { x: 20, y: 20 }
        }
      };
      
      initMockWidget();
      updateConfigCode();
      selectedElement = null;
      document.querySelectorAll('.element-highlight').forEach(el => {
        el.classList.remove('element-highlight');
      });
      showCustomizationControls('default');
    }

    // No edit mode toggle needed - single click = edit, double click = interact

    // Initialize event listeners (moved to document ready)
    $(document).ready(function() {
        $('#resetBtn').on('click', resetToDefaults);
    });

    // Add a way to select panel via a small indicator
    function addPanelSelector() {
      const panel = document.getElementById('mockPanel');
      if (!panel) return;
      if (!panel.querySelector('.panel-selector')) {
        const selector = document.createElement('div');
        selector.className = 'panel-selector';
        selector.style.cssText = 'position: absolute; top: 8px; right: 8px; width: 24px; height: 24px; background: rgba(102, 126, 234, 0.2); border: 2px dashed #667eea; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; z-index: 1001;';
        selector.textContent = '⚙️';
        selector.title = 'Click to customize panel';
        selector.addEventListener('click', (e) => {
          e.stopPropagation();
          selectElement('panel', panel);
        });
        panel.appendChild(selector);
      }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('TalkToPC Widget Customization: DOM ready, initializing...');
        
        // Check if required elements exist
        const mockButton = document.getElementById('mockButton');
        const mockPanel = document.getElementById('mockPanel');
        const customizationControls = document.getElementById('customizationControls');
        const saveBtn = document.getElementById('saveCustomizationBtn');
        
        if (!mockButton || !mockPanel) {
            console.error('TalkToPC Widget Customization: Required elements not found!', {
                mockButton: !!mockButton,
                mockPanel: !!mockPanel,
                customizationControls: !!customizationControls,
                saveBtn: !!saveBtn
            });
            return;
        }
        
        initMockWidget();
        updateConfigCode();
        
        // Make panel selectable (click on panel border/background)
        if (mockPanel) {
            mockPanel.addEventListener('click', (e) => {
                // Only select panel if clicking on the panel itself or empty space, not on child elements
                if (e.target === e.currentTarget || 
                    (e.target.classList.contains('mock-panel-content') && !e.target.querySelector(':hover'))) {
                    selectElement('panel', e.currentTarget);
                }
            });
        }
        
        // Setup WordPress save button
        if (saveBtn) {
            $('#saveCustomizationBtn').on('click', function() {
                saveSettings();
            });
        } else {
            console.warn('TalkToPC Widget Customization: Save button not found!');
        }
        
        console.log('TalkToPC Widget Customization: Initialization complete');
    });
    
    /**
     * Save settings to WordPress via AJAX
     */
    function saveSettings() {
        const $saveBtn = $('#saveCustomizationBtn');
        const $saveStatus = $('#saveStatus');
        
        $saveBtn.prop('disabled', true).text('Saving...');
        $saveStatus.hide();
        
        // Build settings object from widgetConfig
        const settings = {};
        
        // Button settings
        settings.talktopc_button_size = widgetConfig.button.size;
        settings.talktopc_button_shape = widgetConfig.button.shape;
        settings.talktopc_button_bg_color = widgetConfig.button.backgroundColor;
        settings.talktopc_button_hover_color = widgetConfig.button.hoverColor;
        settings.talktopc_button_shadow = widgetConfig.button.shadow ? '1' : '0';
        settings.talktopc_button_shadow_color = widgetConfig.button.shadowColor;
        
        // Icon settings
        settings.talktopc_icon_type = widgetConfig.icon.type;
        settings.talktopc_icon_custom_image = widgetConfig.icon.customImage || '';
        settings.talktopc_icon_emoji = widgetConfig.icon.emoji || '';
        settings.talktopc_icon_text = widgetConfig.icon.text || '';
        
        // Panel settings
        settings.talktopc_panel_width = widgetConfig.panel.width;
        settings.talktopc_panel_height = widgetConfig.panel.height;
        settings.talktopc_panel_border_radius = widgetConfig.panel.borderRadius;
        settings.talktopc_panel_bg_color = widgetConfig.panel.backgroundColor;
        settings.talktopc_panel_border = widgetConfig.panel.border;
        
        // Position settings
        settings.talktopc_position = widgetConfig.position.vertical + '-' + widgetConfig.position.horizontal;
        settings.talktopc_position_offset_x = widgetConfig.position.offset.x;
        settings.talktopc_position_offset_y = widgetConfig.position.offset.y;
        
        // Direction
        settings.talktopc_direction = widgetConfig.direction;
        
        // Header settings
        settings.talktopc_header_title = widgetConfig.header.title;
        settings.talktopc_header_bg_color = widgetConfig.header.backgroundColor;
        settings.talktopc_header_text_color = widgetConfig.header.textColor;
        settings.talktopc_header_show_close = widgetConfig.header.showCloseButton ? '1' : '0';
        
        // Messages settings
        settings.talktopc_msg_user_bg = widgetConfig.messages.userBackgroundColor;
        settings.talktopc_msg_agent_bg = widgetConfig.messages.agentBackgroundColor;
        settings.talktopc_msg_text_color = widgetConfig.messages.textColor;
        settings.talktopc_msg_font_size = widgetConfig.messages.fontSize;
        settings.talktopc_msg_border_radius = widgetConfig.messages.borderRadius;
        
        // Text settings
        settings.talktopc_text_send_btn_text = widgetConfig.text.sendButtonText;
        settings.talktopc_text_send_btn_color = widgetConfig.text.sendButtonColor;
        settings.talktopc_text_send_btn_hover_color = widgetConfig.text.sendButtonHoverColor;
        settings.talktopc_text_input_placeholder = widgetConfig.text.inputPlaceholder;
        settings.talktopc_text_input_focus_color = widgetConfig.text.inputFocusColor;
        
        // Voice settings
        settings.talktopc_voice_mic_color = widgetConfig.voice.micButtonColor;
        settings.talktopc_voice_mic_active_color = widgetConfig.voice.micButtonActiveColor;
        settings.talktopc_voice_avatar_color = widgetConfig.voice.avatarBackgroundColor;
        settings.talktopc_voice_start_btn_text = widgetConfig.voice.startCallButtonText;
        settings.talktopc_voice_start_btn_color = widgetConfig.voice.startCallButtonColor;
        settings.talktopc_voice_start_btn_text_color = widgetConfig.voice.startCallButtonTextColor;
        settings.talktopc_voice_status_title_color = widgetConfig.voice.statusTitleColor;
        settings.talktopc_voice_status_subtitle_color = widgetConfig.voice.statusSubtitleColor;
        settings.talktopc_voice_live_dot_color = widgetConfig.voice.liveDotColor;
        settings.talktopc_voice_live_text_color = widgetConfig.voice.liveTextColor;
        
        // Landing settings
        settings.talktopc_landing_logo = widgetConfig.landing.logo;
        settings.talktopc_landing_title = widgetConfig.landing.title;
        settings.talktopc_landing_title_color = widgetConfig.landing.titleColor;
        settings.talktopc_landing_subtitle_color = widgetConfig.landing.subtitleColor;
        settings.talktopc_landing_voice_title = widgetConfig.landing.voiceCardTitle;
        settings.talktopc_landing_text_title = widgetConfig.landing.textCardTitle;
        // CRITICAL: Always save modeCardBackgroundColor explicitly, even if it's the default
        // This prevents SDK from falling back to header.backgroundColor
        settings.talktopc_landing_card_bg_color = widgetConfig.landing.modeCardBackgroundColor || '#FFFFFF';
        
        // Add nonce
        settings.nonce = talktopcWidgetSettings2.nonce;
        settings.action = talktopcWidgetSettings2.saveAction;
        
        $.ajax({
            url: talktopcWidgetSettings2.ajaxUrl,
            type: 'POST',
            data: settings,
            success: function(response) {
                $saveBtn.prop('disabled', false).text('Save Changes');
                if (response.success) {
                    $saveStatus.removeClass('error').addClass('success').text('Settings saved successfully!').show();
                    setTimeout(function() {
                        $saveStatus.fadeOut();
                    }, 3000);
                } else {
                    $saveStatus.removeClass('success').addClass('error').text('Error saving settings: ' + (response.data || 'Unknown error')).show();
                }
            },
            error: function(xhr, status, error) {
                $saveBtn.prop('disabled', false).text('Save Changes');
                $saveStatus.removeClass('success').addClass('error').text('Error saving settings: ' + error).show();
            }
        });
    }
    
})(jQuery);
